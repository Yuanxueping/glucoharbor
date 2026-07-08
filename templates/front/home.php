<?php
require_once ROOT_PATH . '/includes/bootstrap.php';
$per_page = (int)setting('articles_per_page') ?: 9;
$featured = db_fetchAll("SELECT a.*,c.name cat_name,c.slug cat_slug FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.status='published' AND a.is_featured=1 ORDER BY a.published_at DESC LIMIT 5");
$recent   = db_fetchAll("SELECT a.*,c.name cat_name,c.slug cat_slug FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.status='published' ORDER BY a.published_at DESC LIMIT $per_page");
$products = db_fetchAll("SELECT p.*,pc.name cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE p.is_active=1 ORDER BY p.sort_order,p.created_at DESC LIMIT 4");
$cats     = nav_categories();

$page_title = setting('site_tagline') ?: 'Health Information';
$meta_desc  = setting('site_description');
require ROOT_PATH . '/templates/front/_head.php';
?>

<?php if ($featured): $f = $featured[0]; ?>
<section class="hero">
<div class="container">
<div class="hero-grid">
  <div>
    <a href="/article/<?= e($f['slug']) ?>" class="hero-card">
      <?php if ($f['featured_image']): ?>
      <div class="hero-img" style="background-image:url('<?= e($f['featured_image']) ?>')"></div>
      <?php else: ?><div class="hero-placeholder"></div><?php endif ?>
      <div class="hero-body">
        <?php if ($f['cat_name']): ?><span class="badge"><?= e($f['cat_name']) ?></span><?php endif ?>
        <h1 class="hero-title"><?= e($f['title']) ?></h1>
        <?php if ($f['excerpt']): ?><p class="hero-excerpt"><?= e(mb_substr($f['excerpt'],0,150)) ?>...</p><?php endif ?>
        <span class="read-more">Read Article <i class="fas fa-arrow-right"></i></span>
      </div>
    </a>
  </div>
  <div class="hero-side">
    <?php foreach (array_slice($featured,1,3) as $a): ?>
    <a href="/article/<?= e($a['slug']) ?>" class="hero-side-card">
      <?php if ($a['featured_image']): ?>
      <div class="side-img" style="background-image:url('<?= e($a['featured_image']) ?>')"></div>
      <?php else: ?><div class="side-img side-placeholder"></div><?php endif ?>
      <div class="side-body">
        <?php if ($a['cat_name']): ?><span class="badge badge-sm"><?= e($a['cat_name']) ?></span><?php endif ?>
        <h3><?= e($a['title']) ?></h3>
      </div>
    </a>
    <?php endforeach ?>
  </div>
</div>
</div>
</section>
<?php endif ?>

<section class="cat-bar">
<div class="container">
  <div class="cat-list">
    <?php foreach ($cats as $c): ?>
    <?php $cnt = db_count("SELECT COUNT(*) FROM articles WHERE category_id=? AND status='published'",[$c['id']]); ?>
    <a href="/category/<?= e($c['slug']) ?>" class="cat-chip">
      <?= e($c['name']) ?>
      <?php if ($cnt): ?><span class="cat-count"><?= $cnt ?></span><?php endif ?>
    </a>
    <?php endforeach ?>
  </div>
</div>
</section>

<?php ad_slot('ad_header_code') ?>

<section class="section">
<div class="container">
  <div class="section-header">
    <h2>Latest Health Articles</h2>
    <a href="/articles" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
  </div>
  <?php if ($recent): ?>
  <div class="article-grid">
    <?php foreach ($recent as $a): ?>
    <article class="article-card">
      <a href="/article/<?= e($a['slug']) ?>" class="card-img-link">
        <?php if ($a['featured_image']): ?>
        <img src="<?= e($a['featured_image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
        <?php else: ?><div class="card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif ?>
      </a>
      <div class="card-body">
        <?php if ($a['cat_name']): ?><a href="/category/<?= e($a['cat_slug']) ?>" class="card-cat"><?= e($a['cat_name']) ?></a><?php endif ?>
        <h3><a href="/article/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h3>
        <?php if ($a['excerpt']): ?><p class="card-excerpt"><?= e(mb_substr($a['excerpt'],0,100)) ?>...</p><?php endif ?>
        <div class="card-meta">
          <span><i class="fas fa-calendar-alt"></i> <?= fmt_date($a['published_at']) ?></span>
          <span><i class="fas fa-eye"></i> <?= $a['views'] ?></span>
        </div>
      </div>
    </article>
    <?php endforeach ?>
  </div>
  <?php else: ?>
  <div class="empty"><i class="fas fa-newspaper"></i><p>No articles yet. <a href="/admin/login.php">Add some →</a></p></div>
  <?php endif ?>
</div>
</section>

<?php ad_slot('ad_sidebar_code') ?>

