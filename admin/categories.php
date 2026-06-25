<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

// Delete
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    $cnt = db_count("SELECT COUNT(*) FROM articles WHERE category_id=?",[(int)$_POST['delete_id']]);
    if ($cnt>0) { flash('error',"Cannot delete: $cnt article(s) use this category."); }
    else { db_query("DELETE FROM categories WHERE id=?",[(int)$_POST['delete_id']]); flash('success','Category deleted.'); }
    redirect('/admin/categories.php');
}

// Save (add/edit)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
    $edit_id = (int)($_POST['edit_id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $slug    = unique_slug('categories', make_slug($name), $edit_id);
    if (!$name) { flash('error','Name is required.'); redirect('/admin/categories.php'); }
    if ($edit_id) {
        db_query("UPDATE categories SET name=?,slug=?,description=? WHERE id=?",[$name,$slug,$desc,$edit_id]);
        flash('success','Category updated.');
    } else {
        db_insert("INSERT INTO categories(name,slug,description) VALUES(?,?,?)",[$name,$slug,$desc]);
        flash('success','Category added.');
    }
    redirect('/admin/categories.php');
}

$admin_title = 'Categories';
$cats = db_fetchAll("SELECT c.*, (SELECT COUNT(*) FROM articles a WHERE a.category_id=c.id) art_count FROM categories c ORDER BY c.name");
require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0">Categories</h1>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal" onclick="openAdd()"><i class="fas fa-plus"></i> New Category</button>
</div>

<div class="card-box">
  <table class="table table-hover align-middle">
    <thead class="table-light"><tr><th>Name</th><th>Slug</th><th>Description</th><th>Articles</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!$cats): ?><tr><td colspan="5" class="text-center text-muted py-4">No categories yet.</td></tr><?php endif ?>
      <?php foreach ($cats as $c): ?>
      <tr>
        <td><strong><?= e($c['name']) ?></strong></td>
        <td><code><?= e($c['slug']) ?></code></td>
        <td><small><?= e($c['description']) ?></small></td>
        <td><?= $c['art_count'] ?></td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="openEdit(<?= $c['id'] ?>,<?= htmlspecialchars(json_encode($c['name']),ENT_QUOTES) ?>,<?= htmlspecialchars(json_encode($c['description']),ENT_QUOTES) ?>)" data-bs-toggle="modal" data-bs-target="#catModal"><i class="fas fa-edit"></i></button>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete category?')">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id" value="0">
        <div class="mb-3">
          <label class="form-label fw-semibold">Name *</label>
          <input type="text" name="name" id="cat_name" class="form-control" required>
        </div>
        <div>
          <label class="form-label">Description</label>
          <textarea name="description" id="cat_desc" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="save" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>
<script>
function openAdd() {
  document.getElementById('modalTitle').textContent='New Category';
  document.getElementById('edit_id').value='0';
  document.getElementById('cat_name').value='';
  document.getElementById('cat_desc').value='';
}
function openEdit(id,name,desc) {
  document.getElementById('modalTitle').textContent='Edit Category';
  document.getElementById('edit_id').value=id;
  document.getElementById('cat_name').value=name;
  document.getElementById('cat_desc').value=desc;
}
</script>
<?php require __DIR__ . '/_layout_end.php'; ?>
