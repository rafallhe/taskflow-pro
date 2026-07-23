<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function base_url(): string {
    static $base = null;
    if ($base !== null) return $base;

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $projectRoot = '';

    foreach (['/auth/', '/tasks/', '/admin/', '/profile/', '/notifications/', '/api/', '/reports/', '/docs/', '/teams/'] as $segment) {
        $pos = strpos($scriptName, $segment);
        if ($pos !== false) {
            $projectRoot = substr($scriptName, 0, $pos);
            break;
        }
    }

    if ($projectRoot === '') {
        $projectRoot = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    }

    $base = $projectRoot === '/' ? '' : rtrim($projectRoot, '/');
    return $base;
}

function url(string $path = ''): string {
    return base_url() . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    $location = preg_match('~^https?://~i', $path) ? $path : url($path);
    header("Location: {$location}");
    exit;
}

function set_security_headers(): void {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_auth(): void {
    if (!is_logged_in()) {
        flash('error', 'Please sign in first.');
        redirect('/auth/login.php');
    }
}

function current_user(PDO $pdo): ?array {
    if (!is_logged_in()) return null;
    static $cached = null;
    if ($cached === null) {
        $stmt = $pdo->prepare("SELECT id,name,email,role,avatar_color,bio,api_token,created_at FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $cached = $stmt->fetch() ?: null;
    }
    return $cached;
}

function require_role(PDO $pdo, array $roles): void {
    $user = current_user($pdo);
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid or expired security token.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = compact('type', 'message');
}

function get_flashes(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function validate_date(?string $date): bool {
    if (!$date) return true;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function activity(PDO $pdo, int $userId, string $action, string $details=''): void {
    $stmt = $pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)");
    $stmt->execute([$userId,$action,$details]);
}

function notify(PDO $pdo, int $userId, string $title, string $message): void {
    $stmt = $pdo->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)");
    $stmt->execute([$userId,$title,$message]);
}

function task_counts(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed,
        COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) AS in_progress,
        COALESCE(SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END), 0) AS todo,
        COALESCE(SUM(CASE WHEN status <> 'completed' AND due_date IS NOT NULL AND due_date < CURDATE() THEN 1 ELSE 0 END), 0) AS overdue,
        COALESCE(SUM(CASE WHEN priority = 'high' AND status <> 'completed' THEN 1 ELSE 0 END), 0) AS high_count
        FROM tasks
        WHERE user_id = ?");
    $stmt->execute([$userId]);
    $r=$stmt->fetch() ?: [];
    return array_map('intval',[
        'total'=>$r['total']??0,'completed'=>$r['completed']??0,'in_progress'=>$r['in_progress']??0,
        'todo'=>$r['todo']??0,'overdue'=>$r['overdue']??0,'high_priority'=>$r['high_count']??0
    ]);
}

function status_label(string $s): string {
    return ['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed'][$s] ?? 'Unknown';
}


function task_tags(?string $tags): array {
    if (!$tags) return [];
    $items = array_filter(array_map('trim', explode(',', $tags)));
    return array_values(array_unique(array_slice($items, 0, 10)));
}

function safe_upload_name(string $original): string {
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(20)) . ($ext ? '.' . $ext : '');
}

function format_bytes(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}


function slugify(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    return trim($value, '-') ?: 'team-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

function user_teams(PDO $pdo, int $userId): array {
    $stmt=$pdo->prepare("SELECT t.*,tm.role membership_role
        FROM teams t JOIN team_members tm ON tm.team_id=t.id
        WHERE tm.user_id=? ORDER BY t.name");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function current_team_id(PDO $pdo, int $userId): ?int {
    $requested = isset($_GET['team']) ? (int)$_GET['team'] : (int)($_SESSION['team_id'] ?? 0);
    if ($requested) {
        $stmt=$pdo->prepare("SELECT team_id FROM team_members WHERE team_id=? AND user_id=?");
        $stmt->execute([$requested,$userId]);
        if ($stmt->fetchColumn()) {
            $_SESSION['team_id']=$requested;
            return $requested;
        }
    }
    $stmt=$pdo->prepare("SELECT team_id FROM team_members WHERE user_id=? ORDER BY id LIMIT 1");
    $stmt->execute([$userId]);
    $id=$stmt->fetchColumn();
    if ($id) $_SESSION['team_id']=(int)$id;
    return $id ? (int)$id : null;
}

function require_team_role(PDO $pdo, int $teamId, int $userId, array $roles): void {
    $stmt=$pdo->prepare("SELECT role FROM team_members WHERE team_id=? AND user_id=?");
    $stmt->execute([$teamId,$userId]);
    $role=$stmt->fetchColumn();
    if(!$role || !in_array($role,$roles,true)){
        http_response_code(403);
        exit('Team access denied.');
    }
}

function queue_email(PDO $pdo,string $recipient,string $subject,string $body):void{$pdo->prepare("INSERT INTO email_queue(recipient,subject,body) VALUES(?,?,?)")->execute([$recipient,$subject,$body]);}
function update_task_progress_from_checklist(PDO $pdo,int $taskId):void{$s=$pdo->prepare("SELECT COUNT(*) total,SUM(is_done=1) done FROM task_checklists WHERE task_id=?");$s->execute([$taskId]);$r=$s->fetch()?:['total'=>0,'done'=>0];$total=(int)$r['total'];if($total>0){$progress=(int)round(((int)$r['done']/$total)*100);$status=$progress===100?'completed':($progress>0?'in_progress':'todo');$pdo->prepare("UPDATE tasks SET progress=?,status=? WHERE id=?")->execute([$progress,$status,$taskId]);}}
