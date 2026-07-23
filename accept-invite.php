<?php
require_once __DIR__.'/includes/bootstrap.php';
$token=$_GET['token']??'';
if(!is_logged_in()){$_SESSION['pending_invite']=$token;redirect('/auth/login.php');}
$u=current_user($pdo);
$stmt=$pdo->prepare("SELECT * FROM team_invitations WHERE token=? AND status='pending' AND expires_at>NOW()");$stmt->execute([$token]);$invite=$stmt->fetch();
if(!$invite){flash('error','Invitation is invalid or expired.');redirect('/dashboard.php');}
if(strcasecmp($invite['email'],$u['email'])!==0){flash('error','This invitation belongs to another email address.');redirect('/dashboard.php');}
$pdo->beginTransaction();try{$pdo->prepare("INSERT IGNORE INTO team_members(team_id,user_id,role) VALUES(?,?,?)")->execute([$invite['team_id'],$u['id'],$invite['role']]);$pdo->prepare("UPDATE team_invitations SET status='accepted' WHERE id=?")->execute([$invite['id']]);$pdo->commit();flash('success','You joined the team.');redirect('/teams/view.php?id='.$invite['team_id']);}catch(Throwable $e){$pdo->rollBack();flash('error','Could not accept invitation.');redirect('/dashboard.php');}
