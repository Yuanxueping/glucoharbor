<?php
/**
 * REST API — 供 n8n / DeepSeek 调用
 * 认证方式：Header x-api-token 或 GET api_token
 */
header('Content-Type: application/json; charset=utf-8');

$token = $_SERVER['HTTP_X_API_TOKEN'] ?? $_GET['api_token'] ?? '';
$stored = setting('api_token');

if (!$stored || !$token || !hash_equals($stored, $token)) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$parts  = explode('/', trim($_GET['url'] ?? ''), 4);
$action = $parts[1] ?? '';   // api/{action}
$sub    = $parts[2] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'categories') {
        $cats = db_fetchAll("SELECT id,name,slug FROM categories ORDER BY sort_order,name");
        echo json_encode(['success'=>true,'data'=>$cats]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // 导入单篇文章
    if ($action === 'articles' && $sub === 'import') {
        $title   = trim($body['title'] ?? '');
        $content = trim($body['content'] ?? '');
        if (!$title || !$content) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'title and content are required']);
            exit;
        }
        $cat_id = null;
        if (!empty($body['category_slug'])) {
            $cat = db_fetch("SELECT id FROM categories WHERE slug=?", [$body['category_slug']]);
            if ($cat) $cat_id = $cat['id'];
        }
        $slug = unique_slug('articles', make_slug($title));
        $status = $body['status'] ?? 'published';
        $pub = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $id = db_insert(
            "INSERT INTO articles(title,slug,excerpt,content,featured_image,category_id,status,source,meta_title,meta_description,meta_keywords,published_at)
             VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $title,
                $slug,
                $body['excerpt'] ?? null,
                $content,
                $body['featured_image'] ?? null,
                $cat_id,
                $status,
                $body['source'] ?? 'api',
                $body['meta_title'] ?? null,
                $body['meta_description'] ?? null,
                $body['meta_keywords'] ?? null,
                $pub,
            ]
        );
        if ($status === 'published') indexnow_ping(setting('site_url') . '/article/' . $slug);
        echo json_encode(['success'=>true,'id'=>(int)$id,'slug'=>$slug]);
        exit;
    }

    // 批量导入
    if ($action === 'articles' && $sub === 'import-batch') {
        $articles = $body['articles'] ?? [];
        $results = [];
        foreach ($articles as $item) {
            try {
                $title   = trim($item['title'] ?? '');
                $content = trim($item['content'] ?? '');
                if (!$title || !$content) throw new Exception('title/content missing');
                $cat_id = null;
                if (!empty($item['category_slug'])) {
                    $cat = db_fetch("SELECT id FROM categories WHERE slug=?", [$item['category_slug']]);
                    if ($cat) $cat_id = $cat['id'];
                }
                $slug = unique_slug('articles', make_slug($title));
                $status = $item['status'] ?? 'published';
                $pub = $status === 'published' ? date('Y-m-d H:i:s') : null;
                $id = db_insert(
                    "INSERT INTO articles(title,slug,excerpt,content,featured_image,category_id,status,source,published_at) VALUES(?,?,?,?,?,?,?,?,?)",
                    [$title,$slug,$item['excerpt']??null,$content,$item['featured_image']??null,$cat_id,$status,$item['source']??'api',$pub]
                );
                $results[] = ['success'=>true,'id'=>(int)$id,'title'=>$title];
            } catch (Exception $e) {
                $results[] = ['success'=>false,'title'=>$item['title']??'','error'=>$e->getMessage()];
            }
        }
        echo json_encode(['success'=>true,'results'=>$results]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['success'=>false,'message'=>'Endpoint not found']);
