<?php
// Démarrer la session
session_start();

// Protection : redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?login_required=1');
    exit;
}

// Connexion à la base de données
require_once __DIR__ . '/../DATA/DBConfig.php';

// Récupérer les infos de l'utilisateur
$user_id = $_SESSION['user_id'];
$user_pseudo = $_SESSION['user_pseudo'];

// ========================================
// ÉTAPE 1 : RÉCUPÉRER LES JEUX À 5 ÉTOILES
// ========================================
$topGames = [];
try {
    $sql = "SELECT j.id_jeu, j.titre, j.cover_url, 
                   AVG(a.valeur) as note_moyenne, 
                   COUNT(a.valeur) as nombre_votes
            FROM jeu j
            JOIN avis a ON a.id_jeu = j.id_jeu
            WHERE a.valeur IS NOT NULL
            GROUP BY j.id_jeu
            HAVING note_moyenne = 5
            ORDER BY nombre_votes DESC
            LIMIT 10";
    $stmt = $pdo->query($sql);
    $topGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $topGames = [];
}

// ========================================
// ÉTAPE 2 : RÉCUPÉRER LES JEUX PAR GENRE
// ========================================

// Informations API IGDB
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

// Fonction SIMPLE pour récupérer des jeux par ID de genre
function getGamesByGenreId($genreId, $CLIENT_ID, $TOKEN) {
    // Requête IGDB avec l'ID du genre
    $requete = 'fields id, name, cover.image_id, rating;
                where genres = (' . $genreId . ') & rating != null;
                sort rating desc;
                limit 12;';
    
    $ch = curl_init('https://api.igdb.com/v4/games');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Client-ID: ' . $CLIENT_ID,
        'Authorization: Bearer ' . $TOKEN
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requete);
    
    $reponse = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($reponse, true);
    return is_array($data) ? $data : [];
}

// IDs de genres IGDB (les vrais IDs !)
// RPG = 12
// Shooter = 5
// Racing = 10
// Action = 4 (Platform) ou 25 (Hack and slash)
// Strategy = 15

