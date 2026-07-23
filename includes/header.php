<?php
$user=current_user($pdo);
$pageTitle=$pageTitle??'TaskFlow Pro';
$activePage=$activePage??'';
$flashes=get_flashes();
$stmt=$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([(int)$user['id']]);
$unread=(int)$stmt->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head><base href="<?=e(base_url().'/')?>">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="TaskFlow Pro Enterprise task management system">
<title><?=e($pageTitle)?> | TaskFlow Pro</title>
<link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#07101d"><link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="app-shell">
<aside class="sidebar" id="sidebar">
<a class="brand" href="dashboard.php"><span class="brand-mark">TF</span><span>TaskFlow <b>Pro</b></span></a>
<nav class="nav">
<a class="<?=$activePage==='dashboard'?'active':''?>" href="dashboard.php">◫ Dashboard</a>
<a class="<?=$activePage==='search'?'active':''?>" href="search.php">⌕ Global Search</a>
<a class="<?=$activePage==='tasks'?'active':''?>" href="tasks/index.php">✓ Tasks</a>
<a class="<?=$activePage==='kanban'?'active':''?>" href="tasks/kanban.php">▤ Kanban Board</a>
<a class="<?=$activePage==='calendar'?'active':''?>" href="tasks/calendar.php">▦ Calendar</a>
<a class="<?=$activePage==='reports'?'active':''?>" href="reports/index.php">↧ Reports</a>
<a class="<?=$activePage==='api-docs'?'active':''?>" href="docs/api.php">⌘ API Docs</a>
<a class="<?=$activePage==='new-task'?'active':''?>" href="tasks/create.php">＋ New Task</a>
<a class="<?=$activePage==='notifications'?'active':''?>" href="notifications/index.php">◉ Notifications <?php if($unread):?><span class="nav-badge"><?=$unread?></span><?php endif;?></a>
<a class="<?=$activePage==='profile'?'active':''?>" href="profile/index.php">⚙ Profile</a>
<?php if(in_array($user['role'],['admin','manager'],true)):?>
<a class="<?=$activePage==='teams'?'active':''?>" href="teams/index.php">▦ Teams</a>
<a class="<?=$activePage==='admin'?'active':''?>" href="admin/index.php">⚙ Admin Console</a>
<?php endif;?>
</nav>
<div class="sidebar-footer">
<div class="user-mini"><span class="avatar" style="--avatar:<?=e($user['avatar_color'])?>"><?=e(strtoupper(substr($user['name'],0,1)))?></span><div><strong><?=e($user['name'])?></strong><small><?=e(ucfirst($user['role']))?></small></div></div>
<a class="logout-link" href="auth/logout.php">Sign out</a>
</div>
</aside>
<div class="main-area">
<header class="topbar">
<button id="menuToggle" class="icon-btn">☰</button>
<div><p class="kicker">Professional workspace</p><h1><?=e($pageTitle)?></h1></div>
<a class="icon-btn" href="notifications/index.php">◉<?php if($unread):?><span class="bubble"><?=$unread?></span><?php endif;?></a>
<button id="langToggle" class="icon-btn" title="Language">AR</button><button id="themeToggle" class="icon-btn">◐</button>
</header>
<main class="content">
<?php foreach($flashes as $flash):?><div class="alert alert-<?=e($flash['type'])?>"><?=e($flash['message'])?></div><?php endforeach;?>
