// JS/RECHERCHE.js
// Recherche + filtres (plateforme, genre, éditeur)

// URL de l'API PHP
// En prod : '/API/igdb.php'
// En local si besoin : '../API/igdb.php'
const API_URL = '/API/igdb.php';

// Sélection des éléments DOM
const gameList     = document.getElementById('Game-list');
const searchInput  = document.getElementById('search-input');
const searchBtn    = document.getElementById('search-button');
const errorBox     = document.getElementById('error-message');
const gamesCountEl = document.getElementById('games-count');

const platformFiltersContainer = document.getElementById('platform-filters');
const genreFiltersContainer    = document.getElementById('genre-filters');
const editorFiltersContainer   = document.getElementById('editor-filters');
const filtersResetBtn          = document.getElementById('filters-reset');

// Données en mémoire
let allGames = [];
let activeFilters = {
  platform: null,
  genre: null,
  editor: null,
};

// ---------- UTIL : tri alphabétique ----------
function sortAlphabetically(games){
  return games.slice().sort(function(a, b){
    const an = (a.name || '').toLowerCase();
    const bn = (b.name || '').toLowerCase();
    return an.localeCompare(bn, 'fr');
  });
}

// ---------- CRÉER UNE CARTE JEU ----------
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

  const body = document.createElement('div');
  body.className = 'game-card-body';

  const title = document.createElement('h2');
  title.className = 'game-title';
  title.textContent = game.name || 'Sans nom';

  const info = document.createElement('p');
  info.className = 'game-info';

  const parts = [];

  if (typeof game.rating === 'number') {
    parts.push('Note IGDB : ' + game.rating.toFixed(0) + '/100');
  }

  if (typeof game.first_release_date === 'number') {
    const d = new Date(game.first_release_date * 1000);
    parts.push('Sortie : ' + d.toLocaleDateString('fr-FR'));
  }

  info.textContent = parts.join(' · ');

  // Tags (plateformes + genres + éditeurs)
  const tags = document.createElement('div');
  tags.className = 'game-tags';

  // Plateformes
  if (Array.isArray(game.platforms)) {
    const plats = game.platforms.slice(0, 2); // max 2 affichées
    plats.forEach(function(p){
      if (p && p.name) {
        const tag = document.createElement('span');
        tag.className = 'game-tag';
        tag.textContent = p.name;
        tags.appendChild(tag);
      }
    });
  }

  // Genres
  if (Array.isArray(game.genres)) {
    const gens = game.genres.slice(0, 1); // max 1 pour ne pas surcharger
    gens.forEach(function(g){
      if (g && g.name) {
        const tag = document.createElement('span');
        tag.className = 'game-tag';
        tag.textContent = g.name;
        tags.appendChild(tag);
      }
    });
  }

  // Éditeurs / studios (involved_companies.company.name)
  if (Array.isArray(game.involved_companies)) {
    const firstCompany = game.involved_companies.find(function(ic){
      return ic && ic.company && ic.company.name;
    });
    if (firstCompany && firstCompany.company && firstCompany.company.name) {
      const tag = document.createElement('span');
      tag.className = 'game-tag';
      tag.textContent = firstCompany.company.name;
      tags.appendChild(tag);
    }
  }

  body.appendChild(title);
  body.appendChild(info);
  body.appendChild(tags);

  link.appendChild(img);
  link.appendChild(body);

  li.appendChild(link);

  return li;
}

// ---------- AFFICHER UNE LISTE DE JEUX ----------
function showGames(games) {
  gameList.innerHTML = '';
  errorBox.textContent = '';

  const sorted = sortAlphabetically(games);

  if (!Array.isArray(sorted) || sorted.length === 0) {
    gamesCountEl.textContent = '0 jeu trouvé';
    errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
    return;
  }

  gamesCountEl.textContent =
    sorted.length + (sorted.length > 1 ? ' jeux trouvés' : ' jeu trouvé');

  sorted.forEach(function (game) {
    const card = createGameCard(game);
    gameList.appendChild(card);
  });
}

