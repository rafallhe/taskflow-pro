<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();$u=current_user($pdo);$uid=(int)$u['id'];$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'profile';
 if($action==='password'){
  $current=$_POST['current_password']??'';$newPassword=$_POST['new_password']??'';$confirm=$_POST['confirm_password']??'';
  $stmt=$pdo->prepare("SELECT password_hash FROM users WHERE id=?");$stmt->execute([$uid]);$hash=$stmt->fetchColumn();
  if(!password_verify($current,(string)$hash))$errors[]='Current password is incorrect.';
  if(strlen($newPassword)<8)$errors[]='New password must be at least 8 characters.';
  if($newPassword!==$confirm)$errors[]='New passwords do not match.';
  if(!$errors){$pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($newPassword,PASSWORD_DEFAULT),$uid]);activity($pdo,$uid,'Password changed');flash('success','Password changed successfully.');redirect('/profile/index.php');}
 } else {
  $name=trim($_POST['name']??'');$bio=trim($_POST['bio']??'');$color=$_POST['avatar_color']??'#3b82f6';
  if(mb_strlen($name)<2)$errors[]='Name is too short.';
  if(!$errors){$pdo->prepare("UPDATE users SET name=?,bio=?,avatar_color=? WHERE id=?")->execute([$name,$bio,$color,$uid]);activity($pdo,$uid,'Profile updated');flash('success','Profile updated.');redirect('/profile/index.php');}
 }
}
$stmt=$pdo->prepare("SELECT ip_address,user_agent,created_at FROM login_logs WHERE user_id=? ORDER BY id DESC LIMIT 5");$stmt->execute([$uid]);$loginLogs=$stmt->fetchAll();
$pageTitle='Profile';$activePage='profile';require __DIR__.'/../includes/header.php';
?>
<section class="form-layout"><div class="form-copy"><p class="eyebrow">ACCOUNT</p><h2>Your professional identity.</h2><p>Update your profile and use your API token for integrations.</p></div><form class="panel task-form" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label class="full">Full name<input name="name" value="<?=e($u['name'])?>"></label><label class="full">Email<input value="<?=e($u['email'])?>" disabled></label><label class="full">Bio<textarea name="bio" rows="5"><?=e($u['bio'])?></textarea></label><label>Avatar color<input type="color" name="avatar_color" value="<?=e($u['avatar_color'])?>"></label><label>Role<input value="<?=e(ucfirst($u['role']))?>" disabled></label><label class="full">API token<input value="<?=e($u['api_token'])?>" readonly onclick="this.select()"></label><input type="hidden" name="action" value="profile"><div class="form-actions full"><button class="btn btn-primary">Save Profile</button></div></form></section>
<section class="dashboard-grid profile-sections">
<article class="panel">
 <div class="panel-head"><h3>Change Password</h3></div>
 <form method="post" class="task-form">
  <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
  <input type="hidden" name="action" value="password">
  <label class="full">Current password<input type="password" name="current_password" required></label>
  <label>New password<input type="password" name="new_password" required></label>
  <label>Confirm password<input type="password" name="confirm_password" required></label>
  <div class="form-actions full"><button class="btn btn-secondary">Update Password</button></div>
 </form>
</article>
<article class="panel">
 <div class="panel-head"><h3>Recent Sign-ins</h3></div>
 <div class="login-history">
 <?php foreach($loginLogs as $log):?><div><strong><?=e($log['ip_address']?:'Unknown IP')?></strong><small><?=e(date('M d, Y H:i',strtotime($log['created_at'])))?></small><span><?=e(mb_strimwidth($log['user_agent']?:'Unknown device',0,90,'…'))?></span></div><?php endforeach;?>
 <?php if(!$loginLogs):?><p class="muted">No sign-in history yet.</p><?php endif;?>
 </div>
</article>
</section>
<?php require __DIR__.'/../includes/footer.php';?>
