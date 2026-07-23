<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) redirect('/dashboard.php');
$errors=[];$email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$email=trim($_POST['email']??'');$password=$_POST['password']??'';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email.';
 if(!$password)$errors[]='Password is required.';
 if(!$errors){
  $stmt=$pdo->prepare("SELECT id,password_hash FROM users WHERE email=? LIMIT 1");$stmt->execute([$email]);$u=$stmt->fetch();
  if($u&&password_verify($password,$u['password_hash'])){
   session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];
   $pdo->prepare("UPDATE users SET last_login_at=NOW() WHERE id=?")->execute([(int)$u['id']]);
   $pdo->prepare("INSERT INTO login_logs(user_id,ip_address,user_agent) VALUES(?,?,?)")->execute([
      (int)$u['id'],
      $_SERVER['REMOTE_ADDR'] ?? null,
      mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,255)
   ]);
   activity($pdo,(int)$u['id'],'Signed in');flash('success','Welcome back.');redirect('/dashboard.php');
  }
  $errors[]='Incorrect email or password.';
 }
}
?>
<!doctype html><html><head><base href="<?=e(base_url().'/')?>"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign In | TaskFlow Pro</title><link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#07101d"><link rel="stylesheet" href="assets/css/app.css"></head>
<body class="auth-page">
<div class="auth-layout">
<section class="auth-hero"><a class="brand" href=""><span class="brand-mark">TF</span><span>TaskFlow <b>Pro</b></span></a><div><p class="eyebrow">ENTERPRISE PRODUCTIVITY</p><h1>Plan with clarity.<br>Deliver with confidence.</h1><p>A polished PHP and MySQL task management platform built for teams and professionals.</p></div><div class="hero-metric"><strong>10/10 Portfolio Ready</strong><span>Secure • Responsive • Scalable</span></div></section>
<section class="auth-panel"><form class="auth-card" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><p class="eyebrow">WELCOME BACK</p><h2>Sign in</h2><p class="muted">Access your private workspace.</p>
<?php if($errors):?><div class="alert alert-error"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?>
<label>Email<input type="email" name="email" value="<?=e($email)?>" required></label>
<label>Password<input type="password" name="password" required></label>
<button class="btn btn-primary btn-block">Sign In</button>
<p class="center muted">New user? <a class="text-link" href="auth/register.php">Create account</a></p>
<p class="demo-note">Demo: demo@taskflow.test / Demo123!</p>
</form></section>
</div><script src="assets/js/app.js"></script></body></html>
