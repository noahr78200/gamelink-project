<?php
session_start();

// Connexion BDD
require __DIR__ . '/../DATA/DBConfig.php';

// Fonction sécuriser texte
function h($x) { 
    return htmlspecialchars($x, ENT_QUOTES, 'UTF-8'); 
}

// ID du jeu (ID IGDB)
if (!isset($_GET['id'])) {
    header("Location: RECHERCHE.php");
    exit;
}
$gameId = (int)$_GET['id'];

// -----------------------------------------------------
// TRAITEMENT DES ACTIONS POST EN PREMIER
// -----------------------------------------------------

// Utilisateur connecté
$userId = $_SESSION['user_id'] ?? null;
$userPseudo = $_SESSION['user_pseudo'] ?? null;

// SUPPRESSION DE COMMENTAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    if ($userId) {
        try {
            $sql = "DELETE FROM avis WHERE id_joueur = :u AND id_jeu = :g";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':u' => $userId, ':g' => $gameId]);
        } catch (PDOException $e) {
            error_log("Erreur suppression commentaire: " . $e->getMessage());
        }
    }
    header("Location: game.php?id=" . $gameId);
    exit;
}

// SAUVEGARDE DES NOTES (par AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_rating'])) {
    header('Content-Type: application/json');
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }

    try {
        $rating = max(1, min(5, (int)$_POST['ajax_rating']));
        $now = date("Y-m-d H:i:s");

        // Vérifier si un avis existe déjà
        $sql = "SELECT id_joueur FROM avis WHERE id_joueur = :u AND id_jeu = :g";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':u' => $userId, ':g' => $gameId]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Mettre à jour
            $sql = "UPDATE avis SET valeur = :v, date_notation = :d WHERE id_joueur = :u AND id_jeu = :g";
        } else {
            // Insérer
            $sql = "INSERT INTO avis (id_joueur, id_jeu, valeur, date_notation) VALUES (:u, :g, :v, :d)";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':u' => $userId,
            ':g' => $gameId,
            ':v' => $rating,
            ':d' => $now
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Erreur notation: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
    }
    exit;
}

// SAUVEGARDE DES COMMENTAIRES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    if (!$userId) {
        header("Location: ../PAGE/AUTH.php");
        exit;
    }

    try {
        $comment = trim($_POST['comment_text']);
        $now = date("Y-m-d H:i:s");

        if ($comment !== "") {
            // Vérifier si l'utilisateur a déjà un avis
            $sql = "SELECT id_joueur FROM avis WHERE id_joueur = :u AND id_jeu = :g";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':u' => $userId, ':g' => $gameId]);
            $existingAvis = $stmt->fetch();

            if ($existingAvis) {
                // Mettre à jour le commentaire
                $sql = "UPDATE avis SET texte_commentaire = :t, date_commentaire = :d 
                        WHERE id_joueur = :u AND id_jeu = :g";
            } else {
                // Insérer un nouvel avis avec commentaire
                $sql = "INSERT INTO avis (id_joueur, id_jeu, texte_commentaire, date_commentaire)
                        VALUES (:u, :g, :t, :d)";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':u' => $userId,
                ':g' => $gameId,
                ':t' => $comment,
                ':d' => $now
            ]);
        }
    } catch (PDOException $e) {
        error_log("Erreur commentaire: " . $e->getMessage());
    }

    header("Location: game.php?id=" . $gameId);
    exit;
}

// -----------------------------------------------------
// RÉCUPÉRATION DES DONNÉES DU JEU
// -----------------------------------------------------

// Récupérer les infos du jeu depuis IGDB
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

$body = 'fields id, name, cover.image_id, genres.name, platforms.name, summary, first_release_date, involved_companies.company.name;
         where id = ' . $gameId . ';
         limit 1;';

$ch = curl_init('https://api.igdb.com/v4/games');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Client-ID: ' . $CLIENT_ID,
        'Authorization: Bearer ' . $TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS => $body
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$game = isset($data[0]) ? $data[0] : null;

