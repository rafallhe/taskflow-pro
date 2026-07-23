<?php
require_once __DIR__.'/includes/bootstrap.php';require_auth();
$user=current_user($pdo);$uid=(int)$user['id'];$counts=task_counts($pdo,$uid);
$completion=$counts['total']?round($counts['completed']/$counts['total']*100):0;
$stmt=$pdo->prepare("SELECT * FROM tasks WHERE (user_id=? OR assigned_to=?) ORDER BY FIELD(priority,'high','medium','low'),due_date IS NULL,due_date LIMIT 6");$stmt->execute([$uid,$uid]);$tasks=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT action,details,created_at FROM activity_logs WHERE user_id=? ORDER BY id DESC LIMIT 6");$stmt->execute([$uid]);$logs=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT DATE_FORMAT(created_at,'%b') m,COUNT(*) c FROM tasks WHERE (user_id=? OR assigned_to=?) AND created_at>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH) GROUP BY YEAR(created_at),MONTH(created_at) ORDER BY MIN(created_at)");$stmt->execute([$uid,$uid]);$trend=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT priority,COUNT(*) c FROM tasks WHERE (user_id=? OR assigned_to=?) GROUP BY priority");
$stmt->execute([$uid,$uid]);$priorityRows=$stmt->fetchAll();
$priorityData=['low'=>0,'medium'=>0,'high'=>0];
foreach($priorityRows as $r)$priorityData[$r['priority']]=(int)$r['c'];

$stmt=$pdo->prepare("SELECT DATE_FORMAT(created_at,'%Y-%m-%d') d,COUNT(*) c FROM tasks WHERE (user_id=? OR assigned_to=?) AND created_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY d");
$stmt->execute([$uid,$uid]);$weekRows=$stmt->fetchAll();
$weekMap=[];
foreach($weekRows as $r)$weekMap[$r['d']]=(int)$r['c'];
$weekLabels=[];$weekData=[];
for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-{$i} days"));$weekLabels[]=date('D',strtotime($d));$weekData[]=$weekMap[$d]??0;}

$pageTitle='Dashboard';$activePage='dashboard';require __DIR__.'/includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">WORKSPACE OVERVIEW</p><h2>Welcome back, <?=e(explode(' ',$user['name'])[0])?>.</h2><p>Focus on what matters and keep your workload under control.</p></div><a class="btn btn-primary" href="tasks/create.php">＋ Add New Task</a></section>
<section class="stats-grid">
<?php foreach([['Total Tasks',$counts['total'],'blue'],['In Progress',$counts['in_progress'],'cyan'],['Completed',$counts['completed'],'green'],['Overdue',$counts['overdue'],'red']] as $s):?>
<article class="stat-card"><span class="stat-dot <?=$s[2]?>"></span><div><small><?=$s[0]?></small><strong><?=$s[1]?></strong></div></article>
<?php endforeach;?>
</section>
<section class="dashboard-grid">
<article class="panel"><div class="panel-head"><div><p class="eyebrow">PERFORMANCE</p><h3>Completion Rate</h3></div><strong><?=$completion?>%</strong></div><div class="donut-wrap"><div class="donut" style="--p:<?=$completion*3.6?>deg"><span><?=$completion?>%</span></div><div class="legend"><div><i class="green"></i>Completed <b><?=$counts['completed']?></b></div><div><i class="cyan"></i>In Progress <b><?=$counts['in_progress']?></b></div><div><i class="gray"></i>To Do <b><?=$counts['todo']?></b></div></div></div></article>
<article class="panel"><div class="panel-head"><div><p class="eyebrow">WEEKLY ACTIVITY</p><h3>Tasks Created</h3></div></div><div class="chart-wrap"><canvas id="weeklyChart" height="210"></canvas></div></article>
</section>
<section class="dashboard-grid">
<article class="panel"><div class="panel-head"><h3>Priority Tasks</h3><a class="text-link" href="tasks/index.php">View all →</a></div>
<?php if(!$tasks):?><div class="empty">No tasks yet.</div><?php else:?><div class="task-list"><?php foreach($tasks as $t):?><a href="tasks/edit.php?id=<?=$t['id']?>"><div><strong><?=e($t['title'])?></strong><small><?=e($t['due_date']?:'No deadline')?></small></div><span class="badge priority-<?=e($t['priority'])?>"><?=e(ucfirst($t['priority']))?></span></a><?php endforeach;?></div><?php endif;?>
</article>
<article class="panel"><div class="panel-head"><h3>Recent Activity</h3></div><div class="activity-list"><?php foreach($logs as $l):?><div><span>•</span><p><strong><?=e($l['action'])?></strong><small><?=e($l['details'])?> · <?=e(date('M d, H:i',strtotime($l['created_at'])))?></small></p></div><?php endforeach;?></div></article>

</section>
<section class="dashboard-grid">
<article class="panel"><div class="panel-head"><div><p class="eyebrow">PRIORITY MIX</p><h3>Workload by Priority</h3></div></div><div class="chart-wrap chart-small"><canvas id="priorityChart" height="220"></canvas></div></article>
<article class="panel"><div class="panel-head"><div><p class="eyebrow">MONTHLY TREND</p><h3>Tasks Created</h3></div></div><div class="chart-wrap"><canvas id="monthlyChart" height="220"></canvas></div></article>
</section>
<script>
window.taskflowCharts = {
 weekly: {labels: <?=json_encode($weekLabels)?>, data: <?=json_encode($weekData)?>},
 monthly: {labels: <?=json_encode(array_column($trend,'m'))?>, data: <?=json_encode(array_map('intval',array_column($trend,'c')))?>},
 priority: {labels: ['Low','Medium','High'], data: [<?=$priorityData['low']?>,<?=$priorityData['medium']?>,<?=$priorityData['high']?>]}
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/charts.js"></script>
<?php require __DIR__.'/includes/footer.php';?>