$genres = [
    'RPG' => getGamesByGenreId(12, $CLIENT_ID, $TOKEN),
    'Shooter' => getGamesByGenreId(5, $CLIENT_ID, $TOKEN),
    'Course' => getGamesByGenreId(10, $CLIENT_ID, $TOKEN),
    'Action' => getGamesByGenreId(25, $CLIENT_ID, $TOKEN),
    'Stratégie' => getGamesByGenreId(15, $CLIENT_ID, $TOKEN)
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil | GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="stylesheet" href="../CSS/STYLE_ACCUEIL.css">
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
</head>
<body>

<?php 
if (file_exists(__DIR__ . '/../INCLUDES/header.php')) {
    include __DIR__ . '/../INCLUDES/header.php';
}
?>

<main>
    
    <!-- BANNIÈRE DE BIENVENUE -->
    <div class="welcome-banner">
        <h1>👋 Bienvenue, <?= htmlspecialchars($user_pseudo) ?> !</h1>
        <p>Découvrez les meilleurs jeux vidéo du moment</p>
    </div>

    <!-- SECTION : JEUX À 5 ÉTOILES -->
    <div class="carousel-container">
        <h2 class="section-title">⭐ Les meilleurs jeux (5 étoiles)</h2>
        
        <?php if (empty($topGames)): ?>
            <div class="empty-state">
                <p>Aucun jeu n'a encore 5 étoiles</p>
                <a href="RECHERCHE.php" class="btn-recherche">Rechercher des jeux</a>
            </div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('top-games', -1)">‹</button>
            <div class="carousel" id="top-games">
                <?php foreach ($topGames as $game): ?>
                    <a href="game.php?id=<?= $game['id_jeu'] ?>" class="big-game-card">
                        <img src="<?= htmlspecialchars($game['cover_url']) ?>" 
                             alt="<?= htmlspecialchars($game['titre']) ?>"
                             class="big-game-image">
                        <div class="big-game-info">
                            <p class="big-game-name"><?= htmlspecialchars($game['titre']) ?></p>
                        </div>
                       
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('top-games', 1)">›</button>
        <?php endif; ?>
    </div>

    <!-- SECTION : RPG -->
    <div class="carousel-container">
        <h2 class="section-title">🎭 RPG</h2>
        
        <?php if (empty($genres['RPG'])): ?>
            <div class="loading">Chargement des jeux RPG...</div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('rpg', -1)">‹</button>
            <div class="carousel" id="rpg">
                <?php foreach ($genres['RPG'] as $game): ?>
                    <?php 
                    $coverUrl = isset($game['cover']['image_id']) 
                        ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                        : '../IMG/placeholder.jpg';
                    $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                    ?>
                    <a href="game.php?id=<?= $game['id'] ?>" class="game-card">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             class="game-image">
                        <div class="default-title">
                            <p><?= htmlspecialchars($game['name']) ?></p>
                        </div>
                       
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('rpg', 1)">›</button>
        <?php endif; ?>
    </div>

    <!-- SECTION : SHOOTER -->
    <div class="carousel-container">
        <h2 class="section-title">🔫 Shooter</h2>
        
        <?php if (empty($genres['Shooter'])): ?>
            <div class="loading">Chargement des jeux Shooter...</div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('shooter', -1)">‹</button>
            <div class="carousel" id="shooter">
                <?php foreach ($genres['Shooter'] as $game): ?>
                    <?php 
                    $coverUrl = isset($game['cover']['image_id']) 
                        ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                        : '../IMG/placeholder.jpg';
                    $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                    ?>
                    <a href="game.php?id=<?= $game['id'] ?>" class="game-card">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             class="game-image">
                        <div class="default-title">
                            <p><?= htmlspecialchars($game['name']) ?></p>
                        </div>
                      
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('shooter', 1)">›</button>
        <?php endif; ?>
    </div>

    <!-- SECTION : COURSE -->
    <div class="carousel-container">
        <h2 class="section-title">🏎️ Course</h2>
        
        <?php if (empty($genres['Course'])): ?>
            <div class="loading">Chargement des jeux de course...</div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('course', -1)">‹</button>
            <div class="carousel" id="course">
                <?php foreach ($genres['Course'] as $game): ?>
                    <?php 
                    $coverUrl = isset($game['cover']['image_id']) 
                        ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                        : '../IMG/placeholder.jpg';
                    $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                    ?>
                    <a href="game.php?id=<?= $game['id'] ?>" class="game-card">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             class="game-image">
                        <div class="default-title">
                            <p><?= htmlspecialchars($game['name']) ?></p>
                        </div>
                       
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('course', 1)">›</button>
        <?php endif; ?>
    </div>

    <!-- SECTION : ACTION -->
    <div class="carousel-container">
        <h2 class="section-title">💥 Action</h2>
        
        <?php if (empty($genres['Action'])): ?>
            <div class="loading">Chargement des jeux d'action...</div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('action', -1)">‹</button>
            <div class="carousel" id="action">
                <?php foreach ($genres['Action'] as $game): ?>
                    <?php 
                    $coverUrl = isset($game['cover']['image_id']) 
                        ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                        : '../IMG/placeholder.jpg';
                    $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                    ?>
                    <a href="game.php?id=<?= $game['id'] ?>" class="game-card">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             class="game-image">
                        <div class="default-title">
                            <p><?= htmlspecialchars($game['name']) ?></p>
                        </div>
                       
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('action', 1)">›</button>
        <?php endif; ?>
    </div>

    <!-- SECTION : STRATÉGIE -->
    <div class="carousel-container">
        <h2 class="section-title">🧠 Stratégie</h2>
        
        <?php if (empty($genres['Stratégie'])): ?>
            <div class="loading">Chargement des jeux de stratégie...</div>
        <?php else: ?>
            <button class="arrow arrow-left" onclick="scrollCarousel('strategie', -1)">‹</button>
            <div class="carousel" id="strategie">
                <?php foreach ($genres['Stratégie'] as $game): ?>
                    <?php 
                    $coverUrl = isset($game['cover']['image_id']) 
                        ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                        : '../IMG/placeholder.jpg';
                    $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                    ?>
                    <a href="game.php?id=<?= $game['id'] ?>" class="game-card">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($game['name']) ?>"
                             class="game-image">
                        <div class="default-title">
                            <p><?= htmlspecialchars($game['name']) ?></p>
                        </div>
                        
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="arrow arrow-right" onclick="scrollCarousel('strategie', 1)">›</button>
        <?php endif; ?>
    </div>

</main>

<script>
// Fonction SIMPLE pour faire défiler les carrousels
function scrollCarousel(carouselId, direction) {
    var carousel = document.getElementById(carouselId);
    
    // Taille différente pour le carrousel des jeux à 5 étoiles
    if (carouselId === 'top-games') {
        var scrollAmount = 460; // largeur d'une grande carte + gap
    } else {
        var scrollAmount = 240; // largeur d'une petite carte + gap
    }
    
    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>

</body>
</html>