<?php
// PAGE/game.php
// Affiche les infos détaillées d'un jeu IGDB à partir de son id dans l'URL.

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo "ID invalide";
    exit;
}

// ⚠️ Même config IGDB que igdb.php (tu peux factoriser plus tard si tu veux)
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'lilj1h9k3hhug3bcmkch3ujuraj8y3';

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

// Normalement on a un tableau avec un seul jeu
$game = isset($data[0]) ? $data[0] : null;

if (!$game) {
    http_response_code(404);
    echo "Jeu introuvable.";
    exit;
}

// Préparation des infos pour l'affichage
$name   = isset($game['name']) ? $game['name'] : 'Jeu';
$cover  = '';

if (isset($game['cover']['image_id'])) {
    $cover = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' .
             $game['cover']['image_id'] . '.jpg';
} else {
    $cover = 'https://placehold.co/264x352?text=' . urlencode($name);
}

$rating = isset($game['rating']) ? (int)$game['rating'] : null;
$summary = isset($game['summary']) ? $game['summary'] : 'Aucun résumé disponible.';

$releaseDateText = 'Date inconnue';
if (isset($game['first_release_date'])) {
    $dateObj = new DateTime('@' . $game['first_release_date']);
    $dateObj->setTimezone(new DateTimeZone('Europe/Paris'));
    $releaseDateText = $dateObj->format('d/m/Y');
}

$genres = [];
if (isset($game['genres']) && is_array($game['genres'])) {
    foreach ($game['genres'] as $genre) {
        if (isset($genre['name'])) {
            $genres[] = $genre['name'];
        }
    }
}

$platforms = [];
if (isset($game['platforms']) && is_array($game['platforms'])) {
    foreach ($game['platforms'] as $plat) {
        if (isset($plat['name'])) {
            $platforms[] = $plat['name'];
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($name); ?> | GameLink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
</head>
<body class="game">
    <main class="game-wrap">
        <section class="game-hero">
            <div class="game-grid">
                <div class="game-cover-wrapper">
                    <img class="cover-game" src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="game-infos">
                    <h1 class="game-title"><?php echo htmlspecialchars($name); ?></h1>

                    <p class="game-meta">
                        <strong>Sortie :</strong> <?php echo htmlspecialchars($releaseDateText); ?><br>
                        <?php if ($rating !== null): ?>
                            <strong>Note :</strong> <?php echo $rating; ?>/100
                        <?php else: ?>
                            <strong>Note :</strong> inconnue
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($genres)): ?>
                        <p><strong>Genres :</strong> <?php echo htmlspecialchars(implode(', ', $genres)); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($platforms)): ?>
                        <p><strong>Plateformes :</strong> <?php echo htmlspecialchars(implode(', ', $platforms)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="game-summary">
            <h2>Résumé</h2>
            <p><?php echo nl2br(htmlspecialchars($summary)); ?></p>
        </section>

        <p class="back-link">
            <a href="RECHERCHE.php">← Retour à la recherche</a>
        </p>
    </main>
</body>
</html>
