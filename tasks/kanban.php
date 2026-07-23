<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);
$uid=(int)$u['id'];
$stmt=$pdo->prepare("SELECT id,title,description,status,priority,due_date,category,progress FROM tasks WHERE (user_id=? OR assigned_to=?) ORDER BY FIELD(priority,'high','medium','low'), due_date IS NULL, due_date");
$stmt->execute([$uid,$uid]);
$tasks=$stmt->fetchAll();
$columns=['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed'];
$pageTitle='Kanban Board';
$activePage='kanban';
require __DIR__.'/../includes/header.php';
?>
<section class="hero-row">
 <div><p class="eyebrow">VISUAL WORKFLOW</p><h2>Move work forward.</h2><p>Drag tasks between columns and update status instantly.</p></div>
 <a class="btn btn-primary" href="tasks/create.php">＋ New Task</a>
</section>

<div class="kanban-board" data-csrf="<?=e(csrf_token())?>">
<?php foreach($columns as $status=>$label):?>
<section class="kanban-column" data-status="<?=e($status)?>">
 <div class="kanban-head"><h3><?=e($label)?></h3><span><?=count(array_filter($tasks,fn($t)=>$t['status']===$status))?></span></div>
 <div class="kanban-dropzone">
 <?php foreach($tasks as $t): if($t['status']!==$status) continue; ?>
  <article class="kanban-card" draggable="true" data-id="<?=$t['id']?>">
   <div class="task-card-top">
    <span class="badge priority-<?=e($t['priority'])?>"><?=e(ucfirst($t['priority']))?></span>
    <span class="kanban-grip">⋮⋮</span>
   </div>
   <a href="tasks/view.php?id=<?=$t['id']?>"><h4><?=e($t['title'])?></h4></a>
   <p><?=e(mb_strimwidth($t['description']?:'No description',0,90,'…'))?></p>
   <div class="progress-track"><span style="width:<?=(int)$t['progress']?>%"></span></div>
   <div class="task-meta"><span><?=e($t['category']?:'General')?></span><span><?=e($t['due_date']?:'No due date')?></span></div>
  </article>
 <?php endforeach; ?>
 </div>
</section>
<?php endforeach;?>
</div>
<script src="assets/js/kanban.js"></script>
<?php require __DIR__.'/../includes/footer.php';?>
