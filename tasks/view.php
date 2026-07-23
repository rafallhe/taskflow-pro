<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$stmt=$pdo->prepare("SELECT * FROM tasks WHERE id=? AND user_id=?");
$stmt->execute([$id,$uid]);$task=$stmt->fetch();
if(!$task){flash('error','Task not found.');redirect('/tasks/index.php');}

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $action=$_POST['action']??'';
 if($action==='comment'){
  $body=trim($_POST['body']??'');
  if($body!==''){
   $pdo->prepare("INSERT INTO task_comments(task_id,user_id,body) VALUES(?,?,?)")->execute([$id,$uid,$body]);
   activity($pdo,$uid,'Comment added',$task['title']);
  }
 } elseif($action==='checklist_add'){
  $item=trim($_POST['item_text']??'');
  if($item!==''){
   $pdo->prepare("INSERT INTO task_checklists(task_id,item_text,position_no) SELECT ?,?,COALESCE(MAX(position_no),0)+1 FROM task_checklists WHERE task_id=?")->execute([$id,$item,$id]);
  }
 } elseif($action==='checklist_toggle'){
  $itemId=(int)($_POST['item_id']??0);
  $pdo->prepare("UPDATE task_checklists c JOIN tasks t ON t.id=c.task_id SET c.is_done=NOT c.is_done WHERE c.id=? AND t.user_id=?")->execute([$itemId,$uid]);update_task_progress_from_checklist($pdo,$id);
 }
 redirect('/tasks/view.php?id='.$id);
}
$stmt=$pdo->prepare("SELECT c.*,u.name FROM task_comments c JOIN users u ON u.id=c.user_id WHERE c.task_id=? ORDER BY c.id DESC");$stmt->execute([$id]);$comments=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT * FROM task_checklists WHERE task_id=? ORDER BY position_no,id");$stmt->execute([$id]);$checklist=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT * FROM task_attachments WHERE task_id=? ORDER BY id DESC");$stmt->execute([$id]);$attachments=$stmt->fetchAll();
$pageTitle='Task Details';$activePage='tasks';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row">
 <div><p class="eyebrow">TASK DETAILS</p><h2><?=e($task['title'])?></h2><p><?=e($task['description']?:'No description provided.')?></p></div>
 <a class="btn btn-secondary" href="tasks/edit.php?id=<?=$task['id']?>">Edit Task</a>
</section>
<section class="task-detail-grid">
 <article class="panel">
  <div class="panel-head"><h3>Overview</h3><span class="badge status-<?=e($task['status'])?>"><?=e(status_label($task['status']))?></span></div>
  <dl class="detail-list">
   <div><dt>Priority</dt><dd><span class="badge priority-<?=e($task['priority'])?>"><?=e(ucfirst($task['priority']))?></span></dd></div>
   <div><dt>Category</dt><dd><?=e($task['category'])?></dd></div><div><dt>Tags</dt><dd><div class="tag-list"><?php foreach(task_tags($task['tags']??'') as $tag):?><span><?=e($tag)?></span><?php endforeach;?><?php if(!task_tags($task['tags']??'')):?>None<?php endif;?></div></dd></div>
   <div><dt>Due date</dt><dd><?=e($task['due_date']?:'Not set')?></dd></div>
   <div><dt>Progress</dt><dd><?=(int)$task['progress']?>%</dd></div>
  </dl>
  <div class="progress-track large"><span style="width:<?=(int)$task['progress']?>%"></span></div>
 </article>

 <article class="panel">
  <div class="panel-head"><h3>Checklist</h3><span><?=count(array_filter($checklist,fn($i)=>(bool)$i['is_done']))?>/<?=count($checklist)?></span></div>
  <form method="post" class="inline-add"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="checklist_add"><input name="item_text" placeholder="Add checklist item" required><button class="btn btn-secondary">Add</button></form>
  <div class="checklist">
  <?php foreach($checklist as $item):?>
   <form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="checklist_toggle"><input type="hidden" name="item_id" value="<?=$item['id']?>"><button class="<?=$item['is_done']?'done':''?>"><span><?=$item['is_done']?'✓':'○'?></span><?=e($item['item_text'])?></button></form>
  <?php endforeach;?>
  <?php if(!$checklist):?><p class="muted">No checklist items yet.</p><?php endif;?>
  </div>
 </article>
</section>

<section class="panel attachments-panel">
 <div class="panel-head"><h3>Attachments</h3><span><?=count($attachments)?> files</span></div>
 <form method="post" action="tasks/upload-attachment.php" enctype="multipart/form-data" class="attachment-form drop-upload" id="dropUpload">
  <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="task_id" value="<?=$task['id']?>">
  <label class="drop-zone" id="dropZone"><input type="file" name="attachment" id="attachmentInput" required><span class="drop-icon">⇧</span><strong>Drop a file here</strong><small>or click to browse · max 5 MB</small><em id="selectedFile">No file selected</em></label>
  <button class="btn btn-secondary">Upload File</button>
 </form>
 <div class="attachment-list">
 <?php foreach($attachments as $a):?><article><div><strong><?=e($a['original_name'])?></strong><small><?=e(format_bytes((int)$a['file_size']))?> · <?=e(date('M d, Y',strtotime($a['created_at'])))?></small></div><div class="attachment-actions"><a href="tasks/download-attachment.php?id=<?=$a['id']?>">Download</a><form method="post" action="tasks/delete-attachment.php" onsubmit="return confirm('Delete attachment?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$a['id']?>"><input type="hidden" name="task_id" value="<?=$task['id']?>"><button>Delete</button></form></div></article><?php endforeach;?>
 <?php if(!$attachments):?><p class="muted">No files uploaded.</p><?php endif;?>
 </div>
</section>

<section class="panel comments-panel">
 <div class="panel-head"><h3>Team Discussion</h3><span><?=count($comments)?> comments</span></div>
 <form method="post" class="comment-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="comment"><textarea name="body" rows="3" placeholder="Write a useful update..." required></textarea><button class="btn btn-primary">Post Comment</button></form>
 <div class="comments-list">
 <?php foreach($comments as $c):?><article><span class="avatar" style="--avatar:<?=e($u['avatar_color'])?>"><?=e(strtoupper(substr($c['name'],0,1)))?></span><div><div class="comment-head"><strong><?=e($c['name'])?></strong><small><?=e(date('M d, Y H:i',strtotime($c['created_at'])))?></small></div><p><?=nl2br(e($c['body']))?></p></div></article><?php endforeach;?>
 <?php if(!$comments):?><div class="empty">No comments yet.</div><?php endif;?>
 </div>
</section>
<script src="assets/js/upload.js"></script>
<?php require __DIR__.'/../includes/footer.php';?>