if (!$game) {
    header("Location: RECHERCHE.php");
    exit;
}

// Extraire les infos
$title = $game['name'] ?? "Sans titre";
$cover = isset($game['cover']['image_id']) 
    ? 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg'
    : '../IMG/placeholder.jpg';
$genres = isset($game['genres']) ? implode(", ", array_column($game['genres'], 'name')) : "Non spécifié";
$summary = $game['summary'] ?? "Aucune description disponible.";
$platforms = isset($game['platforms']) ? implode(", ", array_column($game['platforms'], 'name')) : "Non spécifié";

// Date de sortie
$releaseDate = "Non spécifié";
if (isset($game['first_release_date'])) {
    $releaseDate = date('d/m/Y', $game['first_release_date']);
}

// Studio (Éditeur)
$studio = "Non spécifié";
if (isset($game['involved_companies'][0]['company']['name'])) {
    $studio = $game['involved_companies'][0]['company']['name'];
}

// -----------------------------------------------------
// RÉCUPÉRER LES NOTES MOYENNES
// -----------------------------------------------------
try {
    $sql = "SELECT valeur FROM avis WHERE id_jeu = :id AND valeur IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $gameId]);
    $notes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $totalVotes = count($notes);
    $avgRating = 0;
    if ($totalVotes > 0) {
        $avgRating = round(array_sum($notes) / $totalVotes, 1);
    }
} catch (PDOException $e) {
    error_log("Erreur récupération notes: " . $e->getMessage());
    $totalVotes = 0;
    $avgRating = 0;
}

// -----------------------------------------------------
// MA NOTE ACTUELLE
// -----------------------------------------------------
$myRating = 0;
if ($userId) {
    try {
        $sql = "SELECT valeur FROM avis WHERE id_joueur = :u AND id_jeu = :g";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':u' => $userId, ':g' => $gameId]);
        $myRating = $stmt->fetchColumn() ?: 0;
    } catch (PDOException $e) {
        error_log("Erreur récupération ma note: " . $e->getMessage());
    }
}

