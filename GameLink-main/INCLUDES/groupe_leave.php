<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

try {
    $requete = $pdo->prepare("DELETE FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    
    if ($requete->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Groupe quitte']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pas membre']);
    }
    
} catch (Exception $erreur) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}