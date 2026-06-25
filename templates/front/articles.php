<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$per   = (int)setting('articles_per_page') ?: 12;
$page  = max(1,(int)($_GET['page']??1));
$total = db_count("SELECT COUNT(*) FROM articles WHERE status='published'");
$pg    = paginate($total,$per,$page);
$list  = db_fetchAll("SELECT a.*,c.name cat_name,c.slug cat_slug FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.status='published' ORDER BY a.published_at DESC LIMIT $per OFFSET {$pg['offset']}");
$page_title='Health Articles'; $meta_desc='Browse all health articles on '.setting('site_name');
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="page-banner">
<div class="container">
  <nav class="breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <span>Articles</span></nav>
  <h1>Health Articles</h1>
  <p>Evidence-based information on diabetes, hypertension, and blood sugar management</p>
</div>
</section>

<section class="section">
<div class="container layout-sb">
  <div class="content-area">
    <?php ad_slot('ad_article_top_code') ?>
    <?php if ($list): ?>
    <div class="article-grid">
      <?php foreach ($list as $a): ?>
      <article class="article-card">
        <a href="/article/<?= e($a['slug']) ?>" class="card-img-link">
          <?php if ($a['featured_image']): ?><img src="<?= e($a['featured_image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
          <?php else: ?><div class="card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif ?>
        </a>
        <div class="card-body">
          <?php if ($a['cat_name']): ?><a href="/category/<?= e($a['cat_slug']) ?>" class="card-cat"><?= e($a['cat_name']) ?></a><?php endif ?>
          <h2><a href="/article/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h2>
          <?php if ($a['excerpt']): ?><p class="card-excerpt"><?= e(mb_substr($a['excerpt'],0,120)) ?>...</p><?php endif ?>
          <div class="card-meta">
            <span><i class="fas fa-calendar-alt"></i> <?= fmt_date($a['published_at']) ?></span>
            <span><i class="fas fa-eye"></i> <?= $a['views'] ?></span>
          </div>
        </div>
      </article>
      <?php endforeach ?>
    </div>
    <?php if ($pg['pages']>1): ?>
    <nav class="pagination">
      <?php if ($page>1): ?><a href="/articles?page=<?= $page-1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a><?php endif ?>
      <?php for($i=1;$i<=$pg['pages'];$i++): ?>
      <a href="/articles?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor ?>
      <?php if ($page<$pg['pages']): ?><a href="/articles?page=<?= $page+1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a><?php endif ?>
    </nav>
    <?php endif ?>
    <?php else: ?><div class="empty"><i class="fas fa-newspaper"></i><p>No articles yet.</p></div><?php endif ?>
  </div>
  <aside class="sidebar">
    <?php ad_slot('ad_sidebar_code') ?>
    <div class="widget">
      <h3 class="widget-title">Categories</h3>
      <ul class="widget-list">
        <?php foreach (nav_categories() as $c):
          $cnt = db_count("SELECT COUNT(*) FROM articles WHERE category_id=? AND status='published'",[$c['id']]); ?>
        <li><a href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?> <span class="count-badge"><?= $cnt ?></span></a></li>
        <?php endforeach ?>
      </ul>
    </div>
  </aside>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
