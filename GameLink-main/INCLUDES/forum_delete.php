<?php
// INCLUDES/forum_delete.php
// Supprimer une discussion ou une reponse

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

if (!in_array($type, ['discussion', 'reponse'])) {
    echo json_encode(['success' => false, 'message' => 'Type invalide']);
    exit;
}

try {
    if ($type === 'discussion') {
        // Verifier que c'est bien ma discussion
        $stmt = $pdo->prepare("SELECT id_auteur FROM forum_discussion WHERE id_discussion = ?");
        $stmt->execute([$id]);
        $discussion = $stmt->fetch();
        
        if (!$discussion) {
            echo json_encode(['success' => false, 'message' => 'Discussion introuvable']);
            exit;
        }
        
        if ($discussion['id_auteur'] != $mon_id) {
            echo json_encode(['success' => false, 'message' => 'Pas ton post']);
            exit;
        }
        
        // Supprimer (les reponses seront supprimees automatiquement avec ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM forum_discussion WHERE id_discussion = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Discussion supprimee']);
        
    } else {
        // Verifier que c'est bien ma reponse
        $stmt = $pdo->prepare("SELECT id_auteur FROM forum_reponse WHERE id_reponse = ?");
        $stmt->execute([$id]);
        $reponse = $stmt->fetch();
        
        if (!$reponse) {
            echo json_encode(['success' => false, 'message' => 'Reponse introuvable']);
            exit;
        }
        
        if ($reponse['id_auteur'] != $mon_id) {
            echo json_encode(['success' => false, 'message' => 'Pas ton post']);
            exit;
        }
        
        // Supprimer
        $stmt = $pdo->prepare("DELETE FROM forum_reponse WHERE id_reponse = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Reponse supprimee']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}