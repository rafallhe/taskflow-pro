<?php
require_once __DIR__.'/includes/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
$requiredTables=['users','tasks','teams','team_members','task_comments','task_checklists','task_attachments','notifications','activity_logs','login_logs'];
$tables=[];
foreach($requiredTables as $table){
 try{$pdo->query("SELECT 1 FROM `$table` LIMIT 1");$tables[$table]=true;}catch(Throwable $e){$tables[$table]=false;}
}
$checks=[
 'PHP 8.0+'=>version_compare(PHP_VERSION,'8.0.0','>='),
 'PDO MySQL'=>extension_loaded('pdo_mysql'),
 'Database connection'=>isset($pdo),
 'CSS asset'=>file_exists(__DIR__.'/assets/css/app.css'),
 'JavaScript asset'=>file_exists(__DIR__.'/assets/js/app.js'),
 'Upload directory writable'=>is_writable(__DIR__.'/storage/uploads'),
];
?>
<!doctype html><html><head><base href="<?=e(base_url().'/')?>"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow Diagnostics</title><link rel="stylesheet" href="assets/css/app.css"></head><body><main class="content" style="max-width:920px"><section class="panel"><p class="eyebrow">PRODUCTION READINESS</p><h1>TaskFlow Diagnostics</h1><p class="muted">Base URL: <?=e(base_url()?:'/')?></p>
<?php foreach($checks as $name=>$ok):?><div class="diagnostic-row"><strong><?=e($name)?></strong><span class="badge <?=$ok?'status-completed':'priority-high'?>"><?=$ok?'PASS':'FAIL'?></span></div><?php endforeach;?>
<h3 style="margin-top:30px">Database tables</h3><?php foreach($tables as $name=>$ok):?><div class="diagnostic-row"><strong><?=e($name)?></strong><span class="badge <?=$ok?'status-completed':'priority-high'?>"><?=$ok?'PASS':'FAIL'?></span></div><?php endforeach;?>
<p style="margin-top:22px"><a class="btn btn-primary" href="dashboard.php">Open Dashboard</a></p></section></main></body></html>