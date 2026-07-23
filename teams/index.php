<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();
$u=current_user($pdo);$uid=(int)$u['id'];$teams=user_teams($pdo,$uid);
$pageTitle='Teams';$activePage='teams';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">COLLABORATION</p><h2>Your teams.</h2><p>Create workspaces, manage members and assign tasks.</p></div><a class="btn btn-primary" href="teams/create.php">＋ Create Team</a></section>
<div class="team-grid"><?php foreach($teams as $team):?>
<article class="panel team-card"><div class="team-logo" style="--team-color:<?=e($team['color']??'#3b82f6')?>"><?=e($team['logo_initials']?:strtoupper(substr($team['name'],0,2)))?></div><div><h3><?=e($team['name'])?></h3><p><?=e($team['description']?:ucfirst($team['membership_role']).' access')?></p></div><a class="btn btn-secondary" href="teams/view.php?id=<?=$team['id']?>">Open Team</a></article>
<?php endforeach;?><?php if(!$teams):?><div class="panel empty">No teams yet.</div><?php endif;?></div>
<?php require __DIR__.'/../includes/footer.php';?>