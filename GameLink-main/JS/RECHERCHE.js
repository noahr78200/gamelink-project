const API_URL = '../API/igdb.php';

const gameList = document.getElementById('Game-list');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-button');
const showAllBtn = document.getElementById('show-all-button');
const errorBox = document.getElementById('error-message');
const gamesCount = document.getElementById('games-count');
const loadingText = document.getElementById('loading-text');
const resetBtn = document.getElementById('filters-reset');
const chips = document.querySelectorAll('.chip');

let allGames = [];


let activePlatform = '';
let activeGenre = '';
let activeEditor = '';
let activeSort = 'name-asc';

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
        img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' + game.cover.image_id + '.jpg';
    } else {
        img.src = 'https://placehold.co/264x352?text=' + encodeURIComponent(game.name || 'Jeu');
    }

    const title = document.createElement('h2');
    title.className = 'game-title';
    title.textContent = game.name || 'Sans nom';

    const info = document.createElement('p');
    info.className = 'game-info';

    let infoParts = [];

    if (typeof game.rating === 'number') {
        infoParts.push('Note : ' + game.rating.toFixed(0) + '/100');
    }

    if (typeof game.first_release_date === 'number') {
        const date = new Date(game.first_release_date * 1000);
        infoParts.push('Sortie : ' + date.toLocaleDateString('fr-FR'));
    }

    info.textContent = infoParts.join(' · ');

    link.appendChild(img);
    link.appendChild(title);
    link.appendChild(info);
    li.appendChild(link);

    return li;
}

function sortGames(games) {
    let sorted = games.slice();

    if (activeSort === 'name-asc') {
        sorted.sort(function(a, b) {
            const nameA = (a.name || '').toLowerCase();
            const nameB = (b.name || '').toLowerCase();
            return nameA.localeCompare(nameB, 'fr');
        });
    } else if (activeSort === 'name-desc') {
        sorted.sort(function(a, b) {
            const nameA = (a.name || '').toLowerCase();
            const nameB = (b.name || '').toLowerCase();
            return nameB.localeCompare(nameA, 'fr');
        });
    } else if (activeSort === 'date-desc') {
        sorted.sort(function(a, b) {
            const dateA = a.first_release_date || 0;
            const dateB = b.first_release_date || 0;
            return dateB - dateA;
        });
    } else if (activeSort === 'date-asc') {
        sorted.sort(function(a, b) {
            const dateA = a.first_release_date || 0;
            const dateB = b.first_release_date || 0;
            return dateA - dateB;
        });
    } else if (activeSort === 'rating-desc') {
        sorted.sort(function(a, b) {
            const ratingA = a.rating || 0;
            const ratingB = b.rating || 0;
            return ratingB - ratingA;
        });
    }

    return sorted;
}

function showGames(games) {
    gameList.innerHTML = '';
    errorBox.textContent = '';

    if (loadingText) {
        loadingText.style.display = 'none';
    }

    if (!Array.isArray(games) || games.length === 0) {
        if (gamesCount) {
            gamesCount.textContent = '0 jeu trouvé';
        }
        errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
        return;
    }

    const sortedGames = sortGames(games);

    if (gamesCount) {
        gamesCount.textContent = sortedGames.length + (sortedGames.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
    }

    sortedGames.forEach(function(game) {
        const card = createGameCard(game);
        gameList.appendChild(card);
    });
}

function applyFilters() {
    let filtered = allGames.slice();

    if (activePlatform !== '') {
        filtered = filtered.filter(function(game) {
            if (!Array.isArray(game.platforms)) {
                return false;
            }

            const key = activePlatform.toLowerCase();
            return game.platforms.some(function(platform) {
                if (!platform || !platform.name) {
                    return false;
                }
                const platformName = platform.name.toLowerCase();
                return platformName.includes(key);
            });
        });
    }

    if (activeGenre !== '') {
        filtered = filtered.filter(function(game) {
            if (!Array.isArray(game.genres)) {
                return false;
            }

            const key = activeGenre.toLowerCase();
            return game.genres.some(function(genre) {
                if (!genre || !genre.name) {
                    return false;
                }
                const genreName = genre.name.toLowerCase();
                return genreName.includes(key);
            });
        });
    }

    if (activeEditor !== '') {
        filtered = filtered.filter(function(game) {
            if (!Array.isArray(game.involved_companies)) {
                return false;
            }

            const key = activeEditor.toLowerCase();
            return game.involved_companies.some(function(company) {
                if (!company || !company.company || !company.company.name) {
                    return false;
                }
                const companyName = company.company.name.toLowerCase();
                return companyName.includes(key);
            });
        });
    }

    showGames(filtered);
}

function loadAllGames() {
    if (loadingText) {
        loadingText.style.display = 'block';
    }
    errorBox.textContent = '';

    const formData = new FormData();
    formData.append('mode', 'all');

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        allGames = Array.isArray(data) ? data : [];
        applyFilters();
    })
    .catch(function(error) {
        if (loadingText) {
            loadingText.style.display = 'none';
        }
        errorBox.textContent = 'Erreur de chargement des jeux.';
        console.error(error);
    });
}

function searchGames() {
    const query = searchInput.value.trim();

    if (query === '') {
        errorBox.textContent = 'Tape un nom de jeu pour rechercher.';
        return;
    }

    if (loadingText) {
        loadingText.style.display = 'block';
    }
    errorBox.textContent = '';

    const formData = new FormData();
    formData.append('mode', 'search');
    formData.append('search', query);

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        allGames = Array.isArray(data) ? data : [];
        applyFilters();
    })
    .catch(function(error) {
        if (loadingText) {
            loadingText.style.display = 'none';
        }
        errorBox.textContent = 'Erreur lors de la recherche.';
        console.error(error);
    });
}

function initFilters() {
    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            const type = chip.getAttribute('data-type');
            const value = chip.getAttribute('data-value') || '';

            chips.forEach(function(c) {
                if (c.getAttribute('data-type') === type) {
                    c.classList.remove('chip--active');
                }
            });

            chip.classList.add('chip--active');

            if (type === 'platform') {
                activePlatform = value;
            } else if (type === 'genre') {
                activeGenre = value;
            } else if (type === 'editor') {
                activeEditor = value;
            } else if (type === 'sort') {
                activeSort = value;
            }

            applyFilters();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            activePlatform = '';
            activeGenre = '';
            activeEditor = '';
            activeSort = 'name-asc';

            chips.forEach(function(c) {
                const type = c.getAttribute('data-type');
                const value = c.getAttribute('data-value') || '';

                if (value === '' || (type === 'sort' && value === 'name-asc')) {
                    c.classList.add('chip--active');
                } else {
                    c.classList.remove('chip--active');
                }
            });

            if (searchInput) {
                searchInput.value = '';
            }

            applyFilters();
        });
    }
}

function initSearch() {
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchGames();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchGames();
            }
        });
    }

    if (showAllBtn) {
        showAllBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
            }
            loadAllGames();
        });
    }

    initFilters();

    if (loadingText) {
        loadingText.style.display = 'none';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearch);
} else {
    initSearch();
}