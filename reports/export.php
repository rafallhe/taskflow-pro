<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];
$from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-t');
if(!validate_date($from)||!validate_date($to)){http_response_code(422);exit('Invalid date range.');}
$stmt=$pdo->prepare("SELECT title,description,status,priority,category,tags,due_date,progress,created_at,updated_at FROM tasks WHERE (user_id=? OR assigned_to=?) AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC");
$stmt->execute([$uid,$uid,$from,$to]);
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="taskflow-report-'.$from.'-to-'.$to.'.csv"');
echo "\xEF\xBB\xBF";
$out=fopen('php://output','w');
fputcsv($out,['Title','Description','Status','Priority','Category','Tags','Due Date','Progress','Created','Updated']);
while($row=$stmt->fetch())fputcsv($out,$row);
fclose($out);
