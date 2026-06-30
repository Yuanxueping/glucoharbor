<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$p  = $id ? db_fetch("SELECT * FROM products WHERE id=?", [$id]) : null;
if ($id && !$p) { flash('error','Product not found.'); redirect('/admin/products.php'); }

$admin_title = $p ? 'Edit Product' : 'New Product';
$pcats = db_fetchAll("SELECT * FROM product_categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = $_POST['description'] ?? '';
    $short_desc  = trim($_POST['short_description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $price       = trim($_POST['price'] ?? '') !== '' ? (float)$_POST['price'] : null;
    $sale_price  = trim($_POST['sale_price'] ?? '') !== '' ? (float)$_POST['sale_price'] : null;
    $affiliate_url = trim($_POST['affiliate_url'] ?? '');
    $rating      = trim($_POST['rating'] ?? '') !== '' ? min(5, max(0, (float)$_POST['rating'])) : null;
    $review_count = trim($_POST['review_count'] ?? '') !== '' ? (int)$_POST['review_count'] : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $meta_title  = trim($_POST['meta_title'] ?? '');
    $meta_desc   = trim($_POST['meta_desc'] ?? '');

    if (!$name) { $error = 'Name is required.'; goto render; }

    $slug = unique_slug('products', make_slug($name), $id);

    $image = $p['image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $up = upload_image('image');
        if ($up['error']) { $error = $up['error']; goto render; }
        $image = $up['url'];
    } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        $image = '';
    }

    // Handle gallery uploads
    $gallery = json_decode($p['gallery'] ?? '[]', true) ?: [];
    // Remove gallery items marked for deletion
    $remove_gallery = $_POST['remove_gallery'] ?? [];
    if ($remove_gallery) {
        $gallery = array_values(array_filter($gallery, fn($url) => !in_array($url, $remove_gallery)));
    }
    // Upload new gallery images
    if (!empty($_FILES['gallery_new']['name'][0])) {
        foreach ($_FILES['gallery_new']['name'] as $i => $fname) {
            if (!$fname) continue;
            $gfile = [
                'name'     => $_FILES['gallery_new']['name'][$i],
                'type'     => $_FILES['gallery_new']['type'][$i],
                'tmp_name' => $_FILES['gallery_new']['tmp_name'][$i],
                'error'    => $_FILES['gallery_new']['error'][$i],
                'size'     => $_FILES['gallery_new']['size'][$i],
            ];
            $_FILES['_gimg'] = $gfile;
            $up = upload_image('_gimg');
            if (!$up['error']) $gallery[] = $up['url'];
        }
    }
    $gallery_json = json_encode(array_values($gallery));

    if ($p) {
        db_query("UPDATE products SET name=?,slug=?,description=?,short_description=?,category_id=?,image=?,gallery=?,price=?,sale_price=?,affiliate_url=?,rating=?,review_count=?,is_featured=?,is_active=?,meta_title=?,meta_description=? WHERE id=?",
            [$name,$slug,$description,$short_desc,$category_id,$image,$gallery_json,$price,$sale_price,$affiliate_url,$rating,$review_count,$is_featured,$is_active,$meta_title,$meta_desc,$id]);
        flash('success','Product updated.');
    } else {
        $new_id = db_insert("INSERT INTO products(name,slug,description,short_description,category_id,image,gallery,price,sale_price,affiliate_url,rating,review_count,is_featured,is_active,meta_title,meta_description) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$name,$slug,$description,$short_desc,$category_id,$image,$gallery_json,$price,$sale_price,$affiliate_url,$rating,$review_count,$is_featured,$is_active,$meta_title,$meta_desc]);
        flash('success','Product created.');
        redirect('/admin/product-edit.php?id='.$new_id);
    }
    redirect('/admin/products.php');
}

render:
require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0"><?= $admin_title ?></h1>
  <a href="/admin/products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= e($error) ?></div>
<?php endif ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-box mb-3">
      <div class="mb-3">
        <label class="form-label fw-semibold">Product Name *</label>
        <input type="text" name="name" class="form-control form-control-lg" value="<?= e($p['name'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Short Description</label>
        <textarea name="short_description" class="form-control" rows="2"><?= e($p['short_description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="form-label fw-semibold">Full Description</label>
        <div id="editor" style="height:340px;border:1px solid #dee2e6;border-radius:6px"></div>
        <textarea name="description" id="desc-input" hidden></textarea>
        <script>window.__articleContent = <?= json_encode($p['description'] ?? '') ?>;</script>
      </div>
    </div>

    <div class="card-box">
      <div class="card-box-title"><i class="fas fa-search"></i> SEO</div>
      <div class="mb-3">
        <label class="form-label">Meta Title</label>
        <input type="text" name="meta_title" class="form-control" value="<?= e($p['meta_title'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Meta Description</label>
        <textarea name="meta_desc" class="form-control" rows="2"><?= e($p['meta_description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-box mb-3">
      <div class="card-box-title">Publish</div>
      <div class="form-check mb-2">
        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" <?= ($p['is_active']??1)?'checked':'' ?>>
        <label class="form-check-label" for="is_active">Active (visible on site)</label>
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" <?= ($p['is_featured']??0)?'checked':'' ?>>
        <label class="form-check-label" for="is_featured"><i class="fas fa-star text-warning"></i> Featured</label>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product</button>
      </div>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title">Category</div>
      <select name="category_id" class="form-select">
        <option value="">— None —</option>
        <?php foreach ($pcats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($p['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title">Pricing</div>
      <div class="mb-3">
        <label class="form-label">Regular Price ($)</label>
        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= $p['price'] ?? '' ?>">
      </div>
      <div>
        <label class="form-label">Sale Price ($)</label>
        <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?= $p['sale_price'] ?? '' ?>">
      </div>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title">Affiliate Link</div>
      <input type="url" name="affiliate_url" class="form-control" placeholder="https://..." value="<?= e($p['affiliate_url'] ?? '') ?>">
      <small class="text-muted">Visitors will be sent to this URL when clicking "View on Amazon"</small>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title"><i class="fas fa-star text-warning"></i> Rating</div>
      <div class="mb-2">
        <label class="form-label small">Star Rating (0 – 5)</label>
        <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" placeholder="e.g. 4.5"
          value="<?= $p['rating'] ?? '' ?>">
      </div>
      <div>
        <label class="form-label small">Review Count</label>
        <input type="number" name="review_count" class="form-control" min="0" placeholder="e.g. 1283"
          value="<?= $p['review_count'] ?? '' ?>">
      </div>
    </div>

    <div class="card-box mb-3">
      <div class="card-box-title">Product Image</div>
      <?php if (!empty($p['image'])): ?>
      <img src="<?= e($p['image']) ?>" class="img-fluid rounded mb-2" style="max-height:160px;object-fit:cover;width:100%">
      <div class="form-check mb-2">
        <input type="checkbox" name="remove_image" id="remove_image" class="form-check-input" value="1">
        <label class="form-check-label text-danger" for="remove_image">Remove image</label>
      </div>
      <?php endif ?>
      <input type="file" name="image" class="form-control" accept="image/*">
      <small class="text-muted">JPG/PNG/WebP, max 5MB</small>
    </div>

    <div class="card-box">
      <div class="card-box-title">Product Gallery</div>
      <?php
        $cur_gallery = json_decode($p['gallery'] ?? '[]', true) ?: [];
        foreach ($cur_gallery as $gurl):
      ?>
      <div class="d-flex align-items-center gap-2 mb-2">
        <img src="<?= e($gurl) ?>" style="width:64px;height:48px;object-fit:cover;border-radius:4px">
        <div class="flex-grow-1 text-truncate small text-muted"><?= e(basename($gurl)) ?></div>
        <div class="form-check">
          <input type="checkbox" name="remove_gallery[]" class="form-check-input" value="<?= e($gurl) ?>" id="rg_<?= md5($gurl) ?>">
          <label class="form-check-label text-danger small" for="rg_<?= md5($gurl) ?>">Remove</label>
        </div>
      </div>
      <?php endforeach ?>
      <input type="file" name="gallery_new[]" class="form-control mt-2" accept="image/*" multiple>
      <small class="text-muted">Select multiple images. JPG/PNG/WebP, max 5MB each.</small>
    </div>
  </div>
</div>
</form>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [[{header:[1,2,3,false]}],['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link','image'],['clean']] }
});
if (window.__articleContent) {
  quill.root.innerHTML = window.__articleContent;
}
document.querySelector('form').addEventListener('submit', function() {
  document.getElementById('desc-input').value = quill.root.innerHTML;
});
</script>
<?php require __DIR__ . '/_layout_end.php'; ?>
