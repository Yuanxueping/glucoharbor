<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['tab'] ?? 'general';

    if ($tab === 'general') {
        $fields = ['site_name','site_url','site_description','site_keywords','icp_number','ga_tracking_id','articles_per_page','medical_disclaimer'];
        foreach ($fields as $f) { save_setting($f, trim($_POST[$f] ?? '')); }
        // Logo upload
        if (!empty($_FILES['site_logo']['tmp_name'])) {
            $r = upload_image('site_logo');
            if (!$r['error']) save_setting('site_logo', $r['url']);
            else flash('error', 'Logo upload failed: ' . $r['error']);
        }
        // Remove logo
        if (!empty($_POST['remove_logo'])) save_setting('site_logo', '');
        // Favicon upload
        if (!empty($_FILES['site_favicon']['tmp_name'])) {
            $f2 = $_FILES['site_favicon'];
            $allowed_fav = ['image/x-icon','image/vnd.microsoft.icon','image/png','image/gif','image/jpeg','image/webp'];
            if (!in_array($f2['type'], $allowed_fav)) {
                flash('error', 'Favicon must be ICO, PNG, or WebP.');
            } elseif ($f2['size'] > 1 * 1024 * 1024) {
                flash('error', 'Favicon too large (max 1MB).');
            } else {
                if (!is_dir(UPLOADS_PATH)) mkdir(UPLOADS_PATH, 0755, true);
                $ext_fav = strtolower(pathinfo($f2['name'], PATHINFO_EXTENSION));
                $fav_name = 'favicon_' . time() . '.' . $ext_fav;
                if (move_uploaded_file($f2['tmp_name'], UPLOADS_PATH . '/' . $fav_name)) {
                    save_setting('site_favicon', UPLOADS_URL . '/' . $fav_name);
                }
            }
        }
        // Remove favicon
        if (!empty($_POST['remove_favicon'])) save_setting('site_favicon', '');
    } elseif ($tab === 'ads') {
        save_setting('adsense_publisher_id', trim($_POST['adsense_publisher_id'] ?? ''));
        save_setting('adsense_enabled', !empty($_POST['adsense_publisher_id']) ? '1' : '0');
        save_setting('afs_publisher_id', trim($_POST['afs_publisher_id'] ?? ''));
        save_setting('afs_channel', trim($_POST['afs_channel'] ?? ''));
        save_setting('afs_enabled', !empty($_POST['afs_publisher_id']) ? '1' : '0');
        save_setting('ad_header_code', $_POST['ad_header_code'] ?? '');
        save_setting('ad_article_top_code', $_POST['ad_article_top_code'] ?? '');
        save_setting('ad_article_bottom_code', $_POST['ad_article_bottom_code'] ?? '');
        save_setting('ad_sidebar_code', $_POST['ad_sidebar_code'] ?? '');
    } elseif ($tab === 'seo') {
        if (isset($_POST['indexnow_key_manual']) && trim($_POST['indexnow_key_manual']) !== '') {
            save_setting('indexnow_key', trim($_POST['indexnow_key_manual']));
            flash('success', 'IndexNow key saved.');
        } elseif (isset($_POST['generate_indexnow'])) {
            save_setting('indexnow_key', bin2hex(random_bytes(16)));
        }
        redirect('/admin/settings.php?tab=seo');
    } elseif ($tab === 'api') {
        if (isset($_POST['generate_token'])) {
            save_setting('api_token', bin2hex(random_bytes(24)));
        }
    } elseif ($tab === 'social') {
        foreach (['social_facebook','social_twitter','social_instagram','social_youtube','social_pinterest'] as $f) {
            save_setting($f, trim($_POST[$f] ?? ''));
        }
    } elseif ($tab === 'password') {
        $cur = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $con = trim($_POST['confirm_password'] ?? '');
        $user = db_fetch("SELECT * FROM users WHERE id=?", [$_SESSION['admin_id']]);
        if (!verify_password($cur, $user['password'])) {
            flash('error','Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            flash('error','New password must be at least 6 characters.');
        } elseif ($new !== $con) {
            flash('error','Passwords do not match.');
        } else {
            db_query("UPDATE users SET password=? WHERE id=?", [hash_password($new), $_SESSION['admin_id']]);
            flash('success','Password changed.');
        }
        redirect('/admin/settings.php?tab=password');
    }

    if (!get_flash()) flash('success','Settings saved.');
    redirect('/admin/settings.php?tab='.$tab);
}

