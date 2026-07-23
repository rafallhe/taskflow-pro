<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('/tasks/index.php');
verify_csrf();
$u=current_user($pdo);$uid=(int)$u['id'];$taskId=(int)($_POST['task_id']??0);
$stmt=$pdo->prepare("SELECT id,title FROM tasks WHERE id=? AND user_id=?");$stmt->execute([$taskId,$uid]);$task=$stmt->fetch();
if(!$task){flash('error','Task not found.');redirect('/tasks/index.php');}
if(!isset($_FILES['attachment'])||$_FILES['attachment']['error']!==UPLOAD_ERR_OK){flash('error','Choose a valid file.');redirect('/tasks/view.php?id='.$taskId);}
$file=$_FILES['attachment'];
if($file['size']>5*1024*1024){flash('error','Maximum file size is 5 MB.');redirect('/tasks/view.php?id='.$taskId);}
$allowed=['image/jpeg','image/png','image/webp','application/pdf','text/plain','application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$finfo=new finfo(FILEINFO_MIME_TYPE);$mime=$finfo->file($file['tmp_name']);
if(!in_array($mime,$allowed,true)){flash('error','This file type is not allowed.');redirect('/tasks/view.php?id='.$taskId);}
$stored=safe_upload_name($file['name']);$target=__DIR__.'/../storage/uploads/'.$stored;
if(!move_uploaded_file($file['tmp_name'],$target)){flash('error','Upload failed.');redirect('/tasks/view.php?id='.$taskId);}
$stmt=$pdo->prepare("INSERT INTO task_attachments(task_id,user_id,original_name,stored_name,mime_type,file_size) VALUES(?,?,?,?,?,?)");
$stmt->execute([$taskId,$uid,mb_substr(basename($file['name']),0,255),$stored,$mime,(int)$file['size']]);
activity($pdo,$uid,'Attachment uploaded',$task['title']);
flash('success','Attachment uploaded.');redirect('/tasks/view.php?id='.$taskId);
