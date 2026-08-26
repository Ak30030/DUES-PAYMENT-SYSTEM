<?php
if (!getenv('DB_HOST') && file_exists(__DIR__ . '/local_env.php')) {
    require_once __DIR__ . '/local_env.php';
}

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

if (!$db_host || !$db_name || !$db_user) {
    die('Database environment variables are not set.');
}

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed.');
}