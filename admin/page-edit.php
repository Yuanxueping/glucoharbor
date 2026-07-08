<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$pg = $id ? db_fetch("SELECT * FROM pages WHERE id=?", [$id]) : null;
if ($id && !$pg) { flash('error','Page not found.'); redirect('/admin/pages.php'); }

$admin_title = $pg ? 'Edit Page' : 'New Page';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = $_POST['content'] ?? '';
    $status   = $_POST['status'] === 'published' ? 'published' : 'draft';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc  = trim($_POST['meta_desc'] ?? '');
    if (!$title) { $error = 'Title is required.'; goto render; }
    $slug = unique_slug('pages', make_slug($title), $id);
    if ($pg) {
        db_query("UPDATE pages SET title=?,slug=?,content=?,status=?,meta_title=?,meta_description=?,updated_at=NOW() WHERE id=?",
            [$title,$slug,$content,$status,$meta_title,$meta_desc,$id]);
        flash('success','Page updated.');
    } else {
        $new_id = db_insert("INSERT INTO pages(title,slug,content,status,meta_title,meta_description) VALUES(?,?,?,?,?,?)",
            [$title,$slug,$content,$status,$meta_title,$meta_desc]);
        flash('success','Page created.');
        redirect('/admin/page-edit.php?id='.$new_id);
    }
    redirect('/admin/pages.php');
}

render:
require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0"><?= $admin_title ?></h1>
  <a href="/admin/pages.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= e($error) ?></div>
<?php endif ?>

<form method="POST">
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-box mb-3">
      <div class="mb-3">
        <label class="form-label fw-semibold">Title *</label>
        <input type="text" name="title" class="form-control form-control-lg" value="<?= e($pg['title'] ?? '') ?>" required>
      </div>
      <div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label fw-semibold mb-0">Content</label>
          <button type="button" id="toggle-source" class="btn btn-sm btn-outline-secondary">Source Code</button>
        </div>
        <div id="editor" style="height:420px;border:1px solid #dee2e6;border-radius:6px"></div>
        <textarea name="content" id="content-input" hidden></textarea>
        <textarea id="source-editor" class="form-control font-monospace" style="height:420px;display:none;font-size:13px;resize:vertical" spellcheck="false"></textarea>
        <script>window.__articleContent = <?= json_encode($pg['content'] ?? '') ?>;</script>
      </div>
    </div>

    <div class="card-box">
      <div class="card-box-title"><i class="fas fa-search"></i> SEO</div>
      <div class="mb-3">
        <label class="form-label">Meta Title</label>
        <input type="text" name="meta_title" class="form-control" value="<?= e($pg['meta_title'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Meta Description</label>
        <textarea name="meta_desc" class="form-control" rows="2"><?= e($pg['meta_description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-box">
      <div class="card-box-title">Publish</div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select">
          <option value="draft" <?= ($pg['status']??'draft')==='draft'?'selected':'' ?>>Draft</option>
          <option value="published" <?= ($pg['status']??'')==='published'?'selected':'' ?>>Published</option>
        </select>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Page</button>
        <?php if ($pg): ?>
        <a href="/page/<?= e($pg['slug']) ?>" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-eye"></i> View Page</a>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
</form>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [[{header:[1,2,3,false]}],['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['blockquote'],['link'],['clean']] }
});
if (window.__articleContent) {
  quill.root.innerHTML = window.__articleContent;
}
var sourceMode = false;
var srcEl = document.getElementById('source-editor');
var editorEl = document.getElementById('editor');
document.getElementById('toggle-source').addEventListener('click', function() {
  sourceMode = !sourceMode;
  if (sourceMode) {
    srcEl.value = quill.root.innerHTML;
    editorEl.style.display = 'none';
    srcEl.style.display = 'block';
    this.textContent = 'Visual Editor';
    this.classList.replace('btn-outline-secondary','btn-outline-primary');
  } else {
    quill.root.innerHTML = srcEl.value;
    srcEl.style.display = 'none';
    editorEl.style.display = 'block';
    this.textContent = 'Source Code';
    this.classList.replace('btn-outline-primary','btn-outline-secondary');
  }
});
document.querySelector('form').addEventListener('submit', function() {
  if (sourceMode) { quill.root.innerHTML = srcEl.value; }
  document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>
<?php require __DIR__ . '/_layout_end.php'; ?>
