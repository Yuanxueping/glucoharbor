<?php
// 入口引导

define('ROOT_PATH',    __DIR__ . '/..');
define('CONFIG_FILE',  ROOT_PATH . '/config.php');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOADS_URL',  '/uploads');
define('ADMIN_URL',    '/admin');
define('VERSION',      '1.0.0');

// 错误处理
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

// 加载核心模块
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';

// 获取基础URL（兼容子目录部署）
$script = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($script === '/' ? '' : $script, '/'));