// ---------- CONSTRUIRE LES FILTRES À PARTIR DES JEUX ----------
function buildFiltersFromGames(games){
  const platformsSet = new Set();
  const genresSet    = new Set();
  const editorsSet   = new Set();

  games.forEach(function(game){
    if (Array.isArray(game.platforms)) {
      game.platforms.forEach(function(p){
        if (p && p.name) platformsSet.add(p.name);
      });
    }
    if (Array.isArray(game.genres)) {
      game.genres.forEach(function(g){
        if (g && g.name) genresSet.add(g.name);
      });
    }
    if (Array.isArray(game.involved_companies)) {
      game.involved_companies.forEach(function(ic){
        if (ic && ic.company && ic.company.name) {
          editorsSet.add(ic.company.name);
        }
      });
    }
  });

  createFilterChips(platformFiltersContainer, Array.from(platformsSet), 'platform');
  createFilterChips(genreFiltersContainer,    Array.from(genresSet),    'genre');
  createFilterChips(editorFiltersContainer,   Array.from(editorsSet),   'editor');
}

function createFilterChips(container, values, type){
  container.innerHTML = '';

  // Bouton "Tous"
  const allChip = document.createElement('button');
  allChip.type = 'button';
  allChip.className = 'chip chip--active';
  allChip.dataset.type = type;
  allChip.dataset.value = '';
  allChip.textContent = 'Tous';
  container.appendChild(allChip);

  values.sort(function(a, b){
    return a.toLowerCase().localeCompare(b.toLowerCase(), 'fr');
  });

  values.forEach(function(value){
    const chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'chip';
    chip.dataset.type = type;
    chip.dataset.value = value;
    chip.textContent = value;
    container.appendChild(chip);
  });
}

// ---------- APPLIQUER LES FILTRES ----------
function applyFilters(){
  let filtered = allGames.slice();

  if (activeFilters.platform) {
    filtered = filtered.filter(function(game){
      if (!Array.isArray(game.platforms)) return false;
      return game.platforms.some(function(p){
        return p && p.name === activeFilters.platform;
      });
    });
  }

  if (activeFilters.genre) {
    filtered = filtered.filter(function(game){
      if (!Array.isArray(game.genres)) return false;
      return game.genres.some(function(g){
        return g && g.name === activeFilters.genre;
      });
    });
  }

  if (activeFilters.editor) {
    filtered = filtered.filter(function(game){
      if (!Array.isArray(game.involved_companies)) return false;
      return game.involved_companies.some(function(ic){
        return ic && ic.company && ic.company.name === activeFilters.editor;
      });
    });
  }

  showGames(filtered);
}

// ---------- GESTION DES CLICS SUR LES FILTRES ----------
function onFilterClick(event){
  const target = event.target;
  if (!target.classList.contains('chip')) return;

  const type  = target.dataset.type;
  const value = target.dataset.value || '';

  // Désactiver tous les chips de ce type
  const container = target.parentElement;
  const chips = container.querySelectorAll('.chip');
  chips.forEach(function(ch){
    ch.classList.remove('chip--active');
  });

  // Activer celui cliqué
  target.classList.add('chip--active');

  // Mettre à jour l'état
  if (value === '') {
    activeFilters[type] = null;
  } else {
    activeFilters[type] = value;
  }

  applyFilters();
}

if (platformFiltersContainer) {
  platformFiltersContainer.addEventListener('click', onFilterClick);
}
if (genreFiltersContainer) {
  genreFiltersContainer.addEventListener('click', onFilterClick);
}
if (editorFiltersContainer) {
  editorFiltersContainer.addEventListener('click', onFilterClick);
}

if (filtersResetBtn) {
  filtersResetBtn.addEventListener('click', function(){
    activeFilters.platform = null;
    activeFilters.genre    = null;
    activeFilters.editor   = null;

    // Réactiver "Tous" pour chaque groupe
    [platformFiltersContainer, genreFiltersContainer, editorFiltersContainer].forEach(
      function(container){
        if (!container) return;
        const chips = container.querySelectorAll('.chip');
        chips.forEach(function(ch, index){
          ch.classList.toggle('chip--active', index === 0);
        });
      }
    );

    applyFilters();
  });
}

// ---------- CHARGER LES JEUX PAR DÉFAUT (ALPHABÉTIQUE) ----------
function loadDefaultGames() {
  const formData = new FormData();
  formData.append('mode', 'popular'); // côté PHP : tri par nom (sort name asc)

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
    console.log('Jeux reçus :', data);
    allGames = Array.isArray(data) ? data : [];
    buildFiltersFromGames(allGames);
    applyFilters(); // applique tri + filtres (aucun = tout)
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
    // Si barre vide → on recharge la liste par défaut
    loadDefaultGames();
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
    allGames = Array.isArray(data) ? data : [];
    buildFiltersFromGames(allGames);
    applyFilters();
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

  loadDefaultGames();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSearch);
} else {
  initSearch();
}
