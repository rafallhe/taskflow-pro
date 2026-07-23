<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();$u=current_user($pdo);$uid=(int)$u['id'];
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);flash('success','All notifications marked as read.');redirect('/notifications/index.php');}
$overdueStmt=$pdo->prepare("SELECT id,title FROM tasks WHERE user_id=? AND status!='completed' AND due_date IS NOT NULL AND due_date<CURDATE()");
$overdueStmt->execute([$uid]);
foreach($overdueStmt->fetchAll() as $ot){
 $title='Overdue task';
 $message=$ot['title'].' is past its due date.';
 $exists=$pdo->prepare("SELECT id FROM notifications WHERE user_id=? AND title=? AND message=? LIMIT 1");
 $exists->execute([$uid,$title,$message]);
 if(!$exists->fetch())notify($pdo,$uid,$title,$message);
}
$stmt=$pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 50");$stmt->execute([$uid]);$items=$stmt->fetchAll();
$pageTitle='Notifications';$activePage='notifications';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">INBOX</p><h2>Stay informed.</h2><p>Important updates from your workspace.</p></div><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button class="btn btn-secondary">Mark all as read</button></form></section>
<section class="panel notification-list"><?php foreach($items as $n):?><article class="<?=$n['is_read']?'':'unread'?>"><div><strong><?=e($n['title'])?></strong><p><?=e($n['message'])?></p></div><small><?=e(date('M d, Y H:i',strtotime($n['created_at'])))?></small></article><?php endforeach;?><?php if(!$items):?><div class="empty">No notifications yet.</div><?php endif;?></section>
<?php require __DIR__.'/../includes/footer.php';?>
