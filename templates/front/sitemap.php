<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$site_url  = rtrim(setting('site_url'),'https://glucoharbor.com');
$articles  = db_fetchAll("SELECT slug,updated_at FROM articles WHERE status='published' ORDER BY updated_at DESC");
$cats      = db_fetchAll("SELECT slug FROM categories ORDER BY sort_order");
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc><?= e($site_url) ?>/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
<url><loc><?= e($site_url) ?>/articles</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
<url><loc><?= e($site_url) ?>/products</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
<?php foreach ($cats as $c): ?>
<url><loc><?= e($site_url) ?>/category/<?= e($c['slug']) ?></loc><changefreq>daily</changefreq><priority>0.7</priority></url>
<?php endforeach ?>
<?php foreach ($articles as $a): ?>
<url>
  <loc><?= e($site_url) ?>/article/<?= e($a['slug']) ?></loc>
  <lastmod><?= date('Y-m-d',strtotime($a['updated_at'])) ?></lastmod>
  <changefreq>weekly</changefreq><priority>0.6</priority>
</url>
<?php endforeach ?>
</urlset>
