<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../DATA/DBConfig.php';
}

$user_id = $_SESSION['user_id'] ?? null;

$page = $_SERVER['REQUEST_URI'] ?? '/';

try {
    $stmt = $pdo->prepare("
        INSERT INTO page_views (user_id, page_url, viewed_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$user_id, $page]);
} catch (Exception $e) {
}

if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, last_activity, page_url)
            VALUES (?, NOW(), ?)
            ON DUPLICATE KEY UPDATE 
                last_activity = NOW(), 
                page_url = VALUES(page_url)
        ");
        $stmt->execute([$user_id, $page]);
    } catch (Exception $e) {
    }
}