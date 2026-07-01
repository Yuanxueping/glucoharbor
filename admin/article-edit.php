<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$a  = $id ? db_fetch("SELECT * FROM articles WHERE id=?", [$id]) : null;
if ($id && !$a) { flash('error','Article not found.'); redirect('/admin/articles.php'); }

$categories = db_fetchAll("SELECT * FROM categories ORDER BY name");
$admin_title = $a ? 'Edit Article' : 'New Article';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $content     = $_POST['content'] ?? '';
    // If Quill submitted empty/whitespace-only content, keep existing content
    if ($a && trim(strip_tags($content)) === '') {
        $content = $a['content'];
    }
    $excerpt     = trim($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $status      = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $source      = in_array($_POST['source'] ?? '', ['manual','api','ai']) ? $_POST['source'] : 'manual';
    $meta_title  = trim($_POST['meta_title'] ?? '');
    $meta_desc   = trim($_POST['meta_desc'] ?? '');
    $meta_kw     = trim($_POST['meta_keywords'] ?? '');
    $pub_at      = trim($_POST['published_at'] ?? '') ?: null;

    if (!$title) { $error = 'Title is required.'; goto render; }

    $slug = unique_slug('articles', make_slug($title), $id);

    $featured_image = $a['featured_image'] ?? '';
    if (!empty($_FILES['featured_image']['name'])) {
        $up = upload_image('featured_image');
        if ($up['error']) { $error = $up['error']; goto render; }
        $featured_image = $up['url'];
    } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        $featured_image = '';
    }

    if ($a) {
        db_query("UPDATE articles SET title=?,slug=?,content=?,excerpt=?,category_id=?,featured_image=?,status=?,is_featured=?,source=?,meta_title=?,meta_description=?,meta_keywords=?,published_at=? WHERE id=?",
            [$title,$slug,$content,$excerpt,$category_id,$featured_image,$status,$is_featured,$source,$meta_title,$meta_desc,$meta_kw,$pub_at,$id]);
        if ($status === 'published') indexnow_ping(setting('site_url') . '/article/' . $slug);
        flash('success','Article updated.');
    } else {
        $new_id = db_insert("INSERT INTO articles(title,slug,content,excerpt,category_id,featured_image,status,is_featured,source,meta_title,meta_description,meta_keywords,published_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$title,$slug,$content,$excerpt,$category_id,$featured_image,$status,$is_featured,$source,$meta_title,$meta_desc,$meta_kw,$pub_at]);
        if ($status === 'published') indexnow_ping(setting('site_url') . '/article/' . $slug);
        flash('success','Article created.');
        redirect('/admin/article-edit.php?id='.$new_id);
    }
    redirect('/admin/articles.php');
}

render:
require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0"><?= $admin_title ?></h1>
  <a href="/admin/articles.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= e($error) ?></div>
