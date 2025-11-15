// RECHERCHE.js
// - Charge une liste de jeux depuis IGDB via API/igdb.php
// - Permet de chercher par nom
// - Permet de filtrer par plateforme / genre / éditeur (mot-clé)

// URL de l'API PHP (DocumentRoot = GameLink-main)
const API_URL = '/API/igdb.php';

// Données en mémoire
let tousLesJeux = [];

// Filtres actifs
let texteRecherche = '';
let filtrePlateforme = '';
let filtreGenre = '';
let filtreEditeur = '';

// Éléments HTML
const gameList     = document.getElementById('Game-list');
const searchInput  = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const errorBox     = document.getElementById('error-message');
const gamesCountEl = document.getElementById('games-count');
const resetButton  = document.getElementById('filters-reset');

// ===============================
// 1) Charger les jeux depuis l'API
// ===============================
function chargerJeux(texte) {
  const formData = new FormData();
  formData.append('search', texte || '');

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
      if (!Array.isArray(data)) {
        tousLesJeux = [];
      } else {
        tousLesJeux = data;
      }
      appliquerFiltresEtAfficher();
    })
    .catch(function (err) {
      console.error(err);
      errorBox.textContent = 'Erreur lors du chargement des jeux.';
    });
}

// ==================================
// 2) Appliquer filtres + afficher
// ==================================
function appliquerFiltresEtAfficher() {
  gameList.innerHTML = '';
  errorBox.textContent = '';

  let liste = tousLesJeux.filter(function (jeu) {
    const nom = (jeu.name || '').toLowerCase();

    // Recherche texte sur le nom
    if (texteRecherche !== '') {
      if (!nom.includes(texteRecherche.toLowerCase())) {
        return false;
      }
    }

    // Récupération des données IGDB
    let plateformes = [];
    if (Array.isArray(jeu.platforms)) {
      plateformes = jeu.platforms
        .filter(p => p && p.name)
        .map(p => p.name.toLowerCase());
    }

    let genres = [];
    if (Array.isArray(jeu.genres)) {
      genres = jeu.genres
        .filter(g => g && g.name)
        .map(g => g.name.toLowerCase());
    }

    let editeurs = [];
    if (Array.isArray(jeu.involved_companies)) {
      editeurs = jeu.involved_companies
        .filter(ic => ic && ic.company && ic.company.name)
        .map(ic => ic.company.name.toLowerCase());
    }

    // Filtre plateforme (mot-clé, ex: "pc", "playstation 4")
    if (filtrePlateforme !== '') {
      const key = filtrePlateforme.toLowerCase();
      const ok = plateformes.some(p => p.includes(key));
      if (!ok) return false;
    }

    // Filtre genre (ex: "role-playing", "sport")
    if (filtreGenre !== '') {
      const key = filtreGenre.toLowerCase();
      const ok = genres.some(g => g.includes(key));
      if (!ok) return false;
    }

    // Filtre éditeur (ex: "nintendo", "ubisoft")
    if (filtreEditeur !== '') {
      const key = filtreEditeur.toLowerCase();
      const ok = editeurs.some(e => e.includes(key));
      if (!ok) return false;
    }

    return true;
  });

  // Tri alphabétique sur le nom
  liste.sort(function (a, b) {
    const na = (a.name || '').toLowerCase();
    const nb = (b.name || '').toLowerCase();
    return na.localeCompare(nb, 'fr');
  });

  // Si aucun jeu
  if (liste.length === 0) {
    gamesCountEl.textContent = '0 jeu trouvé';
    errorBox.textContent = 'Aucun jeu n’est disponible avec ces critères.';
    return;
  } else {
    gamesCountEl.textContent =
      liste.length + (liste.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
  }

  // Création des cartes
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

    const tags = document.createElement('div');
    tags.className = 'game-tags';

    if (Array.isArray(jeu.platforms)) {
      jeu.platforms.slice(0, 2).forEach(function (p) {
        if (p && p.name) {
          const tag = document.createElement('span');
          tag.className = 'game-tag';
          tag.textContent = p.name;
          tags.appendChild(tag);
        }
      });
    }

    if (Array.isArray(jeu.genres)) {
      jeu.genres.slice(0, 1).forEach(function (g) {
        if (g && g.name) {
          const tag = document.createElement('span');
          tag.className = 'game-tag';
          tag.textContent = g.name;
          tags.appendChild(tag);
        }
      });
    }

    if (Array.isArray(jeu.involved_companies)) {
      const ic = jeu.involved_companies.find(x => x && x.company && x.company.name);
      if (ic && ic.company && ic.company.name) {
        const tag = document.createElement('span');
        tag.className = 'game-tag';
        tag.textContent = ic.company.name;
        tags.appendChild(tag);
      }
    }

    body.appendChild(titre);
    body.appendChild(info);
    body.appendChild(tags);

    lien.appendChild(img);
    lien.appendChild(body);
    li.appendChild(lien);
    gameList.appendChild(li);
  });
}

// ===============================
// 3) Recherche par texte
// ===============================
function lancerRecherche() {
  texteRecherche = searchInput.value.trim();
  // À chaque nouvelle recherche, on recharge depuis l’API
  chargerJeux(texteRecherche);
}

// ===============================
// 4) Gestion des filtres (boutons .chip)
// ===============================
function resetChipsUI() {
  const chips = document.querySelectorAll('.chip');
  chips.forEach(function (c) {
    const value = c.getAttribute('data-value') || '';
    if (value === '') {
      c.classList.add('chip--active');
    } else {
      c.classList.remove('chip--active');
    }
  });
}

function initialiserFiltres() {
  const chips = document.querySelectorAll('.chip');

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      const type  = chip.getAttribute('data-type');
      const value = chip.getAttribute('data-value') || '';

      chips.forEach(function (c) {
        if (c.getAttribute('data-type') === type) {
          c.classList.remove('chip--active');
        }
      });

      chip.classList.add('chip--active');

      if (type === 'platform') {
        filtrePlateforme = value;
      } else if (type === 'genre') {
        filtreGenre = value;
      } else if (type === 'editor') {
        filtreEditeur = value;
      }

      appliquerFiltresEtAfficher();
    });
  });

  if (resetButton) {
    resetButton.addEventListener('click', function () {
      texteRecherche = '';
      if (searchInput) searchInput.value = '';

      filtrePlateforme = '';
      filtreGenre = '';
      filtreEditeur = '';

      resetChipsUI();
      appliquerFiltresEtAfficher();
    });
  }
}

// ===============================
// 5) Initialisation
// ===============================
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

  initialiserFiltres();

  // Chargement initial : liste A→Z, sans filtre ni recherche
  chargerJeux('');
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initRecherche);
} else {
  initRecherche();
}
