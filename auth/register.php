<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) redirect('/dashboard.php');
$errors=[];$name='';$email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$password=$_POST['password']??'';$confirm=$_POST['confirm']??'';
 if(mb_strlen($name)<2)$errors[]='Name is too short.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Invalid email.';
 if(strlen($password)<8)$errors[]='Password must be at least 8 characters.';
 if($password!==$confirm)$errors[]='Passwords do not match.';
 if(!$errors){
  $stmt=$pdo->prepare("SELECT id FROM users WHERE email=?");$stmt->execute([$email]);
  if($stmt->fetch())$errors[]='Email already registered.';
  else{
   $token=bin2hex(random_bytes(24));
   $stmt=$pdo->prepare("INSERT INTO users(name,email,password_hash,role,avatar_color,api_token) VALUES(?,?,?,?,?,?)");
   $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),'member','#3b82f6',$token]);
   $_SESSION['user_id']=(int)$pdo->lastInsertId();session_regenerate_id(true);
   activity($pdo,(int)$_SESSION['user_id'],'Account created');
   notify($pdo,(int)$_SESSION['user_id'],'Welcome to TaskFlow Pro','Your workspace is ready.');
   redirect('/dashboard.php');
  }
 }
}
?>
<!doctype html><html><head><base href="<?=e(base_url().'/')?>"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Account | TaskFlow Pro</title><link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#07101d"><link rel="stylesheet" href="assets/css/app.css"></head>
<body class="auth-page"><div class="auth-layout"><section class="auth-hero"><a class="brand"><span class="brand-mark">TF</span><span>TaskFlow <b>Pro</b></span></a><div><p class="eyebrow">GET STARTED</p><h1>Build momentum.<br>Track every result.</h1><p>Create your professional workspace in less than a minute.</p></div><div class="hero-metric"><strong>Smart Task Management</strong><span>Roles • Reports • API • Notifications</span></div></section>
<section class="auth-panel"><form class="auth-card" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><p class="eyebrow">CREATE ACCOUNT</p><h2>Join TaskFlow Pro</h2>
<?php if($errors):?><div class="alert alert-error"><?=implode('<br>',array_map('e',$errors))?></div><?php endif;?>
<label>Full name<input name="name" value="<?=e($name)?>" required></label><label>Email<input type="email" name="email" value="<?=e($email)?>" required></label><label>Password<input type="password" name="password" required></label><label>Confirm password<input type="password" name="confirm" required></label><button class="btn btn-primary btn-block">Create Account</button><p class="center muted">Already registered? <a class="text-link" href="auth/login.php">Sign in</a></p></form></section></div><script src="assets/js/app.js"></script></body></html>
