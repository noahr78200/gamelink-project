// ==========================================
// FICHIER : RECHERCHE.js
// BUT : Charger et afficher la liste des jeux
// ==========================================

// ==================================
// FONCTION POUR CHARGER LES JEUX
// ==================================

async function LoadGame() {
  // URL de l'API
  const url = '../API/igdb.php';
  
  // Options pour la requête
  const options = {
    method: 'POST',
    headers: { 'content-type': 'text/plain' },
    body:
      'fields name, cover.image_id;' +
      ' where cover != null;' +
      ' sort popularity desc;' +
      ' limit 420;'
  };

  // Envoyer la requête à l'API
  const response = await fetch(url, options);
  
  // Vérifier si la requête a réussi
  if (!response.ok) { 
    ShowError('Erreur de chargement.');
    return;
  }

  // Convertir la réponse en JSON
  const data = await response.json();
  
  // Afficher dans la console (pour déboguer)
  console.log('Jeux reçus (top):', Array.isArray(data) ? data.length : data);
  
  // Afficher les jeux sur la page
  afficherGames(data);
}

// ====================================
// FONCTION POUR AFFICHER LES JEUX
// ====================================

function afficherGames(list) {
  // Récupérer le conteneur où afficher les jeux
  const container = document.getElementById('Game-list');
  
  // Vider le conteneur
  container.innerHTML = '';

  // Vérifier si la liste est vide
  if (!Array.isArray(list) || list.length === 0) {
    container.textContent = 'Aucun jeu trouvé.';
    return;
  }

  // Pour chaque jeu dans la liste
  list.forEach(function (game) {
    // Créer un lien (élément <a>)
    const card = document.createElement('a');
    card.classList.add('card');
    card.href = '/GameLink/PAGE/game.php?id=' + game.id;
    card.style.textDecoration = 'none';

    // Créer une image
    const img = document.createElement('img');
    img.alt = game.name;

    // Si le jeu a une image de couverture
    if (game.cover && game.cover.image_id) {
      img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' + game.cover.image_id + '.jpg';
    } else {
      // Sinon, utiliser une image par défaut
      img.src = 'https://placehold.co/264x352?text=' + encodeURIComponent(game.name || 'Jeu');
    }

    // Créer le titre
    const title = document.createElement('h2');
    title.textContent = game.name;

    // Ajouter l'image et le titre dans la carte
    card.appendChild(img);
    card.appendChild(title);
    
    // Ajouter la carte dans le conteneur
    container.appendChild(card);
  });
}

// ====================================
// FONCTION POUR AFFICHER UNE ERREUR
// ====================================

function ShowError(message) {
  const container = document.getElementById('Game-list');
  if (container) {
    container.textContent = message;
  }
}

// ====================================
// FONCTION POUR RECHERCHER DES JEUX
// ====================================

async function searchItems(query) {
  // URL de l'API
  const url = '../API/igdb.php';
  
  // Échapper les guillemets dans la requête
  const q = query.replace(/"/g, '\\"');

  // Options pour la requête
  const options = {
    method: 'POST',
    headers: { 'content-type': 'text/plain' },
    body:
      'search "' + q + '";' +
      ' fields name, cover.image_id;' +
      ' where cover != null;' +
      ' limit 420;'
  };

  // Envoyer la requête
  const response = await fetch(url, options);
  
  // Vérifier si la requête a réussi
  if (!response.ok) { 
    ShowError('Erreur de chargement.');
    return;
  }

  // Convertir la réponse en JSON
  const data = await response.json();
  
  // Afficher dans la console
  console.log('Jeux reçus (search):', q, Array.isArray(data) ? data.length : data);
  
  // Afficher les résultats
  afficherGames(data);
}

// ======================================
// FONCTION POUR INITIALISER LA RECHERCHE
// ======================================

function initSearch() {
  // Récupérer le champ de recherche
  const input = document.getElementById('search-Bar');

  // Quand l'utilisateur tape dans le champ
  input.addEventListener('input', function () {
    // Récupérer le texte tapé
    const query = input.value.trim();
    
    // Si le champ n'est pas vide
    if (query) {
      // Rechercher les jeux
      searchItems(query);
    } else {
      // Sinon, recharger tous les jeux
      LoadGame();
    }
  });
  
  // Quand l'utilisateur appuie sur Entrée
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      // Empêcher le comportement par défaut
      e.preventDefault();
      
      // Récupérer le texte
      const q = input.value.trim();
      
      // Rechercher ou recharger
      if (q) { 
        searchItems(q);
      } else { 
        LoadGame();
      }
    }
  });
  
  // Quand le champ est vidé
  input.addEventListener('input', function () {
    const q = input.value.trim();
    if (q === '') {
      LoadGame();
    }
  });
}

// =======================================
// DÉMARRER QUAND LA PAGE EST CHARGÉE
// =======================================

// Vérifier si la page est en train de charger
if (document.readyState === 'loading') {
  // Attendre que la page soit chargée
  document.addEventListener('DOMContentLoaded', function () {
    LoadGame();
    initSearch();
  });
} else {
  // La page est déjà chargée
  LoadGame();
  initSearch();
}