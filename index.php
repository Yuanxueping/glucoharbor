<?php
require_once __DIR__ . '/includes/bootstrap.php';

// 若未安装则跳转安装
if (!file_exists(CONFIG_FILE)) {
    header('Location: /install.php');
    exit;
}

// 解析URL
$url = trim($_GET['url'] ?? '', '/');
$parts = explode('/', $url);
$segment0 = $parts[0] ?? '';
$segment1 = $parts[1] ?? '';
$segment2 = $parts[2] ?? '';

// ── 路由 ───────────────────────────────────────────

// Sitemap
if ($url === 'sitemap.xml') {
    require ROOT_PATH . '/templates/front/sitemap.php';
    exit;
}

// Robots
if ($url === 'robots.txt') {
    header('Content-Type: text/plain');
    echo "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /uploads/\nSitemap: " . setting('site_url') . "/sitemap.xml";
    exit;
}

switch ($segment0) {
    case '':
        require ROOT_PATH . '/templates/front/home.php';
        break;

    case 'article':
        // /article/slug
        require ROOT_PATH . '/templates/front/article.php';
        break;

    case 'category':
        // /category/slug
        require ROOT_PATH . '/templates/front/category.php';
        break;

    case 'articles':
        require ROOT_PATH . '/templates/front/articles.php';
        break;

    case 'products':
        require ROOT_PATH . '/templates/front/products.php';
        break;

    case 'product':
        require ROOT_PATH . '/templates/front/product.php';
        break;

    case 'page':
        require ROOT_PATH . '/templates/front/page.php';
        break;

    case 'search':
        require ROOT_PATH . '/templates/front/search.php';
        break;

    // API 接口（供 n8n / DeepSeek 调用）
    case 'api':
        require ROOT_PATH . '/includes/api.php';
        break;

    default:
        http_response_code(404);
        require ROOT_PATH . '/templates/front/404.php';
}
