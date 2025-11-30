<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_discussion = isset($_POST['discussion_id']) ? (int)$_POST['discussion_id'] : 0;
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

if ($id_discussion <= 0) {
    echo json_encode(['success' => false, 'message' => 'Discussion invalide']);
    exit;
}

if (empty($contenu)) {
    echo json_encode(['success' => false, 'message' => 'Message vide']);
    exit;
}

if (strlen($contenu) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Message trop long (max 2000)']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_publication FROM publication WHERE id_publication = ? AND titre IS NOT NULL");
    $stmt->execute([$id_discussion]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Discussion inexistante']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO commentaire (id_publication, id_joueur, contenu, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$id_discussion, $mon_id, $contenu]);
    
    echo json_encode(['success' => true, 'message' => 'Reponse publiee !', 'id_reponse' => $pdo->lastInsertId()]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}