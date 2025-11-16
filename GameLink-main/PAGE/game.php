<?php
// PAGE/game.php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "ID invalide.";
    exit;
}

$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

$body = 'fields id, name, cover.image_id, genres.name, platforms.name, summary, rating, first_release_date;
         where id = ' . $id . ';
         limit 1;';

$ch = curl_init('https://api.igdb.com/v4/games');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Client-ID: ' . $CLIENT_ID,
        'Authorization: Bearer ' . $TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS     => $body
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);

curl_close($ch);

if ($response === false || $status !== 200) {
    http_response_code(500);
    echo "Erreur lors du chargement du jeu.";
    exit;
}

$data = json_decode($response, true);
$game = isset($data[0]) ? $data[0] : null;

if (!$game) {
    http_response_code(404);
    echo "Jeu introuvable.";
    exit;
}

$name = $game['name'] ?? 'Jeu';
$cover = isset($game['cover']['image_id'])
    ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
    : 'https://placehold.co/264x352?text=' . urlencode($name);

$rating = isset($game['rating']) ? (int)$game['rating'] : null;
$summary = $game['summary'] ?? 'Aucun résumé disponible.';

$release = 'Date inconnue';
if (isset($game['first_release_date'])) {
    $d = new DateTime('@' . $game['first_release_date']);
    $d->setTimezone(new DateTimeZone('Europe/Paris'));
    $release = $d->format('d/m/Y');
}

$genres = [];
if (!empty($game['genres'])) {
    foreach ($game['genres'] as $g) {
        if (!empty($g['name'])) {
            $genres[] = $g['name'];
        }
    }
}

$platforms = [];
if (!empty($game['platforms'])) {
    foreach ($game['platforms'] as $p) {
        if (!empty($p['name'])) {
            $platforms[] = $p['name'];
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($name); ?> | GameLink</title>
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">
</head>
 <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
<body class="game">
    <main class="game-wrap">
        <section class="game-hero">
            <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="cover-game">
            <h1><?php echo htmlspecialchars($name); ?></h1>
            <p><strong>Sortie :</strong> <?php echo htmlspecialchars($release); ?></p>
            <p>
                <strong>Note :</strong>
                <?php echo $rating !== null ? $rating . '/100' : 'Inconnue'; ?>
            </p>
            <?php if ($genres): ?>
                <p><strong>Genres :</strong> <?php echo htmlspecialchars(implode(', ', $genres)); ?></p>
            <?php endif; ?>
            <?php if ($platforms): ?>
                <p><strong>Plateformes :</strong> <?php echo htmlspecialchars(implode(', ', $platforms)); ?></p>
            <?php endif; ?>
        </section>

        <section class="game-summary">
            <h2>Résumé</h2>
            <p><?php echo nl2br(htmlspecialchars($summary)); ?></p>
        </section>

        <p><a href="RECHERCHE.php">← Retour à la recherche</a></p>
    </main>
</body>
</html>
