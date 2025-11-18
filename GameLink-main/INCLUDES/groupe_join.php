<?php
// INCLUDES/groupe_join.php - Rejoindre un groupe
session_start();
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$groupe_id = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

// Vérifier que le groupe existe
try {
    $stmt = $pdo->prepare("SELECT id_communaute FROM communaute WHERE id_communaute = ?");
    $stmt->execute([$groupe_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ce groupe n\'existe pas']);
        exit;
    }
    
    // Vérifier si l'utilisateur n'est pas déjà membre
    $stmt = $pdo->prepare("SELECT id_joueur FROM adhesion WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'");
    $stmt->execute([$user_id, $groupe_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tu es déjà membre de ce groupe']);
        exit;
    }
    
    // Ajouter l'utilisateur au groupe
    $stmt = $pdo->prepare("
        INSERT INTO adhesion (id_joueur, id_communaute, role, statut, date_entree) 
        VALUES (?, ?, 'membre', 'actif', NOW())
    ");
    $stmt->execute([$user_id, $groupe_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tu as rejoint le groupe avec succès !'
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur rejoindre groupe : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}