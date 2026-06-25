<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$q    = trim($_GET['q'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$per  = 12;
if ($q) {
    $like  = '%'.str_replace(['%','_'],['\\%','\\_'],$q).'%';
    $total = db_count("SELECT COUNT(*) FROM articles WHERE status='published' AND (title LIKE ? OR excerpt LIKE ?)",[$like,$like]);
    $pg    = paginate($total,$per,$page);
    $list  = db_fetchAll("SELECT a.*,c.name cat_name,c.slug cat_slug FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.status='published' AND (a.title LIKE ? OR a.excerpt LIKE ?) ORDER BY a.published_at DESC LIMIT $per OFFSET {$pg['offset']}",[$like,$like]);
} else { $total=0; $list=[]; $pg=paginate(0,$per,1); }
$page_title = $q ? 'Search: '.e($q) : 'Search';
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="page-banner">
<div class="container">
  <h1>Search Results</h1>
  <?php if ($q): ?><p>Found <strong><?= $total ?></strong> results for "<strong><?= e($q) ?></strong>"</p><?php endif ?>
  <form action="/search" method="get" class="search-form-lg">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search articles...">
    <button type="submit"><i class="fas fa-search"></i> Search</button>
  </form>
</div>
</section>
<section class="section">
<div class="container">
  <?php if (!$q): ?>
  <div class="empty"><i class="fas fa-search"></i><p>Enter a keyword to search.</p></div>
  <?php elseif (!$list): ?>
  <div class="empty"><i class="fas fa-search"></i><p>No results found. Try different keywords.</p></div>
  <?php else: ?>
  <div class="search-list">
    <?php foreach ($list as $a): ?>
    <article class="search-item">
      <?php if ($a['featured_image']): ?>
      <a href="/article/<?= e($a['slug']) ?>" class="search-thumb"><img src="<?= e($a['featured_image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy"></a>
      <?php endif ?>
      <div class="search-body">
        <?php if ($a['cat_name']): ?><a href="/category/<?= e($a['cat_slug']) ?>" class="card-cat"><?= e($a['cat_name']) ?></a><?php endif ?>
        <h2><a href="/article/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h2>
        <?php if ($a['excerpt']): ?><p style="color:#6b7280"><?= e(mb_substr($a['excerpt'],0,200)) ?>...</p><?php endif ?>
        <div class="card-meta"><span><i class="fas fa-calendar-alt"></i> <?= fmt_date($a['published_at']) ?></span></div>
      </div>
    </article>
    <?php endforeach ?>
  </div>
  <?php if ($pg['pages']>1): ?>
  <nav class="pagination">
    <?php for($i=1;$i<=$pg['pages'];$i++): ?>
    <a href="/search?q=<?= urlencode($q) ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor ?>
  </nav>
  <?php endif ?>
  <?php endif ?>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
