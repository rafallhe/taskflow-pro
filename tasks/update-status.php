<?php
require_once __DIR__.'/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
if(!is_logged_in()){http_response_code(401);echo json_encode(['ok'=>false,'message'=>'Unauthorized']);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false]);exit;}
verify_csrf();
$u=current_user($pdo);
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$status=$_POST['status']??'';
if(!$id || !in_array($status,['todo','in_progress','completed'],true)){http_response_code(422);echo json_encode(['ok'=>false,'message'=>'Invalid input']);exit;}
$progress=$status==='completed'?100:($status==='in_progress'?50:0);
$stmt=$pdo->prepare("UPDATE tasks SET status=?,progress=? WHERE id=? AND user_id=?");
$stmt->execute([$status,$progress,$id,$u['id']]);
if(!$stmt->rowCount()){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'Task not found']);exit;}
activity($pdo,(int)$u['id'],'Task moved',"Task #{$id} → ".status_label($status));
echo json_encode(['ok'=>true,'status'=>$status,'progress'=>$progress]);
