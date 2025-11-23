<?php
// Démarrer la session
session_start();

// Se connecter à la base de données
require __DIR__ . '/../DATA/DBConfig.php';

// Fonction pour sécuriser le texte
function h($texte) { 
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8'); 
}

// Récupérer l'ID du jeu
if (!isset($_GET['id'])) {
    header("Location: RECHERCHE.php");
    exit;
}
$gameId = (int)$_GET['id'];

// Récupérer l'utilisateur connecté
$userId = $_SESSION['user_id'] ?? null;

// ========================================
// RÉCUPÉRER LES INFOS DU JEU D'ABORD
// ========================================

$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

$requete = 'fields id, name, cover.image_id, genres.name, platforms.name, summary, first_release_date, involved_companies.company.name;
            where id = ' . $gameId . ';
            limit 1;';

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
$jeu = $data[0] ?? null;

if (!$jeu) {
    header("Location: RECHERCHE.php");
    exit;
}

// Extraire les informations
$titre = $jeu['name'] ?? "Sans titre";
$resume = $jeu['summary'] ?? "Aucune description disponible.";
$genres = isset($jeu['genres']) ? implode(", ", array_column($jeu['genres'], 'name')) : "Non spécifié";
$plateformes = isset($jeu['platforms']) ? implode(", ", array_column($jeu['platforms'], 'name')) : "Non spécifié";
$editeur = $jeu['involved_companies'][0]['company']['name'] ?? "Non spécifié";
$dateSortie = isset($jeu['first_release_date']) ? date('Y-m-d', $jeu['first_release_date']) : null;

// Image de couverture
if (isset($jeu['cover']['image_id'])) {
    $image = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $jeu['cover']['image_id'] . '.jpg';
} else {
    $image = '../IMG/placeholder.jpg';
}

// ========================================
// FONCTION : CRÉER LE JEU DANS LA BDD
// ========================================
function creerJeuDansBDD($pdo, $gameId, $titre, $editeur, $dateSortie, $resume, $image) {
    try {
        // Vérifier si le jeu existe déjà
        $sql = "SELECT id_jeu FROM jeu WHERE id_jeu = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$gameId]);
        
        if (!$stmt->fetch()) {
            // Le jeu n'existe pas, on doit d'abord créer l'éditeur
            
            // 1. Vérifier si l'éditeur existe
            $sql = "SELECT id_editeur FROM editeur WHERE nom = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$editeur]);
            $editeurData = $stmt->fetch();
            
            if (!$editeurData) {
                // Créer l'éditeur
                $sql = "INSERT INTO editeur (nom) VALUES (?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$editeur]);
                $idEditeur = $pdo->lastInsertId();
            } else {
                $idEditeur = $editeurData['id_editeur'];
            }
            
            // 2. Créer le jeu
            $sql = "INSERT INTO jeu (id_jeu, id_editeur, titre, date_sortie, description, cover_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$gameId, $idEditeur, $titre, $dateSortie, $resume, $image]);
        }
    } catch (PDOException $e) {
        // Si erreur, on ne bloque pas, on continue
        error_log("Erreur création jeu: " . $e->getMessage());
    }
}

// Créer le jeu dans la BDD si nécessaire
creerJeuDansBDD($pdo, $gameId, $titre, $editeur, $dateSortie, $resume, $image);

// ========================================
// ÉTAPE 1 : GÉRER LES ACTIONS
// ========================================

// ACTION : Supprimer un commentaire
if (isset($_POST['delete_comment']) && $userId) {
    try {
        $sql = "DELETE FROM avis WHERE id_joueur = ? AND id_jeu = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $gameId]);
        header("Location: game.php?id=" . $gameId);
        exit;
    } catch (PDOException $e) {
        die("ERREUR SUPPRESSION: " . $e->getMessage());
    }
}

