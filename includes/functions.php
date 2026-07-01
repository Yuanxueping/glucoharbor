<?php

// ── 设置 ──────────────────────────────────────────
function get_settings() {
    static $settings = null;
    if ($settings !== null) return $settings;
    $rows = db_fetchAll("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
    return $settings;
}

function setting($key, $default = '') {
    $s = get_settings();
    return $s[$key] ?? $default;
}

function save_setting($key, $value) {
    db_query("INSERT INTO settings (setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=?", [$key, $value, $value]);
}

// ── 认证 ──────────────────────────────────────────
function is_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function hash_password($pass) { return password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]); }
function verify_password($pass, $hash) { return password_verify($pass, $hash); }

// ── Slug ──────────────────────────────────────────
function make_slug($str) {
    $str = mb_strtolower(trim($str));
    $str = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
    $str = preg_replace('/[\s_]+/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

function unique_slug($table, $slug, $id = 0) {
    $base = $slug; $i = 2;
    while (true) {
        $exists = db_count("SELECT COUNT(*) FROM `$table` WHERE slug=? AND id!=?", [$slug, $id]);
        if (!$exists) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ── 上传 ──────────────────────────────────────────
function upload_image($file_key) {
    if (empty($_FILES[$file_key]['tmp_name'])) return ['error'=>'No file uploaded','url'=>''];
    $f = $_FILES[$file_key];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($f['type'], $allowed)) return ['error'=>'Invalid file type. JPG/PNG/WebP/GIF only.','url'=>''];
    if ($f['size'] > 5 * 1024 * 1024) return ['error'=>'File too large (max 5MB).','url'=>''];
    if (!is_dir(UPLOADS_PATH)) {
        mkdir(UPLOADS_PATH, 0755, true);
    }
    $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $name = uniqid('img_', true) . '.' . $ext;
    $dest = UPLOADS_PATH . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) return ['error'=>'Failed to save file. Check uploads/ directory permissions.','url'=>''];
    return ['error'=>'', 'url'=> UPLOADS_URL . '/' . $name];
}

// ── 分页 ──────────────────────────────────────────
function paginate($total, $per_page, $current) {
    return [
        'total'   => $total,
        'pages'   => max(1, (int)ceil($total / $per_page)),
        'current' => $current,
        'offset'  => ($current - 1) * $per_page,
    ];
}

// ── 输出 ──────────────────────────────────────────
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

function redirect($url) { header('Location: ' . $url); exit; }

function flash($type, $msg) { $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg]; }

function get_flash() {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ── 时间 ──────────────────────────────────────────
function fmt_date($dt, $fmt = 'M j, Y') {
    if (!$dt) return '';
    return date($fmt, strtotime($dt));
}

// ── 前台分类列表（全局使用） ──
function nav_categories() {
    static $cats = null;
    if ($cats !== null) return $cats;
    $cats = db_fetchAll("SELECT id,name,slug FROM categories ORDER BY sort_order,name LIMIT 10");
    return $cats;
}

function footer_pages() {
    static $fps = null;
    if ($fps !== null) return $fps;
    $fps = db_fetchAll("SELECT id,title,slug FROM pages WHERE status='published' AND in_footer=1 ORDER BY sort_order");
    return $fps;
}

// ── IndexNow ──
function indexnow_ping($urls) {
    $key = setting('indexnow_key');
    $host = parse_url(setting('site_url'), PHP_URL_HOST);
    if (!$key || !$host) return false;
    if (is_string($urls)) $urls = [$urls];
    $payload = json_encode([
        'host'    => $host,
        'key'     => $key,
        'keyLocation' => setting('site_url') . '/' . $key . '.txt',
        'urlList' => array_values($urls),
    ]);
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
        'content' => $payload,
        'timeout' => 5,
        'ignore_errors' => true,
    ]]);
    @file_get_contents('https://api.indexnow.org/IndexNow', false, $ctx);
    return true;
}

// ── 星级评分 HTML ──
function star_rating($rating, $review_count = null) {
    if (!$rating) return '';
    $rating = (float)$rating;
    $full   = (int)floor($rating);
    $half   = ($rating - $full) >= 0.25 && ($rating - $full) < 0.75;
    $empty  = 5 - $full - ($half ? 1 : 0);
    $html   = '<span class="star-rating">';
    for ($i = 0; $i < $full; $i++)  $html .= '<i class="fas fa-star"></i>';
    if ($half)                        $html .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = 0; $i < $empty; $i++) $html .= '<i class="far fa-star"></i>';
    $html .= ' <span class="star-score">' . number_format($rating, 1) . '</span>';
    if ($review_count) $html .= ' <span class="star-count">(' . number_format($review_count) . ')</span>';
    $html .= '</span>';
    return $html;
}

// ── 广告位辅助 ──
function ad_slot($key) {
    $code = setting($key);
    if ($code) echo '<div class="ad-slot">' . $code . '</div>';
}
