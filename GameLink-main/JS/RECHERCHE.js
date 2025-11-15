// RECHERCHE.js
// VERSION ULTRA SIMPLE
// - Affiche des jeux par défaut (A -> Z)
// - Permet de chercher par NOM (Zelda, FIFA...)
// - PAS DE FILTRES POUR L'INSTANT

const API_URL = '/API/igdb.php';

// On récupère les éléments HTML
const gameList     = document.getElementById('Game-list');
const searchInput  = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const errorBox     = document.getElementById('error-message');
const gamesCountEl = document.getElementById('games-count');

// Fonction qui va chercher les jeux auprès de l'API PHP
function chargerJeux(texteRecherche) {
  // On prépare les données pour POST
  const formData = new FormData();
  formData.append('search', texteRecherche || '');

  // On vide les messages
  if (errorBox) errorBox.textContent = '';

  fetch(API_URL, {
    method: 'POST',
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erreur HTTP : ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      // data doit être un tableau de jeux
      if (!Array.isArray(data)) {
        afficherJeux([]);
      } else {
        afficherJeux(data);
      }
    })
    .catch(function (err) {
      console.error(err);
      if (errorBox) {
        errorBox.textContent = 'Erreur lors du chargement des jeux.';
      }
      afficherJeux([]);
    });
}

// Fonction qui affiche les jeux dans la liste <ul id="Game-list">
function afficherJeux(liste) {
  if (!gameList) return;
  gameList.innerHTML = '';

  // Si aucun jeu trouvé
  if (!Array.isArray(liste) || liste.length === 0) {
    if (gamesCountEl) {
      gamesCountEl.textContent = '0 jeu trouvé';
    }
    if (errorBox) {
      errorBox.textContent = 'Aucun jeu trouvé.';
    }
    return;
  }

  // Tri alphabétique A -> Z sur le nom
  liste.sort(function (a, b) {
    const na = (a.name || '').toLowerCase();
    const nb = (b.name || '').toLowerCase();
    return na.localeCompare(nb, 'fr');
  });

  if (gamesCountEl) {
    gamesCountEl.textContent =
      liste.length + (liste.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
  }

  // Pour chaque jeu, on crée une carte simple
  liste.forEach(function (jeu) {
    const li = document.createElement('li');
    li.className = 'game-card';

    const lien = document.createElement('a');
    lien.className = 'game-link';
    lien.href = 'game.php?id=' + jeu.id;

    const img = document.createElement('img');
    img.className = 'game-cover';
    img.alt = jeu.name || 'Jeu';

    if (jeu.cover && jeu.cover.image_id) {
      img.src =
        'https://images.igdb.com/igdb/image/upload/t_cover_big/' +
        jeu.cover.image_id +
        '.jpg';
    } else {
      img.src =
        'https://placehold.co/264x352?text=' +
        encodeURIComponent(jeu.name || 'Jeu');
    }

    const body = document.createElement('div');
    body.className = 'game-card-body';

    const titre = document.createElement('h2');
    titre.className = 'game-title';
    titre.textContent = jeu.name || 'Sans nom';

    const info = document.createElement('p');
    info.className = 'game-info';

    const morceaux = [];

    if (typeof jeu.rating === 'number') {
      morceaux.push('Note : ' + Math.round(jeu.rating) + '/100');
    }

    if (typeof jeu.first_release_date === 'number') {
      const d = new Date(jeu.first_release_date * 1000);
      morceaux.push('Sortie : ' + d.toLocaleDateString('fr-FR'));
    }

    info.textContent = morceaux.join(' · ');

    body.appendChild(titre);
    body.appendChild(info);

    lien.appendChild(img);
    lien.appendChild(body);
    li.appendChild(lien);
    gameList.appendChild(li);
  });
}

// Quand on clique sur "Rechercher"
function lancerRecherche() {
  const texte = searchInput ? searchInput.value.trim() : '';
  chargerJeux(texte);
}

// Initialisation de la page
function initRecherche() {
  if (searchButton) {
    searchButton.addEventListener('click', lancerRecherche);
  }

  if (searchInput) {
    searchInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        lancerRecherche();
      }
    });
  }

  // Au chargement, on affiche des jeux par défaut (search vide)
  chargerJeux('');
}

// On lance init quand la page est prête
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initRecherche);
} else {
  initRecherche();
}
