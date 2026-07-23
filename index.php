<?php
require_once __DIR__.'/includes/bootstrap.php';
if(is_logged_in()) redirect('/dashboard.php');
?>
<!doctype html>
<html lang="en">
<head>
<base href="<?=e(base_url().'/')?>">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#07101d">
<link rel="manifest" href="manifest.webmanifest">
<link rel="stylesheet" href="assets/css/app.css">
<title>TaskFlow Pro — Modern Team Productivity</title>
</head>
<body class="landing-page">
<header class="landing-nav">
 <a class="brand" href=""><span class="brand-mark">TF</span><span>TaskFlow <b>Pro</b></span></a>
 <nav><a href="#features">Features</a><a href="#workflow">Workflow</a><a href="#security">Security</a></nav>
 <div><a class="btn btn-ghost" href="auth/login.php">Sign In</a><a class="btn btn-primary" href="auth/register.php">Start Free</a></div>
</header>
<main>
<section class="landing-hero">
 <div class="landing-copy">
  <p class="eyebrow">MODERN TEAM PRODUCTIVITY</p>
  <h1>Plan clearly.<br>Move faster.<br>Deliver together.</h1>
  <p>TaskFlow Pro combines tasks, Kanban, calendars, reports, teams, files and real-time updates in one polished workspace.</p>
  <div class="landing-actions"><a class="btn btn-primary" href="auth/register.php">Create Workspace</a><a class="btn btn-secondary" href="auth/login.php">Open Demo</a></div>
  <div class="trust-row"><span>Secure PHP backend</span><span>MySQL data model</span><span>Responsive SaaS UI</span></div>
 </div>
 <div class="landing-product">
  <div class="mock-window">
   <div class="mock-top"><span></span><span></span><span></span></div>
   <div class="mock-layout">
    <aside><b>TF</b><i></i><i></i><i></i><i></i></aside>
    <section><p>Dashboard</p><h3>Welcome back, Demo.</h3><div class="mock-stats"><i></i><i></i><i></i></div><div class="mock-chart"></div></section>
   </div>
  </div>
 </div>
</section>
<section id="features" class="landing-section">
 <p class="eyebrow">ONE COMPLETE WORKSPACE</p><h2>Everything needed to manage real work.</h2>
 <div class="feature-grid">
  <article><span>01</span><h3>Visual planning</h3><p>Kanban, calendars and progress tracking keep work visible.</p></article>
  <article><span>02</span><h3>Team collaboration</h3><p>Assign tasks, manage roles, comments, files and checklists.</p></article>
  <article><span>03</span><h3>Actionable reports</h3><p>Measure progress with charts, KPIs and downloadable reports.</p></article>
  <article><span>04</span><h3>Secure by design</h3><p>CSRF protection, password hashing, audit logs and ownership checks.</p></article>
 </div>
</section>
<section id="workflow" class="landing-section split-section">
 <div><p class="eyebrow">BUILT FOR MOMENTUM</p><h2>From idea to done without losing context.</h2></div>
 <div class="workflow-list"><div><b>1</b><span>Create and prioritize</span></div><div><b>2</b><span>Assign and collaborate</span></div><div><b>3</b><span>Track and report</span></div></div>
</section>
<section id="security" class="landing-cta"><p class="eyebrow">READY TO START?</p><h2>Turn scattered work into a clear system.</h2><a class="btn btn-primary" href="auth/register.php">Create your account</a></section>
</main>
<footer class="landing-footer"><span>© <?=date('Y')?> TaskFlow Pro</span><span>Portfolio SaaS Project</span></footer>
<script src="assets/js/app.js"></script>
<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('service-worker.js').catch(()=>{});}</script>
</body></html>