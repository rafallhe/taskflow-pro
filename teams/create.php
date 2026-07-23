<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];$errors=[];$name='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=trim($_POST['name']??'');$description=trim($_POST['description']??'');$color=$_POST['color']??'#3b82f6';
 if(mb_strlen($name)<3)$errors[]='Team name must be at least 3 characters.';
 if(!$errors){$slug=slugify($name).'-'.substr(bin2hex(random_bytes(3)),0,6);
  $pdo->beginTransaction();
  try{$pdo->prepare("INSERT INTO teams(owner_id,name,description,color,slug) VALUES(?,?,?,?,?)")->execute([$uid,$name,$description?:null,$color,$slug]);$teamId=(int)$pdo->lastInsertId();
   $pdo->prepare("INSERT INTO team_members(team_id,user_id,role) VALUES(?,?,?)")->execute([$teamId,$uid,'admin']);
   $pdo->commit();activity($pdo,$uid,'Team created',$name);redirect('/teams/view.php?id='.$teamId);
  }catch(Throwable $e){$pdo->rollBack();$errors[]='Could not create team.';}
 }}
$pageTitle='Create Team';$activePage='teams';require __DIR__.'/../includes/header.php';
?>
<section class="form-layout"><div class="form-copy"><p class="eyebrow">NEW WORKSPACE</p><h2>Create a team.</h2><p>Bring people, tasks and decisions into one place.</p></div><form method="post" class="panel task-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><?php if($errors):?><div class="alert alert-error full"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?><label class="full">Team name<input name="name" value="<?=e($name)?>" placeholder="Product Team" required></label><label class="full">Description<textarea name="description" rows="4" placeholder="What does this team own?"></textarea></label><label>Team color<input type="color" name="color" value="#3b82f6"></label><div class="form-actions full"><a class="btn btn-ghost" href="teams/index.php">Cancel</a><button class="btn btn-primary">Create Team</button></div></form></section>
<?php require __DIR__.'/../includes/footer.php';?>