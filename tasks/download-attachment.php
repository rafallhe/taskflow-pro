<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$stmt=$pdo->prepare("SELECT a.* FROM task_attachments a JOIN tasks t ON t.id=a.task_id WHERE a.id=? AND t.user_id=?");
$stmt->execute([$id,$u['id']]);$a=$stmt->fetch();if(!$a){http_response_code(404);exit('File not found.');}
$path=__DIR__.'/../storage/uploads/'.$a['stored_name'];if(!is_file($path)){http_response_code(404);exit('Stored file missing.');}
header('Content-Type: '.$a['mime_type']);header('Content-Length: '.filesize($path));header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($a['original_name']));
readfile($path);
