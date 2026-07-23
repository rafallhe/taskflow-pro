<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
require_team_role($pdo,$id,$uid,['admin']);
$stmt=$pdo->prepare("SELECT * FROM teams WHERE id=?");$stmt->execute([$id]);$team=$stmt->fetch();
if(!$team){flash('error','Team not found.');redirect('/teams/index.php');}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'save';
 if($action==='delete'){
  $pdo->prepare("DELETE FROM teams WHERE id=? AND owner_id=?")->execute([$id,$uid]);
  activity($pdo,$uid,'Team deleted',$team['name']);flash('success','Team deleted.');redirect('/teams/index.php');
 }
 $name=trim($_POST['name']??'');$description=trim($_POST['description']??'');$color=$_POST['color']??'#3b82f6';$initials=mb_strtoupper(trim($_POST['logo_initials']??''));
 if(mb_strlen($name)<3)$errors[]='Team name must be at least 3 characters.';
 if(!preg_match('/^#[0-9a-fA-F]{6}$/',$color))$errors[]='Invalid team color.';
 if(mb_strlen($initials)>4)$errors[]='Initials must be 4 characters or fewer.';
 if(!$errors){
  $pdo->prepare("UPDATE teams SET name=?,description=?,color=?,logo_initials=? WHERE id=?")->execute([$name,$description?:null,$color,$initials?:null,$id]);
  activity($pdo,$uid,'Team updated',$name);flash('success','Team updated.');redirect('/teams/view.php?id='.$id);
 }
}
$pageTitle='Edit Team';$activePage='teams';require __DIR__.'/../includes/header.php';
?>
<section class="form-layout"><div class="form-copy"><p class="eyebrow">TEAM SETTINGS</p><h2>Shape your workspace.</h2><p>Update identity, purpose and visual style.</p></div>
<form method="post" class="panel task-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save">
<?php if($errors):?><div class="alert alert-error full"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?>
<label class="full">Team name<input name="name" value="<?=e($_POST['name']??$team['name'])?>" required></label>
<label class="full">Description<textarea name="description" rows="5"><?=e($_POST['description']??$team['description'])?></textarea></label>
<label>Color<input type="color" name="color" value="<?=e($_POST['color']??$team['color'])?>"></label>
<label>Logo initials<input name="logo_initials" maxlength="4" value="<?=e($_POST['logo_initials']??$team['logo_initials'])?>" placeholder="TF"></label>
<div class="form-actions full"><a class="btn btn-ghost" href="teams/view.php?id=<?=$id?>">Cancel</a><button class="btn btn-primary">Save Team</button></div></form></section>
<section class="panel danger-zone"><div><h3>Delete team</h3><p class="muted">This permanently removes the team. Tasks become personal or unassigned.</p></div><form method="post" onsubmit="return confirm('Delete this team permanently?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><button class="btn btn-danger">Delete Team</button></form></section>
<?php require __DIR__.'/../includes/footer.php';?>