<?php
session_start();

require_once __DIR__ . '/../DATA/DBConfig.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];

$json = file_get_contents('php://input');
$config = json_decode($json, true);

if (!$config || !isset($config['skinColor'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE joueur SET avatar_config = ? WHERE id_joueur = ?");
    $stmt->execute([json_encode($config), $user_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}