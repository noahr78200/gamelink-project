<?php
// API/igdb.php
// Ce fichier parle avec IGDB et renvoie la liste des jeux au JavaScript.

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ⚠️ MET ICI TES INFOS IGDB / TWITCH
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'jmapwgfaw3021u1ce2zdrqix57gxhz'; // access_token sans espace au début

// On lit les données envoyées en POST (form-data simple)
$mode   = isset($_POST['mode'])   ? $_POST['mode']   : 'popular';
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// On nettoie un peu le texte de recherche pour éviter les guillemets
$search = str_replace('"', '', $search);

// On construit la requête IGDB très simplement
if ($mode === 'search' && $search !== '') {
    // Recherche par nom
    $body = 'search "' . $search . '";
             fields id, name, cover.image_id, first_release_date, rating;
             limit 20;';
} else {
    // Liste "populaire" par défaut
    $body = 'fields id, name, cover.image_id, first_release_date, rating;
             sort popularity desc;
             limit 20;';
}

// On prépare l'appel cURL vers IGDB
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

// Si cURL casse totalement
if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Requête IGDB échouée',
        'details' => $error
    ]);
    exit;
}

// On renvoie exactement le code HTTP d’IGDB (200, 401, etc.)
http_response_code($status);
echo $response;