// -----------------------------------------------------
// RÉCUPÉRER LES COMMENTAIRES
// -----------------------------------------------------
$comments = [];
try {
    $sql = "SELECT a.texte_commentaire, a.date_commentaire, a.id_joueur, j.pseudo
            FROM avis a
            JOIN joueur j ON j.id_joueur = a.id_joueur
            WHERE a.id_jeu = :g AND a.texte_commentaire IS NOT NULL
            ORDER BY a.date_commentaire DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':g' => $gameId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération commentaires: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> - GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
</head>
<body>

<?php include __DIR__ . '/../INCLUDES/header.php'; ?>

<div class="game-container">
    
    <!-- Bouton retour -->
    <a href="RECHERCHE.php" class="btn-back">← Retour à la recherche</a>

    <!-- SECTION PRINCIPALE : Image à gauche, infos à droite -->
    <section class="game-main">
        
        <!-- IMAGE À GAUCHE -->
        <div class="game-left">
            <img src="<?= h($cover) ?>" alt="<?= h($title) ?>" class="game-poster">
        </div>
        
        <!-- INFOS À DROITE -->
        <div class="game-right">
            
            <!-- Titre -->
            <h1 class="game-title"><?= h($title) ?></h1>
            
            <!-- Résumé pliable (limité à 5 lignes) -->
            <div class="game-summary">
                <p class="summary-text" id="summaryText"><?= nl2br(h($summary)) ?></p>
                <?php if (strlen($summary) > 300): ?>
                    <button class="btn-toggle" id="toggleBtn" onclick="toggleSummary()">Voir plus</button>
                <?php endif; ?>
            </div>
            
            <!-- Métadonnées -->
            <div class="game-meta">
                <div class="meta-item">
                    <span class="meta-label">Genre:</span>
                    <span><?= h($genres) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Plateforme:</span>
                    <span><?= h($platforms) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Éditeur:</span>
                    <span><?= h($studio) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Sortie:</span>
                    <span><?= h($releaseDate) ?></span>
                </div>
            </div>
            
            <!-- Section notation -->
            <div class="rating-box">
                
                <!-- Note moyenne -->
                <div class="rating-display">
                    <div class="rating-number"><?= $avgRating ?></div>
                    <div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= round($avgRating) ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count"><?= $totalVotes ?> note<?= $totalVotes > 1 ? 's' : '' ?></div>
                    </div>
                </div>

                <!-- Ma notation -->
                <?php if ($userId): ?>
                    <div class="user-rating">
                        <h3>Votre note :</h3>
                        <div class="stars-input" id="stars-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star-btn <?= $i <= $myRating ? 'active' : '' ?>" 
                                      data-rating="<?= $i ?>"
                                      onclick="rateGame(<?= $i ?>)">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="user-rating">
                        <p class="login-msg">Connectez-vous pour noter ce jeu</p>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
    </section>

    <!-- SECTION COMMENTAIRES -->
    <section class="comments-section">
        <h2>💬 Commentaires (<?= count($comments) ?>)</h2>

        <!-- Formulaire nouveau commentaire -->
        <?php if ($userId): ?>
            <form method="post" class="comment-form">
                <textarea name="comment_text" 
                          placeholder="Partagez votre avis sur ce jeu..." 
                          required></textarea>
                <button type="submit" class="btn-submit">Publier</button>
            </form>
        <?php else: ?>
            <p class="login-msg" style="text-align: center; margin: 30px 0;">
                Connectez-vous pour laisser un commentaire
            </p>
        <?php endif; ?>

        <!-- Liste des commentaires -->
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p class="no-comments">Aucun commentaire pour le moment. Soyez le premier !</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author"><?= h($comment['pseudo']) ?></span>
                            <span class="comment-date">
                                <?php
                                $time = strtotime($comment['date_commentaire']);
                                $diff = time() - $time;
                                if ($diff < 60) {
                                    echo "À l'instant";
                                } elseif ($diff < 3600) {
                                    echo 'Il y a ' . floor($diff / 60) . ' min';
                                } elseif ($diff < 86400) {
                                    echo 'Il y a ' . floor($diff / 3600) . 'h';
                                } elseif ($diff < 604800) {
                                    echo 'Il y a ' . floor($diff / 86400) . ' jour(s)';
                                } else {
                                    echo date('d/m/Y à H:i', $time);
                                }
                                ?>
                            </span>
                        </div>
                        <p class="comment-text"><?= nl2br(h($comment['texte_commentaire'])) ?></p>
                        
                        <?php if ($userId && $comment['id_joueur'] == $userId): ?>
                            <form method="post" style="text-align: right;">
                                <button type="submit" name="delete_comment" value="1" class="btn-delete" 
                                        onclick="return confirm('Supprimer votre commentaire ?')">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<script>
// Fonction pour plier/déplier le résumé
function toggleSummary() {
    const text = document.getElementById('summaryText');
    const btn = document.getElementById('toggleBtn');
    
    if (text.classList.contains('expanded')) {
        text.classList.remove('expanded');
        btn.textContent = 'Voir plus';
    } else {
        text.classList.add('expanded');
        btn.textContent = 'Voir moins';
    }
}

// Fonction pour noter le jeu (AJAX)
function rateGame(rating) {
    // Mettre à jour visuellement immédiatement
    const stars = document.querySelectorAll('.star-btn');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });

    // Envoyer au serveur
    const formData = new FormData();
    formData.append('ajax_rating', rating);

    fetch('game.php?id=<?= $gameId ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger la page pour voir la nouvelle moyenne
            location.reload();
        } else {
            alert('Erreur lors de la notation');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la notation');
    });
}
</script>

</body>
</html>