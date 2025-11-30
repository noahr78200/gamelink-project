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

if ($id_groupe <= 0) {
    echo json_encode(['success' => false, 'message' => 'Groupe invalide']);
    exit;
}

try {
    $requete = $pdo->prepare("SELECT id_communaute FROM communaute WHERE id_communaute = ?");
    $requete->execute([$id_groupe]);
    $groupe = $requete->fetch();
    
    if (!$groupe) {
        echo json_encode(['success' => false, 'message' => 'Groupe inexistant']);
        exit;
    }
    
    $requete = $pdo->prepare("SELECT COUNT(*) as nb FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    $resultat = $requete->fetch();
    
    if ($resultat['nb'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Deja membre']);
        exit;
    }
    
    $requete = $pdo->prepare("
        INSERT INTO adhesion (id_joueur, id_communaute, role, statut, date_entree) 
        VALUES (?, ?, 'membre', 'actif', NOW())
    ");
    $requete->execute([$mon_id, $id_groupe]);
    
    echo json_encode(['success' => true, 'message' => 'Groupe rejoint !']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}