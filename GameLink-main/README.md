# 🎮 GameLink - Plateforme de Gestion de Jeux Vidéo

Projet annuel de première année - École d'informatique

## 📝 Description

GameLink est une plateforme web permettant aux joueurs de :
- 📚 Découvrir et référencer des jeux vidéo
- ⭐ Noter et commenter leurs jeux
- ❤️ Mettre des jeux en favoris
- 📋 Créer des playlists personnalisées
- 🎯 Suivre leur progression (à jouer, en cours, terminé, en pause)
- 💬 Échanger sur des forums
- 👥 Créer et rejoindre des communautés
- ✉️ Envoyer des messages privés
- 🎪 Participer à des événements gaming

## 🛠️ Technologies utilisées

- **Frontend** : HTML5, CSS3, JavaScript
- **Backend** : PHP 8.x
- **Base de données** : MariaDB/MySQL
- **Serveur web** : Apache
- **Hébergement** : VPS OVH

## 📁 Structure du projet

```
GameLink/
├── index.php              # Page d'accueil publique
├── CSS/                   # Feuilles de style
│   ├── HEADER.css
│   ├── AUTH.css
│   ├── STYLE_ACCUEIL.css
│   └── ...
├── JS/                    # Scripts JavaScript
│   ├── AUTH.js
│   ├── RECHERCHE.js
│   └── ...
├── DATA/                  # Configuration et données
│   ├── DBConfig.php      # Configuration BDD
│   ├── gamelink_schema_mariadb.sql  # Schéma complet
│   └── captcha_bank.json # Questions captcha
├── INCLUDES/             # Scripts PHP de traitement
│   ├── auth_login.php
│   ├── auth_register.php
│   └── logout.php
├── PAGE/                 # Pages principales
│   ├── AUTH.php         # Connexion/Inscription
│   ├── ACCUEIL.php      # Espace utilisateur
│   ├── captcha.php      # Validation captcha
│   ├── RECHERCHE.php    # Recherche de jeux
│   ├── COMMUNAUTE.php   # Forums et groupes
│   ├── ADMIN.php        # Administration
│   └── manage_captcha.php
├── API/                  # Intégrations API
│   ├── igdb.php         # API IGDB
│   └── functionTemp.php
├── ICON/                 # Images et logos
└── FONTS/                # Polices personnalisées
```

## 🗄️ Schéma de base de données

### Tables principales

- **joueur** : Utilisateurs de la plateforme
- **jeu** : Catalogue de jeux vidéo
- **editeur** : Éditeurs de jeux
- **genre** / **plateforme** : Classifications
- **playlist** : Listes de jeux personnalisées
- **avis** : Notes et commentaires
- **statistique_jeu** : Suivi de progression
- **communaute** : Groupes de joueurs
- **conversation** / **message** : Messagerie privée
- **evenement** : Événements gaming
- **badge** : Système de récompenses

Voir le fichier `DATA/gamelink_schema_mariadb.sql` pour le schéma complet.

## 🚀 Installation locale

### Prérequis
- PHP 8.0 ou supérieur
- MariaDB 10.5 ou supérieur
- Apache 2.4 ou supérieur

### Étapes

1. **Cloner le projet**
```bash
git clone [votre-repo]
cd GameLink
```

2. **Configurer la base de données**
```bash
mysql -u root -p
```
```sql
CREATE DATABASE gamelink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

3. **Importer le schéma**
```bash
mysql -u root -p gamelink < DATA/gamelink_schema_mariadb.sql
```

4. **Configurer la connexion**

Modifiez `DATA/DBConfig.php` :
```php
$host = "localhost";
$dbname = "gamelink";
$user = "votre_user";
$pass = "votre_password";
```

5. **Lancer le serveur** (développement)
```bash
php -S localhost:8000
```

Accédez à `http://localhost:8000`

## 🌐 Déploiement sur VPS OVH

Consultez le fichier `DEPLOIEMENT_OVH.md` pour un guide complet de déploiement.

## 🔒 Système de sécurité

### Authentification
- Hashage des mots de passe avec `password_hash()` (BCRYPT)
- Protection CSRF sur tous les formulaires
- Sessions PHP sécurisées
- Validation des données côté serveur

### Captcha
- Système de captcha personnalisé avec questions/réponses
- Banque de questions modifiable via interface admin
- Normalisation des réponses (insensible à la casse et accents)

### Protection des pages
- Middleware de vérification de connexion
- Redirection automatique vers la page de connexion
- Gestion des sessions utilisateur

## 👥 Fonctionnalités par rôle

### Utilisateur
- Créer un compte et se connecter
- Rechercher des jeux
- Noter et commenter
- Créer des playlists
- Participer aux forums et communautés
- Envoyer des messages

### Admin (à venir)
- Gérer les utilisateurs
- Modérer les contenus
- Gérer les questions du captcha
- Consulter les statistiques
- Gérer les jeux et éditeurs

## 📊 Fonctionnalités implémentées

### ✅ Phase 1 (Actuelle)
- [x] Système d'authentification complet
- [x] Inscription avec validation
- [x] Connexion sécurisée
- [x] Captcha personnalisé
- [x] Page d'accueil publique
- [x] Espace utilisateur personnel
- [x] Déconnexion
- [x] Schéma de base de données complet

### 🔄 Phase 2 (En cours)
- [ ] Intégration API IGDB pour les jeux
- [ ] Recherche de jeux
- [ ] Ajout de jeux en favoris
- [ ] Système de notation
- [ ] Gestion des playlists

### 📅 Phase 3 (À venir)
- [ ] Forums et communautés
- [ ] Messagerie privée
- [ ] Système d'amis
- [ ] Événements gaming
- [ ] Backoffice admin complet

## 🤝 Équipe

Projet de groupe - Première année école d'informatique

## 📄 Licence

Projet académique - Tous droits réservés

## 🐛 Bugs connus

Aucun bug critique connu pour le moment.

## 📞 Support

Pour toute question ou problème :
1. Consultez le fichier `DEPLOIEMENT_OVH.md`
2. Vérifiez les logs Apache : `/var/log/apache2/gamelink_error.log`
3. Contactez votre professeur/référent

## 🎯 Roadmap

- [x] Authentification et sécurité
- [ ] Catalogue de jeux avec API
- [ ] Système social (forums, groupes)
- [ ] Messagerie en temps réel
- [ ] Application mobile (future)

---

**Dernière mise à jour** : Novembre 2024
