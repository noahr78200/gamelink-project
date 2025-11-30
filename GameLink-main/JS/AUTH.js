document.addEventListener('DOMContentLoaded', function() {
  
  var onglets = document.querySelectorAll('.auth-tab');
  var formulaires = document.querySelectorAll('.auth-form');
  
  onglets.forEach(function(onglet) {
    onglet.addEventListener('click', function() {
      
      onglets.forEach(function(autreOnglet) {
        autreOnglet.classList.remove('is-active');
      });
      
      formulaires.forEach(function(formulaire) {
        formulaire.classList.remove('is-active');
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
    var bouton = formulaire.querySelector('button[type="submit"], input[type="submit"]');
    
    if (!bouton) {
      return;
    }
    
    bouton.dataset.texteOriginal = bouton.textContent || bouton.value || '';
    
    if (bouton.tagName === 'BUTTON') {
      bouton.textContent = 'Envoi…';
    } else {
      bouton.value = 'Envoi…';
    }
    
    bouton.disabled = true;
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
        
        if (formulaireConnexion.reportValidity) {
          formulaireConnexion.reportValidity();
        }
        
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
        
        if (formulaireInscription.reportValidity) {
          formulaireInscription.reportValidity();
        }
        
        return;
      }
      
      bloquerBouton(formulaireInscription);
    });
  }
  
  var emailSauvegarde = localStorage.getItem('rememberEmail');
  
  if (emailSauvegarde) {
    var champEmailConnexion = document.getElementById('loginEmail');
    if (champEmailConnexion) {
      champEmailConnexion.value = emailSauvegarde;
    }
    
    var caseSeSouvenir = document.getElementById('rememberMe');
    if (caseSeSouvenir) {
      caseSeSouvenir.checked = true;
    }
  }
});