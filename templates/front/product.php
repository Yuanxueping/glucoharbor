<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$slug = $parts[1] ?? '';
$p = db_fetch("SELECT p.*,pc.name cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE p.slug=? AND p.status='active'",[$slug]);
if (!$p) { http_response_code(404); require ROOT_PATH.'/templates/front/404.php'; exit; }
$page_title = $p['meta_title'] ?: $p['name'];
$meta_desc  = $p['meta_description'] ?: $p['short_desc'];
$og_image   = $p['image'];
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="section">
<div class="container">
  <nav class="inner-breadcrumb breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <a href="/products">Products</a> <i class="fas fa-chevron-right fa-xs"></i> <span><?= e($p['name']) ?></span></nav>

  <div class="prod-detail" style="margin-top:24px">
    <div>
      <?php if ($p['image']): ?>
      <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
      <?php else: ?><div class="prod-placeholder lg"><i class="fas fa-box-open"></i></div><?php endif ?>
    </div>
    <div class="prod-detail-info">
      <?php if ($p['cat_name']): ?><span class="prod-cat"><?= e($p['cat_name']) ?></span><?php endif ?>
      <h1><?= e($p['name']) ?></h1>
      <?php if ($p['short_desc']): ?><p style="color:#6b7280;font-size:16px;margin:12px 0 20px"><?= e($p['short_desc']) ?></p><?php endif ?>
      <?php if ($p['price']): ?>
      <div class="price-lg">
        <span class="price-big"><?= e($p['currency']?:'$') ?><?= number_format($p['price'],2) ?></span>
        <?php if ($p['original_price']): ?>
        <span class="price-orig-big"><?= e($p['currency']?:'$') ?><?= number_format($p['original_price'],2) ?></span>
        <span class="discount">-<?= round((1-$p['price']/$p['original_price'])*100) ?>%</span>
        <?php endif ?>
      </div>
      <?php endif ?>
      <?php if ($p['affiliate_url']): ?>
      <a href="<?= e($p['affiliate_url']) ?>" target="_blank" rel="noopener sponsored" class="btn-buy-lg">
        <i class="fas fa-shopping-cart"></i> Buy Now
      </a>
      <?php endif ?>
    </div>
  </div>

  <?php if ($p['description']): ?>
  <div style="margin-top:40px;padding-top:32px;border-top:1px solid #e5e7eb">
    <h2 style="font-size:22px;font-weight:700;margin-bottom:16px">Product Description</h2>
    <div class="article-body"><?= $p['description'] ?></div>
  </div>
  <?php endif ?>
</div>
</section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
