// RECHERCHE.js
// - Charge une liste de jeux depuis IGDB via API/igdb.php
// - Permet de chercher par nom
// - Permet de filtrer par plateforme / genre / éditeur (en mode "mot-clé")

// URL de l'API PHP
// Sur ton serveur : racine du site = GameLink-main → /API/igdb.php
const API_URL = '/API/igdb.php';

// Tableau avec tous les jeux reçus de l'API
let tousLesJeux = [];

// Filtres actifs (texte + filtres boutons)
let texteRecherche = '';
let filtrePlateforme = '';
let filtreGenre = '';
let filtreEditeur = '';

// Récupération des éléments HTML
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

      // Une fois qu'on a les jeux → on applique les filtres et on affiche
      appliquerFiltresEtAfficher();
    })
    .catch(function (err) {
      console.error(err);
      errorBox.textContent = 'Erreur lors du chargement des jeux.';
    });
}

// ==================================
// 2) Appliquer les filtres + afficher
// ==================================
function appliquerFiltresEtAfficher() {
  gameList.innerHTML = '';
  errorBox.textContent = '';

  // On crée une liste filtrée
  let liste = tousLesJeux.filter(function (jeu) {
    const nom = (jeu.name || '').toLowerCase();

    // --- recherche texte (sur le nom du jeu) ---
    if (texteRecherche !== '') {
      if (!nom.includes(texteRecherche.toLowerCase())) {
        return false;
      }
    }

    // Récupérer plateformes / genres / éditeurs sous forme de tableaux de chaînes
    let plateformes = [];
    if (Array.isArray(jeu.platforms)) {
      plateformes = jeu.platforms
        .filter(function (p) { return p && p.name; })
        .map(function (p) { return p.name; });
    }

    let genres = [];
    if (Array.isArray(jeu.genres)) {
      genres = jeu.genres
        .filter(function (g) { return g && g.name; })
        .map(function (g) { return g.name; });
    }

    let editeurs = [];
    if (Array.isArray(jeu.involved_companies)) {
      editeurs = jeu.involved_companies
        .filter(function (ic) { return ic && ic.company && ic.company.name; })
        .map(function (ic) { return ic.company.name; });
    }

    // On travaille en minuscule pour comparer
    const plateformesMin = plateformes.map(function (p) { return p.toLowerCase(); });
    const genresMin      = genres.map(function (g) { return g.toLowerCase(); });
    const editeursMin    = editeurs.map(function (e) { return e.toLowerCase(); });

    // Filtre plateforme (mot-clé, ex : "pc", "playstation 4")
    if (filtrePlateforme !== '') {
      const key = filtrePlateforme.toLowerCase();
      let ok = false;
      for (let i = 0; i < plateformesMin.length; i++) {
        if (plateformesMin[i].includes(key)) {
          ok = true;
          break;
        }
      }
      if (!ok) return false;
    }

    // Filtre genre (ex : "rpg", "sport", "adventure")
    if (filtreGenre !== '') {
      const key = filtreGenre.toLowerCase();
      let ok = false;
      for (let i = 0; i < genresMin.length; i++) {
        if (genresMin[i].includes(key)) {
          ok = true;
          break;
        }
      }
      if (!ok) return false;
    }

    // Filtre éditeur (ex : "nintendo", "ubisoft")
    if (filtreEditeur !== '') {
      const key = filtreEditeur.toLowerCase();
      let ok = false;
      for (let i = 0; i < editeursMin.length; i++) {
        if (editeursMin[i].includes(key)) {
          ok = true;
          break;
        }
      }
      if (!ok) return false;
    }

    return true;
  });

  // Tri alphabétique A → Z sur le nom du jeu
  liste.sort(function (a, b) {
    const na = (a.name || '').toLowerCase();
    const nb = (b.name || '').toLowerCase();
    return na.localeCompare(nb, 'fr');
  });

  // Si aucun jeu
  if (liste.length === 0) {
    gamesCountEl.textContent = '0 jeu trouvé';
    errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
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

    // Image
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

    // Contenu texte
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

    // Tags (plateforme + genre + éditeur)
    const tags = document.createElement('div');
    tags.className = 'game-tags';

    // Plateformes
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

    // Genres
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

    // Éditeur
    if (Array.isArray(jeu.involved_companies)) {
      const ic = jeu.involved_companies.find(function (x) {
        return x && x.company && x.company.name;
      });
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

  // Quand tu lances une nouvelle recherche, on remet les filtres à "Tous"
  filtrePlateforme = '';
  filtreGenre = '';
  filtreEditeur = '';
  resetChipsUI();

  // On recharge depuis l'API avec le texte de recherche
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

      // On enlève la classe active pour les autres du même type
      chips.forEach(function (c) {
        if (c.getAttribute('data-type') === type) {
          c.classList.remove('chip--active');
        }
      });

      // On active ce bouton
      chip.classList.add('chip--active');

      // Mise à jour des variables de filtre
      if (type === 'platform') {
        filtrePlateforme = value;
      } else if (type === 'genre') {
        filtreGenre = value;
      } else if (type === 'editor') {
        filtreEditeur = value;
      }

      // On ne recharge pas l'API, on filtre juste la liste en mémoire
      appliquerFiltresEtAfficher();
    });
  });

  // Bouton "Réinitialiser"
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
// 5) Initialisation de la page
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

  // Au chargement de la page : on récupère une liste A→Z
  chargerJeux('');
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initRecherche);
} else {
  initRecherche();
}