$admin_title = 'Settings';
$tab = $_GET['tab'] ?? 'general';
require __DIR__ . '/_layout.php';
?>
<h1 class="page-title mb-3">Settings</h1>

<ul class="nav nav-tabs mb-3">
  <?php foreach (['general'=>'General','seo'=>'SEO','ads'=>'Advertising','api'=>'API / Import','social'=>'Social','password'=>'Password'] as $t=>$l): ?>
  <li class="nav-item">
    <a class="nav-link <?= $tab===$t?'active':'' ?>" href="/admin/settings.php?tab=<?= $t ?>"><?= $l ?></a>
  </li>
  <?php endforeach ?>
</ul>

<!-- General -->
<?php if ($tab==='general'): ?>
<div class="card-box">
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="tab" value="general">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Site Name</label>
      <input type="text" name="site_name" class="form-control" value="<?= e(setting('site_name')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">Site URL</label>
      <input type="url" name="site_url" class="form-control" placeholder="https://glucoharbor.com" value="<?= e(setting('site_url')) ?>">
    </div>
    <div class="col-12">
      <label class="form-label fw-semibold">Site Description</label>
      <textarea name="site_description" class="form-control" rows="2"><?= e(setting('site_description')) ?></textarea>
    </div>
    <div class="col-12">
      <label class="form-label fw-semibold">Site Keywords</label>
      <input type="text" name="site_keywords" class="form-control" value="<?= e(setting('site_keywords')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">ICP Number <small class="text-muted">(optional, shown in footer)</small></label>
      <input type="text" name="icp_number" class="form-control" value="<?= e(setting('icp_number')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Google Analytics Tracking ID</label>
      <input type="text" name="ga_tracking_id" class="form-control" placeholder="G-XXXXXXXXXX" value="<?= e(setting('ga_tracking_id')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Articles Per Page</label>
      <input type="number" name="articles_per_page" class="form-control" min="5" max="50" value="<?= e(setting('articles_per_page','12')) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Medical Disclaimer Text <small class="text-muted">(shown in footer)</small></label>
      <textarea name="medical_disclaimer" class="form-control" rows="3"><?= e(setting('medical_disclaimer')) ?></textarea>
    </div>

    <!-- Logo -->
    <div class="col-md-6">
      <label class="form-label fw-semibold">Site Logo</label>
      <?php $logo = setting('site_logo'); if ($logo): ?>
      <div class="mb-2 d-flex align-items-center gap-3">
        <img src="<?= e($logo) ?>" alt="Logo" style="max-height:50px;max-width:180px;object-fit:contain;background:#f1f5f9;border-radius:6px;padding:4px">
        <label class="text-danger small" style="cursor:pointer">
          <input type="checkbox" name="remove_logo" value="1"> Remove logo
        </label>
      </div>
      <?php endif ?>
      <input type="file" name="site_logo" class="form-control" accept="image/*">
      <small class="text-muted">PNG/WebP recommended, transparent background. Leave blank to keep current.</small>
    </div>

    <!-- Favicon -->
    <div class="col-md-6">
      <label class="form-label fw-semibold">Site Favicon <small class="text-muted">(browser tab icon)</small></label>
      <?php $fav = setting('site_favicon'); if ($fav): ?>
      <div class="mb-2 d-flex align-items-center gap-3">
        <img src="<?= e($fav) ?>" alt="Favicon" style="width:32px;height:32px;object-fit:contain;background:#f1f5f9;border-radius:4px;padding:2px">
        <label class="text-danger small" style="cursor:pointer">
          <input type="checkbox" name="remove_favicon" value="1"> Remove favicon
        </label>
      </div>
      <?php endif ?>
      <input type="file" name="site_favicon" class="form-control" accept=".ico,.png,.webp,image/x-icon,image/png,image/webp">
      <small class="text-muted">ICO or PNG, recommended 32×32 or 64×64. Leave blank to keep current.</small>
    </div>

  </div>
  <div class="mt-3">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
  </div>
