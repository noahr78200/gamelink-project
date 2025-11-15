// JS/RECHERCHE.js
// Version simple pour afficher les jeux à partir d'igdb.php

// URL de ton endpoint PHP
const API_URL = '../API/igdb.php';

// On récupère les éléments de la page
const gameList    = document.getElementById('Game-list');
const searchInput = document.getElementById('search-input');
const searchBtn   = document.getElementById('search-button');
const errorBox    = document.getElementById('error-message');

// ---------- CREER UNE CARTE JEU ----------
function createGameCard(game) {
  const li = document.createElement('li');
  li.className = 'game-card';

  const link = document.createElement('a');
  link.href = 'game.php?id=' + game.id;
  link.className = 'game-link';

  const img = document.createElement('img');
  img.className = 'game-cover';
  img.alt = game.name || 'Jeu';

  if (game.cover && game.cover.image_id) {
    img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' +
              game.cover.image_id + '.jpg';
  } else {
    img.src = 'https://placehold.co/264x352?text=' +
              encodeURIComponent(game.name || 'Jeu');
  }

  const title = document.createElement('h2');
  title.className = 'game-title';
  title.textContent = game.name || 'Sans nom';

  const info = document.createElement('p');
  info.className = 'game-info';

  const parts = [];

  if (typeof game.rating === 'number') {
    parts.push('Note : ' + game.rating.toFixed(0) + '/100');
  }

  if (typeof game.first_release_date === 'number') {
    const d = new Date(game.first_release_date * 1000);
    parts.push('Sortie : ' + d.toLocaleDateString('fr-FR'));
  }

  info.textContent = parts.join(' · ');

  link.appendChild(img);
  link.appendChild(title);
  link.appendChild(info);

  li.appendChild(link);

  return li;
}

// ---------- AFFICHER UNE LISTE DE JEUX ----------
function showGames(games) {
  gameList.innerHTML = '';
  errorBox.textContent = '';

  if (!Array.isArray(games) || games.length === 0) {
    errorBox.textContent = 'Aucun jeu trouvé.';
    return;
  }

  games.forEach(function (game) {
    const card = createGameCard(game);
    gameList.appendChild(card);
  });
}

// ---------- CHARGER LES JEUX POPULAIRES ----------
function loadPopularGames() {
  const formData = new FormData();
  formData.append('mode', 'popular');

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
    console.log('Jeux populaires reçus :', data);
    showGames(data);
  })
  .catch(function (err) {
    console.error(err);
    errorBox.textContent = 'Erreur de chargement des jeux.';
  });
}

// ---------- RECHERCHER DES JEUX ----------
function searchGames() {
  const q = searchInput.value.trim();

  if (q === '') {
    // Si recherche vide, on recharge la liste de base
    loadPopularGames();
    return;
  }

  const formData = new FormData();
  formData.append('mode', 'search');
  formData.append('search', q);

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
    console.log('Résultats pour', q, ':', data);
    showGames(data);
  })
  .catch(function (err) {
    console.error(err);
    errorBox.textContent = 'Erreur lors de la recherche.';
  });
}

// ---------- INITIALISATION ----------
function initSearch() {
  if (searchBtn) {
    searchBtn.addEventListener('click', function () {
      searchGames();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        searchGames();
      }
    });
  }

  loadPopularGames();
}

// Lancer une fois la page chargée
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSearch);
} else {
  initSearch();
}
