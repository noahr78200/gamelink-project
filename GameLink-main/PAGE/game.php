<?php
session_start();

// Connexion BDD
require __DIR__ . '/../DATA/DBConfig.php';
require_once __DIR__ . '/../INCLUDES/track.php';
require_once __DIR__ . '/../INCLUDES/check_admin.php';

// On inclut TON fichier igdb actuel sans rien modifier
require __DIR__ . '/../API/igdb.php';

// Fonction sécuriser texte
function h($x) { return htmlspecialchars($x, ENT_QUOTES, 'UTF-8'); }

// ID du jeu (ID IGDB)
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}
$gameId = (int)$_GET['id'];

// Récupère le jeu depuis TON igdb.php
$game = getGameById($gameId); // ⚠️ Ton fichier utilise sûrement getGameById()
if (!$game) {
    header("Location: ../index.php");
    exit;
}

// Infos importantes
$title  = $game['name'] ?? "Sans titre";
$cover  = isset($game['cover']['url']) ? $game['cover']['url'] : "../IMG/placeholder.jpg";
$genres = isset($game['genres']) ? implode(", ", array_column($game['genres'], 'name')) : "Inconnu";
$summary = $game['summary'] ?? "Aucune description";

// Joueur connecté
$userId = $_SESSION['user_id'] ?? null;

// Connexion PDO
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// -----------------------------------------------------
// 1) SAUVEGARDE DES NOTES
// -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$userId) {
        header("Location: AUTH.php");
        exit;
    }

    $rating = max(1, min(5, (int)$_POST['rating']));
    $now = date("Y-m-d H:i:s");

    $sql = "INSERT INTO avis (id_joueur, id_jeu, valeur, date_notation)
            VALUES (:u, :g, :v, :d)
            ON DUPLICATE KEY UPDATE valeur = :v, date_notation = :d";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':u' => $userId,
        ':g' => $gameId,
        ':v' => $rating,
        ':d' => $now
    ]);

    header("Location: game.php?id=" . $gameId);
    exit;
}

// -----------------------------------------------------
// 2) SAUVEGARDE DES COMMENTAIRES
// -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    if (!$userId) {
        header("Location: AUTH.php");
        exit;
    }

    $comment = trim($_POST['comment_text']);
    $now = date("Y-m-d H:i:s");

    if ($comment !== "") {
        $sql = "INSERT INTO avis (id_joueur, id_jeu, texte_commentaire, date_commentaire)
                VALUES (:u, :g, :t, :d)
                ON DUPLICATE KEY UPDATE texte_commentaire = :t, date_commentaire = :d";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':u' => $userId,
            ':g' => $gameId,
            ':t' => $comment,
            ':d' => $now
        ]);
    }

    header("Location: game.php?id=" . $gameId);
    exit;
}

// -----------------------------------------------------
// 3) RECUP NOTES
// -----------------------------------------------------
$sql = "SELECT valeur FROM avis WHERE id_jeu = :id AND valeur IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $gameId]);
$notes = $stmt->fetchAll();

$count = count($notes);
$avg = 0;
if ($count > 0) {
    $sum = 0;
    foreach ($notes as $n) $sum += (int)$n['valeur'];
    $avg = round($sum / $count, 1);
}

// -----------------------------------------------------
// 4) MA NOTE
// -----------------------------------------------------
$myRating = null;
if ($userId) {
    $sql = "SELECT valeur FROM avis WHERE id_joueur = :u AND id_jeu = :g";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':u' => $userId, ':g' => $gameId]);
    $myRating = $stmt->fetchColumn();
}

// -----------------------------------------------------
// 5) COMMENTAIRES
// -----------------------------------------------------
$sql = "SELECT a.texte_commentaire, a.date_commentaire, j.pseudo
        FROM avis a
        JOIN joueur j ON j.id_joueur = a.id_joueur
        WHERE a.id_jeu = :g AND texte_commentaire IS NOT NULL
        ORDER BY date_commentaire DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':g' => $gameId]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">

</head>
 <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
<body>

<a href="ACCUEIL.php" class="back">← Retour</a>

<div class="page">

    <div class="left">
        <img src="<?= h($cover) ?>" class="cover">
    </div>

    <div class="right">

        <h1><?= h($title) ?></h1>
        <p class="genres"><?= h($genres) ?></p>

        <h2>Note moyenne : <?= $avg ?> / 5</h2>

        <p>
            <?php for ($i=1; $i<=5; $i++): ?>
                <?= ($i <= round($avg)) ? "★" : "☆" ?>
            <?php endfor; ?>
        </p>

        <!-- Donner une note -->
        <?php if ($userId): ?>
        <form method="post">
            <button name="rating" value="1">★</button>
            <button name="rating" value="2">★★</button>
            <button name="rating" value="3">★★★</button>
            <button name="rating" value="4">★★★★</button>
            <button name="rating" value="5">★★★★★</button>
        </form>
        <?php else: ?>
            <p>Connecte-toi pour noter.</p>
        <?php endif; ?>

        <h2>Description</h2>
        <p><?= nl2br(h($summary)) ?></p>

        <h2>Commentaires</h2>

        <!-- Form commentaire -->
        <?php if ($userId): ?>
        <form method="post">
            <textarea name="comment_text" required></textarea>
            <button>Envoyer</button>
        </form>
        <?php else: ?>
            <p>Connecte-toi pour commenter.</p>
        <?php endif; ?>

        <!-- Liste -->
        <?php if (empty($comments)): ?>
            <p>Aucun commentaire pour l'instant.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div class="comment">
                    <strong><?= h($c['pseudo']) ?></strong>
                    <small><?= h($c['date_commentaire']) ?></small>
                    <p><?= nl2br(h($c['texte_commentaire'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>

</body>

</html>