// ACTION : Noter le jeu (AJAX)
if (isset($_POST['ajax_rating']) && $userId) {
    header('Content-Type: application/json');
    
    try {
        $note = (int)$_POST['ajax_rating'];
        
        if ($note >= 1 && $note <= 5) {
            // Vérifier si une note existe déjà
            $sql = "SELECT id_joueur FROM avis WHERE id_joueur = ? AND id_jeu = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $gameId]);
            $existe = $stmt->fetch();
            
            if ($existe) {
                // Mettre à jour
                $sql = "UPDATE avis SET valeur = ?, date_notation = NOW() WHERE id_joueur = ? AND id_jeu = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$note, $userId, $gameId]);
            } else {
                // Créer
                $sql = "INSERT INTO avis (id_joueur, id_jeu, valeur, date_notation) VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $gameId, $note]);
            }
            
            echo json_encode(['success' => true]);
            exit;
        }
        
        echo json_encode(['success' => false, 'error' => 'Note invalide']);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ACTION : Favoris (simple POST toggle)
if (isset($_POST['toggle_fav']) && $userId) {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM favoris WHERE id_joueur = ? AND id_jeu = ?");
        $stmt->execute([$userId, $gameId]);

        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_joueur = ? AND id_jeu = ?");
            $stmt->execute([$userId, $gameId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO favoris (id_joueur, id_jeu) VALUES (?, ?)");
            $stmt->execute([$userId, $gameId]);
        }

        header("Location: game.php?id=" . $gameId);
        exit;
    } catch (PDOException $e) {
        // ne pas bloquer l'utilisateur : log et rediriger
        error_log('Favoris error: ' . $e->getMessage());
        header("Location: game.php?id=" . $gameId);
        exit;
    }
}

// ACTION : Ajouter un commentaire
if (isset($_POST['comment_text']) && $userId) {
    try {
        $commentaire = trim($_POST['comment_text']);
        
        if ($commentaire != "") {
            // Vérifier si un avis existe déjà
            $sql = "SELECT id_joueur FROM avis WHERE id_joueur = ? AND id_jeu = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $gameId]);
            $existe = $stmt->fetch();
            
            if ($existe) {
                // Mettre à jour
                $sql = "UPDATE avis SET texte_commentaire = ?, date_commentaire = NOW() WHERE id_joueur = ? AND id_jeu = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$commentaire, $userId, $gameId]);
            } else {
                // Créer
                $sql = "INSERT INTO avis (id_joueur, id_jeu, texte_commentaire, date_commentaire) VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $gameId, $commentaire]);
            }
        }
        
        header("Location: game.php?id=" . $gameId);
        exit;
        
    } catch (PDOException $e) {
        die("ERREUR COMMENTAIRE: " . $e->getMessage());
    }
}

// ========================================
// ÉTAPE 2 : RÉCUPÉRER LES NOTES
// ========================================

try {
    // Note moyenne
    $sql = "SELECT valeur FROM avis WHERE id_jeu = ? AND valeur IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gameId]);
    $notes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $nombreNotes = count($notes);
    $noteMoyenne = 0;
    if ($nombreNotes > 0) {
        $noteMoyenne = round(array_sum($notes) / $nombreNotes, 1);
    }

    // Ma note personnelle
    $maNote = 0;
    if ($userId) {
        $sql = "SELECT valeur FROM avis WHERE id_joueur = ? AND id_jeu = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $gameId]);
        $maNote = $stmt->fetchColumn() ?: 0;
    }
} catch (PDOException $e) {
    $nombreNotes = 0;
    $noteMoyenne = 0;
    $maNote = 0;
}

// ========================================
// ÉTAPE 3 : RÉCUPÉRER LES COMMENTAIRES
// ========================================

try {
    $sql = "SELECT a.texte_commentaire, a.date_commentaire, a.id_joueur, j.pseudo
            FROM avis a
            JOIN joueur j ON j.id_joueur = a.id_joueur
            WHERE a.id_jeu = ? AND a.texte_commentaire IS NOT NULL
            ORDER BY a.date_commentaire DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gameId]);
    $commentaires = $stmt->fetchAll();
} catch (PDOException $e) {
    $commentaires = [];
}

// Vérifier si le jeu est en favoris pour l'utilisateur connecté
$isFavori = false;
if ($userId) {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM favoris WHERE id_joueur = ? AND id_jeu = ?");
        $stmt->execute([$userId, $gameId]);
        $isFavori = (bool) $stmt->fetch();
    } catch (PDOException $e) {
        $isFavori = false;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titre) ?> - GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
</head>
<body>

<?php 
// Inclure le header seulement s'il existe
if (file_exists(__DIR__ . '/../INCLUDES/header.php')) {
    include __DIR__ . '/../INCLUDES/header.php';
}
?>

