<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    db_query("DELETE FROM pages WHERE id=?",[(int)$_POST['delete_id']]);
    flash('success','Page deleted.');
    redirect('/admin/pages.php');
}

$admin_title = 'Pages';
$pages = db_fetchAll("SELECT * FROM pages ORDER BY title");
require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0">Pages</h1>
  <a href="/admin/page-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Page</a>
</div>

<div class="card-box">
  <table class="table table-hover align-middle">
    <thead class="table-light"><tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!$pages): ?><tr><td colspan="5" class="text-center text-muted py-4">No pages yet.</td></tr><?php endif ?>
      <?php foreach ($pages as $pg): ?>
      <tr>
        <td><a href="/admin/page-edit.php?id=<?= $pg['id'] ?>"><?= e($pg['title']) ?></a></td>
        <td><code><?= e($pg['slug']) ?></code></td>
        <td><span class="badge <?= $pg['status']==='published'?'bg-success':'bg-secondary' ?>"><?= $pg['status'] ?></span></td>
        <td><small><?= date('Y-m-d',strtotime($pg['updated_at']??$pg['created_at'])) ?></small></td>
        <td>
          <div class="btn-group btn-group-sm">
            <a href="/admin/page-edit.php?id=<?= $pg['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
            <a href="/page/<?= e($pg['slug']) ?>" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-eye"></i></a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
              <input type="hidden" name="delete_id" value="<?= $pg['id'] ?>">
              <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>
