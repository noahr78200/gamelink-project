<?php
// PAGE/COMMUNAUTE.php
// Page avec GROUPES et FORUM

session_start();

// Verifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$mon_pseudo = $_SESSION['user_pseudo'];

// RECUPERER LES 6 GROUPES
$groupes = [];

try {
    $resultat = $pdo->query("
        SELECT 
            id_communaute as id,
            nom,
            description,
            (SELECT COUNT(*) FROM adhesion WHERE id_communaute = communaute.id_communaute) as nb_membres,
            (SELECT COUNT(*) FROM adhesion WHERE id_communaute = communaute.id_communaute AND id_joueur = $mon_id) as je_suis_membre
        FROM communaute
        WHERE id_communaute <= 6
        ORDER BY id_communaute
    ");
    
    $groupes = $resultat->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $erreur) {
    $groupes = [];
}

// RECUPERER LES DISCUSSIONS DU FORUM
$discussions = [];

try {
    $resultat = $pdo->query("
        SELECT 
            p.id_publication as id,
            p.titre,
            p.contenu,
            p.date_creation,
            j.pseudo as auteur,
            p.id_joueur as id_auteur,
            (SELECT COUNT(*) FROM commentaire WHERE id_publication = p.id_publication) as nb_reponses
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.titre IS NOT NULL
        ORDER BY p.date_creation DESC
        LIMIT 50
    ");
    
    $discussions = $resultat->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $erreur) {
    $discussions = [];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communaute - GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="stylesheet" href="../CSS/STYLE_COMMUNAUTE.css">
    <link rel="icon" type="image/svg+xml" href="/ICON/LogoSimple.svg">
</head>
 

    
<body>

   
 <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN) .
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>


<main>
    <!-- ONGLETS -->
    <div class="onglets-container">
        <button class="onglet actif" onclick="afficherOnglet('groupes')">
            &#128188; GROUPES
        </button>
        <button class="onglet" onclick="afficherOnglet('forum')">
            &#128172; FORUM
        </button>
    </div>

    <!-- ONGLET GROUPES -->
    <div id="onglet-groupes" class="contenu-onglet actif">
        <main class="page-communaute">
            <div class="titre-section">
                <h1>&#127918; Groupes de Jeux</h1>
                <p>Rejoins un groupe pour discuter avec d'autres joueurs !</p>
            </div>

            <div class="grille-groupes">
                <?php foreach ($groupes as $groupe): ?>
                    <div class="carte-groupe">
                        <div class="image-groupe">
                            <img src="/IMAGES/groupes/groupe_<?= $groupe['id'] ?>.jpg" 
                                 alt="<?= htmlspecialchars($groupe['nom']) ?>"
                                 onerror="this.style.display='none';">
                        </div>
                        <div class="info-groupe">
                            <h3><?= htmlspecialchars($groupe['nom']) ?></h3>
                            <p><?= htmlspecialchars($groupe['description']) ?></p>
                            <p class="petit-texte">&#128101; <?= $groupe['nb_membres'] ?> membres</p>
                        </div>
                        <div class="boutons-groupe">
                            <?php if ($groupe['je_suis_membre'] > 0): ?>
                                <button class="bouton bleu ouvrir-chat" 
                                        data-groupe-id="<?= $groupe['id'] ?>"
                                        data-groupe-nom="<?= htmlspecialchars($groupe['nom']) ?>">
                                    &#128172; Ouvrir le chat
                                </button>
                                <button class="bouton rouge quitter-groupe" 
                                        data-groupe-id="<?= $groupe['id'] ?>">
                                    Quitter
                                </button>
                            <?php else: ?>
                                <button class="bouton vert rejoindre-groupe" 
                                        data-groupe-id="<?= $groupe['id'] ?>">
                                    + Rejoindre
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- ONGLET FORUM -->
    <div id="onglet-forum" class="contenu-onglet">
        <main class="page-forum">
            <div class="titre-section">
                <h1>&#128172; Forum de Discussion</h1>
                <p>Partage tes idees avec la communaute !</p>
                <button class="bouton bleu" onclick="ouvrirPopupCreerDiscussion()">
                    &#10010; Creer une discussion
                </button>
            </div>

            <div class="liste-discussions">
                <?php if (empty($discussions)): ?>
                    <div class="aucune-discussion">
                        <p>&#128269; Aucune discussion pour le moment.</p>
                        <p>Sois le premier a en creer une !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($discussions as $discussion): ?>
                        <div class="carte-discussion" onclick="ouvrirDiscussion(<?= $discussion['id'] ?>)">
                            <div class="discussion-titre">
                                <h3>&#128172; <?= htmlspecialchars($discussion['titre']) ?></h3>
                            </div>
                            <div class="discussion-info">
                                <span class="auteur">Par: <?= htmlspecialchars($discussion['auteur']) ?></span>
                                <span class="date">
                                    <?php
                                    $temps = strtotime($discussion['date_creation']);
                                    $diff = time() - $temps;
                                    if ($diff < 3600) {
                                        echo 'Il y a ' . floor($diff / 60) . ' min';
                                    } elseif ($diff < 86400) {
                                        echo 'Il y a ' . floor($diff / 3600) . 'h';
                                    } else {
                                        echo date('d/m/Y', $temps);
                                    }
                                    ?>
                                </span>
                                <span class="reponses"><?= $discussion['nb_reponses'] ?> reponses</span>
                            </div>
                            <div class="discussion-extrait">
                                <?php
                                $extrait = substr($discussion['contenu'], 0, 150);
                                if (strlen($discussion['contenu']) > 150) $extrait .= '...';
                                echo htmlspecialchars($extrait);
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- POPUP CREER DISCUSSION -->
    <div id="popup-creer-discussion" class="popup-overlay">
        <div class="popup-contenu">
            <div class="popup-header">
                <h3>&#128172; Creer une Discussion</h3>
                <button class="bouton-fermer" onclick="fermerPopupCreerDiscussion()">&#10006;</button>
            </div>
            <form id="form-creer-discussion">
                <div class="form-groupe">
                    <label for="discussion-titre">Titre :</label>
                    <input type="text" id="discussion-titre" placeholder="Ex: Meilleurs jeux RPG" required maxlength="180">
                </div>
                <div class="form-groupe">
                    <label for="discussion-contenu">Message :</label>
                    <textarea id="discussion-contenu" rows="8" placeholder="Ecris ici..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="bouton gris" onclick="fermerPopupCreerDiscussion()">Annuler</button>
                    <button type="submit" class="bouton bleu">Publier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP VOIR DISCUSSION -->
    <div id="popup-voir-discussion" class="popup-overlay">
        <div class="popup-contenu grande">
            <div class="popup-header">
                <h3 id="discussion-titre-complet">Titre</h3>
                <button class="bouton-fermer" onclick="fermerDiscussion()">&#10006;</button>
            </div>
            <div id="discussion-post-original"></div>
            <div id="discussion-reponses"></div>
            <form id="form-repondre-discussion" class="form-repondre">
                <textarea id="reponse-contenu" rows="4" placeholder="Ta reponse..." required></textarea>
                <button type="submit" class="bouton bleu">Repondre</button>
            </form>
        </div>
    </div>

    <!-- BULLE CHAT GROUPES -->
    <div id="bulle-chat" class="bulle-chat">
        <div class="contenu-bulle">
            <div class="haut-bulle">
                <h3 id="nom-groupe-chat">Nom du groupe</h3>
                <button class="bouton-fermer" onclick="fermerChat()">&#10006;</button>
            </div>
            <div id="zone-messages" class="zone-messages">
                <p class="texte-centre">Chargement...</p>
            </div>
            <form id="formulaire-message" class="formulaire-message">
                <input type="hidden" id="id-groupe-actuel">
                <textarea id="mon-message" placeholder="Ton message..." rows="3" required></textarea>
                <button type="submit" class="bouton bleu">Envoyer</button>
            </form>
            <div class="bas-bulle">
                <button class="bouton rouge quitter-depuis-chat">Quitter ce groupe</button>
            </div>
        </div>
    </div>

    <div id="fond-sombre" class="fond-sombre"></div>

    <script src="/JS/COMMUNAUTE.js"></script>
</main>
</body>
</html>