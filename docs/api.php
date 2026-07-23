<?php
require_once __DIR__.'/../includes/bootstrap.php';require_auth();
$u=current_user($pdo);
$pageTitle='API Documentation';$activePage='api-docs';require __DIR__.'/../includes/header.php';
?>
<section class="hero-row"><div><p class="eyebrow">DEVELOPER PLATFORM</p><h2>TaskFlow REST API.</h2><p>Use your profile API token in the <code>X-API-TOKEN</code> header.</p></div><a class="btn btn-secondary" href="docs/openapi.json" target="_blank">OpenAPI JSON</a></section>
<section class="panel api-intro"><div><strong>Base endpoint</strong><code><?=e(url('api/tasks.php'))?></code></div><div><strong>Your token</strong><code><?=e($u['api_token'])?></code></div></section>
<section class="api-grid">
<article class="panel endpoint"><span class="method get">GET</span><h3>/api/tasks.php</h3><p>List all accessible tasks. Add <code>?id=1</code> to retrieve one task.</p><pre>curl -H "X-API-TOKEN: <?=e($u['api_token'])?>" "<?=e(url('api/tasks.php'))?>"</pre></article>
<article class="panel endpoint"><span class="method post">POST</span><h3>/api/tasks.php</h3><p>Create a task using JSON.</p><pre>curl -X POST \
-H "Content-Type: application/json" \
-H "X-API-TOKEN: <?=e($u['api_token'])?>" \
-d '{"title":"Prepare proposal","priority":"high"}' \
"<?=e(url('api/tasks.php'))?>"</pre></article>
<article class="panel endpoint"><span class="method put">PUT</span><h3>/api/tasks.php?id=1</h3><p>Update selected fields.</p><pre>curl -X PUT \
-H "Content-Type: application/json" \
-H "X-API-TOKEN: <?=e($u['api_token'])?>" \
-d '{"status":"completed","progress":100}' \
"<?=e(url('api/tasks.php?id=1'))?>"</pre></article>
<article class="panel endpoint"><span class="method delete">DELETE</span><h3>/api/tasks.php?id=1</h3><p>Delete a task owned by the API user.</p><pre>curl -X DELETE \
-H "X-API-TOKEN: <?=e($u['api_token'])?>" \
"<?=e(url('api/tasks.php?id=1'))?>"</pre></article>
</section>
<?php require __DIR__.'/../includes/footer.php';?>