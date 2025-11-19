<?php
/*
 * ========================================
 * RÉCUPÉRER LES MESSAGES - VERSION SIMPLE
 * ========================================
 * 
 * Ce fichier récupère tous les messages d'un groupe
 * 
 */

// ÉTAPE 1 : Démarrer la session
session_start();

// ÉTAPE 2 : Dire qu'on envoie du JSON
header('Content-Type: application/json');

// ÉTAPE 3 : Vérifier que tu es connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

// ÉTAPE 4 : Se connecter à la base de données
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 5 : Récupérer les infos
$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_GET['groupe_id']) ? (int)$_GET['groupe_id'] : 0;

// Vérifier que l'ID du groupe est valide
if ($id_groupe <= 0) {
    echo json_encode(['success' => false, 'message' => 'Groupe invalide']);
    exit;
}

try {
    // ÉTAPE 6 : Vérifier que je suis membre du groupe
    $requete = $pdo->prepare("
        SELECT id_adhesion 
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $requete->execute([$mon_id, $id_groupe]);
    
    if (!$requete->fetch()) {
        // Je ne suis pas membre
        echo json_encode(['success' => false, 'message' => 'Tu dois être membre pour voir les messages']);
        exit;
    }
    
    // ÉTAPE 7 : Récupérer les 50 derniers messages
    $requete = $pdo->prepare("
        SELECT p.id_publication, p.contenu, p.date_creation, j.pseudo
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.id_communaute = ? AND p.titre IS NULL
        ORDER BY p.date_creation ASC
        LIMIT 50
    ");
    $requete->execute([$id_groupe]);
    $messages_bruts = $requete->fetchAll();
    
    // ÉTAPE 8 : Mettre en forme les messages
    $messages = [];
    foreach ($messages_bruts as $msg) {
        $messages[] = [
            'id' => $msg['id_publication'],
            'pseudo' => $msg['pseudo'],
            'contenu' => $msg['contenu'],
            'heure' => date('H:i', strtotime($msg['date_creation']))  // Juste l'heure (ex: 14:30)
        ];
    }
    
    // ÉTAPE 9 : Envoyer les messages
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'nombre' => count($messages)
    ]);
    
} catch (Exception $erreur) {
    // Problème
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}