<?php
// On démarre la session pour savoir qui est connecté
session_start();


// On inclut le fichier qui contient la connexion à la base de données
require __DIR__ . '/../DATA/DBConfig.php';
require_once __DIR__ . '/../INCLUDES/track.php';
require_once __DIR__ . '/../INCLUDES/check_admin.php';



// Connexion à la base avec PDO (comme en cours)
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Erreur de base de données");
}

// // On récupère l'id du jeu dans l'URL : game.php?id=1 par exemple
// if (isset($_GET['id'])) {
//     $id_jeu = (int)$_GET['id'];
// } else {
//     // Si pas d'id, on retourne à l'accueil
//     header("Location: ../index.php");
//     exit;
// }

// On regarde si un joueur est connecté
$id_joueur_connecte = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// ---------------------------------------------------------------------
// 1) TRAITER LES FORMULAIRES (NOTE + COMMENTAIRE)
// ---------------------------------------------------------------------

// Si quelqu'un envoie une note (formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {

    // Il faut être connecté pour noter
    if ($id_joueur_connecte === null) {
        header("Location: AUTH.php");
        exit;
    }

    // On récupère la note choisie (1 à 5)
    $note = (int)$_POST['rating'];
    if ($note < 1) $note = 1;
    if ($note > 5) $note = 5;

    // On regarde si ce joueur a déjà mis un avis sur ce jeu
    $sql = "SELECT * FROM avis WHERE id_joueur = :id_joueur AND id_jeu = :id_jeu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_joueur' => $id_joueur_connecte,
        ':id_jeu'    => $id_jeu
    ]);
    $avis_existant = $stmt->fetch();

    $date_maintenant = date("Y-m-d H:i:s");

    if ($avis_existant) {
        // Il a déjà un avis -> on met à jour la note
        $sql = "UPDATE avis
                SET valeur = :valeur, date_notation = :date_notation
                WHERE id_joueur = :id_joueur AND id_jeu = :id_jeu";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':valeur'        => $note,
            ':date_notation' => $date_maintenant,
            ':id_joueur'     => $id_joueur_connecte,
            ':id_jeu'        => $id_jeu
        ]);
    } else {
        // Pas encore d'avis -> on en crée un
        $sql = "INSERT INTO avis (id_joueur, id_jeu, valeur, date_notation)
                VALUES (:id_joueur, :id_jeu, :valeur, :date_notation)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_joueur'     => $id_joueur_connecte,
            ':id_jeu'        => $id_jeu,
            ':valeur'        => $note,
            ':date_notation' => $date_maintenant
        ]);
    }

    // On recharge la page pour voir la nouvelle note
    header("Location: game.php?id=" . $id_jeu);
    exit;
}

// Si quelqu'un envoie un commentaire (formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {

    // Il faut être connecté pour commenter
    if ($id_joueur_connecte === null) {
        header("Location: AUTH.php");
        exit;
    }

    // On récupère le texte du commentaire
    $commentaire = trim($_POST['comment_text']);

    if ($commentaire !== "") {
        // On regarde si ce joueur a déjà une ligne dans avis pour ce jeu
        $sql = "SELECT * FROM avis WHERE id_joueur = :id_joueur AND id_jeu = :id_jeu";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_joueur' => $id_joueur_connecte,
            ':id_jeu'    => $id_jeu
        ]);
        $avis_existant = $stmt->fetch();

        $date_maintenant = date("Y-m-d H:i:s");

        if ($avis_existant) {
            // On met à jour le commentaire
            $sql = "UPDATE avis
                    SET texte_commentaire = :texte_commentaire, date_commentaire = :date_commentaire
                    WHERE id_joueur = :id_joueur AND id_jeu = :id_jeu";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':texte_commentaire' => $commentaire,
                ':date_commentaire'  => $date_maintenant,
                ':id_joueur'         => $id_joueur_connecte,
                ':id_jeu'            => $id_jeu
            ]);
        } else {
            // On crée la ligne avec juste le commentaire
            $sql = "INSERT INTO avis (id_joueur, id_jeu, texte_commentaire, date_commentaire)
                    VALUES (:id_joueur, :id_jeu, :texte_commentaire, :date_commentaire)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_joueur'         => $id_joueur_connecte,
                ':id_jeu'            => $id_jeu,
                ':texte_commentaire' => $commentaire,
                ':date_commentaire'  => $date_maintenant
            ]);
        }
    }

    // On recharge la page pour voir le commentaire
    header("Location: game.php?id=" . $id_jeu);
    exit;
}

// ---------------------------------------------------------------------
// 2) CHARGER LES INFOS DU JEU
// ---------------------------------------------------------------------

// On récupère le jeu
$sql = "SELECT j.*, e.nom AS nom_editeur
        FROM jeu j
        LEFT JOIN editeur e ON e.id_editeur = j.id_editeur
        WHERE j.id_jeu = :id_jeu";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_jeu' => $id_jeu]);
$jeu = $stmt->fetch();

if (!$jeu) {
    // Si le jeu n'existe pas, on retourne à l'accueil
    header("Location: ../index.php");
    exit;
}

// On récupère les genres du jeu
$sql = "SELECT g.nom
        FROM genre g
        JOIN jeu_genre jg ON jg.id_genre = g.id_genre
        WHERE jg.id_jeu = :id_jeu";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_jeu' => $id_jeu]);
$genres = $stmt->fetchAll();
$liste_genres = [];
foreach ($genres as $g) {
    $liste_genres[] = $g['nom'];
}
$texte_genres = implode(", ", $liste_genres);

