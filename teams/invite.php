<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];$teamId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);require_team_role($pdo,$teamId,$uid,['admin','manager']);$errors=[];
$stmt=$pdo->prepare("SELECT * FROM teams WHERE id=?");$stmt->execute([$teamId]);$team=$stmt->fetch();if(!$team)redirect('/teams/index.php');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$email=trim($_POST['email']??'');$role=$_POST['role']??'member';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email.';
 if(!in_array($role,['manager','member'],true))$errors[]='Invalid role.';
 if(!$errors){$token=bin2hex(random_bytes(24));$pdo->prepare("INSERT INTO team_invitations(team_id,email,token,role,expires_at) VALUES(?,?,?,?,DATE_ADD(NOW(),INTERVAL 7 DAY))")->execute([$teamId,$email,$token,$role]);activity($pdo,$uid,'Team invitation created',$email);flash('success','Invitation created. Share the invitation link below.');redirect('/teams/view.php?id='.$teamId);}
}
$pageTitle='Invite Member';$activePage='teams';require __DIR__.'/../includes/header.php';
?>
<section class="form-layout"><div class="form-copy"><p class="eyebrow">TEAM INVITATION</p><h2>Invite to <?=e($team['name'])?>.</h2><p>Create an invitation valid for seven days.</p></div><form class="panel task-form" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><?php if($errors):?><div class="alert alert-error full"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?><label class="full">Email<input type="email" name="email" required></label><label class="full">Role<select name="role"><option value="member">Member</option><option value="manager">Manager</option></select></label><div class="form-actions full"><a class="btn btn-ghost" href="teams/view.php?id=<?=$teamId?>">Cancel</a><button class="btn btn-primary">Create Invitation</button></div></form></section>
<?php require __DIR__.'/../includes/footer.php';?>