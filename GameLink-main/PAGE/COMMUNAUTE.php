<?php
/*
 * ========================================
 * PAGE COMMUNAUTÉ - VERSION DÉBUTANT
 * ========================================
 * 
 * Cette page affiche :
 * - Les 6 groupes de jeux (comme des clubs)
 * - Une bulle de chat pour parler dans un groupe
 * 
 */

// ÉTAPE 1 : Démarrer la session
// (C'est comme se connecter, pour que le site sache qui tu es)
session_start();

// ÉTAPE 2 : Vérifier si tu es connecté
// Si tu n'es pas connecté, retour à la page d'accueil
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// ÉTAPE 3 : Se connecter à la base de données
// (La base de données, c'est comme un grand classeur avec toutes les infos)
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 4 : Récupérer tes informations
$mon_id = $_SESSION['user_id'];        // Ton numéro d'identification
$mon_pseudo = $_SESSION['user_pseudo'];  // Ton pseudo

// ÉTAPE 5 : Récupérer les 6 groupes
$groupes = []; // On crée un panier vide pour mettre les groupes dedans

try {
    // On demande à la base de données : "Donne-moi les 6 groupes"
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
    
    // On met les groupes dans notre panier
    $groupes = $resultat->fetchAll();
    
} catch (Exception $erreur) {
    // Si ça marche pas, on laisse le panier vide
    $groupes = [];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communauté - GameLink</title>
     <link rel="stylesheet" href="/CSS/HEADER.css">
    <link rel="stylesheet" href="/CSS/STYLE_COMMUNAUTE.css">

    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
</head>
 <header>
    <?php include_once __DIR__ . '/../INCLUDES/HEADER.php'; ?>
</header>
<body>
   

    <!-- =================== EN-TÊTE =================== -->
   
    <!-- =================== CONTENU PRINCIPAL =================== -->
    <main class="page-communaute">
        
        <!-- TITRE -->
        <div class="titre-section">
            <h1>🎮 Groupes de Jeux</h1>
            <p>Rejoins un groupe pour discuter avec d'autres joueurs !</p>
        </div>

        <!-- LES 6 GROUPES (comme 6 boîtes) -->
        <div class="grille-groupes">
            
            <?php
            // Pour chaque groupe, on affiche une carte
            foreach ($groupes as $groupe):
            ?>
            
                <div class="carte-groupe">
                    
                    <!-- IMAGE DU GROUPE (avec un joli dégradé violet si pas d'image) -->
                    <div class="image-groupe">
                        <img src="../IMAGES/groupes/groupe_<?= $groupe['id'] ?>.jpg" 
                             alt="<?= $groupe['nom'] ?>"
                             onerror="this.style.display='none';">
                    </div>
                    
                    <!-- INFORMATIONS DU GROUPE -->
                    <div class="info-groupe">
                        <h3><?= $groupe['nom'] ?></h3>
                        <p><?= $groupe['description'] ?></p>
                        <p class="petit-texte">👥 <?= $groupe['nb_membres'] ?> membres</p>
                    </div>
                    
                    <!-- BOUTONS -->
                    <div class="boutons-groupe">
                        <?php if ($groupe['je_suis_membre'] > 0): ?>
                            <!-- Si je suis membre : afficher "Ouvrir le chat" -->
                            <button class="bouton bleu ouvrir-chat" 
                                    data-groupe-id="<?= $groupe['id'] ?>"
                                    data-groupe-nom="<?= $groupe['nom'] ?>">
                                💬 Ouvrir le chat
                            </button>
                            <button class="bouton rouge quitter-groupe" 
                                    data-groupe-id="<?= $groupe['id'] ?>">
                                Quitter
                            </button>
                        <?php else: ?>
                            <!-- Si je ne suis pas membre : afficher "Rejoindre" -->
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

    <!-- =================== BULLE DE CHAT (cachée au début) =================== -->
    <div id="bulle-chat" class="bulle-chat">
        <div class="contenu-bulle">
            
            <!-- EN-TÊTE DE LA BULLE -->
            <div class="haut-bulle">
                <h3 id="nom-groupe-chat">Nom du groupe</h3>
                <button class="bouton-fermer" onclick="fermerChat()">✖</button>
            </div>
            
            <!-- MESSAGES -->
            <div id="zone-messages" class="zone-messages">
                <p class="texte-centre">Chargement des messages...</p>
            </div>
            
            <!-- FORMULAIRE POUR ENVOYER UN MESSAGE -->
            <form id="formulaire-message" class="formulaire-message">
                <input type="hidden" id="id-groupe-actuel">
                <textarea id="mon-message" 
                          placeholder="Écris ton message ici..." 
                          rows="3" 
                          required></textarea>
                <button type="submit" class="bouton bleu">Envoyer</button>
            </form>
            
            <!-- BOUTON QUITTER LE GROUPE -->
            <div class="bas-bulle">
                <button class="bouton rouge quitter-depuis-chat">Quitter ce groupe</button>
            </div>
            
        </div>
    </div>

    <!-- FOND SOMBRE DERRIÈRE LA BULLE -->
    <div id="fond-sombre" class="fond-sombre" onclick="fermerChat()"></div>

    <!-- LE JAVASCRIPT (pour faire marcher les boutons) -->
    <!-- <script src="../JS/COMMUNAUTE.js"></script> -->

</body>
</html>