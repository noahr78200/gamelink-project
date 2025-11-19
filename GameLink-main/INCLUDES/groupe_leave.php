<?php
/*
 * ========================================
 * QUITTER UN GROUPE - VERSION SIMPLE
 * ========================================
 * 
 * Ce fichier permet de quitter un groupe
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
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

try {
    // ÉTAPE 6 : Me retirer du groupe
    $requete = $pdo->prepare("DELETE FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    
    // ÉTAPE 7 : Répondre
    if ($requete->rowCount() > 0) {
        // Ça a marché
        echo json_encode(['success' => true, 'message' => 'Tu as quitté le groupe']);
    } else {
        // Tu n'étais pas membre
        echo json_encode(['success' => false, 'message' => 'Tu n\'étais pas membre de ce groupe']);
    }
    
} catch (Exception $erreur) {
    // Problème
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}