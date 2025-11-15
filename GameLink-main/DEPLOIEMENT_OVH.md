# 🚀 GUIDE DE DÉPLOIEMENT - GAMELINK SUR VPS OVH

## 📋 Prérequis sur votre VPS OVH
Vous avez déjà :
- ✅ MariaDB installé
- ✅ PHP installé
- ✅ Apache installé

## 🗂️ ÉTAPE 1 : Transférer les fichiers sur le serveur

### Option A : Via FTP (FileZilla recommandé)
1. Téléchargez FileZilla : https://filezilla-project.org/
2. Connectez-vous à votre VPS :
   - Hôte : `votre-ip-ovh`
   - Nom d'utilisateur : `root` (ou votre user)
   - Mot de passe : votre mot de passe VPS
   - Port : 22 (SFTP)

3. Transférez tous les fichiers du dossier `GameLink-main/` vers `/var/www/html/gamelink/`

### Option B : Via SSH et Git
```bash
# Connexion SSH
ssh root@votre-ip-ovh

# Aller dans le répertoire web
cd /var/www/html/

# Cloner ou télécharger votre projet
# Si vous avez un repo Git :
git clone votre-repo-url gamelink

# Sinon, utilisez FTP/SCP pour uploader les fichiers
```

## 🗄️ ÉTAPE 2 : Configuration de la base de données

### 1. Connexion à MariaDB
```bash
mysql -u root -p
```

### 2. Créer la base de données
```sql
CREATE DATABASE gamelink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Créer un utilisateur dédié (RECOMMANDÉ pour la sécurité)
```sql
CREATE USER 'gamelink_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL PRIVILEGES ON gamelink.* TO 'gamelink_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Importer le schéma de base de données
```bash
mysql -u root -p gamelink < /var/www/html/gamelink/DATA/gamelink_schema_mariadb.sql
```

### 5. Vérifier que les tables sont créées
```bash
mysql -u root -p gamelink
```
```sql
SHOW TABLES;
-- Vous devriez voir : joueur, jeu, editeur, playlist, avis, etc.
EXIT;
```

## ⚙️ ÉTAPE 3 : Configuration de l'application

### 1. Modifier le fichier de configuration
```bash
nano /var/www/html/gamelink/DATA/DBConfig.php
```

Modifiez les informations de connexion :
```php
<?php
$host = "localhost";
$dbname = "gamelink";
$user = "gamelink_user";  // ← Modifier ici
$pass = "VotreMotDePasseSecurise";  // ← Modifier ici
```

Enregistrez : `Ctrl+X`, puis `Y`, puis `Entrée`

### 2. Vérifier les permissions des fichiers
```bash
# Donner les bonnes permissions
chown -R www-data:www-data /var/www/html/gamelink/
chmod -R 755 /var/www/html/gamelink/

# Le fichier captcha_bank.json doit être en écriture
chmod 666 /var/www/html/gamelink/DATA/captcha_bank.json
```

## 🌐 ÉTAPE 4 : Configuration Apache

### 1. Créer un VirtualHost
```bash
nano /etc/apache2/sites-available/gamelink.conf
```

Collez cette configuration :
```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    ServerAlias www.votre-domaine.com
    
    DocumentRoot /var/www/html/gamelink
    
    <Directory /var/www/html/gamelink>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/gamelink_error.log
    CustomLog ${APACHE_LOG_DIR}/gamelink_access.log combined
</VirtualHost>
```

### 2. Activer le site et redémarrer Apache
```bash
a2ensite gamelink.conf
a2enmod rewrite
systemctl restart apache2
```

### 3. Si vous n'avez pas de nom de domaine (accès par IP)
Modifiez directement le fichier de config par défaut :
```bash
nano /etc/apache2/sites-available/000-default.conf
```

Changez `DocumentRoot` :
```apache
DocumentRoot /var/www/html/gamelink
```

Redémarrez Apache :
```bash
systemctl restart apache2
```

## 🔒 ÉTAPE 5 : Sécurisation (IMPORTANT)

### 1. Installer un certificat SSL (HTTPS)
```bash
apt install certbot python3-certbot-apache
certbot --apache -d votre-domaine.com -d www.votre-domaine.com
```

### 2. Configurer le pare-feu
```bash
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
```

### 3. Sécuriser PHP
```bash
nano /etc/php/8.x/apache2/php.ini
```

