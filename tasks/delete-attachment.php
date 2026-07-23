<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('/tasks/index.php');
verify_csrf();$u=current_user($pdo);$id=(int)($_POST['id']??0);$taskId=(int)($_POST['task_id']??0);
$stmt=$pdo->prepare("SELECT a.stored_name FROM task_attachments a JOIN tasks t ON t.id=a.task_id WHERE a.id=? AND a.task_id=? AND t.user_id=?");
$stmt->execute([$id,$taskId,$u['id']]);$a=$stmt->fetch();
if($a){$path=__DIR__.'/../storage/uploads/'.$a['stored_name'];if(is_file($path))unlink($path);$pdo->prepare("DELETE FROM task_attachments WHERE id=?")->execute([$id]);flash('success','Attachment deleted.');}
redirect('/tasks/view.php?id='.$taskId);
