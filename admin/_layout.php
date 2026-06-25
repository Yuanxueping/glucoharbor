<?php
// 后台公共布局 - 头部
$admin_title = ($admin_title ?? 'Dashboard') . ' — Admin';
$site_name   = setting('site_name') ?: 'GlucoHarbor';
$flash       = get_flash();
$cur         = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($admin_title) ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
:root{--sidebar:#0f172a;--primary:#1a56db;--bg:#f1f5f9}
body{background:var(--bg);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
.admin-wrap{display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--sidebar);flex-shrink:0;position:fixed;top:0;bottom:0;overflow-y:auto;transition:transform .3s;z-index:100}
.sidebar-brand{padding:20px;border-bottom:1px solid rgba(255,255,255,.08)}
.sidebar-brand .logo-row{display:flex;align-items:center;gap:8px}
.sidebar-brand .logo-row i{font-size:24px;color:#ef4444}
.sidebar-brand .logo-row span{font-size:18px;font-weight:800;color:#fff}
.sidebar-brand small{color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:1px;display:block;margin-top:4px}
.nav-section{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#475569;padding:14px 20px 4px}
.sidebar ul{list-style:none;padding:8px 0;margin:0}
.sidebar ul li a{display:flex;align-items:center;gap:10px;padding:9px 20px;color:#94a3b8;font-size:14px;font-weight:500;text-decoration:none;border-left:3px solid transparent;transition:all .15s}
.sidebar ul li a i{width:16px;text-align:center}
.sidebar ul li a:hover,.sidebar ul li a.active{background:rgba(255,255,255,.06);color:#fff;border-left-color:var(--primary)}
.sidebar ul li a.active{background:rgba(26,86,219,.2)}
.hr-div{height:1px;background:rgba(255,255,255,.06);margin:6px 0}
.admin-main{flex:1;margin-left:240px;display:flex;flex-direction:column}
.topbar{height:56px;background:#fff;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:50;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.topbar-left{display:flex;align-items:center;gap:12px}
.sidebar-toggle{background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;display:none}
.page-title{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:20px}
.card-box{background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:20px}
.card-box-title{font-size:16px;font-weight:700;margin-bottom:16px;color:#0f172a}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:12px;padding:20px;display:flex;align-items:center;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.stat-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0}
.stat-info h3{font-size:26px;font-weight:800;color:#0f172a}
.stat-info p{font-size:13px;color:#64748b;margin:0}
.badge-published{background:#dcfce7;color:#16a34a}
.badge-draft{background:#fef9c3;color:#a16207}
.badge-archived,.badge-inactive{background:#f1f5f9;color:#64748b}
.badge-active{background:#dcfce7;color:#16a34a}
.source-manual{background:#eff6ff;color:#1d4ed8}
.source-api{background:#fef3c7;color:#b45309}
.source-ai{background:#f0fdf4;color:#15803d}
.quick-actions{display:flex;gap:10px;flex-wrap:wrap}
.quick-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s}
.quick-btn:hover{background:var(--primary);border-color:var(--primary);color:#fff}
.status-badge{display:inline-block;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:capitalize}
@media(max-width:768px){.sidebar-toggle{display:block}.sidebar{transform:translateX(-100%)}.sidebar.open{transform:none}.admin-main{margin-left:0}.stat-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="admin-wrap">
<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="logo-row"><i class="fas fa-heartbeat"></i><span><?= e($site_name) ?></span></div>
    <small>Admin Panel</small>
  </div>
  <ul>
    <li class="nav-section">Main</li>
    <li><a href="/admin/" class="<?= $cur==='/admin/'||$cur==='/admin/index.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li class="nav-section">Content</li>
    <li><a href="/admin/articles.php" class="<?= strpos($cur,'/admin/article')===0?'active':'' ?>"><i class="fas fa-newspaper"></i> Articles</a></li>
    <li><a href="/admin/categories.php" class="<?= strpos($cur,'/admin/categor')===0?'active':'' ?>"><i class="fas fa-folder"></i> Categories</a></li>
    <li><a href="/admin/pages.php" class="<?= strpos($cur,'/admin/pages')===0?'active':'' ?>"><i class="fas fa-file-alt"></i> Pages</a></li>
    <li class="nav-section">Shop</li>
    <li><a href="/admin/products.php" class="<?= strpos($cur,'/admin/product')===0?'active':'' ?>"><i class="fas fa-box-open"></i> Products</a></li>
    <li class="nav-section">Config</li>
    <li><a href="/admin/settings.php" class="<?= strpos($cur,'/admin/settings')===0?'active':'' ?>"><i class="fas fa-cog"></i> Settings</a></li>
    <li><div class="hr-div"></div></li>
    <li><a href="/" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
    <li><a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</nav>
<div class="admin-main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <span style="font-weight:700;color:#0f172a"><?= e($admin_title) ?></span>
    </div>
    <div><i class="fas fa-user-circle text-muted me-1"></i><small><?= e($_SESSION['admin_username']??'') ?></small></div>
  </header>
  <div class="p-4">
  <?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"><i class="fas fa-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?> me-1"></i><?= e($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif ?>
