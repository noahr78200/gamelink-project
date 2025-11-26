<?php
session_start();

require_once __DIR__ . '/../INCLUDES/track.php';
require_once __DIR__ . '/../DATA/DBConfig.php';
require_once __DIR__ . '/../INCLUDES/avatar_svg.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT pseudo, email, bio, avatar_config FROM joueur WHERE id_joueur = ?");$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé");
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="Profil GameLink">
        <title>Profil | GameLink</title>
        <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="../CSS/STYLE_PROFIL.css" type="text/css"/>
        <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    </head>
    <body>
    <?php 
    
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
        <main>
            <section class="profile-card">
                <a href="avatar.php" class="edit-btn">
                    <img src="../ICON/edit.svg" alt="Logo edit" width="30px">
                </a>
                <div class="profile-info">
                <?php echo generateAvatarSVG($user['avatar_config'], 100); ?>
                <div class="name-bio">
                    <h2><?= htmlspecialchars($user['pseudo']) ?></h2>
                    <p><?= htmlspecialchars($user['bio']) ?></p>
                </div>
                </div>

                <div class="stats">
                    <div class="stat">
                        <span>123</span>
                        <small>Jeux joués</small>
                    </div>
                    <div class="stat">
                        <span>1,255</span>
                        <small>Abonnements</small>
                    </div>
                    <div class="stat">
                        <span>653</span>
                        <small>Abonnés</small>
                    </div>
                    <div class="stat">
                        <span>60</span>
                        <small>Posts</small>
                    </div>
                </div>
            </section>

            <section class="profile">
                <div class="tabs">
                    <button class="tab active" data-tab="act"><h2>Activité</h2></button>
                    <button class="tab" data-tab="set"><h2>Paramètres</h2></button>
                </div>

                <section id="act" class="box active">
                    <div>
                        <h2>Jeux Favoris :</h2>
                        <div class="favorites-grid">
                            <?php
                            //On récupére les jeux favoris 
                            $stmt_favoris = $pdo->prepare("SELECT j.id_jeu, j.titre, j.cover_url
                                FROM favoris f
                                JOIN jeu j ON f.id_jeu = j.id_jeu
                                WHERE f.id_joueur = ?
                                ORDER BY f.date_ajout DESC
                                LIMIT 15");
                            $stmt_favoris->execute([$user_id]);
                            $favoris = $stmt_favoris->fetchAll();
                            
                            if (empty($favoris)): ?>
                                <p class="no-favorites">Aucun jeu favori pour le moment. Ajoutez des jeux à vos favoris !</p>
                            <?php else: ?>
                                <?php foreach ($favoris as $jeu): ?>
                                    <div class="game-card">
                                        <a href="game.php?id=<?= $jeu['id_jeu'] ?>">
                                            <img src="<?= htmlspecialchars($jeu['cover_url']) ?>" 
                                                 alt="<?= htmlspecialchars($jeu['titre']) ?>"
                                                 onerror="this.src='../ICON/placeholder.jpg'">
                                            <p><?= htmlspecialchars($jeu['titre']) ?></p>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h2>Jeux :</h2>
                    </div>
                </section>
                <section id="set" class="box">
                    <h2>Paramètres du compte</h2>

                    <form action="../INCLUDES/update_email.php" method="POST" class="form">
                        <label for="email">Nouvel e-mail</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                        <button type="submit" class="btn">Modifier l'e-mail</button>
                    </form>

                    <form action="../INCLUDES/update_mdp.php" method="POST" class="form">
                        <label for="old_mdp">Ancien mot de passe</label>
                        <input type="password" name="old_mdp" id="old_mdp" required>
                        <br>
                        <label for="new_mdp">Nouveau mot de passe</label>
                        <input type="password" name="new_mdp" id="new_mdp" required>

                        <button type="submit" class="btn red">Modifier le mot de passe</button>
                    </form>

                    <h2>Apparence</h2>
                    <button id="themeToggle" class="btn">Mode Sombre / Mode Clair</button>
                </section>

            </section>
        </main>
        <script src="../JS/PROFIL.js"></script>
    </body>
</html>