<?php if ($products): ?>
<section class="section section-alt">
<div class="container">
  <div class="section-header">
    <h2>Recommended Products</h2>
    <a href="/products" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
  </div>
  <div class="product-grid">
    <?php foreach ($products as $p): ?>
    <div class="product-card">
      <a href="/product/<?= e($p['slug']) ?>" class="prod-img-link">
        <?php if ($p['image']): ?>
        <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php else: ?><div class="prod-placeholder"><i class="fas fa-box-open"></i></div><?php endif ?>
        <?php if ($p['badge']): ?><span class="prod-badge"><?= e($p['badge']) ?></span><?php endif ?>
      </a>
      <div class="prod-body">
        <?php if ($p['cat_name']): ?><span class="prod-cat"><?= e($p['cat_name']) ?></span><?php endif ?>
        <h3><a href="/product/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
        <?php if ($p['short_description']): ?><p><?= e(mb_substr($p['short_description'],0,80)) ?>...</p><?php endif ?>
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
          <?php else: ?>
          <a href="/product/<?= e($p['slug']) ?>" class="btn-buy">Details</a>
          <?php endif ?>
        </div>
      </div>
    </div>
    <?php endforeach ?>
  </div>
</div>
</section>
<?php endif ?>

<section class="faq-section">
<div class="container">
  <div class="section-header">
    <h2>Frequently Asked Questions</h2>
  </div>
  <div class="faq-grid">
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">Is the information on GlucoHarbor written by medical professionals?</button>
      <div class="faq-a">
        <p>Our content is researched and written by health writers with expertise in diabetes and metabolic health, reviewed against current clinical guidelines from the ADA, WHO, and NIH. All medical claims are cross-referenced with peer-reviewed literature before publication. See our <a href="/page/our-editorial-team">Editorial Team</a> for more details.</p>
      </div>
    </div>
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">Can I use GlucoHarbor content as medical advice?</button>
      <div class="faq-a">
        <p>No. All content on GlucoHarbor is for informational and educational purposes only. It does not replace the advice, diagnosis, or treatment of a licensed healthcare provider. Always consult your doctor or diabetes care specialist before making changes to your treatment plan.</p>
      </div>
    </div>
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">How are products selected and reviewed?</button>
      <div class="faq-a">
        <p>Products are selected based on accuracy ratings, clinical utility, user reviews, and price. We do not accept payment to include or rank products. Some links are affiliate links — we may earn a commission if you purchase through them, at no extra cost to you. This never influences our recommendations.</p>
      </div>
    </div>
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">How often is the content updated?</button>
      <div class="faq-a">
        <p>We publish new articles regularly and review existing content on an ongoing basis. Articles covering drug approvals, ADA guidelines, and device updates are prioritized for annual review or sooner when major changes occur. Each article displays its last updated date.</p>
      </div>
    </div>
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">Does GlucoHarbor accept sponsored content?</button>
      <div class="faq-a">
        <p>We do accept sponsored articles, which are always clearly labeled as "Sponsored." Sponsors have no influence over our editorial articles, clinical accuracy standards, or product rankings. Editorial and commercial decisions are kept strictly separate.</p>
      </div>
    </div>
    <div class="faq-item">
      <button class="faq-q" aria-expanded="false">How can I report an error or suggest a topic?</button>
      <div class="faq-a">
        <p>We welcome reader feedback. To report a factual error or suggest a topic, email us at <a href="mailto:editorial@glucoharbor.com">editorial@glucoharbor.com</a>. Please include the article URL and a brief description. We aim to respond within 5 business days. Learn more about our <a href="/page/editorial-policy">Editorial Policy</a>.</p>
      </div>
    </div>
  </div>
</div>
</section>

<style>
.faq-section{padding:60px 0;background:#f8faff}
.faq-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:8px}
@media(max-width:700px){.faq-grid{grid-template-columns:1fr}}
.faq-item{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
.faq-q{width:100%;background:none;border:none;text-align:left;padding:18px 20px;font-size:15px;font-weight:600;color:#1a1a2e;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:12px;line-height:1.4}
.faq-q::after{content:'+';font-size:20px;font-weight:400;color:#1a56db;flex-shrink:0;transition:transform .25s}
.faq-q[aria-expanded="true"]::after{transform:rotate(45deg)}
.faq-a{display:none;padding:0 20px 18px;font-size:14.5px;color:#4a5568;line-height:1.7}
.faq-a p{margin:0}
.faq-a a{color:#1a56db}
.faq-item.open .faq-a{display:block}
</style>
<script>
document.querySelectorAll('.faq-q').forEach(function(btn){
  btn.addEventListener('click',function(){
    var item=this.closest('.faq-item');
    var open=item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(function(i){i.classList.remove('open');i.querySelector('.faq-q').setAttribute('aria-expanded','false');});
    if(!open){item.classList.add('open');this.setAttribute('aria-expanded','true');}
  });
});
</script>

<?php require ROOT_PATH . '/templates/front/_foot.php'; ?>
