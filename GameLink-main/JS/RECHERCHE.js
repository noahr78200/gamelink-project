// JS/RECHERCHE.js
// Version basée sur TON code qui marchait + filtres simples.

// URL de ton fichier PHP qui appelle IGDB
const API_URL = '../API/igdb.php';

// Sélecteurs utiles
const gameList    = document.getElementById('Game-list');
const searchInput = document.getElementById('search-input');
const searchBtn   = document.getElementById('search-button');
const errorBox    = document.getElementById('error-message');
const gamesCount  = document.getElementById('games-count');

// Pour les filtres
const resetBtn = document.getElementById('filters-reset');
const chips    = document.querySelectorAll('.chip');

// On stocke tous les jeux reçus pour pouvoir filtrer côté client
let allGames = [];

// Filtres actifs
let activePlatform = ''; // ex: "pc"
let activeGenre    = ''; // ex: "role-playing"
let activeEditor   = ''; // ex: "nintendo"

// ----------- FONCTION UTILITAIRE POUR CRÉER UNE CARTE JEU ----------
function createGameCard(game) {
    const li = document.createElement('li');
    li.className = 'game-card';

    const link = document.createElement('a');
    link.href = 'game.php?id=' + game.id;
    link.className = 'game-link';

    // Image de couverture
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

    // Titre
    const title = document.createElement('h2');
    title.className = 'game-title';
    title.textContent = game.name || 'Sans nom';

    // Infos simples (note + date)
    const info = document.createElement('p');
    info.className = 'game-info';

    let parts = [];

    if (typeof game.rating === 'number') {
        parts.push('Note : ' + game.rating.toFixed(0) + '/100');
    }

    if (typeof game.first_release_date === 'number') {
        const date = new Date(game.first_release_date * 1000);
        parts.push('Sortie : ' + date.toLocaleDateString('fr-FR'));
    }

    info.textContent = parts.join(' · ');

    // On assemble la carte
    link.appendChild(img);
    link.appendChild(title);
    link.appendChild(info);

    li.appendChild(link);

    return li;
}

// ------------- AFFICHER UNE LISTE DE JEUX (après tri) ----------------
function showGames(games) {
    gameList.innerHTML = '';
    errorBox.textContent = '';

    // Tri alphabétique A → Z
    games.sort(function (a, b) {
        const na = (a.name || '').toLowerCase();
        const nb = (b.name || '').toLowerCase();
        return na.localeCompare(nb, 'fr');
    });

    if (!Array.isArray(games) || games.length === 0) {
        if (gamesCount) gamesCount.textContent = '0 jeu trouvé';
        errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
        return;
    }

    if (gamesCount) {
        gamesCount.textContent =
            games.length + (games.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
    }

    games.forEach(function (game) {
        const card = createGameCard(game);
        gameList.appendChild(card);
    });
}

// ------------- APPLIQUER LES FILTRES SUR allGames ----------------
function applyFilters() {
    let filtered = allGames.slice();

    // On bosse en minuscule pour comparer
    filtered = filtered.filter(function (game) {
        let ok = true;

        // Plateformes
        if (activePlatform !== '') {
            const key = activePlatform.toLowerCase();
            let plats = [];
            if (Array.isArray(game.platforms)) {
                plats = game.platforms
                    .filter(p => p && p.name)
                    .map(p => p.name.toLowerCase());
            }
            if (!plats.some(p => p.includes(key))) {
                ok = false;
            }
        }

        // Genres
        if (ok && activeGenre !== '') {
            const key = activeGenre.toLowerCase();
            let gens = [];
            if (Array.isArray(game.genres)) {
                gens = game.genres
                    .filter(g => g && g.name)
                    .map(g => g.name.toLowerCase());
            }
            if (!gens.some(g => g.includes(key))) {
                ok = false;
            }
        }

        // Éditeurs
        if (ok && activeEditor !== '') {
            const key = activeEditor.toLowerCase();
            let editors = [];
            if (Array.isArray(game.involved_companies)) {
                editors = game.involved_companies
                    .filter(ic => ic && ic.company && ic.company.name)
                    .map(ic => ic.company.name.toLowerCase());
            }
            if (!editors.some(e => e.includes(key))) {
                ok = false;
            }
        }

        return ok;
    });

    showGames(filtered);
}

// ------------- CHARGER LES JEUX POPULAIRES ----------------
function loadPopularGames() {
    const formData = new FormData();
    formData.append('mode', 'popular');

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function (data) {
        allGames = Array.isArray(data) ? data : [];
        applyFilters(); // applique filtres (au début = aucun)
    })
    .catch(function (error) {
        errorBox.textContent = 'Erreur de chargement des jeux.';
        console.error(error);
    });
}

// ------------- RECHERCHER DES JEUX ----------------
function searchGames() {
    const query = searchInput.value.trim();

    // Si la barre est vide → on recharge la liste populaire
    if (query === '') {
        loadPopularGames();
        return;
    }

    const formData = new FormData();
    formData.append('mode', 'search');
    formData.append('search', query);

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function (data) {
        allGames = Array.isArray(data) ? data : [];
        applyFilters();
    })
    .catch(function (error) {
        errorBox.textContent = 'Erreur lors de la recherche.';
        console.error(error);
    });
}

// ------------- GESTION DES FILTRES (chips) ----------------
function initFilters() {
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const type  = chip.getAttribute('data-type');
            const value = chip.getAttribute('data-value') || '';

            // Enlever chip--active sur les autres du même type
            chips.forEach(function (c) {
                if (c.getAttribute('data-type') === type) {
                    c.classList.remove('chip--active');
                }
            });

            // Activer celui qu'on vient de cliquer
            chip.classList.add('chip--active');

            if (type === 'platform') {
                activePlatform = value;
            } else if (type === 'genre') {
                activeGenre = value;
            } else if (type === 'editor') {
                activeEditor = value;
            }

            applyFilters();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            activePlatform = '';
            activeGenre    = '';
            activeEditor   = '';

            // Remettre tous les "Tous" actifs
            chips.forEach(function (c) {
                const type  = c.getAttribute('data-type');
                const value = c.getAttribute('data-value') || '';
                if (value === '') {
                    c.classList.add('chip--active');
                } else {
                    c.classList.remove('chip--active');
                }
            });

            applyFilters();
        });
    }
}

// ------------- INITIALISATION -------------
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

    initFilters();

    // On charge les jeux populaires au début
    loadPopularGames();
}

// Quand la page est prête on lance tout
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearch);
} else {
    initSearch();
}
