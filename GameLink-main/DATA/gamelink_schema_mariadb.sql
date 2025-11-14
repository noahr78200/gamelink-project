-- ========================
-- SCHEMA GAMELINK - MariaDB/MySQL
-- ========================

-- Joueurs (utilisateurs)
CREATE TABLE joueur (
    id_joueur       INT AUTO_INCREMENT PRIMARY KEY,
    pseudo          VARCHAR(50) NOT NULL UNIQUE,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    avatar_url      VARCHAR(255),
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bio             TEXT,
    pays            VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Éditeurs de jeux
CREATE TABLE editeur (
    id_editeur      INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL UNIQUE,
    site_url        VARCHAR(255),
    pays            VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- JEUX
-- ========================
CREATE TABLE jeu (
    id_jeu          INT AUTO_INCREMENT PRIMARY KEY,
    id_editeur      INT NOT NULL,
    titre           VARCHAR(200) NOT NULL,
    date_sortie     DATE,
    pegi            VARCHAR(10),
    description     TEXT,
    cover_url       VARCHAR(255),
    UNIQUE KEY unique_titre_editeur (titre, id_editeur),
    FOREIGN KEY (id_editeur) REFERENCES editeur(id_editeur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE genre (
    id_genre        INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plateforme (
    id_plateforme   INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(120) NOT NULL UNIQUE,
    fabriquant      VARCHAR(120)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- RELATIONS JEUX
-- ========================
CREATE TABLE jeu_genre (
    id_jeu      INT NOT NULL,
    id_genre    INT NOT NULL,
    PRIMARY KEY (id_jeu, id_genre),
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE CASCADE,
    FOREIGN KEY (id_genre) REFERENCES genre(id_genre) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jeu_plateforme (
    id_jeu        INT NOT NULL,
    id_plateforme INT NOT NULL,
    edition       VARCHAR(120),
    prix          DECIMAL(10,2),
    PRIMARY KEY (id_jeu, id_plateforme),
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE CASCADE,
    FOREIGN KEY (id_plateforme) REFERENCES plateforme(id_plateforme) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- PLAYLIST
-- ========================
CREATE TABLE playlist (
    id_playlist    INT AUTO_INCREMENT PRIMARY KEY,
    id_joueur      INT NOT NULL,
    titre          VARCHAR(150) NOT NULL,
    description    TEXT,
    visibilite     ENUM('public','prive','non_liste') DEFAULT 'public',
    date_creation  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE playlist_jeu (
    id_playlist    INT NOT NULL,
    id_jeu         INT NOT NULL,
    rang           INT NOT NULL,
    PRIMARY KEY (id_playlist, id_jeu),
    UNIQUE KEY unique_playlist_rang (id_playlist, rang),
    FOREIGN KEY (id_playlist) REFERENCES playlist(id_playlist) ON DELETE CASCADE,
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- AVIS & STATS
-- ========================
CREATE TABLE avis (
    id_joueur       INT NOT NULL,
    id_jeu          INT NOT NULL,
    valeur          TINYINT CHECK (valeur BETWEEN 1 AND 5),
    texte_commentaire TEXT,
    date_notation   TIMESTAMP NULL,
    date_commentaire TIMESTAMP NULL,
    PRIMARY KEY (id_joueur, id_jeu),
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE statistique_jeu (
    id_joueur   INT NOT NULL,
    id_jeu      INT NOT NULL,
    heures_jouees INT DEFAULT 0,
    progression   TINYINT CHECK (progression BETWEEN 0 AND 100),
    dernier_launch TIMESTAMP NULL,
    PRIMARY KEY (id_joueur, id_jeu),
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- COMMUNAUTES
-- ========================
CREATE TABLE communaute (
    id_communaute   INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL UNIQUE,
    description     TEXT,
    visibilite      ENUM('public','prive') DEFAULT 'public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE adhesion (
    id_joueur       INT NOT NULL,
    id_communaute   INT NOT NULL,
    role            ENUM('membre','modo','admin') DEFAULT 'membre',
    statut          ENUM('actif','banni','en_attente') DEFAULT 'actif',
    date_entree     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_joueur, id_communaute),
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_communaute) REFERENCES communaute(id_communaute) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE publication (
    id_publication  INT AUTO_INCREMENT PRIMARY KEY,
    id_joueur       INT NOT NULL,
    id_communaute   INT NOT NULL,
    titre           VARCHAR(180),
    contenu         TEXT,
    media_url       VARCHAR(255),
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_communaute) REFERENCES communaute(id_communaute) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE commentaire (
    id_commentaire  INT AUTO_INCREMENT PRIMARY KEY,
    id_publication  INT NOT NULL,
    id_joueur       INT NOT NULL,
    contenu         TEXT NOT NULL,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_publication) REFERENCES publication(id_publication) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- MESSAGERIE
-- ========================
CREATE TABLE conversation (
    id_conversation INT AUTO_INCREMENT PRIMARY KEY,
    sujet           VARCHAR(200),
    type            ENUM('prive','groupe') DEFAULT 'prive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversation_participant (
    id_conversation INT NOT NULL,
    id_joueur       INT NOT NULL,
    date_join       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_muted        BOOLEAN DEFAULT FALSE,
    is_admin        BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id_conversation, id_joueur),
    FOREIGN KEY (id_conversation) REFERENCES conversation(id_conversation) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message (
    id_message      INT AUTO_INCREMENT PRIMARY KEY,
    id_conversation INT NOT NULL,
    id_joueur       INT NOT NULL,
    contenu         TEXT NOT NULL,
    date_envoi      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_conversation) REFERENCES conversation(id_conversation) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_lu (
    id_message      INT NOT NULL,
    id_joueur       INT NOT NULL,
    date_lecture    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_message, id_joueur),
    FOREIGN KEY (id_message) REFERENCES message(id_message) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- AMITIES
-- ========================
CREATE TABLE amitie (
    id_amitie    INT AUTO_INCREMENT PRIMARY KEY,
    id_demandeur INT NOT NULL,
    id_receveur  INT NOT NULL,
    statut       ENUM('pending','accepted','blocked') DEFAULT 'pending',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (id_demandeur <> id_receveur),
    FOREIGN KEY (id_demandeur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_receveur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- EVENEMENTS
-- ========================
CREATE TABLE evenement (
    id_evenement    INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(200) NOT NULL,
    description     TEXT,
    date_debut      TIMESTAMP NOT NULL,
    date_fin        TIMESTAMP NULL,
    lieu            VARCHAR(200),
    type            VARCHAR(60),
    id_communaute   INT,
    id_jeu          INT,
    FOREIGN KEY (id_communaute) REFERENCES communaute(id_communaute) ON DELETE SET NULL,
    FOREIGN KEY (id_jeu) REFERENCES jeu(id_jeu) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE evenement_participant (
    id_evenement    INT NOT NULL,
    id_joueur       INT NOT NULL,
    statut          ENUM('inscrit','interesse','present') DEFAULT 'inscrit',
    PRIMARY KEY (id_evenement, id_joueur),
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE,
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================
-- BADGES
-- ========================
CREATE TABLE badge (
    id_badge        INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(120) NOT NULL UNIQUE,
    description     TEXT,
    icone_url       VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE joueur_badge (
    id_joueur   INT NOT NULL,
    id_badge    INT NOT NULL,
    date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_joueur, id_badge),
    FOREIGN KEY (id_joueur) REFERENCES joueur(id_joueur) ON DELETE CASCADE,
    FOREIGN KEY (id_badge) REFERENCES badge(id_badge) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ==========================================
-- BOÎTE 1 : Qui est connecté ?
-- ==========================================
-- Cette boîte note quand quelqu'un visite ton site

CREATE TABLE IF NOT EXISTS user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    page_url VARCHAR(255),
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- BOÎTE 2 : Quelle page est visitée ?
-- ==========================================
-- Cette boîte note chaque fois que quelqu'un regarde une page

CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    page_url VARCHAR(255) NOT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_viewed_at (viewed_at),
    INDEX idx_page_url (page_url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- BOÎTE 3 : Résumé de chaque jour
-- ==========================================
-- Cette boîte fait le résumé de la journée

CREATE TABLE IF NOT EXISTS daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL UNIQUE,
    dau INT DEFAULT 0,
    new_users INT DEFAULT 0,
    total_page_views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_stat_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- C'EST FINI ! 🎉
-- ==========================================
-- Maintenant tu as 3 boîtes pour ranger les informations