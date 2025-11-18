<?php
// INCLUDES/groupe_messages.php - Récupérer les messages d'un groupe
session_start();
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$groupe_id = isset($_GET['groupe_id']) ? (int)$_GET['groupe_id'] : 0;

try {
    // Vérifier que l'utilisateur est membre du groupe
    $stmt = $pdo->prepare("
        SELECT id_joueur 
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $stmt->execute([$user_id, $groupe_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tu dois être membre pour voir les messages']);
        exit;
    }
    
    // Récupérer les messages (les 50 derniers)
    $stmt = $pdo->prepare("
        SELECT p.id_publication, p.contenu, p.date_creation, j.pseudo
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.id_communaute = ?
        ORDER BY p.date_creation ASC
        LIMIT 50
    ");
    $stmt->execute([$groupe_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les messages avec l'heure
    $messages_formated = [];
    foreach ($messages as $msg) {
        $messages_formated[] = [
            'id' => $msg['id_publication'],
            'pseudo' => $msg['pseudo'],
            'contenu' => $msg['contenu'],
            'heure' => date('H:i', strtotime($msg['date_creation']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages_formated
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur récupération messages : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}