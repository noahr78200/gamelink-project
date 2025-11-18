<?php
// INCLUDES/groupe_leave.php - Quitter un groupe
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

try {
    // Supprimer l'adhésion
    $stmt = $pdo->prepare("
        DELETE FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ?
    ");
    $stmt->execute([$user_id, $groupe_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tu as quitté le groupe'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Tu n\'es pas membre de ce groupe'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erreur quitter groupe : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}