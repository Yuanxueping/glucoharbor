<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    db_query("DELETE FROM products WHERE id=?",[(int)$_POST['delete_id']]);
    flash('success','Product deleted.');
    redirect('/admin/products.php');
}

$admin_title = 'Products';
$per   = 20;
$page  = max(1,(int)($_GET['page']??1));
$search= trim($_GET['q']??'');
$where = ['1=1']; $params=[];
if ($search) { $where[]='p.name LIKE ?'; $params[]="%$search%"; }
$w     = implode(' AND ',$where);
$total = db_count("SELECT COUNT(*) FROM products p WHERE $w",$params);
$pg    = paginate($total,$per,$page);
$list  = db_fetchAll("SELECT p.*,pc.name cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE $w ORDER BY p.created_at DESC LIMIT $per OFFSET {$pg['offset']}",$params);

require __DIR__ . '/_layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0">Products <small class="text-muted fs-6">(<?= $total ?>)</small></h1>
  <a href="/admin/product-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Product</a>
</div>

<div class="card-box">
  <form method="GET" class="d-flex gap-2 mb-3">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search products..." class="form-control" style="max-width:280px">
    <button class="btn btn-outline-primary"><i class="fas fa-search"></i> Search</button>
    <a href="/admin/products.php" class="btn btn-outline-secondary">Reset</a>
  </form>

  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-light"><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!$list): ?><tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr><?php endif ?>
      <?php foreach ($list as $p): ?>
      <tr>
        <td>
          <?php if ($p['image']): ?>
          <img src="<?= e($p['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px">
          <?php else: ?><span class="text-muted">—</span><?php endif ?>
        </td>
        <td><a href="/admin/product-edit.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a></td>
        <td><small><?= e($p['cat_name']??'—') ?></small></td>
        <td>
          <?php if ($p['sale_price']): ?>
          <span class="text-danger">$<?= number_format($p['sale_price'],2) ?></span>
          <del class="text-muted ms-1">$<?= number_format($p['price'],2) ?></del>
          <?php elseif ($p['price']): ?>
          $<?= number_format($p['price'],2) ?>
          <?php else: ?>—<?php endif ?>
        </td>
        <td><span class="badge <?= $p['is_active']?'bg-success':'bg-secondary' ?>"><?= $p['is_active']?'Active':'Inactive' ?></span></td>
        <td>
          <div class="btn-group btn-group-sm">
            <a href="/admin/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
            <a href="/product/<?= e($p['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
              <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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
      <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="/admin/products.php?page=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a></li>
      <?php endfor ?>
    </ul>
  </nav>
  <?php endif ?>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>
