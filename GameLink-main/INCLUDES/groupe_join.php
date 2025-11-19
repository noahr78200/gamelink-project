<?php
// INCLUDES/groupe_join.php
// Rejoindre un groupe

session_start();
header('Content-Type: application/json');

// Verifier connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

try {
    // Verifier que le groupe existe
    $requete = $pdo->prepare("SELECT id_communaute FROM communaute WHERE id_communaute = ?");
    $requete->execute([$id_groupe]);
    
    if (!$requete->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Groupe inexistant']);
        exit;
    }
    
    // Verifier que je ne suis pas deja membre
    $requete = $pdo->prepare("SELECT id_adhesion FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    
    if ($requete->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Deja membre']);
        exit;
    }
    
    // M'ajouter au groupe
    $requete = $pdo->prepare("
        INSERT INTO adhesion (id_joueur, id_communaute, role, statut, date_entree) 
        VALUES (?, ?, 'membre', 'actif', NOW())
    ");
    $requete->execute([$mon_id, $id_groupe]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Groupe rejoint !'
    ]);
    
} catch (Exception $erreur) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}