<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$per    = (int)setting('products_per_page') ?: 12;
$page   = max(1,(int)($_GET['page']??1));
$cat_id = (int)($_GET['category']??0);
$where  = $cat_id ? "p.is_active=1 AND p.category_id=$cat_id" : "p.is_active=1";
$total  = db_count("SELECT COUNT(*) FROM products p WHERE $where");
$pg     = paginate($total,$per,$page);
$list   = db_fetchAll("SELECT p.*,pc.name cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE $where ORDER BY p.sort_order,p.created_at DESC LIMIT $per OFFSET {$pg['offset']}");
$pcats  = db_fetchAll("SELECT * FROM product_categories ORDER BY name");
$page_title='Health Products'; $meta_desc='Curated health products for blood sugar and blood pressure management.';
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="page-banner">
<div class="container">
  <nav class="breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <span>Products</span></nav>
  <h1>Health & Wellness Products</h1>
  <p>Curated products to support your health journey</p>
</div>
</section>
<section class="section">
<div class="container">
  <?php if ($pcats): ?>
  <div class="filter-bar">
    <a href="/products" class="filter-btn <?= !$cat_id?'active':'' ?>">All</a>
    <?php foreach ($pcats as $c): ?>
    <a href="/products?category=<?= $c['id'] ?>" class="filter-btn <?= $cat_id==$c['id']?'active':'' ?>"><?= e($c['name']) ?></a>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php if ($list): ?>
  <div class="product-grid">
    <?php foreach ($list as $p): ?>
    <div class="product-card">
      <a href="/product/<?= e($p['slug']) ?>" class="prod-img-link">
        <?php if ($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php else: ?><div class="prod-placeholder"><i class="fas fa-box-open"></i></div><?php endif ?>
        <?php if ($p['badge']): ?><span class="prod-badge"><?= e($p['badge']) ?></span><?php endif ?>
      </a>
      <div class="prod-body">
        <?php if ($p['cat_name']): ?><span class="prod-cat"><?= e($p['cat_name']) ?></span><?php endif ?>
        <h2><a href="/product/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h2>
        <?php if ($p['short_description']): ?><p><?= e(mb_substr($p['short_description'],0,100)) ?>...</p><?php endif ?>
        <?php if ($p['rating']): ?><?= star_rating($p['rating'], $p['review_count']) ?><?php endif ?>
        <div class="price-row">
          <?php if ($p['price']): ?>
          <?php if ($p['sale_price']): ?>
          <span class="price"><?= e($p['currency']?:'$') ?><?= number_format($p['sale_price'],2) ?></span>
          <span class="price-orig"><?= e($p['currency']?:'$') ?><?= number_format($p['price'],2) ?></span>
          <?php else: ?>
          <span class="price"><?= e($p['currency']?:'$') ?><?= number_format($p['price'],2) ?></span>
          <?php endif ?>
          <?php endif ?>
          <?php if ($p['affiliate_url']): ?>
          <a href="<?= e($p['affiliate_url']) ?>" target="_blank" rel="noopener sponsored" class="btn-buy"><i class="fab fa-amazon"></i> View on Amazon</a>
          <?php else: ?><a href="/product/<?= e($p['slug']) ?>" class="btn-buy">Learn More</a><?php endif ?>
        </div>
      </div>
    </div>
    <?php endforeach ?>
  </div>
  <?php if ($pg['pages']>1): ?>
  <nav class="pagination">
    <?php for($i=1;$i<=$pg['pages'];$i++): ?>
    <a href="/products?page=<?= $i ?><?= $cat_id?"&category=$cat_id":'' ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor ?>
  </nav>
  <?php endif ?>
  <?php else: ?><div class="empty"><i class="fas fa-box-open"></i><p>No products available yet.</p></div><?php endif ?>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
