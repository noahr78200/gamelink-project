
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// 🔍 Test : est-ce que curl existe ?
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode([
        'error' => 'L’extension PHP cURL n’est pas installée ou activée.'
    ]);
    exit;
}

// 🟢 TES IDENTIFIANTS IGDB / TWITCH
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'jmapwgfaw3021u1ce2zdrqix57gxhz'; // access_token récupéré avec curl

// On lit les données envoyées en POST (FormData)
$mode   = isset($_POST['mode'])   ? $_POST['mode']   : 'popular';
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// On nettoie un peu
$search = str_replace('"', '', $search);

// On prépare la requête texte pour IGDB
if ($mode === 'search' && $search !== '') {
    // Recherche par nom
    $body = 'search "' . $search . '";
             fields id, name, cover.image_id, first_release_date, rating;
             limit 20;';
} else {
    // Jeux “populaires” par défaut
    $body = 'fields id, name, cover.image_id, first_release_date, rating, popularity;
             sort popularity desc;
             limit 20;';
}

// On prépare cURL vers IGDB
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

// Si cURL plante complètement
if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Requête IGDB échouée',
        'details' => $error
    ]);
    exit;
}

// On renvoie le code HTTP d’IGDB (200, 401…)
http_response_code($status);
echo $response;
