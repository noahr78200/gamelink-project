// =========================================
// FICHIER : AUTH.js (VERSION DÉBUTANT)
// Gère les formulaires de connexion et inscription
// =========================================

// Attendre que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', function() {
  
  // ===== PARTIE 1 : GESTION DES ONGLETS =====
  
  // Récupérer tous les onglets (boutons "Connexion" et "Inscription")
  var onglets = document.querySelectorAll('.auth-tab');
  
  // Récupérer tous les formulaires
  var formulaires = document.querySelectorAll('.auth-form');
  
  // Ajouter un écouteur de clic sur chaque onglet
  onglets.forEach(function(onglet) {
    onglet.addEventListener('click', function() {
      
      // Enlever la classe "is-active" de tous les onglets
      onglets.forEach(function(autreOnglet) {
        autreOnglet.classList.remove('is-active');
      });
      
      // Enlever la classe "is-active" de tous les formulaires
      formulaires.forEach(function(formulaire) {
        formulaire.classList.remove('is-active');
      });
      
      // Ajouter la classe "is-active" à l'onglet cliqué
      onglet.classList.add('is-active');
      
      // Trouver le formulaire correspondant et l'afficher
      var cible = document.querySelector(onglet.dataset.target);
      if (cible) {
        cible.classList.add('is-active');
      }
    });
  });
  
  
  // ===== PARTIE 2 : AFFICHER/MASQUER LES MOTS DE PASSE =====
  
  // Récupérer tous les boutons "œil" (toggle-visibility)
  var boutonsVisibilite = document.querySelectorAll('.toggle-visibility');
  
  // Ajouter un écouteur de clic sur chaque bouton
  boutonsVisibilite.forEach(function(bouton) {
    bouton.addEventListener('click', function() {
      
      // Trouver le champ de mot de passe juste avant le bouton
      var champMotDePasse = bouton.previousElementSibling;
      
      if (champMotDePasse) {
        // Si le champ est de type "password", le changer en "text"
        // Sinon, le remettre en "password"
        if (champMotDePasse.type === 'password') {
          champMotDePasse.type = 'text';
        } else {
          champMotDePasse.type = 'password';
        }
      }
    });
  });
  
  
  // ===== PARTIE 3 : FONCTIONS UTILITAIRES =====
  
  // === FONCTION : Afficher un message d'erreur ===
  function afficherErreur(idChamp, message) {
    // Trouver l'élément qui affiche l'erreur pour ce champ
    var elementErreur = document.querySelector('.error[data-for="' + idChamp + '"]');
    
    if (elementErreur) {
      elementErreur.textContent = message || '';
    }
  }
  
  // === FONCTION : Bloquer le bouton pendant l'envoi ===
  function bloquerBouton(formulaire) {
    // Trouver le bouton de soumission du formulaire
    var bouton = formulaire.querySelector('button[type="submit"], input[type="submit"]');
    
    if (!bouton) {
      return;
    }
    
    // Sauvegarder le texte original du bouton
    bouton.dataset.texteOriginal = bouton.textContent || bouton.value || '';
    
    // Changer le texte et désactiver le bouton
    if (bouton.tagName === 'BUTTON') {
      bouton.textContent = 'Envoi…';
    } else {
      bouton.value = 'Envoi…';
    }
    
    bouton.disabled = true;
  }
  
  
  // ===== PARTIE 4 : VALIDATION DU FORMULAIRE DE CONNEXION =====
  
  var formulaireConnexion = document.getElementById('loginForm');
  
  if (formulaireConnexion) {
    formulaireConnexion.addEventListener('submit', function(evenement) {
      
      // Récupérer les champs du formulaire
      var champEmail = document.getElementById('loginEmail');
      var champPassword = document.getElementById('loginPassword');
      
      // Effacer les anciennes erreurs
      afficherErreur('loginEmail', '');
      afficherErreur('loginPassword', '');
      
      // Variable pour savoir si le formulaire est valide
      var formulaireValide = true;
      
      // Vérifier l'email
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('loginEmail', 'Email invalide');
      }
      
      // Vérifier le mot de passe (minimum 6 caractères)
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('loginPassword', '6 caractères minimum');
      }
      
      // Si le formulaire n'est pas valide, empêcher l'envoi
      if (!formulaireValide) {
        evenement.preventDefault();
        
        // Afficher les messages d'erreur HTML5 (si disponibles)
        if (formulaireConnexion.reportValidity) {
          formulaireConnexion.reportValidity();
        }
        
        return;
      }
      
      // Si tout est OK, bloquer le bouton pour éviter les doubles clics
      bloquerBouton(formulaireConnexion);
      
      // Le formulaire sera envoyé vers auth_login.php
    });
  }
  
  
  // ===== PARTIE 5 : VALIDATION DU FORMULAIRE D'INSCRIPTION =====
  
  var formulaireInscription = document.getElementById('signupForm');
  
  if (formulaireInscription) {
    formulaireInscription.addEventListener('submit', function(evenement) {
      
      // Récupérer les champs du formulaire
      var champNom = document.getElementById('suName');
      var champEmail = document.getElementById('suEmail');
      var champPassword = document.getElementById('suPass');
      var champConfirmation = document.getElementById('suConfirm');
      var caseACocherCGU = document.getElementById('suCgu');
      
      // Effacer les anciennes erreurs
      afficherErreur('suName', '');
      afficherErreur('suEmail', '');
      afficherErreur('suPass', '');
      afficherErreur('suConfirm', '');
      
      // Variable pour savoir si le formulaire est valide
      var formulaireValide = true;
      
      // Vérifier le pseudo (minimum 3 caractères)
      if (!champNom || !champNom.value || champNom.value.length < 3) {
        formulaireValide = false;
        afficherErreur('suName', '3 caractères minimum');
      }
      
      // Vérifier l'email
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('suEmail', 'Email invalide');
      }
      
      // Vérifier le mot de passe (minimum 6 caractères)
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('suPass', '6 caractères minimum');
      }
      
      // Vérifier que les mots de passe correspondent
      if (!champConfirmation || champPassword.value !== champConfirmation.value) {
        formulaireValide = false;
        afficherErreur('suConfirm', 'Les mots de passe ne correspondent pas');
      }
      
      // Vérifier que la case CGU est cochée
      if (caseACocherCGU && !caseACocherCGU.checked) {
        formulaireValide = false;
        alert('Vous devez accepter les CGU.');
      }
      
      // Si le formulaire n'est pas valide, empêcher l'envoi
      if (!formulaireValide) {
        evenement.preventDefault();
        
        // Afficher les messages d'erreur HTML5 (si disponibles)
        if (formulaireInscription.reportValidity) {
          formulaireInscription.reportValidity();
        }
        
        return;
      }
      
      // Si tout est OK, bloquer le bouton pour éviter les doubles clics
      bloquerBouton(formulaireInscription);
      
      // Le formulaire sera envoyé vers auth_register.php
    });
  }
  
  
  // ===== PARTIE 6 : SE SOUVENIR DE L'EMAIL (facultatif) =====
  
  // Vérifier si un email est sauvegardé dans le navigateur
  var emailSauvegarde = localStorage.getItem('rememberEmail');
  
  if (emailSauvegarde) {
    // Pré-remplir le champ email
    var champEmailConnexion = document.getElementById('loginEmail');
    if (champEmailConnexion) {
      champEmailConnexion.value = emailSauvegarde;
    }
    
    // Cocher la case "Se souvenir"
    var caseSeSouvenir = document.getElementById('rememberMe');
    if (caseSeSouvenir) {
      caseSeSouvenir.checked = true;
    }
  }
});