Modifiez ces paramètres :
```ini
display_errors = Off
log_errors = On
expose_php = Off
```

Redémarrez Apache :
```bash
systemctl restart apache2
```

## ✅ ÉTAPE 6 : Test de l'installation

### 1. Accéder au site
Ouvrez votre navigateur et allez sur :
- Avec domaine : `http://votre-domaine.com`
- Sans domaine : `http://votre-ip-ovh`

### 2. Tester l'inscription
1. Cliquez sur "Créer un compte"
2. Remplissez le formulaire
3. Validez le captcha
4. Vous devriez être redirigé vers votre espace personnel

### 3. Tester la connexion
1. Déconnectez-vous
2. Reconnectez-vous avec vos identifiants
3. Validez le captcha
4. Accédez à votre espace

## 🛠️ ÉTAPE 7 : Gestion du captcha

### Ajouter/Modifier des questions
1. Connectez-vous sur le site
2. Allez sur : `http://votre-domaine.com/PAGE/manage_captcha.php`
3. Ajoutez, activez/désactivez ou supprimez des questions

## 📁 Structure des fichiers sur le serveur

```
/var/www/html/gamelink/
├── index.php                 # Page d'accueil publique
├── CSS/                      # Styles
├── JS/                       # Scripts JavaScript
├── ICON/                     # Images et logos
├── FONTS/                    # Polices
├── DATA/
│   ├── DBConfig.php         # Configuration BDD
│   ├── gamelink_schema_mariadb.sql  # Schéma BDD
│   └── captcha_bank.json    # Questions captcha
├── INCLUDES/
│   ├── auth_login.php       # Traitement connexion
│   ├── auth_register.php    # Traitement inscription
│   └── logout.php           # Déconnexion
├── PAGE/
│   ├── AUTH.php             # Page connexion/inscription
│   ├── ACCUEIL.php          # Espace utilisateur
│   ├── captcha.php          # Validation captcha
│   ├── manage_captcha.php   # Gestion questions captcha
│   ├── RECHERCHE.php        # Recherche de jeux
│   ├── COMMUNAUTE.php       # Communauté
│   └── ADMIN.php            # Administration
└── API/                      # API externes (IGDB, etc.)
```

## 🔧 Dépannage

### Erreur "Connection refused" à la BDD
```bash
# Vérifier que MariaDB fonctionne
systemctl status mariadb

# Redémarrer si nécessaire
systemctl restart mariadb
```

### Erreur 500 Internal Server Error
```bash
# Vérifier les logs Apache
tail -f /var/log/apache2/gamelink_error.log

# Vérifier les permissions
ls -la /var/www/html/gamelink/
```

### Les images/CSS ne chargent pas
Vérifiez que les chemins sont corrects et que les permissions sont bonnes :
```bash
chmod -R 755 /var/www/html/gamelink/
```

### Le captcha ne fonctionne pas
```bash
# Vérifier que le fichier JSON existe et est accessible en écriture
ls -l /var/www/html/gamelink/DATA/captcha_bank.json
chmod 666 /var/www/html/gamelink/DATA/captcha_bank.json
```

## 📞 Support

En cas de problème :
1. Vérifiez les logs : `/var/log/apache2/gamelink_error.log`
2. Vérifiez les logs PHP : `/var/log/php8.x-fpm.log`
3. Vérifiez les permissions des fichiers
4. Vérifiez que tous les services sont actifs

## 🎉 Félicitations !

Votre site GameLink est maintenant déployé et fonctionnel sur votre VPS OVH !

---

## 📝 Notes importantes

- **Sécurité** : Changez TOUS les mots de passe par défaut
- **Sauvegarde** : Pensez à sauvegarder régulièrement la base de données
- **Mises à jour** : Gardez PHP, Apache et MariaDB à jour
- **Monitoring** : Surveillez les logs régulièrement

## 🔄 Commandes utiles

```bash
# Redémarrer Apache
systemctl restart apache2

# Redémarrer MariaDB
systemctl restart mariadb

# Voir les logs en temps réel
tail -f /var/log/apache2/gamelink_error.log

# Backup de la base de données
mysqldump -u root -p gamelink > backup_$(date +%Y%m%d).sql

# Restaurer une sauvegarde
mysql -u root -p gamelink < backup_20241110.sql
```