<div class="game-container">
    
    <!-- Bouton retour -->
    <a href="RECHERCHE.php" class="btn-back">← Retour</a>

    <!-- CARTE PRINCIPALE -->
    <div class="game-main">
        
        <!-- Image à gauche -->
        <div class="game-left">
            <img src="<?= h($image) ?>" alt="<?= h($titre) ?>">
        </div>
        
        <!-- Infos à droite -->
        <div class="game-right">
            
            <!-- Titre -->
            <h1 class="game-title"><?= h($titre) ?></h1>

            <!-- Favoris -->
                <?php if ($userId): ?>
                    <div class="favorite-box">
                        <form method="post" style="display:inline">
                            <button type="submit" name="toggle_fav" value="1" class="btn-fav <?= $isFavori ? 'active' : '' ?>">
                                <?= $isFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?> 
            
            <!-- Résumé (limité à 5 lignes) -->
            <div class="game-summary">
                <p class="summary-text" id="resume"><?= nl2br(h($resume)) ?></p>
                <?php if (strlen($resume) > 300): ?>
                    <button class="btn-toggle" onclick="toggleResume()">Voir plus</button>
                <?php endif; ?>
            </div>
            
            <!-- Métadonnées -->
            <div class="game-meta">
                <div class="meta-item">
                    <span class="meta-label">Genre:</span> <?= h($genres) ?>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Plateforme:</span> <?= h($plateformes) ?>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Éditeur:</span> <?= h($editeur) ?>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Sortie:</span> <?= h($dateSortie ? date('d/m/Y', strtotime($dateSortie)) : 'Non spécifié') ?>
                </div>
            </div>
            
            <!-- Notation -->
            <div class="rating-box">
                
                <!-- Note moyenne -->
                <div class="rating-display">
                    <div class="rating-number"><?= $noteMoyenne ?></div>
                    <div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= round($noteMoyenne) ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count"><?= $nombreNotes ?> note<?= $nombreNotes > 1 ? 's' : '' ?></div>
                    </div>
                </div>

                <!-- Ma notation -->
                <?php if ($userId): ?>
                    <div class="user-rating">
                        <h3>Votre note :</h3>
                        <div class="stars-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star-btn <?= $i <= $maNote ? 'active' : '' ?>" 
                                      onclick="noterJeu(<?= $i ?>)">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="login-msg">Connectez-vous pour noter</p>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>

    <!-- COMMENTAIRES -->
    <div class="comments-section">
        <h2>💬 Commentaires (<?= count($commentaires) ?>)</h2>

        <!-- Formulaire -->
        <?php if ($userId): ?>
            <form method="post" class="comment-form">
                <textarea name="comment_text" placeholder="Votre avis..." required></textarea>
                <button type="submit" class="btn-submit">Publier</button>
            </form>
        <?php else: ?>
            <p class="login-msg" style="text-align: center; margin: 30px 0;">
                Connectez-vous pour commenter
            </p>
        <?php endif; ?>

        <!-- Liste -->
        <div class="comments-list">
            <?php if (empty($commentaires)): ?>
                <p class="no-comments">Aucun commentaire</p>
            <?php else: ?>
                <?php foreach ($commentaires as $com): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author"><?= h($com['pseudo']) ?></span>
                            <span class="comment-date">
                                <?php
                                $temps = time() - strtotime($com['date_commentaire']);
                                if ($temps < 60) echo "À l'instant";
                                elseif ($temps < 3600) echo floor($temps/60) . " min";
                                elseif ($temps < 86400) echo floor($temps/3600) . "h";
                                else echo date('d/m/Y', strtotime($com['date_commentaire']));
                                ?>
                            </span>
                        </div>
                        <p class="comment-text"><?= nl2br(h($com['texte_commentaire'])) ?></p>
                        
                        <?php if ($userId && $com['id_joueur'] == $userId): ?>
                            <form method="post" style="text-align: right;">
                                <button type="submit" name="delete_comment" value="1" class="btn-delete"
                                        onclick="return confirm('Supprimer ?')">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
// Fonction pour déplier le résumé
function toggleResume() {
    var texte = document.getElementById('resume');
    var bouton = event.target;
    
    if (texte.classList.contains('expanded')) {
        texte.classList.remove('expanded');
        bouton.textContent = 'Voir plus';
    } else {
        texte.classList.add('expanded');
        bouton.textContent = 'Voir moins';
    }
}

// Fonction pour noter
function noterJeu(note) {
    // Afficher visuellement
    var etoiles = document.querySelectorAll('.star-btn');
    for (var i = 0; i < etoiles.length; i++) {
        if (i < note) {
            etoiles[i].classList.add('active');
        } else {
            etoiles[i].classList.remove('active');
        }
    }
    
    // Envoyer au serveur
    var donnees = new FormData();
    donnees.append('ajax_rating', note);
    
    fetch('game.php?id=<?= $gameId ?>', {
        method: 'POST',
        body: donnees
    })
    .then(function(reponse) { 
        return reponse.json(); 
    })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + (data.error || 'Inconnue'));
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion');
    });
}

// Bouton favoris : toggle visuel immédiat avant submit
document.addEventListener('DOMContentLoaded', function(){
    var favBtn = document.querySelector('.btn-fav');
    if (!favBtn) return;
    favBtn.addEventListener('click', function(e){
        this.classList.toggle('active');
        this.textContent = this.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
    });
});
</script>

</body>
</html>