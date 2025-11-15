<?php
session_start();
require_once __DIR__ . '/../INCLUDES/track.php';  // ← Ajoute cette ligne
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="ACCUEIL GameLink">
        <title>Communauté | GameLink</title>
        <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="../CSS/COMMUNAUTE.css" type="text/css"/>
        <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    </head>
    <?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
    <body>
   

        
         <main>
  <h1 class="community-title">Communauté</h1>

  <section class="community-layout">
    <article class="community-card">
      <div class="community-feed-header">
        <h2>Activité récente</h2>
        <span>À venir</span>
      </div>
      <p class="community-empty">La communauté sera bientôt disponible 👀</p>
    </article>

    <aside class="community-side-card">
      <h3>Top joueurs</h3>
      <p class="community-empty">Classement en construction…</p>
    </aside>
  </section>
</main>

        


         
    </body>