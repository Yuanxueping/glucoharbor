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

<?php
$related_products = db_fetchAll("SELECT id,name,slug,image,price,sale_price,currency,affiliate_url,badge FROM products WHERE is_active=1 AND is_featured=1 ORDER BY sort_order,id LIMIT 4");
if (!$related_products) {
    $related_products = db_fetchAll("SELECT id,name,slug,image,price,sale_price,currency,affiliate_url,badge FROM products WHERE is_active=1 ORDER BY sort_order,id LIMIT 4");
}
?>
<?php if ($related_products): ?>
<section class="you-may-like-section">
<div class="container">
  <h2 class="section-title">You May Also Like</h2>
  <div class="you-may-like-grid">
    <?php foreach ($related_products as $rp): ?>
    <a href="/product/<?= e($rp['slug']) ?>" class="ymal-card">
      <div class="ymal-img">
        <?php if ($rp['image']): ?>
        <img src="<?= e($rp['image']) ?>" alt="<?= e($rp['name']) ?>" loading="lazy">
        <?php else: ?><div class="ymal-placeholder"><i class="fas fa-box-open"></i></div><?php endif ?>
        <?php if ($rp['badge']): ?><span class="ymal-badge"><?= e($rp['badge']) ?></span><?php endif ?>
      </div>
      <div class="ymal-info">
        <h3><?= e($rp['name']) ?></h3>
        <?php if ($rp['price']): ?>
        <div class="ymal-price">
          <?php if ($rp['sale_price']): ?>
          <span class="ymal-sale"><?= e($rp['currency']?:'$') ?><?= number_format($rp['sale_price'],2) ?></span>
          <span class="ymal-orig"><?= e($rp['currency']?:'$') ?><?= number_format($rp['price'],2) ?></span>
          <?php else: ?>
          <span><?= e($rp['currency']?:'$') ?><?= number_format($rp['price'],2) ?></span>
          <?php endif ?>
        </div>
        <?php endif ?>
        <?php if ($rp['affiliate_url']): ?><span class="ymal-btn"><i class="fab fa-amazon"></i> View on Amazon</span><?php endif ?>
      </div>
    </a>
    <?php endforeach ?>
  </div>
</div>
</section>
<?php endif ?>

<?php if ($related): ?>
<section class="related-section">
<div class="container">
  <h2 class="section-title">Related Articles</h2>
  <div class="related-list">
    <?php foreach ($related as $r): ?>
    <a href="/article/<?= e($r['slug']) ?>" class="related-item">
      <?php if ($r['featured_image']): ?>
      <img src="<?= e($r['featured_image']) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
      <?php else: ?><div class="related-placeholder"><i class="fas fa-newspaper"></i></div><?php endif ?>
      <div class="related-info">
        <h3><?= e($r['title']) ?></h3>
        <span><?= fmt_date($r['published_at']) ?></span>
      </div>
    </a>
    <?php endforeach ?>
  </div>
</div>
</section>
<?php endif ?>

<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"Article",
  "headline":<?= json_encode($article['title']) ?>,
  "description":<?= json_encode($article['excerpt'] ?? '') ?>,
  "url":<?= json_encode($site_url . '/article/' . $article['slug']) ?>,
  "datePublished":<?= json_encode(str_replace(' ','T',$article['published_at']).'+00:00') ?>,
  "dateModified":<?= json_encode(str_replace(' ','T',$article['updated_at']).'+00:00') ?>,
  "inLanguage":"en-US",
  "author":{"@type":"Organization","name":<?= json_encode($site_name) ?>,"url":<?= json_encode($site_url) ?>},
  "publisher":{"@type":"Organization","name":<?= json_encode($site_name) ?>,"url":<?= json_encode($site_url) ?>,"logo":{"@type":"ImageObject","url":<?= json_encode($site_url . '/assets/img/logo.png') ?>}}
  <?php if ($article['featured_image']): ?>,"image":{"@type":"ImageObject","url":<?= json_encode($article['featured_image']) ?>,"width":1200,"height":630}<?php endif ?>
  <?php if ($article['cat_name']): ?>,"articleSection":<?= json_encode($article['cat_name']) ?><?php endif ?>
}
</script>

<?php require ROOT_PATH . '/templates/front/_foot.php'; ?>
