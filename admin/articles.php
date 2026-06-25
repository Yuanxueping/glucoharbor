<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

// 删除
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    db_query("DELETE FROM articles WHERE id=?",[(int)$_POST['delete_id']]);
    flash('success','Article deleted.');
    redirect('/admin/articles.php');
}

$admin_title = 'Articles';
$per    = 20;
$page   = max(1,(int)($_GET['page']??1));
$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$where  = ['1=1']; $params = [];
if ($status) { $where[] = 'a.status=?'; $params[] = $status; }
if ($search) { $where[] = 'a.title LIKE ?'; $params[] = "%$search%"; }
$w     = implode(' AND ', $where);
$total = db_count("SELECT COUNT(*) FROM articles a WHERE $w", $params);
$pg    = paginate($total,$per,$page);
$list  = db_fetchAll("SELECT a.*,c.name cat_name FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE $w ORDER BY a.created_at DESC LIMIT $per OFFSET {$pg['offset']}", $params);

require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0">Articles <small class="text-muted fs-6">(<?= $total ?>)</small></h1>
  <a href="/admin/article-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Article</a>
</div>

<div class="card-box">
  <form method="GET" class="d-flex gap-2 flex-wrap mb-3">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search title..." class="form-control" style="max-width:280px">
    <select name="status" class="form-select" style="width:auto">
      <option value="">All Status</option>
      <?php foreach (['published','draft','archived'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach ?>
    </select>
    <button class="btn btn-outline-primary"><i class="fas fa-search"></i> Filter</button>
    <a href="/admin/articles.php" class="btn btn-outline-secondary">Reset</a>
  </form>

  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-light"><tr><th>Title</th><th>Category</th><th>Status</th><th>Source</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!$list): ?><tr><td colspan="7" class="text-center text-muted py-4">No articles found.</td></tr><?php endif ?>
      <?php foreach ($list as $a): ?>
      <tr>
        <td>
          <?php if ($a['is_featured']): ?><i class="fas fa-star text-warning me-1"></i><?php endif ?>
          <a href="/admin/article-edit.php?id=<?= $a['id'] ?>"><?= e(mb_substr($a['title'],0,65)) ?><?= mb_strlen($a['title'])>65?'...':'' ?></a>
        </td>
        <td><small><?= e($a['cat_name']??'-') ?></small></td>
        <td><span class="badge status-badge badge-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
        <td><span class="badge source-<?= $a['source'] ?>"><?= $a['source'] ?></span></td>
        <td><?= $a['views'] ?></td>
        <td><small><?= $a['published_at'] ? date('Y-m-d',strtotime($a['published_at'])) : date('Y-m-d',strtotime($a['created_at'])) ?></small></td>
        <td>
          <div class="btn-group btn-group-sm">
            <a href="/admin/article-edit.php?id=<?= $a['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
            <a href="/article/<?= e($a['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this article?')">
              <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
              <button class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  </div>

  <?php if ($pg['pages']>1): ?>
  <nav class="d-flex justify-content-center mt-3">
    <ul class="pagination">
      <?php for($i=1;$i<=$pg['pages'];$i++): ?>
      <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="/admin/articles.php?page=<?= $i ?>&status=<?= urlencode($status) ?>&q=<?= urlencode($search) ?>"><?= $i ?></a></li>
      <?php endfor ?>
    </ul>
  </nav>
  <?php endif ?>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
