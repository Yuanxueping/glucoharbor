<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$slug = $parts[1] ?? '';
$cat  = db_fetch("SELECT * FROM categories WHERE slug=?",[$slug]);
if (!$cat) { http_response_code(404); require ROOT_PATH.'/templates/front/404.php'; exit; }
$per   = (int)setting('articles_per_page') ?: 12;
$page  = max(1,(int)($_GET['page']??1));
$total = db_count("SELECT COUNT(*) FROM articles WHERE category_id=? AND status='published'",[$cat['id']]);
$pg    = paginate($total,$per,$page);
$list  = db_fetchAll("SELECT a.*,c.name cat_name,c.slug cat_slug FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.category_id=? AND a.status='published' ORDER BY a.published_at DESC LIMIT $per OFFSET {$pg['offset']}",[$cat['id']]);
$page_title = $cat['meta_title'] ?: $cat['name'];
$meta_desc  = $cat['meta_description'] ?: $cat['description'];
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="page-banner">
<div class="container">
  <nav class="breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <a href="/articles">Articles</a> <i class="fas fa-chevron-right fa-xs"></i> <span><?= e($cat['name']) ?></span></nav>
  <h1><?= e($cat['name']) ?></h1>
  <?php if ($cat['description']): ?><p><?= e($cat['description']) ?></p><?php endif ?>
</div>
</section>

<section class="section">
<div class="container layout-sb">
  <div class="content-area">
    <?php if ($list): ?>
    <div class="article-grid">
      <?php foreach ($list as $a): ?>
      <article class="article-card">
        <a href="/article/<?= e($a['slug']) ?>" class="card-img-link">
          <?php if ($a['featured_image']): ?><img src="<?= e($a['featured_image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
          <?php else: ?><div class="card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif ?>
        </a>
        <div class="card-body">
          <h2><a href="/article/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h2>
          <?php if ($a['excerpt']): ?><p class="card-excerpt"><?= e(mb_substr($a['excerpt'],0,120)) ?>...</p><?php endif ?>
          <div class="card-meta"><span><i class="fas fa-calendar-alt"></i> <?= fmt_date($a['published_at']) ?></span></div>
        </div>
      </article>
      <?php endforeach ?>
    </div>
    <?php if ($pg['pages']>1): ?>
    <nav class="pagination">
      <?php for($i=1;$i<=$pg['pages'];$i++): ?>
      <a href="/category/<?= e($cat['slug']) ?>?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor ?>
    </nav>
    <?php endif ?>
    <?php else: ?><div class="empty"><i class="fas fa-newspaper"></i><p>No articles in this category yet.</p></div><?php endif ?>
  </div>
  <aside class="sidebar">
    <div class="widget">
      <h3 class="widget-title">All Categories</h3>
      <ul class="widget-list">
        <?php foreach (nav_categories() as $c):
          $cnt = db_count("SELECT COUNT(*) FROM articles WHERE category_id=? AND status='published'",[$c['id']]); ?>
        <li class="<?= $c['slug']===$slug?'active':'' ?>"><a href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?> <span class="count-badge"><?= $cnt ?></span></a></li>
        <?php endforeach ?>
      </ul>
    </div>
  </aside>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