</form>
</div>

<!-- SEO -->
<?php elseif ($tab==='seo'): ?>
<div class="card-box mb-3">
  <div class="card-box-title"><i class="fas fa-bolt"></i> IndexNow (Bing / Yandex instant indexing)</div>
  <p class="text-muted small mb-3">IndexNow lets you notify Bing instantly when a page is published or updated. Generate a key, then Bing will verify it at <code><?= e(setting('site_url')) ?>/<strong>{key}</strong>.txt</code>. Once set, every published article auto-pings Bing.</p>

  <?php $ikey = setting('indexnow_key'); ?>
  <?php if ($ikey): ?>
  <div class="mb-3">
    <label class="form-label fw-semibold">Your IndexNow Key</label>
    <div class="input-group">
      <input type="text" class="form-control font-monospace" value="<?= e($ikey) ?>" id="ikey_field" readonly>
      <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('ikey_field').value).then(()=>alert('Copied!'))">
        <i class="fas fa-copy"></i> Copy
      </button>
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label fw-semibold">Key File URL (auto-served, no upload needed)</label>
    <input type="text" class="form-control font-monospace" value="<?= e(setting('site_url')) ?>/<?= e($ikey) ?>.txt" readonly>
  </div>
  <div class="mb-3">
    <label class="form-label fw-semibold">Submit to Bing Webmaster Tools</label>
    <p class="text-muted small">Go to <a href="https://www.bing.com/webmasters" target="_blank">Bing Webmaster Tools</a> → Settings → IndexNow → paste your key above.</p>
  </div>
  <?php endif ?>

  <form method="POST">
    <input type="hidden" name="tab" value="seo">
    <div class="mb-3">
      <label class="form-label fw-semibold">Paste key from Bing IndexNow page</label>
      <div class="input-group">
        <input type="text" name="indexnow_key_manual" class="form-control font-monospace"
          placeholder="e.g. 13328e6d5a4641d49f98c5be6bc40ed5"
          value="<?= e($ikey) ?>">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Key</button>
      </div>
      <small class="text-muted">Copy the key from <a href="https://www.bing.com/indexnow" target="_blank">Bing IndexNow page</a> and paste here.</small>
    </div>
  </form>
</div>

<!-- Advertising -->
<?php elseif ($tab==='ads'): ?>
<form method="POST">
<input type="hidden" name="tab" value="ads">

<div class="card-box mb-3">
  <div class="card-box-title"><i class="fab fa-google"></i> Google AdSense</div>
  <p class="text-muted small mb-3">Filling in the Publisher ID will automatically activate AdSense auto-ads.</p>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold">AdSense Publisher ID</label>
      <input type="text" name="adsense_publisher_id" class="form-control" placeholder="pub-0000000000000000" value="<?= e(setting('adsense_publisher_id')) ?>">
      <small class="text-muted">Leave blank to disable AdSense</small>
    </div>
  </div>
</div>

<div class="card-box mb-3">
  <div class="card-box-title"><i class="fas fa-search-dollar"></i> Google AFS (Search Ads)</div>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold">AFS Publisher ID</label>
      <input type="text" name="afs_publisher_id" class="form-control" placeholder="pub-0000000000000000" value="<?= e(setting('afs_publisher_id')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">AFS Channel</label>
      <input type="text" name="afs_channel" class="form-control" value="<?= e(setting('afs_channel')) ?>">
    </div>
  </div>
</div>

<div class="card-box mb-3">
  <div class="card-box-title">Custom Ad Slots</div>
  <p class="text-muted small mb-3">Paste any ad code (AdSense unit, banner HTML, etc.) into the slots below. Leave blank to hide that slot.</p>
  <?php
  $slots = [
    'ad_header_code'         => 'Header Ad (below top navigation)',
    'ad_article_top_code'    => 'Article Top Ad (above article content)',
    'ad_article_bottom_code' => 'Article Bottom Ad (below article content)',
    'ad_sidebar_code'        => 'Sidebar Ad',
  ];
  foreach ($slots as $key=>$label): ?>
  <div class="mb-4">
    <label class="form-label fw-semibold"><?= $label ?></label>
    <textarea name="<?= $key ?>" class="form-control font-monospace" rows="4" style="font-size:12px"><?= e(setting($key)) ?></textarea>
  </div>
  <?php endforeach ?>
