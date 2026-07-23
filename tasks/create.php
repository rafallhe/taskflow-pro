<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();$u=current_user($pdo);$uid=(int)$u['id'];$teams=user_teams($pdo,$uid);$teamId=current_team_id($pdo,$uid);$members=[];if($teamId){$ms=$pdo->prepare("SELECT u.id,u.name FROM team_members tm JOIN users u ON u.id=tm.user_id WHERE tm.team_id=? ORDER BY u.name");$ms->execute([$teamId]);$members=$ms->fetchAll();}
$editing=isset($task);
$data=$editing?$task:['title'=>'','description'=>'','status'=>'todo','priority'=>'medium','due_date'=>'','category'=>'General','tags'=>'','team_id'=>$teamId,'assigned_to'=>$uid,'progress'=>0];
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach(['title','description','status','priority','due_date','category','tags','team_id','assigned_to','progress'] as $k)$data[$k]=trim((string)($_POST[$k]??''));
 if(mb_strlen($data['title'])<3)$errors[]='Title must be at least 3 characters.';
 if(!in_array($data['status'],['todo','in_progress','completed'],true))$errors[]='Invalid status.';
 if(!in_array($data['priority'],['low','medium','high'],true))$errors[]='Invalid priority.';
 if(!validate_date($data['due_date']))$errors[]='Invalid date.';
 $data['progress']=max(0,min(100,(int)$data['progress']));
 if($data['status']==='completed')$data['progress']=100;
 if(!$errors){
  if($editing){
   $stmt=$pdo->prepare("UPDATE tasks SET team_id=?,assigned_to=?,title=?,description=?,status=?,priority=?,due_date=?,category=?,tags=?,progress=? WHERE id=? AND user_id=?");
   $stmt->execute([$data['team_id']?:null,$data['assigned_to']?:null,$data['title'],$data['description']?:null,$data['status'],$data['priority'],$data['due_date']?:null,$data['category']?:'General',$data['tags']?:null,$data['progress'],$task['id'],$uid]);
   activity($pdo,$uid,'Task updated',$data['title']);notify($pdo,$uid,'Task updated',$data['title']);
  }else{
   $stmt=$pdo->prepare("INSERT INTO tasks(user_id,team_id,assigned_to,title,description,status,priority,due_date,category,tags,progress) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
   $stmt->execute([$uid,$data['team_id']?:null,$data['assigned_to']?:null,$data['title'],$data['description']?:null,$data['status'],$data['priority'],$data['due_date']?:null,$data['category']?:'General',$data['tags']?:null,$data['progress']]);
   activity($pdo,$uid,'Task created',$data['title']);notify($pdo,$uid,'New task created',$data['title']);
  }
  flash('success',$editing?'Task updated successfully.':'Task created successfully.');redirect('/tasks/index.php');
 }
}
?>
<?php $pageTitle='Create Task';$activePage='new-task';require __DIR__.'/../includes/header.php';?>
<section class="form-layout"><div class="form-copy"><p class="eyebrow">NEW TASK</p><h2>Create a clear, measurable action.</h2><p>Set ownership, urgency and progress from the start.</p></div><form class="panel task-form" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><?php if($errors):?><div class="alert alert-error full"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?>
<label class="full">Title<input name="title" value="<?=e($data['title'])?>" required></label><label class="full">Description<textarea name="description" rows="6"><?=e($data['description'])?></textarea></label><label>Status<select name="status"><option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></label><label>Priority<select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></label><label>Category<input name="category" value="<?=e($data['category'])?>"></label><label>Team<select name="team_id"><option value="">Personal</option><?php foreach($teams as $team):?><option value="<?=$team['id']?>" <?=((int)($data['team_id']??0)===(int)$team['id'])?'selected':''?>><?=e($team['name'])?></option><?php endforeach;?></select></label><label>Assign to<select name="assigned_to"><option value="">Unassigned</option><?php foreach($members as $member):?><option value="<?=$member['id']?>" <?=((int)($data['assigned_to']??0)===(int)$member['id'])?'selected':''?>><?=e($member['name'])?></option><?php endforeach;?></select></label><label>Tags<input name="tags" value="<?=e($data['tags']??'')?>" placeholder="frontend, api, urgent"></label><label>Due date<input type="date" name="due_date" value="<?=e($data['due_date'])?>"></label><label class="full">Progress<input type="range" name="progress" min="0" max="100" value="<?=e((string)$data['progress'])?>" oninput="this.nextElementSibling.value=this.value+'%'"><output><?=e((string)$data['progress'])?>%</output></label><div class="form-actions full"><a class="btn btn-ghost" href="tasks/index.php">Cancel</a><button class="btn btn-primary">Create Task</button></div></form></section>
<?php require __DIR__.'/../includes/footer.php';?>
