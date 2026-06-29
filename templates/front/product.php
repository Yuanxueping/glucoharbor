<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$slug = $parts[1] ?? '';
$p = db_fetch("SELECT p.*,pc.name cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE p.slug=? AND p.is_active=1",[$slug]);
if (!$p) { http_response_code(404); require ROOT_PATH.'/templates/front/404.php'; exit; }
$page_title = $p['meta_title'] ?: $p['name'];
$meta_desc  = $p['meta_description'] ?: $p['short_description'];
$og_image   = $p['image'];
$gallery    = json_decode($p['gallery'] ?? '[]', true) ?: [];
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="section">
<div class="container">
  <nav class="inner-breadcrumb breadcrumb"><a href="/">Home</a> <i class="fas fa-chevron-right fa-xs"></i> <a href="/products">Products</a> <i class="fas fa-chevron-right fa-xs"></i> <span><?= e($p['name']) ?></span></nav>

  <div class="prod-detail" style="margin-top:24px">
    <div>
      <?php
        $all_images = [];
        if ($p['image']) $all_images[] = $p['image'];
        foreach ($gallery as $gi) { if ($gi && $gi !== $p['image']) $all_images[] = $gi; }
      ?>
      <?php if ($all_images): ?>
      <img id="prod-main-img" src="<?= e($all_images[0]) ?>" alt="<?= e($p['name']) ?>" style="width:100%;border-radius:8px;object-fit:cover;max-height:400px">
      <?php if (count($all_images) > 1): ?>
      <div class="prod-gallery-thumbs">
        <?php foreach ($all_images as $gi): ?>
        <img src="<?= e($gi) ?>" alt="" onclick="document.getElementById('prod-main-img').src=this.src" class="gallery-thumb <?= $gi===$all_images[0]?'active':'' ?>">
        <?php endforeach ?>
      </div>
      <?php endif ?>
      <?php else: ?><div class="prod-placeholder lg"><i class="fas fa-box-open"></i></div><?php endif ?>
    </div>
    <div class="prod-detail-info">
      <?php if ($p['cat_name']): ?><span class="prod-cat"><?= e($p['cat_name']) ?></span><?php endif ?>
      <h1><?= e($p['name']) ?></h1>
      <?php if ($p['short_description']): ?><p style="color:#6b7280;font-size:16px;margin:12px 0 20px"><?= e($p['short_description']) ?></p><?php endif ?>
      <?php if ($p['price']): ?>
      <div class="price-lg">
        <?php if ($p['sale_price']): ?>
        <span class="price-big"><?= e($p['currency']?:'$') ?><?= number_format($p['sale_price'],2) ?></span>
        <span class="price-orig-big"><?= e($p['currency']?:'$') ?><?= number_format($p['price'],2) ?></span>
        <span class="discount">-<?= round((1-$p['sale_price']/$p['price'])*100) ?>%</span>
        <?php else: ?>
        <span class="price-big"><?= e($p['currency']?:'$') ?><?= number_format($p['price'],2) ?></span>
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
<style>
.prod-gallery-thumbs{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.gallery-thumb{width:70px;height:52px;object-fit:cover;border-radius:4px;cursor:pointer;border:2px solid transparent;opacity:.75;transition:.2s}
.gallery-thumb:hover,.gallery-thumb.active{border-color:#2563eb;opacity:1}
</style>
<script>
document.querySelectorAll('.gallery-thumb').forEach(function(t){
  t.addEventListener('click',function(){
    document.querySelectorAll('.gallery-thumb').forEach(function(x){x.classList.remove('active')});
    t.classList.add('active');
  });
});
</script>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
