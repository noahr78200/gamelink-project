<?php
// INCLUDES/forum_get.php
// Recuperer une discussion et ses reponses

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_discussion = isset($_GET['discussion_id']) ? (int)$_GET['discussion_id'] : 0;

if ($id_discussion <= 0) {
    echo json_encode(['success' => false, 'message' => 'Discussion invalide']);
    exit;
}

try {
    // Recuperer la discussion
    $stmt = $pdo->prepare("
        SELECT p.id_publication, p.titre, p.contenu, p.date_creation, p.id_joueur, j.pseudo
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.id_publication = ? AND p.titre IS NOT NULL
    ");
    $stmt->execute([$id_discussion]);
    $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$discussion) {
        echo json_encode(['success' => false, 'message' => 'Discussion introuvable']);
        exit;
    }
    
    // Formater la date
    $temps = strtotime($discussion['date_creation']);
    $diff = time() - $temps;
    if ($diff < 3600) {
        $date_affichee = 'Il y a ' . floor($diff / 60) . ' min';
    } elseif ($diff < 86400) {
        $date_affichee = 'Il y a ' . floor($diff / 3600) . 'h';
    } else {
        $date_affichee = date('d/m/Y H:i', $temps);
    }
    
    $discussion_formatee = [
        'id' => $discussion['id_publication'],
        'titre' => $discussion['titre'],
        'contenu' => $discussion['contenu'],
        'auteur' => $discussion['pseudo'],
        'date' => $date_affichee,
        'est_auteur' => ($discussion['id_joueur'] == $mon_id)
    ];
    
    // Recuperer les reponses (commentaires)
    $stmt = $pdo->prepare("
        SELECT c.id_commentaire, c.contenu, c.date_creation, c.id_joueur, j.pseudo
        FROM commentaire c
        JOIN joueur j ON c.id_joueur = j.id_joueur
        WHERE c.id_publication = ?
        ORDER BY c.date_creation ASC
    ");
    $stmt->execute([$id_discussion]);
    $reponses_brutes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $reponses = [];
    foreach ($reponses_brutes as $rep) {
        $temps = strtotime($rep['date_creation']);
        $diff = time() - $temps;
        if ($diff < 3600) {
            $date_affichee = 'Il y a ' . floor($diff / 60) . ' min';
        } elseif ($diff < 86400) {
            $date_affichee = 'Il y a ' . floor($diff / 3600) . 'h';
        } else {
            $date_affichee = date('d/m/Y H:i', $temps);
        }
        
        $reponses[] = [
            'id' => $rep['id_commentaire'],
            'contenu' => $rep['contenu'],
            'auteur' => $rep['pseudo'],
            'date' => $date_affichee,
            'est_auteur' => ($rep['id_joueur'] == $mon_id)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'discussion' => $discussion_formatee,
        'reponses' => $reponses
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}