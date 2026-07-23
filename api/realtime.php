<?php
require_once __DIR__.'/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
if(!is_logged_in()){http_response_code(401);echo json_encode(['ok'=>false]);exit;}
$u=current_user($pdo);$uid=(int)$u['id'];
$stmt=$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");$stmt->execute([$uid]);$unread=(int)$stmt->fetchColumn();
$stmt=$pdo->prepare("SELECT id,title,message,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 5");$stmt->execute([$uid]);$latest=$stmt->fetchAll();
echo json_encode(['ok'=>true,'unread'=>$unread,'latest'=>$latest],JSON_UNESCAPED_UNICODE);
