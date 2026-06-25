<?php
// 数据库连接（PDO）
function get_db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!file_exists(CONFIG_FILE)) {
        header('Location: /install.php');
        exit;
    }
    $cfg = require CONFIG_FILE;
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['db_host']};port={$cfg['db_port']};dbname={$cfg['db_name']};charset=utf8mb4",
            $cfg['db_user'],
            $cfg['db_pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        die('<h2 style="color:red;font-family:sans-serif">数据库连接失败：' . htmlspecialchars($e->getMessage()) . '<br><a href="/install.php">重新配置</a></h2>');
    }
    return $pdo;
}

function db_query($sql, $params = []) {
    $st = get_db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function db_fetch($sql, $params = []) {
    return db_query($sql, $params)->fetch();
}

function db_fetchAll($sql, $params = []) {
    return db_query($sql, $params)->fetchAll();
}

function db_insert($sql, $params = []) {
    db_query($sql, $params);
    return get_db()->lastInsertId();
}

function db_count($sql, $params = []) {
    return (int) db_query($sql, $params)->fetchColumn();
}
