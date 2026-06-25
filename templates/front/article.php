<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$slug = $parts[1] ?? '';
$article = db_fetch("SELECT a.*,c.name cat_name,c.slug cat_slug,u.username author_name FROM articles a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN users u ON a.author_id=u.id WHERE a.slug=? AND a.status='published'", [$slug]);
if (!$article) { http_response_code(404); require ROOT_PATH.'/templates/front/404.php'; exit; }

db_query("UPDATE articles SET views=views+1 WHERE id=?", [$article['id']]);

$related = db_fetchAll("SELECT id,title,slug,featured_image,published_at FROM articles WHERE category_id=? AND id!=? AND status='published' ORDER BY published_at DESC LIMIT 4", [$article['category_id'],$article['id']]);

$page_title = $article['meta_title'] ?: $article['title'];
$meta_desc  = $article['meta_description'] ?: $article['excerpt'];
$meta_keys  = $article['meta_keywords'];
$og_image   = $article['featured_image'];
$site_url   = setting('site_url');
$site_name  = setting('site_name') ?: 'GlucoHarbor';

require ROOT_PATH . '/templates/front/_head.php';
?>

<div class="article-header">
<div class="container">
  <nav class="breadcrumb">
    <a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i>
    <?php if ($article['cat_name']): ?>
    <a href="/articles">Articles</a> <i class="fas fa-chevron-right fa-xs"></i>
    <a href="/category/<?= e($article['cat_slug']) ?>"><?= e($article['cat_name']) ?></a> <i class="fas fa-chevron-right fa-xs"></i>
    <?php else: ?>
    <a href="/articles">Articles</a> <i class="fas fa-chevron-right fa-xs"></i>
    <?php endif ?>
    <span><?= e(mb_substr($article['title'],0,50)) ?>...</span>
  </nav>
  <?php if ($article['cat_name']): ?><a href="/category/<?= e($article['cat_slug']) ?>" class="badge badge-lg"><?= e($article['cat_name']) ?></a><?php endif ?>
  <h1 itemprop="headline"><?= e($article['title']) ?></h1>
  <div class="article-meta">
    <span><i class="fas fa-calendar-alt"></i> <time datetime="<?= e($article['published_at']) ?>"><?= fmt_date($article['published_at'],'F j, Y') ?></time></span>
    <?php if ($article['author_name']): ?><span><i class="fas fa-user"></i> <?= e($article['author_name']) ?></span><?php endif ?>
    <span><i class="fas fa-eye"></i> <?= $article['views'] ?> views</span>
  </div>
</div>
</div>

<div class="container layout-sb" style="padding-top:32px">
<div class="content-area">
  <?php if ($article['featured_image']): ?>
  <figure class="article-featured-img">
    <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>">
  </figure>
  <?php endif ?>

  <?php ad_slot('ad_article_top_code') ?>

  <div class="article-body" itemprop="articleBody">
    <?= $article['content'] ?>
  </div>

  <?php ad_slot('ad_article_bottom_code') ?>

  <div class="share-bar">
    <span>Share:</span>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($site_url.'/article/'.$article['slug']) ?>" target="_blank" rel="noopener" class="share-btn share-fb"><i class="fab fa-facebook-f"></i> Facebook</a>
    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($site_url.'/article/'.$article['slug']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="share-btn share-tw"><i class="fab fa-x-twitter"></i> X</a>
  </div>
</div>

<aside class="sidebar">
  <?php ad_slot('ad_sidebar_code') ?>
  <div class="widget">
    <h3 class="widget-title">Categories</h3>
    <ul class="widget-list">
      <?php foreach (nav_categories() as $c):
        $cnt = db_count("SELECT COUNT(*) FROM articles WHERE category_id=? AND status='published'",[$c['id']]); ?>
      <li class="<?= $article['category_id']==$c['id']?'active':'' ?>">
        <a href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?> <span class="count-badge"><?= $cnt ?></span></a>
      </li>
      <?php endforeach ?>
    </ul>
  </div>
</aside>
</div>

<?php if ($related): ?>
<section class="related-section">
<div class="container">
  <h2 class="section-title">Related Articles</h2>
  <div class="article-grid cols-4">
    <?php foreach ($related as $r): ?>
    <article class="article-card">
      <a href="/article/<?= e($r['slug']) ?>" class="card-img-link">
        <?php if ($r['featured_image']): ?>
        <img src="<?= e($r['featured_image']) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
        <?php else: ?><div class="card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif ?>
      </a>
      <div class="card-body">
        <h3><a href="/article/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></h3>
        <span class="card-meta"><?= fmt_date($r['published_at']) ?></span>
      </div>
    </article>
    <?php endforeach ?>
  </div>
</div>
</section>
<?php endif ?>

<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Article",
"headline":"<?= addslashes(e($article['title'])) ?>",
"description":"<?= addslashes(e($article['excerpt']??'')) ?>",
"url":"<?= $site_url ?>/article/<?= $article['slug'] ?>",
"datePublished":"<?= $article['published_at'] ?>","dateModified":"<?= $article['updated_at'] ?>",
"author":{"@type":"Organization","name":"<?= e($site_name) ?>"},
"publisher":{"@type":"Organization","name":"<?= e($site_name) ?>","url":"<?= $site_url ?>"}
<?php if ($article['featured_image']): ?>,"image":"<?= $site_url . $article['featured_image'] ?>"<?php endif ?>}
</script>

<?php require ROOT_PATH . '/templates/front/_foot.php'; ?>
