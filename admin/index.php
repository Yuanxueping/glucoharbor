<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$admin_title = 'Dashboard';
$articles_total  = db_count("SELECT COUNT(*) FROM articles");
$published_total = db_count("SELECT COUNT(*) FROM articles WHERE status='published'");
$categories_total= db_count("SELECT COUNT(*) FROM categories");
$products_total  = db_count("SELECT COUNT(*) FROM products");
$recent = db_fetchAll("SELECT a.*,c.name cat_name FROM articles a LEFT JOIN categories c ON a.category_id=c.id ORDER BY a.created_at DESC LIMIT 8");
require __DIR__ . '/_layout.php';
?>

<div class="stat-grid">
  <div class="stat-card"><div class="stat-icon" style="background:#1a56db"><i class="fas fa-newspaper"></i></div><div class="stat-info"><h3><?= $articles_total ?></h3><p>Total Articles</p></div></div>
  <div class="stat-card"><div class="stat-icon" style="background:#059669"><i class="fas fa-check-circle"></i></div><div class="stat-info"><h3><?= $published_total ?></h3><p>Published</p></div></div>
  <div class="stat-card"><div class="stat-icon" style="background:#d97706"><i class="fas fa-folder"></i></div><div class="stat-info"><h3><?= $categories_total ?></h3><p>Categories</p></div></div>
  <div class="stat-card"><div class="stat-icon" style="background:#7c3aed"><i class="fas fa-box-open"></i></div><div class="stat-info"><h3><?= $products_total ?></h3><p>Products</p></div></div>
</div>

<div class="card-box">
  <div class="card-box-title">Quick Actions</div>
  <div class="quick-actions">
    <a href="/admin/article-edit.php" class="quick-btn"><i class="fas fa-plus"></i> New Article</a>
    <a href="/admin/categories.php" class="quick-btn"><i class="fas fa-folder-plus"></i> Categories</a>
    <a href="/admin/product-edit.php" class="quick-btn"><i class="fas fa-box"></i> New Product</a>
    <a href="/admin/pages.php" class="quick-btn"><i class="fas fa-file-plus"></i> Pages</a>
    <a href="/admin/settings.php" class="quick-btn"><i class="fas fa-cog"></i> Settings</a>
  </div>
</div>

<div class="card-box">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="card-box-title mb-0">Recent Articles</div>
    <a href="/admin/articles.php" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Title</th><th>Status</th><th>Source</th><th>Views</th><th>Date</th><th></th></tr></thead>
    <tbody>
      <?php if (!$recent): ?><tr><td colspan="6" class="text-center text-muted py-3">No articles yet.</td></tr><?php endif ?>
      <?php foreach ($recent as $a): ?>
      <tr>
        <td><a href="/admin/article-edit.php?id=<?= $a['id'] ?>"><?= e(mb_substr($a['title'],0,60)) ?><?= mb_strlen($a['title'])>60?'...':'' ?></a></td>
        <td><span class="badge status-badge badge-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
        <td><span class="badge source-<?= $a['source'] ?>"><?= $a['source'] ?></span></td>
        <td><?= $a['views'] ?></td>
        <td><small><?= $a['published_at'] ? date('M j,Y',strtotime($a['published_at'])) : date('M j,Y',strtotime($a['created_at'])) ?></small></td>
        <td><a href="/article/<?= e($a['slug']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary btn-sm"><i class="fas fa-eye"></i></a></td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card-box">
  <div class="card-box-title"><i class="fas fa-plug"></i> n8n / DeepSeek API Integration</div>
  <p class="text-muted small mb-3">Use the API endpoint below to import AI-generated articles from n8n workflows connected to DeepSeek:</p>
  <div style="background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;font-family:monospace;font-size:13px;line-height:1.8">
    POST <?= e(setting('site_url')) ?>/api/articles/import<br>
    Header: x-api-token: <em>[Generate in Settings → API]</em><br><br>
    Body (JSON):<br>
    {<br>
    &nbsp;&nbsp;"title": "Article Title",<br>
    &nbsp;&nbsp;"content": "&lt;p&gt;HTML content...&lt;/p&gt;",<br>
    &nbsp;&nbsp;"excerpt": "Brief summary",<br>
    &nbsp;&nbsp;"category_slug": "diabetes-management",<br>
    &nbsp;&nbsp;"featured_image": "https://...",<br>
    &nbsp;&nbsp;"status": "published",<br>
    &nbsp;&nbsp;"source": "ai"<br>
    }
  </div>
  <a href="/admin/settings.php#api" class="btn btn-sm btn-primary mt-3"><i class="fas fa-key"></i> Manage API Token</a>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
