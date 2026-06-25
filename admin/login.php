<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_logged_in()) { redirect('/admin/'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = db_fetch("SELECT * FROM users WHERE username=?", [trim($_POST['username']??'')]);
    if ($user && verify_password($_POST['password']??'', $user['password'])) {
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role']     = $user['role'];
        redirect('/admin/');
    }
    $error = 'Invalid username or password.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:linear-gradient(135deg,#0f172a,#1a56db);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:16px;padding:40px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.logo{text-align:center;margin-bottom:28px}
.logo i{font-size:48px;color:#ef4444}
.logo h1{font-size:24px;font-weight:800;color:#1a56db;margin-top:8px}
.logo p{color:#6b7280;font-size:14px}
.form-group{margin-bottom:18px}
label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
input{width:100%;padding:12px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:15px;outline:none;transition:border-color .15s}
input:focus{border-color:#1a56db}
.btn{width:100%;padding:13px;background:#1a56db;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;margin-top:8px}
.btn:hover{background:#1245b8}
.error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px}
</style>
</head>
<body>
<div class="box">
  <div class="logo"><i class="fas fa-heartbeat"></i><h1>GlucoHarbor</h1><p>Admin Panel</p></div>
  <?php if ($error): ?><div class="error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div><?php endif ?>
  <form method="POST">
    <div class="form-group"><label>Username</label><input type="text" name="username" autofocus required placeholder="Enter username"></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required placeholder="Enter password"></div>
    <button type="submit" class="btn">Sign In <i class="fas fa-arrow-right"></i></button>
  </form>
</div>
</body>
</html>
