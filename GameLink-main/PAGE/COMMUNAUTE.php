<?php
// PAGE/COMMUNAUTE.php
// Page communaute avec les groupes de jeux

session_start();

// Verifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$mon_pseudo = $_SESSION['user_pseudo'];

// Recuperer les 6 groupes
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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communaute - GameLink</title>
    <link rel="stylesheet" href="/CSS/HEADER.css">
    <link rel="stylesheet" href="/CSS/STYLE_COMMUNAUTE.css">
    <link rel="icon" type="image/svg+xml" href="/ICON/LogoSimple.svg">
</head>
<body>

    <!-- EN-TETE -->
    <header>
        <?php include_once __DIR__ . '/../INCLUDES/HEADER.php'; ?>
    </header>

    <!-- CONTENU PRINCIPAL -->
    <main class="page-communaute">
        
        <!-- TITRE -->
        <div class="titre-section">
            <h1>&#127918; Groupes de Jeux</h1>
            <p>Rejoins un groupe pour discuter avec d'autres joueurs !</p>
        </div>

        <!-- LES 6 GROUPES -->
        <div class="grille-groupes">
            
            <?php foreach ($groupes as $groupe): ?>
            
                <div class="carte-groupe">
                    
                    <!-- IMAGE DU GROUPE -->
                    <div class="image-groupe">
                        <img src="/IMAGES/groupes/groupe_<?= $groupe['id'] ?>.jpg" 
                             alt="<?= htmlspecialchars($groupe['nom']) ?>"
                             onerror="this.style.display='none';">
                    </div>
                    
                    <!-- INFORMATIONS -->
                    <div class="info-groupe">
                        <h3><?= htmlspecialchars($groupe['nom']) ?></h3>
                        <p><?= htmlspecialchars($groupe['description']) ?></p>
                        <p class="petit-texte">&#128101; <?= $groupe['nb_membres'] ?> membres</p>
                    </div>
                    
                    <!-- BOUTONS -->
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

    <!-- BULLE DE CHAT -->
    <div id="bulle-chat" class="bulle-chat">
        <div class="contenu-bulle">
            
            <div class="haut-bulle">
                <h3 id="nom-groupe-chat">Nom du groupe</h3>
                <button class="bouton-fermer" onclick="fermerChat()">&#10006;</button>
            </div>
            
            <div id="zone-messages" class="zone-messages">
                <p class="texte-centre">Chargement des messages...</p>
            </div>
            
            <form id="formulaire-message" class="formulaire-message">
                <input type="hidden" id="id-groupe-actuel">
                <textarea id="mon-message" 
                          placeholder="Ecris ton message ici..." 
                          rows="3" 
                          required></textarea>
                <button type="submit" class="bouton bleu">Envoyer</button>
            </form>
            
            <div class="bas-bulle">
                <button class="bouton rouge quitter-depuis-chat">Quitter ce groupe</button>
            </div>
            
        </div>
    </div>

    <!-- FOND SOMBRE -->
    <div id="fond-sombre" class="fond-sombre" onclick="fermerChat()"></div>

    <!-- JAVASCRIPT -->
    <script src="/JS/COMMUNAUTE.js"></script>

</body>
</html>