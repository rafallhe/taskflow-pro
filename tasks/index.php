<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();$u=current_user($pdo);$uid=(int)$u['id'];
$q=trim($_GET['q']??'');$status=$_GET['status']??'';$priority=$_GET['priority']??'';
$where=['user_id=?'];$p=[$uid];
if($q!==''){$where[]='(title LIKE ? OR description LIKE ?)';$p[]="%$q%";$p[]="%$q%";}
if(in_array($status,['todo','in_progress','completed'],true)){$where[]='status=?';$p[]=$status;}
if(in_array($priority,['low','medium','high'],true)){$where[]='priority=?';$p[]=$priority;}
$stmt=$pdo->prepare("SELECT * FROM tasks WHERE ".implode(' AND ',$where)." ORDER BY FIELD(status,'in_progress','todo','completed'),FIELD(priority,'high','medium','low'),due_date IS NULL,due_date");
$stmt->execute($p);$tasks=$stmt->fetchAll();
$pageTitle='Tasks';$activePage='tasks';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">TASK MANAGEMENT</p><h2>Manage work with precision.</h2><p>Search, filter and update every task from one view.</p></div><a class="btn btn-primary" href="tasks/create.php">＋ New Task</a></section>
<form class="panel filters"><label>Search<input name="q" value="<?=e($q)?>" placeholder="Title or description"></label><label>Status<select name="status"><option value="">All</option><option value="todo" <?=$status==='todo'?'selected':''?>>To Do</option><option value="in_progress" <?=$status==='in_progress'?'selected':''?>>In Progress</option><option value="completed" <?=$status==='completed'?'selected':''?>>Completed</option></select></label><label>Priority<select name="priority"><option value="">All</option><option value="high" <?=$priority==='high'?'selected':''?>>High</option><option value="medium" <?=$priority==='medium'?'selected':''?>>Medium</option><option value="low" <?=$priority==='low'?'selected':''?>>Low</option></select></label><button class="btn btn-secondary">Apply</button><a class="btn btn-ghost" href="tasks/index.php">Reset</a></form>
<div class="task-grid"><?php foreach($tasks as $t):$over=$t['status']!=='completed'&&$t['due_date']&&$t['due_date']<date('Y-m-d');?>
<article class="task-card <?=$over?'overdue':''?>"><div class="task-card-top"><span class="badge priority-<?=e($t['priority'])?>"><?=e(ucfirst($t['priority']))?></span><span class="badge status-<?=e($t['status'])?>"><?=e(status_label($t['status']))?></span></div><a href="tasks/view.php?id=<?=$t['id']?>"><h3><?=e($t['title'])?></h3></a><p><?=e($t['description']?:'No description provided.')?></p><div class="tag-list"><?php foreach(task_tags($t['tags']??'') as $tag):?><span><?=e($tag)?></span><?php endforeach;?></div><div class="task-meta"><span><?=$t['due_date']?'Due '.e($t['due_date']):'No deadline'?></span><span><?=e($t['category']?:'General')?></span></div><div class="card-actions"><a href="tasks/edit.php?id=<?=$t['id']?>">Edit</a><form method="post" action="tasks/delete.php" onsubmit="return confirm('Delete this task?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$t['id']?>"><button>Delete</button></form></div></article>
<?php endforeach;?></div>
<?php if(!$tasks):?><div class="panel empty">No matching tasks.</div><?php endif;?>
<?php require __DIR__.'/../includes/footer.php';?>
