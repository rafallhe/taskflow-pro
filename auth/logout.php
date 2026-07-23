<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) activity($pdo,(int)$_SESSION['user_id'],'Signed out');
$_SESSION=[];session_destroy();session_start();flash('success','Signed out successfully.');redirect('/auth/login.php');