</div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Ad Settings</button>
</form>

<!-- API -->
<?php elseif ($tab==='api'): ?>
<div class="card-box">
  <div class="card-box-title"><i class="fas fa-plug"></i> API Token</div>
  <p class="text-muted small">Use this token to authenticate API requests from n8n, DeepSeek, or any external service.</p>

  <?php $token = setting('api_token'); ?>
  <?php if ($token): ?>
  <div class="mb-3">
    <label class="form-label fw-semibold">Current Token</label>
    <div class="input-group">
      <input type="text" class="form-control font-monospace" value="<?= e($token) ?>" id="api_token_field" readonly>
      <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('api_token_field').value).then(()=>alert('Copied!'))">
        <i class="fas fa-copy"></i> Copy
      </button>
    </div>
  </div>
  <?php endif ?>

  <form method="POST">
    <input type="hidden" name="tab" value="api">
    <button type="submit" name="generate_token" class="btn <?= $token?'btn-outline-warning':'btn-primary' ?>">
      <i class="fas fa-sync-alt"></i> <?= $token?'Regenerate Token':'Generate Token' ?>
    </button>
    <?php if ($token): ?><small class="text-muted ms-2">This will invalidate the current token.</small><?php endif ?>
  </form>

  <?php if ($token): ?>
  <hr>
  <div class="card-box-title mt-3">API Usage</div>
  <div style="background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;font-family:monospace;font-size:12px;line-height:2">
    POST <?= e(setting('site_url')) ?>/api/articles/import<br>
    Header: x-api-token: <?= e($token) ?><br><br>
    {<br>
    &nbsp;&nbsp;"title": "...",<br>
    &nbsp;&nbsp;"content": "&lt;p&gt;HTML...&lt;/p&gt;",<br>
    &nbsp;&nbsp;"excerpt": "...",<br>
    &nbsp;&nbsp;"category_slug": "diabetes-management",<br>
    &nbsp;&nbsp;"featured_image": "https://...",<br>
    &nbsp;&nbsp;"status": "published",<br>
    &nbsp;&nbsp;"source": "ai"<br>
    }
  </div>
  <?php endif ?>
</div>

<!-- Social -->
<?php elseif ($tab==='social'): ?>
<div class="card-box">
<form method="POST">
  <input type="hidden" name="tab" value="social">
  <div class="row g-3">
    <?php
    $socials = ['social_facebook'=>['fab fa-facebook','Facebook URL'],'social_twitter'=>['fab fa-twitter','Twitter/X URL'],'social_instagram'=>['fab fa-instagram','Instagram URL'],'social_youtube'=>['fab fa-youtube','YouTube URL'],'social_pinterest'=>['fab fa-pinterest','Pinterest URL']];
    foreach ($socials as $key=>[$icon,$label]): ?>
    <div class="col-md-6">
      <label class="form-label"><i class="<?= $icon ?>"></i> <?= $label ?></label>
      <input type="url" name="<?= $key ?>" class="form-control" placeholder="https://..." value="<?= e(setting($key)) ?>">
    </div>
    <?php endforeach ?>
  </div>
  <div class="mt-3">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
  </div>
</form>
</div>

<!-- Password -->
<?php elseif ($tab==='password'): ?>
<div class="card-box" style="max-width:480px">
<form method="POST">
  <input type="hidden" name="tab" value="password">
  <div class="mb-3">
    <label class="form-label fw-semibold">Current Password</label>
    <input type="password" name="current_password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label fw-semibold">New Password</label>
    <input type="password" name="new_password" class="form-control" minlength="6" required>
  </div>
  <div class="mb-3">
    <label class="form-label fw-semibold">Confirm New Password</label>
    <input type="password" name="confirm_password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
</form>
</div>
<?php endif ?>

<?php require __DIR__ . '/_layout_end.php'; ?>
