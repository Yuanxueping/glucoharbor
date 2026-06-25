<?php
/**
 * GlucoHarbor 安装向导
 * 安装完成后会自动删除此文件
 */
define('ROOT_PATH', __DIR__);
define('CONFIG_FILE', ROOT_PATH . '/config.php');

$step = $_GET['step'] ?? '1';
$error = '';
$success = '';

// 已安装则跳首页
if (file_exists(CONFIG_FILE) && $step !== 'done') {
    // 允许重新安装（安全验证）
    if (!isset($_GET['reinstall'])) {
        header('Location: /');
        exit;
    }
}

// ─── 步骤2：处理表单提交 ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '2') {
    $cfg = [
        'db_host'  => trim($_POST['db_host'] ?? 'localhost'),
        'db_port'  => trim($_POST['db_port'] ?? '3306'),
        'db_name'  => trim($_POST['db_name'] ?? ''),
        'db_user'  => trim($_POST['db_user'] ?? ''),
        'db_pass'  => trim($_POST['db_pass'] ?? ''),
        'site_url' => rtrim(trim($_POST['site_url'] ?? ''), '/'),
    ];
    $admin_user  = trim($_POST['admin_user'] ?? 'admin');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass  = trim($_POST['admin_pass'] ?? '');

    if (!$cfg['db_name'] || !$cfg['db_user'] || !$admin_email || !$admin_pass) {
        $error = '请填写所有必填项';
    } else {
        // 测试数据库连接
        try {
            $pdo = new PDO(
                "mysql:host={$cfg['db_host']};port={$cfg['db_port']};charset=utf8mb4",
                $cfg['db_user'], $cfg['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // 创建数据库
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$cfg['db_name']}`");

            // 建表
            $pdo->exec(file_get_contents(ROOT_PATH . '/includes/schema.sql'));

            // 写入站点URL
            $pdo->exec("INSERT INTO settings(setting_key,setting_value) VALUES('site_url','" . addslashes($cfg['site_url']) . "') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");

            // 创建管理员
            $hash = password_hash($admin_pass, PASSWORD_BCRYPT, ['cost'=>12]);
            $st = $pdo->prepare("INSERT IGNORE INTO users(username,email,password,role) VALUES(?,?,?,'admin')");
            $st->execute([$admin_user, $admin_email, $hash]);

            // 写 config.php
            $conf_content = "<?php\nreturn " . var_export([
                'db_host' => $cfg['db_host'],
                'db_port' => $cfg['db_port'],
                'db_name' => $cfg['db_name'],
                'db_user' => $cfg['db_user'],
                'db_pass' => $cfg['db_pass'],
            ], true) . ";\n";
            file_put_contents(CONFIG_FILE, $conf_content);

            // 创建 uploads 目录
            @mkdir(ROOT_PATH . '/uploads', 0755, true);
            file_put_contents(ROOT_PATH . '/uploads/.htaccess', "Options -Indexes\nphp_flag engine off\n");

            header('Location: /install.php?step=done&user=' . urlencode($admin_user));
            exit;

        } catch (PDOException $e) {
            $error = '数据库错误：' . $e->getMessage();
        } catch (Exception $e) {
            $error = '安装失败：' . $e->getMessage();
        }
    }
}

if ($step === 'done') {
    // 自动删除安装文件（可选）
    // @unlink(__FILE__);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GlucoHarbor 安装向导</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#0f172a,#1a56db);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:16px;padding:40px;max-width:520px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.logo{text-align:center;margin-bottom:28px}
.logo-icon{font-size:48px;margin-bottom:8px}
.logo h1{font-size:26px;font-weight:800;color:#1a56db}
.logo p{color:#6b7280;font-size:14px;margin-top:4px}
.step-bar{display:flex;gap:8px;margin-bottom:28px}
.step{flex:1;height:4px;border-radius:2px;background:#e5e7eb}
.step.done{background:#1a56db}
.form-group{margin-bottom:18px}
label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
label span{color:#ef4444}
input{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:15px;transition:border-color .15s;outline:none}
input:focus{border-color:#1a56db}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;padding:13px;background:#1a56db;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;transition:background .15s;margin-top:8px}
.btn:hover{background:#1245b8}
.error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px}
.success-box{text-align:center;padding:20px 0}
.success-box .icon{font-size:64px;margin-bottom:16px}
.success-box h2{font-size:22px;font-weight:700;color:#15803d;margin-bottom:8px}
.success-box p{color:#6b7280;margin-bottom:20px;font-size:15px}
.info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:16px;font-size:14px}
.info-box strong{color:#1d4ed8}
.link-btn{display:inline-block;padding:12px 28px;background:#1a56db;color:#fff;border-radius:8px;font-weight:700;text-decoration:none;margin:4px}
.link-btn.secondary{background:#f1f5f9;color:#374151}
hr{border:none;border-top:1px solid #e5e7eb;margin:20px 0}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <div class="logo-icon">❤️</div>
    <h1>GlucoHarbor</h1>
    <p>网站安装向导</p>
  </div>

  <?php if ($step === 'done'): ?>
  <div class="success-box">
    <div class="icon">🎉</div>
    <h2>安装成功！</h2>
    <p>GlucoHarbor 已成功安装，现在可以访问您的网站了。</p>
    <div class="info-box">
      <strong>管理员账号：</strong><?= htmlspecialchars($_GET['user'] ?? 'admin') ?><br>
      <strong>提示：</strong>请妥善保管您的登录密码
    </div>
    <a href="/" class="link-btn">🌐 访问前台</a>
    <a href="/admin/login.php" class="link-btn">🔧 进入后台</a>
  </div>

  <?php else: ?>

  <div class="step-bar">
    <div class="step done"></div>
    <div class="step <?= $step==='2'?'done':'' ?>"></div>
  </div>

  <?php if ($step === '1'): ?>
  <h2 style="font-size:18px;font-weight:700;margin-bottom:6px">欢迎安装 GlucoHarbor</h2>
  <p style="color:#6b7280;font-size:14px;margin-bottom:20px">开始前请确保已在宝塔面板创建好 MySQL 数据库。</p>
  <div class="info-box">
    ✅ PHP 版本：<?= phpversion() ?><br>
    ✅ PDO MySQL：<?= extension_loaded('pdo_mysql') ? '已安装' : '<span style="color:red">未安装（请在宝塔安装PHP扩展）</span>' ?><br>
    ✅ 写入权限：<?= is_writable(__DIR__) ? '正常' : '<span style="color:red">目录不可写</span>' ?>
  </div>
  <a href="/install.php?step=2" class="btn" style="display:block;text-align:center;text-decoration:none">下一步：配置数据库 →</a>

  <?php else: ?>
  <h2 style="font-size:18px;font-weight:700;margin-bottom:20px">填写配置信息</h2>
  <?php if ($error): ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif ?>
  <form method="POST" action="/install.php?step=2">
    <div class="row">
      <div class="form-group">
        <label>数据库地址 <span>*</span></label>
        <input name="db_host" value="localhost" placeholder="localhost">
      </div>
      <div class="form-group">
        <label>端口</label>
        <input name="db_port" value="3306" placeholder="3306">
      </div>
    </div>
    <div class="form-group">
      <label>数据库名 <span>*</span></label>
      <input name="db_name" placeholder="glucoharbor" value="<?= e($_POST['db_name']??'') ?>">
    </div>
    <div class="row">
      <div class="form-group">
        <label>数据库用户名 <span>*</span></label>
        <input name="db_user" placeholder="root" value="<?= e($_POST['db_user']??'') ?>">
      </div>
      <div class="form-group">
        <label>数据库密码</label>
        <input type="password" name="db_pass" placeholder="数据库密码">
      </div>
    </div>
    <div class="form-group">
      <label>网站地址 <span>*</span></label>
      <input name="site_url" placeholder="https://glucoharbor.com" value="<?= e($_POST['site_url']??'https://'.$_SERVER['HTTP_HOST']) ?>">
    </div>
    <hr>
    <div class="form-group">
      <label>管理员用户名 <span>*</span></label>
      <input name="admin_user" placeholder="admin" value="<?= e($_POST['admin_user']??'admin') ?>">
    </div>
    <div class="form-group">
      <label>管理员邮箱 <span>*</span></label>
      <input type="email" name="admin_email" placeholder="admin@glucoharbor.com" value="<?= e($_POST['admin_email']??'') ?>">
    </div>
    <div class="form-group">
      <label>管理员密码 <span>*</span></label>
      <input type="password" name="admin_pass" placeholder="至少8位">
    </div>
    <button type="submit" class="btn">🚀 开始安装</button>
  </form>
  <?php endif ?>
  <?php endif ?>
</div>
</body>
</html>
<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