// ---------------------------------------------------------------------
// 3) CHARGER LES NOTES + MOYENNE
// ---------------------------------------------------------------------
$sql = "SELECT valeur FROM avis WHERE id_jeu = :id_jeu AND valeur IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_jeu' => $id_jeu]);
$toutes_les_notes = $stmt->fetchAll();

$nombre_notes = count($toutes_les_notes);
$note_moyenne = 0;
$distribution = [0,0,0,0,0,0]; // index 0 à 5, on utilisera 1 à 5

if ($nombre_notes > 0) {
    $somme = 0;
    foreach ($toutes_les_notes as $row) {
        $val = (int)$row['valeur'];
        $somme += $val;
        if ($val >=1 && $val <=5) {
            $distribution[$val] = $distribution[$val] + 1;
        }
    }
    $note_moyenne = round($somme / $nombre_notes, 1);
}

// La note du joueur connecté (si connecté)
$ma_note = null;
if ($id_joueur_connecte !== null) {
    $sql = "SELECT valeur FROM avis WHERE id_joueur = :id_joueur AND id_jeu = :id_jeu AND valeur IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_joueur' => $id_joueur_connecte,
        ':id_jeu'    => $id_jeu
    ]);
    $ma_note = $stmt->fetchColumn();
}

// ---------------------------------------------------------------------
// 4) CHARGER LES COMMENTAIRES
// ---------------------------------------------------------------------
$sql = "SELECT a.texte_commentaire, a.date_commentaire, j.pseudo
        FROM avis a
        JOIN joueur j ON j.id_joueur = a.id_joueur
        WHERE a.id_jeu = :id_jeu
          AND a.texte_commentaire IS NOT NULL
        ORDER BY a.date_commentaire DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_jeu' => $id_jeu]);
$commentaires = $stmt->fetchAll();

// Petite fonction pour sécuriser le texte (comme htmlspecialchars)
function h($texte) {
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= h($jeu['titre']) ?> | GameLink</title>
    <!-- On lie notre fichier CSS séparé -->
    <link rel="stylesheet" href="../CSS/STYLE_GAME.css">
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">

</head>
 <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
<body>

<a class="back-link" href="ACCUEIL.php">← Retour</a>

<div class="game-page">

    <!-- Colonne gauche : image du jeu -->
    <div class="game-left">
        <img src="<?= h($jeu['cover_url']) ?>" alt="<?= h($jeu['titre']) ?>" class="game-cover">
    </div>

    <!-- Colonne droite : infos + notes + commentaires -->
    <div class="game-right">
        <h1><?= h($jeu['titre']) ?></h1>
        <p class="game-genres"><?= h($texte_genres) ?></p>

        <!-- Bloc de note -->
        <h2>Avis des joueurs</h2>

        <div class="rating-block">
            <p>Note moyenne : <strong><?= $note_moyenne ?></strong> / 5 (<?= $nombre_notes ?> avis)</p>

            <!-- On affiche des étoiles remplies ou vides pour la moyenne -->
            <p>
                <?php
                $moy_arrondie = round($note_moyenne); // pour l'affichage
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $moy_arrondie) {
                        echo "★";
                    } else {
                        echo "☆";
                    }
                }
                ?>
            </p>

            <!-- Distribution simple : combien de notes 5, 4, 3, 2, 1 -->
            <div class="rating-distribution">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="rating-row">
                        <span><?= $i ?>★</span>
                        <div class="rating-bar">
                            <?php
                            if ($nombre_notes > 0) {
                                $pourcentage = ($distribution[$i] / $nombre_notes) * 100;
                            } else {
                                $pourcentage = 0;
                            }
                            ?>
                            <div class="rating-bar-fill" style="width: <?= $pourcentage ?>%;"></div>
                        </div>
                        <span><?= $distribution[$i] ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Formulaire pour donner une note (1 à 5) avec des boutons étoile -->
            <?php if ($id_joueur_connecte !== null): ?>
                <p>Ta note actuelle :
                    <?php
                    if ($ma_note === null) {
                        echo "aucune pour l'instant.";
                    } else {
                        echo $ma_note . " / 5";
                    }
                    ?>
                </p>

                <form method="post" class="rating-form">
                    <p>Donner une note :</p>
                    <!-- Chaque bouton envoie une note différente -->
                    <button type="submit" name="rating" value="1">★</button>
                    <button type="submit" name="rating" value="2">★★</button>
                    <button type="submit" name="rating" value="3">★★★</button>
                    <button type="submit" name="rating" value="4">★★★★</button>
                    <button type="submit" name="rating" value="5">★★★★★</button>
                </form>
            <?php else: ?>
                <p class="info-text">Connecte-toi pour pouvoir noter ce jeu.</p>
            <?php endif; ?>
        </div>

        <!-- Description du jeu -->
        <h2>Description</h2>
        <p class="game-description"><?= nl2br(h($jeu['description'])) ?></p>

        <!-- Commentaires -->
        <h2>Commentaires</h2>

        <?php if ($id_joueur_connecte !== null): ?>
            <form method="post" class="comment-form">
                <textarea name="comment_text" placeholder="Écris ton avis sur ce jeu..." required></textarea>
                <button type="submit">Envoyer le commentaire</button>
            </form>
        <?php else: ?>
            <p class="info-text">Connecte-toi pour écrire un commentaire.</p>
        <?php endif; ?>

        <div class="comments-list">
            <?php if (empty($commentaires)): ?>
                <p class="info-text">Pas encore de commentaires.</p>
            <?php else: ?>
                <?php foreach ($commentaires as $c): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <strong><?= h($c['pseudo']) ?></strong>
                            <span class="comment-date"><?= h($c['date_commentaire']) ?></span>
                        </div>
                        <p class="comment-text"><?= nl2br(h($c['texte_commentaire'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>
</html>
