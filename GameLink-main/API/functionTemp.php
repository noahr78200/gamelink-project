<?php
// functionTemp.php
// Petite API pour noter un jeu, le mettre en favori, backlog, etc.

session_start();
header('Content-Type: application/json');

// Ici on suppose que tu as une connexion PDO dans DBConfig.php
require __DIR__ . '/../DBConfig.php';

// Id utilisateur (à adapter quand tu auras ton vrai système d'auth)
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

if ($user_id <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Utilisateur non connecté']);
    exit;
}

// On choisit l’action grâce au paramètre ?action=...
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ---------------- NOTER UN JEU ----------------
if ($action === 'rate') {
    $game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
    $rating  = isset($_POST['rating'])  ? (int)$_POST['rating']  : -1;

    if ($game_id <= 0 || $rating < 0 || $rating > 100) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paramètres invalides']);
        exit;
    }

    // On supprime l’ancienne note puis on insère la nouvelle
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user_id, $game_id]);

    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, game_id, rating) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $game_id, $rating]);

    echo json_encode(['ok' => true]);
    exit;
}

// --------------- FAVORI (ON/OFF) ----------------
if ($action === 'favorite') {
    $game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;

    if ($game_id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paramètres invalides']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user_id, $game_id]);

    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND game_id = ?")
            ->execute([$user_id, $game_id]);
        echo json_encode(['ok' => true, 'favori' => false]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, game_id) VALUES (?, ?)")
            ->execute([$user_id, $game_id]);
        echo json_encode(['ok' => true, 'favori' => true]);
    }
    exit;
}

// --------------- BACKLOG (A FAIRE) ON/OFF --------------
if ($action === 'backlog') {
    $game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;

    if ($game_id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paramètres invalides']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM backlog WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user_id, $game_id]);

    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM backlog WHERE user_id = ? AND game_id = ?")
            ->execute([$user_id, $game_id]);
        echo json_encode(['ok' => true, 'todo' => false]);
    } else {
        $pdo->prepare("INSERT INTO backlog (user_id, game_id) VALUES (?, ?)")
            ->execute([$user_id, $game_id]);
        echo json_encode(['ok' => true, 'todo' => true]);
    }
    exit;
}

// --------------- LISTE DES COMMENTAIRES --------------
if ($action === 'list_comments') {
    $game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

    if ($game_id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paramètres invalides']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, content, created_at FROM comments WHERE game_id = ? ORDER BY created_at DESC");
    $stmt->execute([$game_id]);

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'comments' => $comments]);
    exit;
}

// --------------- AJOUTER UN COMMENTAIRE --------------
if ($action === 'add_comment') {
    $game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if ($game_id <= 0 || $content === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Paramètres invalides']);
        exit;
    }

    if (mb_strlen($content) > 2000) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Commentaire trop long']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO comments (user_id, game_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $game_id, $content]);

    echo json_encode(['ok' => true]);
    exit;
}

// --------------- ACTION INCONNUE --------------
http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Action inconnue']);
