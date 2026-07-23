<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];
$month=$_GET['month']??date('Y-m');
if(!preg_match('/^\d{4}-\d{2}$/',$month))$month=date('Y-m');
$first=new DateTime($month.'-01');
$start=(clone $first)->modify('monday this week');
$end=(clone $first)->modify('last day of this month')->modify('sunday this week');

$stmt=$pdo->prepare("SELECT id,title,status,priority,due_date,category FROM tasks WHERE (user_id=? OR assigned_to=?) AND due_date BETWEEN ? AND ? ORDER BY due_date,FIELD(priority,'high','medium','low')");
$stmt->execute([$uid,$uid,$start->format('Y-m-d'),$end->format('Y-m-d')]);
$byDate=[];
foreach($stmt->fetchAll() as $t)$byDate[$t['due_date']][]=$t;

$prev=(clone $first)->modify('-1 month')->format('Y-m');
$next=(clone $first)->modify('+1 month')->format('Y-m');
$pageTitle='Task Calendar';$activePage='calendar';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row">
 <div><p class="eyebrow">DEADLINE PLANNING</p><h2><?=e($first->format('F Y'))?></h2><p>See every deadline in a clear monthly view.</p></div>
 <div class="calendar-actions"><a class="btn btn-secondary" href="tasks/calendar.php?month=<?=$prev?>">← Previous</a><a class="btn btn-ghost" href="tasks/calendar.php">Today</a><a class="btn btn-secondary" href="tasks/calendar.php?month=<?=$next?>">Next →</a></div>
</section>
<section class="calendar-grid">
<?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day):?><div class="calendar-weekday"><?=e($day)?></div><?php endforeach;?>
<?php
$cursor=clone $start;
while($cursor<=$end):
 $key=$cursor->format('Y-m-d');$outside=$cursor->format('m')!==$first->format('m');$today=$key===date('Y-m-d');
?>
<article class="calendar-day <?=$outside?'outside':''?> <?=$today?'today':''?>">
 <div class="calendar-date"><span><?=$cursor->format('j')?></span><?php if($today):?><small>Today</small><?php endif;?></div>
 <div class="calendar-items">
 <?php foreach($byDate[$key]??[] as $task):?>
  <a class="calendar-task priority-border-<?=e($task['priority'])?>" href="tasks/view.php?id=<?=$task['id']?>">
   <strong><?=e($task['title'])?></strong><small><?=e($task['category'])?> · <?=e(status_label($task['status']))?></small>
  </a>
 <?php endforeach;?>
 </div>
</article>
<?php $cursor->modify('+1 day'); endwhile;?>
</section>
<?php require __DIR__.'/../includes/footer.php';?>
