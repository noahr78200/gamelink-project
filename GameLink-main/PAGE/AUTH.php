<?php

session_start();

$messages_flash = $_SESSION['flash'] ?? ['errors' => [], 'old' => []];
unset($_SESSION['flash']);

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$chemin_script = $_SERVER['SCRIPT_NAME'];
$dossier_page = dirname($chemin_script);
$dossier_site = dirname($dossier_page);
$url_login = $dossier_site . '../INCLUDES/auth_login.php';
$url_signup = $dossier_site . '../INCLUDES/auth_register.php';

function ancienne_valeur($nom_champ, $valeur_par_defaut = '') {
    global $messages_flash;
    $valeur = $messages_flash['old'][$nom_champ] ?? $valeur_par_defaut;
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>GameLink – Connexion / Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../CSS/AUTH2.css" type="text/css"/>
  

  
  
</head>
<body>

<header>
  <div class="brand">
    <a href="../index.php">
      <img src="../ICON/LogoComplet.svg" alt="GameLink">
    </a>
    <span></span>
  </div>
  <nav>
    <a href="../index.php">Accueil</a>
  </nav>
</header>

<main>
  <section class="card">
    <h1 class="title">Bienvenue </h1>
    <p class="muted">
      Connecte-toi ou crée un compte pour accéder à GameLink. 
      Le captcha sera demandé après une inscription ou une connexion réussie.
    </p>
  </section>

  <section class="grid">
    
    <div class="card">
      
      <div class="tabbar">
        <button class="auth-tab is-active" data-target="#loginForm">Connexion</button>
        <button class="auth-tab" data-target="#signupForm">Inscription</button>
      </div>

      <form id="loginForm" class="auth-form is-active" action="<?php echo htmlspecialchars($url_login); ?>" method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Connexion</h2>

        <div class="field">
          <label for="loginEmail">Email</label>
          <input id="loginEmail" name="email" type="email" required value="<?php echo ancienne_valeur('email'); ?>">
          <p class="error" data-for="loginEmail">
            <?php echo $messages_flash['errors']['loginEmail'] ?? ''; ?>
          </p>
        </div>

        <div class="field">
          <label for="loginPassword">Mot de passe</label>
          <div class="password-wrap">
            <input id="loginPassword" name="password" type="password" required minlength="6">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="loginPassword">
            <?php echo $messages_flash['errors']['loginPassword'] ?? ''; ?>
          </p>
        </div>

        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <button type="submit" class="btn-primary">Se connecter</button>
        
        <p class="error note">
          <?php echo $messages_flash['errors']['general'] ?? ''; ?>
        </p>
      </form>

      <form id="signupForm" class="auth-form" action="<?php echo htmlspecialchars($url_signup); ?>" method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Créer un compte</h2>

        <div class="field">
          <label for="suName">Pseudo</label>
          <input id="suName" name="name" type="text" required minlength="3" value="<?php echo ancienne_valeur('name'); ?>">
          <p class="error" data-for="suName">
            <?php echo $messages_flash['errors']['suName'] ?? ''; ?>
          </p>
        </div>

        <div class="field">
          <label for="suEmail">Email</label>
          <input id="suEmail" name="email" type="email" required value="<?php echo ancienne_valeur('email'); ?>">
          <p class="error" data-for="suEmail">
            <?php echo $messages_flash['errors']['suEmail'] ?? ''; ?>
          </p>
        </div>

        <div class="field">
          <label for="suPass">Mot de passe</label>
          <div class="password-wrap">
            <input id="suPass" name="password" type="password" required minlength="6" placeholder="Minimum 6 caractères">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suPass">
            <?php echo $messages_flash['errors']['suPass'] ?? ''; ?>
          </p>
        </div>

        <div class="field">
          <label for="suConfirm">Confirmer le mot de passe</label>
          <div class="password-wrap">
            <input id="suConfirm" name="confirm" type="password" required minlength="6" placeholder="Répétez le mot de passe">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suConfirm">
            <?php echo $messages_flash['errors']['suConfirm'] ?? ''; ?>
          </p>
        </div>

        <div class="field">
          <label>
            <input id="suCgu" type="checkbox" required> 
            J'accepte les CGU
          </label>
        </div>

        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <button type="submit" class="btn-primary">Créer le compte</button>
        
        <p class="error note">
          <?php echo $messages_flash['errors']['general'] ?? ''; ?>
        </p>
      </form>
    </div>

    <div class="card">
      <h3 class="title">Conseils</h3>
      <ul class="muted">
        <li>Utilise un email valide (ex : nom@domaine.com).</li>
        <li>Mot de passe : au moins 6 caractères.</li>
        <li>Après connexion/inscription réussie, tu seras redirigé vers un <strong>captcha</strong>, puis vers l'accueil.</li>
      </ul>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  
  var onglets = document.querySelectorAll('.auth-tab');
  var formulaires = document.querySelectorAll('.auth-form');
  
  onglets.forEach(function(onglet) {
    onglet.addEventListener('click', function() {
      onglets.forEach(function(o) {
        o.classList.remove('is-active');
      });
      formulaires.forEach(function(f) {
        f.classList.remove('is-active');
      });
      
      onglet.classList.add('is-active');
      
      var cible = document.querySelector(onglet.dataset.target);
      if (cible) {
        cible.classList.add('is-active');
      }
    });
  });

  var boutonsVisibilite = document.querySelectorAll('.toggle-visibility');
  
  boutonsVisibilite.forEach(function(bouton) {
    bouton.addEventListener('click', function() {
      var champMotDePasse = bouton.previousElementSibling;
      
      if (champMotDePasse) {
        if (champMotDePasse.type === 'password') {
          champMotDePasse.type = 'text';
        } else {
          champMotDePasse.type = 'password';
        }
      }
    });
  });

  function afficherErreur(idChamp, message) {
    var elementErreur = document.querySelector('.error[data-for="' + idChamp + '"]');
    if (elementErreur) {
      elementErreur.textContent = message || '';
    }
  }

  function bloquerBouton(formulaire) {
    var bouton = formulaire.querySelector('button[type="submit"]');
    if (bouton) {
      bouton.textContent = 'Envoi…';
      bouton.disabled = true;
    }
  }

  var formulaireConnexion = document.getElementById('loginForm');
  
  if (formulaireConnexion) {
    formulaireConnexion.addEventListener('submit', function(evenement) {
      var champEmail = document.getElementById('loginEmail');
      var champPassword = document.getElementById('loginPassword');
      
      afficherErreur('loginEmail', '');
      afficherErreur('loginPassword', '');
      
      var formulaireValide = true;
      
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('loginEmail', 'Email invalide');
      }
      
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('loginPassword', '6 caractères minimum');
      }
      
      if (!formulaireValide) {
        evenement.preventDefault();
        return;
      }
      
      bloquerBouton(formulaireConnexion);
    });
  }

  var formulaireInscription = document.getElementById('signupForm');
  
  if (formulaireInscription) {
    formulaireInscription.addEventListener('submit', function(evenement) {
      var champNom = document.getElementById('suName');
      var champEmail = document.getElementById('suEmail');
      var champPassword = document.getElementById('suPass');
      var champConfirmation = document.getElementById('suConfirm');
      var caseACocherCGU = document.getElementById('suCgu');
      
      afficherErreur('suName', '');
      afficherErreur('suEmail', '');
      afficherErreur('suPass', '');
      afficherErreur('suConfirm', '');
      
      var formulaireValide = true;
      
      if (!champNom || !champNom.value || champNom.value.length < 3) {
        formulaireValide = false;
        afficherErreur('suName', '3 caractères minimum');
      }
      
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('suEmail', 'Email invalide');
      }
      
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('suPass', '6 caractères minimum');
      }
      
      if (!champConfirmation || champPassword.value !== champConfirmation.value) {
        formulaireValide = false;
        afficherErreur('suConfirm', 'Les mots de passe ne correspondent pas');
      }
      
      if (caseACocherCGU && !caseACocherCGU.checked) {
        formulaireValide = false;
        alert('Vous devez accepter les CGU.');
      }
      
      if (!formulaireValide) {
        evenement.preventDefault();
        return;
      }
      
      bloquerBouton(formulaireInscription);
    });
  }
});
</script>

</body>
</html>