<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$slug = $parts[1] ?? '';
$pg   = db_fetch("SELECT * FROM pages WHERE slug=? AND status='published'",[$slug]);
if (!$pg) { http_response_code(404); require ROOT_PATH.'/templates/front/404.php'; exit; }
$page_title = $pg['meta_title'] ?: $pg['title'];
$meta_desc  = $pg['meta_description'];
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="page-banner">
<div class="container">
  <nav class="breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <span><?= e($pg['title']) ?></span></nav>
  <h1><?= e($pg['title']) ?></h1>
</div>
</section>
<section class="section">
<div class="container narrow">
  <div class="page-content"><?= $pg['content'] ?></div>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
