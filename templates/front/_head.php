<?php
// 公共头部
$site_name  = setting('site_name') ?: 'GlucoHarbor';
$site_url   = setting('site_url')  ?: 'https://glucoharbor.com';
$page_title = isset($page_title) ? $page_title . ' | ' . $site_name : $site_name;
$meta_desc  = $meta_desc  ?? setting('site_description');
$meta_keys  = $meta_keys  ?? '';
$og_image   = $og_image   ?? '';
$canonical  = $site_url . $_SERVER['REQUEST_URI'];
$cats       = nav_categories();
$fpages     = footer_pages();
$cur_path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($meta_desc) ?>">
<?php if ($meta_keys): ?><meta name="keywords" content="<?= e($meta_keys) ?>"><?php endif ?>
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($meta_desc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($og_image): ?><meta property="og:image" content="<?= e($og_image) ?>"><?php endif ?>
<?php $favicon = setting('site_favicon'); if ($favicon): ?>
<link rel="icon" href="<?= e($favicon) ?>"><?php endif ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
<?php $ga = setting('ga_tracking_id'); if ($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif ?>
<?php if (setting('adsense_enabled')==='1' && setting('adsense_publisher_id')): ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e(setting('adsense_publisher_id')) ?>" crossorigin="anonymous"></script>
<?php endif ?>
<?php if ($cur_path === '/'): ?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebSite","name":"<?= addslashes(e($site_name)) ?>","url":"<?= e($site_url) ?>","description":"<?= addslashes(e(setting('site_description'))) ?>","potentialAction":{"@type":"SearchAction","target":{"@type":"EntryPoint","urlTemplate":"<?= e($site_url) ?>/search?q={search_term_string}"},"query-input":"required name=search_term_string"}}
</script>
<?php endif ?>
</head>
<body>
<header class="site-header">
<div class="container">
<div class="header-inner">
  <div class="logo">
    <a href="/">
      <?php $logo = setting('site_logo'); if ($logo): ?>
      <img src="<?= e($logo) ?>" alt="<?= e($site_name) ?>">
      <?php else: ?>
      <span class="heart"><i class="fas fa-heartbeat"></i></span> <?= e($site_name) ?>
      <?php endif ?>
    </a>
  </div>
  <nav class="main-nav">
    <button class="nav-toggle" id="navToggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
    <ul class="nav-list" id="navList">
      <li><a href="/" <?= $cur_path==='/'?'class="active"':'' ?>>Home</a></li>
      <li class="has-dropdown">
        <a href="/articles" <?= strpos($cur_path,'/article')===0||strpos($cur_path,'/category')===0?'class="active"':'' ?>>Articles <i class="fas fa-chevron-down fa-xs"></i></a>
        <ul class="dropdown">
          <li><a href="/articles">All Articles</a></li>
          <?php foreach ($cats as $c): ?>
          <li><a href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach ?>
        </ul>
      </li>
      <li><a href="/products" <?= strpos($cur_path,'/product')===0?'class="active"':'' ?>>Products</a></li>
      <li class="nav-search-mobile">
        <form action="/search" method="get" role="search">
          <input type="text" name="q" placeholder="Search..." aria-label="Search" value="<?= e($_GET['q']??'') ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
      </li>
    </ul>
  </nav>
  <div class="header-search">
    <form action="/search" method="get" role="search">
      <input type="text" name="q" placeholder="Search..." aria-label="Search" value="<?= e($_GET['q']??'') ?>">
      <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
    </form>
  </div>
</div>
</div>
</header>
<main>
