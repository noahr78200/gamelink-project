<?php
// Démarrer la session
session_start();
require_once __DIR__ . '/../INCLUDES/track.php';
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
// 1. RÉCUPÉRER LES 10 MEILLEURS JEUX (5 étoiles)
// ========================================
$topGames = [];
try {
    // Récupérer les jeux avec une note moyenne de 5 étoiles
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
    // Si erreur, on continue avec un tableau vide
    $topGames = [];
}

// ========================================
// 2. RÉCUPÉRER LES JEUX PAR GENRE DEPUIS IGDB
// ========================================

// Informations API IGDB
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

// Fonction pour récupérer des jeux par genre
function getGamesByGenre($genreName, $CLIENT_ID, $TOKEN) {
    // Chercher 7 jeux populaires du genre
    $requete = 'search "' . $genreName . '";
                fields id, name, cover.image_id, rating;
                where rating != null;
                sort rating desc;
                limit 7;';
    
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
    return $data ?: [];
}

// Récupérer les jeux pour chaque genre
$genres = [
    'RPG' => getGamesByGenre('RPG', $CLIENT_ID, $TOKEN),
    'Shooter' => getGamesByGenre('Shooter', $CLIENT_ID, $TOKEN),
    'Racing' => getGamesByGenre('Racing', $CLIENT_ID, $TOKEN),
    'Action' => getGamesByGenre('Action', $CLIENT_ID, $TOKEN),
    'Strategy' => getGamesByGenre('Strategy', $CLIENT_ID, $TOKEN)
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
// Inclure le header
if (file_exists(__DIR__ . '/../INCLUDES/header.php')) {
    include __DIR__ . '/../INCLUDES/header.php';
}
?>

<main>
    
    <!-- BANNIÈRE DE BIENVENUE -->
    <div class="welcome-banner">
        <h1>👋 Bienvenue, <?= htmlspecialchars($user_pseudo) ?> !</h1>
        <p>Découvrez les meilleurs jeux sélectionnés pour vous</p>
    </div>

    <!-- GRAND CARROUSEL : TOP 10 JEUX À 5 ÉTOILES -->
    <section class="carousel-section">
        <h2>⭐ Les meilleurs jeux (5 étoiles)</h2>
        
        <?php if (empty($topGames)): ?>
            <div class="empty-state">
                <p>Aucun jeu n'a encore 5 étoiles</p>
                <a href="RECHERCHE.php" class="btn-recherche">Rechercher des jeux</a>
            </div>
        <?php else: ?>
            <div class="big-carousel" id="bigCarousel">
                <!-- Flèche gauche -->
                <button class="carousel-arrow left" onclick="moveCarousel(-1)">‹</button>
                
                <!-- Jeux -->
                <div class="big-carousel-wrapper" id="bigWrapper">
                    <?php foreach ($topGames as $game): ?>
                        <div class="big-game-card">
                            <img src="<?= htmlspecialchars($game['cover_url']) ?>" 
                                 alt="<?= htmlspecialchars($game['titre']) ?>">
                            <div class="big-game-info">
                                <h3><?= htmlspecialchars($game['titre']) ?></h3>
                                <div class="big-game-rating">
                                    <span class="stars">★★★★★</span>
                                    <span><?= $game['nombre_votes'] ?> vote<?= $game['nombre_votes'] > 1 ? 's' : '' ?></span>
                                </div>
                                <a href="game.php?id=<?= $game['id_jeu'] ?>" class="btn-voir">Voir le jeu</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Flèche droite -->
                <button class="carousel-arrow right" onclick="moveCarousel(1)">›</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- PETITS CARROUSELS PAR GENRE -->
    
    <!-- RPG -->
    <section class="carousel-section">
        <h2>🎭 RPG</h2>
        <?php if (empty($genres['RPG'])): ?>
            <div class="loading">Chargement...</div>
        <?php else: ?>
            <div class="small-carousel">
                <button class="small-arrow left" onclick="scrollCarousel('rpg', -1)">‹</button>
                <div class="small-carousel-wrapper" id="rpg">
                    <?php foreach ($genres['RPG'] as $game): ?>
                        <?php 
                        $coverUrl = isset($game['cover']['image_id']) 
                            ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                            : '../IMG/placeholder.jpg';
                        $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                        ?>
                        <div class="small-game-card" onclick="window.location.href='game.php?id=<?= $game['id'] ?>'">
                            <img src="<?= htmlspecialchars($coverUrl) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>">
                            <div class="small-game-hover">
                                <h4><?= htmlspecialchars($game['name']) ?></h4>
                                <div class="rating">
                                    <span class="stars">★</span>
                                    <span><?= $rating ?>/5</span>
                                </div>
                                <a href="game.php?id=<?= $game['id'] ?>" class="btn-voir-small">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="small-arrow right" onclick="scrollCarousel('rpg', 1)">›</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- SHOOTER -->
    <section class="carousel-section">
        <h2>🔫 Shooter</h2>
        <?php if (empty($genres['Shooter'])): ?>
            <div class="loading">Chargement...</div>
        <?php else: ?>
            <div class="small-carousel">
                <button class="small-arrow left" onclick="scrollCarousel('shooter', -1)">‹</button>
                <div class="small-carousel-wrapper" id="shooter">
                    <?php foreach ($genres['Shooter'] as $game): ?>
                        <?php 
                        $coverUrl = isset($game['cover']['image_id']) 
                            ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                            : '../IMG/placeholder.jpg';
                        $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                        ?>
                        <div class="small-game-card" onclick="window.location.href='game.php?id=<?= $game['id'] ?>'">
                            <img src="<?= htmlspecialchars($coverUrl) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>">
                            <div class="small-game-hover">
                                <h4><?= htmlspecialchars($game['name']) ?></h4>
                                <div class="rating">
                                    <span class="stars">★</span>
                                    <span><?= $rating ?>/5</span>
                                </div>
                                <a href="game.php?id=<?= $game['id'] ?>" class="btn-voir-small">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="small-arrow right" onclick="scrollCarousel('shooter', 1)">›</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- COURSE -->
    <section class="carousel-section">
        <h2>🏎️ Course</h2>
        <?php if (empty($genres['Racing'])): ?>
            <div class="loading">Chargement...</div>
        <?php else: ?>
            <div class="small-carousel">
                <button class="small-arrow left" onclick="scrollCarousel('racing', -1)">‹</button>
                <div class="small-carousel-wrapper" id="racing">
                    <?php foreach ($genres['Racing'] as $game): ?>
                        <?php 
                        $coverUrl = isset($game['cover']['image_id']) 
                            ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                            : '../IMG/placeholder.jpg';
                        $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                        ?>
                        <div class="small-game-card" onclick="window.location.href='game.php?id=<?= $game['id'] ?>'">
                            <img src="<?= htmlspecialchars($coverUrl) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>">
                            <div class="small-game-hover">
                                <h4><?= htmlspecialchars($game['name']) ?></h4>
                                <div class="rating">
                                    <span class="stars">★</span>
                                    <span><?= $rating ?>/5</span>
                                </div>
                                <a href="game.php?id=<?= $game['id'] ?>" class="btn-voir-small">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="small-arrow right" onclick="scrollCarousel('racing', 1)">›</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- ACTION -->
    <section class="carousel-section">
        <h2>💥 Action</h2>
        <?php if (empty($genres['Action'])): ?>
            <div class="loading">Chargement...</div>
        <?php else: ?>
            <div class="small-carousel">
                <button class="small-arrow left" onclick="scrollCarousel('action', -1)">‹</button>
                <div class="small-carousel-wrapper" id="action">
                    <?php foreach ($genres['Action'] as $game): ?>
                        <?php 
                        $coverUrl = isset($game['cover']['image_id']) 
                            ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                            : '../IMG/placeholder.jpg';
                        $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                        ?>
                        <div class="small-game-card" onclick="window.location.href='game.php?id=<?= $game['id'] ?>'">
                            <img src="<?= htmlspecialchars($coverUrl) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>">
                            <div class="small-game-hover">
                                <h4><?= htmlspecialchars($game['name']) ?></h4>
                                <div class="rating">
                                    <span class="stars">★</span>
                                    <span><?= $rating ?>/5</span>
                                </div>
                                <a href="game.php?id=<?= $game['id'] ?>" class="btn-voir-small">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="small-arrow right" onclick="scrollCarousel('action', 1)">›</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- STRATÉGIE -->
    <section class="carousel-section">
        <h2>🧠 Stratégie</h2>
        <?php if (empty($genres['Strategy'])): ?>
            <div class="loading">Chargement...</div>
        <?php else: ?>
            <div class="small-carousel">
                <button class="small-arrow left" onclick="scrollCarousel('strategy', -1)">‹</button>
                <div class="small-carousel-wrapper" id="strategy">
                    <?php foreach ($genres['Strategy'] as $game): ?>
                        <?php 
                        $coverUrl = isset($game['cover']['image_id']) 
                            ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
                            : '../IMG/placeholder.jpg';
                        $rating = isset($game['rating']) ? round($game['rating'] / 20, 1) : 0;
                        ?>
                        <div class="small-game-card" onclick="window.location.href='game.php?id=<?= $game['id'] ?>'">
                            <img src="<?= htmlspecialchars($coverUrl) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>">
                            <div class="small-game-hover">
                                <h4><?= htmlspecialchars($game['name']) ?></h4>
                                <div class="rating">
                                    <span class="stars">★</span>
                                    <span><?= $rating ?>/5</span>
                                </div>
                                <a href="game.php?id=<?= $game['id'] ?>" class="btn-voir-small">Voir plus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="small-arrow right" onclick="scrollCarousel('strategy', 1)">›</button>
            </div>
        <?php endif; ?>
    </section>

</main>

<script>
// ===== GRAND CARROUSEL (manuel) =====
let currentSlide = 0;

function moveCarousel(direction) {
    const wrapper = document.getElementById('bigWrapper');
    const cards = wrapper.querySelectorAll('.big-game-card');
    const totalSlides = cards.length;
    
    currentSlide = currentSlide + direction;
    
    // Revenir au début ou à la fin
    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    } else if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }
    
    // Déplacer le carrousel
    wrapper.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
}

// ===== PETITS CARROUSELS (défilement horizontal) =====
function scrollCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    const scrollAmount = 230; // largeur d'une carte + gap
    
    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>

</body>
</html>