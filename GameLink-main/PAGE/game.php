<?php
//id depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo "ID invalide"; exit; }

$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = '04ier75mkj5q1ez34le11sydhn3ckg';

$body = 'fields name, cover.image_id, genres.name, platforms.name, summary, rating, first_release_date;
         where id = ' . $id . ';
         limit 1;';

$ch = curl_init('https://api.igdb.com/v4/games');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Client-ID: $CLIENT_ID",
    "Authorization: Bearer $TOKEN",
    "Accept: application/json",
    "Content-Type: text/plain"
  ],
  CURLOPT_POSTFIELDS => $body,
  CURLOPT_TIMEOUT => 12,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 400 || $resp === false) { http_response_code($code ?: 500); echo "Erreur IGDB"; exit; }

$data = json_decode($resp, true);
$g = (is_array($data) && isset($data[0])) ? $data[0] : null;
if (!$g) { http_response_code(404); echo "Jeu introuvable"; exit; }

$cover = (isset($g['cover']['image_id']))
  ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/'.$g['cover']['image_id'].'.jpg'
  : 'https://placehold.co/400x540?text='.urlencode($g['name'] ?? 'Jeu');

function listNames($arr){ return $arr ? implode(', ', array_map(fn($x)=>$x['name'], $arr)) : '—'; }
?>


<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($g['name'] ?? 'Jeu') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/GameLink/CSS/STYLE_GAME.css">
  <link rel="icon" type="image/png" sizes="32x32" href="/GameLink/ICON/LogoSimple.svg">
</head>
  <body class="game">
    <main class="game-wrap">
      <section class="game-hero">
        <div class="game-grid">
          <img class="cover-game" src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($g['name'] ?? 'cover') ?>">
          <div class="game-info">
            <h1><?= htmlspecialchars($g['name'] ?? '') ?></h1>

            <strong>Genres :</strong> <?= htmlspecialchars(listNames($g['genres'] ?? null)) ?><br>
            <strong>Plateformes :</strong> <?= htmlspecialchars(listNames($g['platforms'] ?? null)) ?><br>
            <strong>Note IGDB :</strong> <?= isset($g['rating']) ? round($g['rating']).' ⭐' : '—' ?><br>
            <strong>Sortie :</strong> 
            <?php
            if (!empty($g['first_release_date'])) {
              echo date('d/m/Y', (int)$g['first_release_date']);
            } else {
              echo '—';
            }
            ?>

            <h3 class="game-subtitle">Description :</h3>
            <p class="game-desc">
              <p><?= nl2br(htmlspecialchars($g['summary'] ?? 'Aucun résumé.')) ?></p>
            </p>
          </div>
        </div>
      </section>
    </main>

    <a class="back" href="/GameLink/PAGE/RECHERCHE.php">←</a>
    
    <button class="fab-fav" aria-label="Favori">
      <img class="star" src="/GameLink/ICON/SVG/STAR_GREY.svg" alt="STAR" width="">
    </button>
    
    <button class="fab-fav" aria-label="Add">
      <img class="star" src="/GameLink/ICON/SVG/ADD_LOGO.svg" alt="Add btn" width="">
    </button>
    
  </body>

</html>