<?php endif ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
  <!-- Main column -->
  <div class="col-lg-8">
    <div class="card-box mb-3">
      <div class="mb-3">
        <label class="form-label fw-semibold">Title *</label>
        <input type="text" name="title" class="form-control form-control-lg" value="<?= e($a['title'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Excerpt</label>
        <textarea name="excerpt" class="form-control" rows="2" placeholder="Brief summary shown in article lists..."><?= e($a['excerpt'] ?? '') ?></textarea>
      </div>
      <div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label fw-semibold mb-0">Content *</label>
          <button type="button" id="toggle-source" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-code"></i> Source HTML
          </button>
        </div>
        <div id="editor" style="height:480px;border:1px solid #dee2e6;border-radius:6px"></div>
        <textarea id="source-editor" class="form-control font-monospace" rows="20"
          style="display:none;font-size:12px;height:480px;resize:vertical"></textarea>
        <textarea name="content" id="content-input" hidden></textarea>
        <script>window.__articleContent = <?= json_encode($a['content'] ?? '') ?>;</script>
      </div>
    </div>

    <!-- SEO -->
    <div class="card-box">
      <div class="card-box-title"><i class="fas fa-search"></i> SEO</div>
      <div class="mb-3">
        <label class="form-label">Meta Title <small class="text-muted">(leave blank to use article title)</small></label>
        <input type="text" name="meta_title" class="form-control" value="<?= e($a['meta_title'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Meta Description</label>
        <textarea name="meta_desc" class="form-control" rows="2"><?= e($a['meta_description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="form-label">Meta Keywords</label>
        <input type="text" name="meta_keywords" class="form-control" value="<?= e($a['meta_keywords'] ?? '') ?>" placeholder="keyword1, keyword2">
      </div>
    </div>
  </div>

  <!-- Sidebar column -->
  <div class="col-lg-4">
    <div class="card-box mb-3">
      <div class="card-box-title">Publish</div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['draft'=>'Draft','published'=>'Published','archived'=>'Archived'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($a['status']??'draft')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Source</label>
        <select name="source" class="form-select">
          <?php foreach (['manual'=>'Manual','api'=>'API','ai'=>'AI'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($a['source']??'manual')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Publish Date</label>
        <input type="datetime-local" name="published_at" class="form-control"
          value="<?= $a['published_at'] ? date('Y-m-d\TH:i', strtotime($a['published_at'])) : '' ?>">
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" <?= ($a['is_featured']??0)?'checked':'' ?>>
        <label class="form-check-label" for="is_featured"><i class="fas fa-star text-warning"></i> Featured Article</label>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Article</button>
        <?php if ($a): ?>
        <a href="/article/<?= e($a['slug']) ?>" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-eye"></i> View Article</a>
        <?php endif ?>
      </div>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title">Category</div>
      <select name="category_id" class="form-select">
        <option value="">— None —</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($a['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="card-box">
      <div class="card-box-title">Featured Image</div>
      <?php if (!empty($a['featured_image'])): ?>
      <img src="<?= e($a['featured_image']) ?>" class="img-fluid rounded mb-2" style="max-height:160px;object-fit:cover;width:100%">
      <div class="form-check mb-2">
        <input type="checkbox" name="remove_image" id="remove_image" class="form-check-input" value="1">
        <label class="form-check-label text-danger" for="remove_image">Remove image</label>
      </div>
      <?php endif ?>
      <input type="file" name="featured_image" class="form-control" accept="image/*">
      <small class="text-muted">JPG/PNG/WebP, max 5MB</small>
    </div>
  </div>
</div>
</form>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: {
    toolbar: [
      [{ header: [1,2,3,false] }],
      ['bold','italic','underline','strike'],
      [{ color:[] },{ background:[] }],
      [{ list:'ordered' },{ list:'bullet' }],
      ['blockquote','code-block'],
      ['link','image'],
      ['clean']
    ]
  }
});

var sourceEditor = document.getElementById('source-editor');
var editorEl     = document.getElementById('editor');
var toggleBtn    = document.getElementById('toggle-source');
var contentInput = document.getElementById('content-input');

// Detect complex HTML (AI-generated content with style tags, gd-* classes, etc.)
function isComplexHTML(html) {
  return /<style[\s>]/i.test(html) ||
         /class="gd-/i.test(html) ||
         /<(section|figure|table|iframe|video|script)/i.test(html) ||
         html.length > 5000;
}

var sourceMode = false;

function enterSourceMode() {
  sourceMode = true;
  editorEl.style.display = 'none';
  sourceEditor.style.display = 'block';
  toggleBtn.innerHTML = '<i class="fas fa-eye"></i> Visual';
  toggleBtn.classList.replace('btn-outline-secondary', 'btn-outline-primary');
}

function enterVisualMode() {
  sourceMode = false;
  // Sync source → Quill
  quill.clipboard.dangerouslyPasteHTML(sourceEditor.value);
  editorEl.style.display = 'block';
  sourceEditor.style.display = 'none';
  toggleBtn.innerHTML = '<i class="fas fa-code"></i> Source HTML';
  toggleBtn.classList.replace('btn-outline-primary', 'btn-outline-secondary');
}

// Init: load content, auto-switch to source mode for complex HTML
sourceEditor.value = window.__articleContent || '';
if (isComplexHTML(window.__articleContent || '')) {
  enterSourceMode();
} else {
  quill.clipboard.dangerouslyPasteHTML(window.__articleContent || '');
}

toggleBtn.addEventListener('click', function() {
  if (sourceMode) {
    enterVisualMode();
  } else {
    sourceEditor.value = quill.root.innerHTML;
    enterSourceMode();
  }
});

document.querySelector('form').addEventListener('submit', function() {
  contentInput.value = sourceMode ? sourceEditor.value : quill.root.innerHTML;
});
</script>
<?php require __DIR__ . '/_layout_end.php'; ?>
