<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];
$from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-t');
if(!validate_date($from))$from=date('Y-m-01');if(!validate_date($to))$to=date('Y-m-t');

$stmt=$pdo->prepare("SELECT status,COUNT(*) total FROM tasks WHERE (user_id=? OR assigned_to=?) AND DATE(created_at) BETWEEN ? AND ? GROUP BY status");
$stmt->execute([$uid,$uid,$from,$to]);$statusRows=$stmt->fetchAll();
$summary=['todo'=>0,'in_progress'=>0,'completed'=>0];
foreach($statusRows as $r)$summary[$r['status']]=(int)$r['total'];

$stmt=$pdo->prepare("SELECT category,COUNT(*) total,SUM(status='completed') completed FROM tasks WHERE (user_id=? OR assigned_to=?) AND DATE(created_at) BETWEEN ? AND ? GROUP BY category ORDER BY total DESC");
$stmt->execute([$uid,$uid,$from,$to]);$categories=$stmt->fetchAll();

$stmt=$pdo->prepare("SELECT COUNT(*) total,SUM(status='completed') completed,AVG(progress) avg_progress FROM tasks WHERE (user_id=? OR assigned_to=?) AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$uid,$uid,$from,$to]);$totals=$stmt->fetch();
$rate=(int)$totals['total']?round(((int)$totals['completed']/(int)$totals['total'])*100):0;

$pageTitle='Reports';$activePage='reports';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">PRODUCTIVITY REPORTS</p><h2>Measure what gets done.</h2><p>Filter results by period and export task data.</p></div><div class="report-actions"><a class="btn btn-secondary" target="_blank" href="reports/pdf.php?from=<?=e($from)?>&to=<?=e($to)?>">PDF / Print</a><a class="btn btn-primary" href="reports/export.php?from=<?=e($from)?>&to=<?=e($to)?>">↧ Export CSV</a></div></section>
<form class="panel report-filter" method="get"><label>From<input type="date" name="from" value="<?=e($from)?>"></label><label>To<input type="date" name="to" value="<?=e($to)?>"></label><button class="btn btn-secondary">Generate Report</button></form>
<section class="stats-grid report-stats">
 <article class="stat-card"><span class="stat-dot blue"></span><div><small>Tasks Created</small><strong><?=(int)$totals['total']?></strong></div></article>
 <article class="stat-card"><span class="stat-dot green"></span><div><small>Completed</small><strong><?=(int)$totals['completed']?></strong></div></article>
 <article class="stat-card"><span class="stat-dot cyan"></span><div><small>Completion Rate</small><strong><?=$rate?>%</strong></div></article>
 <article class="stat-card"><span class="stat-dot red"></span><div><small>Average Progress</small><strong><?=round((float)$totals['avg_progress'])?>%</strong></div></article>
</section>
<section class="dashboard-grid">
 <article class="panel"><div class="panel-head"><h3>Status Distribution</h3></div>
  <div class="report-bars">
   <?php foreach([['To Do',$summary['todo'],'gray'],['In Progress',$summary['in_progress'],'cyan'],['Completed',$summary['completed'],'green']] as $x):?>
   <div><div class="report-label"><span><?=$x[0]?></span><strong><?=$x[1]?></strong></div><div class="progress-track large"><span class="<?=$x[2]?>" style="width:<?=max(2,$totals['total']?($x[1]/$totals['total']*100):0)?>%"></span></div></div>
   <?php endforeach;?>
  </div>
 </article>
 <article class="panel"><div class="panel-head"><h3>Categories</h3></div>
  <div class="category-report"><?php foreach($categories as $c):?><div><span><?=e($c['category'])?></span><strong><?=(int)$c['completed']?> / <?=(int)$c['total']?> completed</strong></div><?php endforeach;?><?php if(!$categories):?><p class="muted">No data in this period.</p><?php endif;?></div>
 </article>
</section>
<?php require __DIR__.'/../includes/footer.php';?>
