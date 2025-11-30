<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_reponse = isset($_POST['reponse_id']) ? (int)$_POST['reponse_id'] : 0;

if ($id_reponse <= 0) {
    echo json_encode(['success' => false, 'message' => 'Reponse invalide']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_joueur FROM commentaire WHERE id_commentaire = ?");
    $stmt->execute([$id_reponse]);
    $reponse = $stmt->fetch();
    
    if (!$reponse) {
        echo json_encode(['success' => false, 'message' => 'Reponse inexistante']);
        exit;
    }
    
    if ($reponse['id_joueur'] != $mon_id) {
        echo json_encode(['success' => false, 'message' => 'Pas ta reponse']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
    $stmt->execute([$id_reponse]);
    
    echo json_encode(['success' => true, 'message' => 'Reponse supprimee']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}