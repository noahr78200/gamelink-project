<?php
session_start();



require_once __DIR__ . '/../INCLUDES/track.php';





require_once __DIR__ . '/../DATA/DBConfig.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="Profil GameLink">
        <title>Profil | GameLink</title>
        <link rel="stylesheet" href="/../CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="/../CSS/STYLE_PROFIL.css" type="text/css"/>
        <link rel="icon" type="image/png" sizes="32x32" href="/../ICON/LogoSimple.svg">
    </head>
    <body>
        <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
        <main>
            <section class="profile-card">
                

                <div class="profile-info">
                <img src="/../ICON/iconProfil.svg" alt="avatar">
                <div class="name-bio">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
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
                    </div>
                    <div>
                        <h2>Jeux Fini :</h2>
                    </div>
                </section>
                <section id="set" class="box">
                    <h2>Paramètres du compte</h2>

                    <form action="/../INCLUDES/update_email.php" method="POST" class="form">
                        <label for="email">Nouvel e-mail</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                        <button type="submit" class="btn">Modifier l'e-mail</button>
                    </form>

                    <form action="/../INCLUDES/update_mdp.php" method="POST" class="form">
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
        <script src="/../JS/PROFIL.js"></script>
    </body>