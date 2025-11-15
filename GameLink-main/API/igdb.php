<?php
// API/igdb.php
// - Si on reçoit search vide  => liste de jeux A→Z
// - Si search contient un texte => recherche par nom

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ⚠️ Tes identifiants IGDB / Twitch :
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'jmapwgfaw3021u1ce2zdrqix57gxhz'; // ton access_token

// On lit la recherche envoyée par le JS
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$search = str_replace('"', '', $search); // on enlève les " pour pas casser la requête

// Corps de la requête IGDB (en langage IGDB)
if ($search !== '') {
    // 🔎 Recherche par nom
    $body = 'search "' . $search . '";
             fields id, name, first_release_date, rating,
                    cover.image_id,
                    genres.name,
                    platforms.name,
                    involved_companies.company.name;
             sort name asc;
             limit ;';
} else {
    // 📚 Liste A → Z par défaut
    $body = 'fields id, name, first_release_date, rating,
                    cover.image_id,
                    genres.name,
                    platforms.name,
                    involved_companies.company.name;
             sort name asc;
             limit 50;';
}

// Vérifier que cURL existe
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL PHP n\'est pas installé.']);
    exit;
}

// Appel vers IGDB
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

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Requête IGDB échouée',
        'details' => $error
    ]);
    exit;
}

// On renvoie ce que dit IGDB
http_response_code($status);
echo $response;
