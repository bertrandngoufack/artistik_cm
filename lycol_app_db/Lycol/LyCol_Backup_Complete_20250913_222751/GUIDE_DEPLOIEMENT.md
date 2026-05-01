# GUIDE DE DÉPLOIEMENT - LYCCOL
## Système de Gestion Scolaire Intégré

**Version :** 1.0.0  
**Date :** 13 Septembre 2025  
**Framework :** CodeIgniter 4.6.3  

---

## 📋 PRÉREQUIS SYSTÈME

### Serveur minimum
- **OS :** Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **RAM :** 4GB minimum (8GB recommandé)
- **CPU :** 2 cœurs minimum (4 cœurs recommandé)
- **Stockage :** 20GB minimum (50GB recommandé)
- **Réseau :** Connexion stable avec accès internet

### Logiciels requis
- **PHP :** 8.1+ (8.4.5 recommandé)
- **MariaDB :** 10.3+ (10.6+ recommandé)
- **Apache/Nginx :** Version récente
- **Composer :** 2.0+
- **Git :** Pour le déploiement

### Extensions PHP requises
```bash
php-mysql
php-pdo
php-mbstring
php-intl
php-curl
php-zip
php-gd
php-xml
php-json
php-session
php-tokenizer
```

---

## 🚀 ÉTAPES DE DÉPLOIEMENT

### 1. PRÉPARATION DU SERVEUR

#### Installation des dépendances (Ubuntu/Debian)
```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation de PHP 8.4
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-intl php8.4-curl php8.4-zip php8.4-gd php8.4-xml php8.4-json -y

# Installation de MariaDB
sudo apt install mariadb-server mariadb-client -y

# Installation d'Apache
sudo apt install apache2 -y

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Git
sudo apt install git -y
```

#### Installation des dépendances (CentOS/RHEL)
```bash
# Mise à jour du système
sudo yum update -y

# Installation d'EPEL
sudo yum install epel-release -y

# Installation de PHP 8.4
sudo yum install https://rpms.remirepo.net/enterprise/remi-release-8.rpm -y
sudo yum module enable php:remi-8.4 -y
sudo yum install php php-cli php-fpm php-mysqlnd php-mbstring php-intl php-curl php-zip php-gd php-xml php-json -y

# Installation de MariaDB
sudo yum install mariadb-server mariadb -y

# Installation d'Apache
sudo yum install httpd -y

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Git
sudo yum install git -y
```

### 2. CONFIGURATION DE LA BASE DE DONNÉES

#### Démarrage de MariaDB
```bash
# Ubuntu/Debian
sudo systemctl start mariadb
sudo systemctl enable mariadb

# CentOS/RHEL
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

#### Sécurisation de MariaDB
```bash
sudo mysql_secure_installation
```

#### Création de la base de données
```bash
# Connexion à MariaDB
mysql -u root -p

# Création de la base de données
CREATE DATABASE lycol_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Création d'un utilisateur dédié
CREATE USER 'lycol_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
GRANT ALL PRIVILEGES ON lycol_db.* TO 'lycol_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Importation de la sauvegarde
```bash
# Importation de la base de données
mysql -u root -p lycol_db < backup_all_databases_20250913_222731.sql
```

### 3. INSTALLATION DE L'APPLICATION

#### Création du répertoire web
```bash
# Création du répertoire
sudo mkdir -p /var/www/lycol
sudo chown -R www-data:www-data /var/www/lycol
sudo chmod -R 755 /var/www/lycol
```

#### Copie des fichiers
```bash
# Copie de tous les fichiers du projet
sudo cp -r * /var/www/lycol/
```

#### Installation des dépendances Composer
```bash
cd /var/www/lycol
sudo composer install --no-dev --optimize-autoloader
```

#### Configuration des permissions
```bash
# Permissions pour le dossier writable
sudo chmod -R 775 /var/www/lycol/writable
sudo chown -R www-data:www-data /var/www/lycol/writable

# Permissions pour le dossier public
sudo chmod -R 755 /var/www/lycol/public
sudo chown -R www-data:www-data /var/www/lycol/public
```

### 4. CONFIGURATION D'APACHE

#### Création du Virtual Host
```bash
sudo nano /etc/apache2/sites-available/lycol.conf
```

#### Contenu du fichier de configuration
```apache
<VirtualHost *:80>
    ServerName lycol.local
    ServerAlias www.lycol.local
    DocumentRoot /var/www/lycol/public
    
    <Directory /var/www/lycol/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/lycol_error.log
    CustomLog ${APACHE_LOG_DIR}/lycol_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName lycol.local
    ServerAlias www.lycol.local
    DocumentRoot /var/www/lycol/public
    
    <Directory /var/www/lycol/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Configuration SSL (à configurer selon votre certificat)
    # SSLEngine on
    # SSLCertificateFile /path/to/certificate.crt
    # SSLCertificateKeyFile /path/to/private.key
    
    ErrorLog ${APACHE_LOG_DIR}/lycol_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/lycol_ssl_access.log combined
</VirtualHost>
```

#### Activation du site
```bash
# Activation du site
sudo a2ensite lycol.conf

# Activation des modules requis
sudo a2enmod rewrite
sudo a2enmod ssl

# Redémarrage d'Apache
sudo systemctl restart apache2
```

### 5. CONFIGURATION DE L'APPLICATION

#### Configuration de la base de données
```bash
sudo nano /var/www/lycol/app/Config/Database.php
```

#### Configuration de l'application
```bash
sudo nano /var/www/lycol/app/Config/App.php
```

#### Variables d'environnement
```bash
sudo nano /var/www/lycol/.env
```

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'http://lycol.local/'
app.forceGlobalSecureRequests = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = localhost
database.default.database = lycol_db
database.default.username = lycol_user
database.default.password = mot_de_passe_securise
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

### 6. CONFIGURATION DE NGINX (ALTERNATIVE)

#### Installation de Nginx
```bash
# Ubuntu/Debian
sudo apt install nginx -y

# CentOS/RHEL
sudo yum install nginx -y
```

#### Configuration de Nginx
```bash
sudo nano /etc/nginx/sites-available/lycol
```

```nginx
server {
    listen 80;
    server_name lycol.local www.lycol.local;
    root /var/www/lycol/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

#### Activation du site Nginx
```bash
sudo ln -s /etc/nginx/sites-available/lycol /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. CONFIGURATION SSL/HTTPS

#### Installation de Certbot
```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-apache -y

# CentOS/RHEL
sudo yum install certbot python3-certbot-apache -y
```

#### Génération du certificat SSL
```bash
sudo certbot --apache -d lycol.local -d www.lycol.local
```

### 8. CONFIGURATION DU FIREWALL

#### Configuration UFW (Ubuntu)
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

#### Configuration firewalld (CentOS)
```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

### 9. CONFIGURATION DES TÂCHES CRON

#### Création des tâches automatiques
```bash
sudo crontab -e
```

```cron
# Sauvegarde quotidienne de la base de données
0 2 * * * mysqldump -u lycol_user -p'mot_de_passe_securise' lycol_db > /var/backups/lycol_$(date +\%Y\%m\%d).sql

# Nettoyage des logs (hebdomadaire)
0 3 * * 0 find /var/www/lycol/writable/logs -name "*.log" -mtime +30 -delete

# Redémarrage des services (quotidien)
0 4 * * * systemctl restart apache2
```

### 10. TESTS DE DÉPLOIEMENT

#### Vérification des services
```bash
# Vérification d'Apache
sudo systemctl status apache2

# Vérification de MariaDB
sudo systemctl status mariadb

# Vérification de PHP
php -v

# Vérification de Composer
composer --version
```

#### Tests de l'application
```bash
# Test de la page d'accueil
curl -I http://lycol.local

# Test de la page de connexion
curl -I http://lycol.local/auth/login

# Test de la base de données
mysql -u lycol_user -p lycol_db -e "SELECT COUNT(*) FROM users;"
```

---

## 🔧 CONFIGURATION AVANCÉE

### Optimisation des performances

#### Configuration PHP
```bash
sudo nano /etc/php/8.4/apache2/php.ini
```

```ini
# Optimisations recommandées
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 64M
upload_max_filesize = 64M
max_file_uploads = 20

# Cache OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### Configuration MariaDB
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

```ini
[mysqld]
# Optimisations recommandées
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 64M
max_connections = 200
```

### Monitoring et logs

#### Configuration des logs
```bash
# Logs d'application
sudo nano /var/www/lycol/app/Config/Logger.php

# Logs Apache
sudo tail -f /var/log/apache2/lycol_error.log
sudo tail -f /var/log/apache2/lycol_access.log

# Logs MariaDB
sudo tail -f /var/log/mysql/error.log
```

#### Monitoring des performances
```bash
# Installation de htop
sudo apt install htop -y

# Installation de iotop
sudo apt install iotop -y

# Installation de nethogs
sudo apt install nethogs -y
```

---

## 🚨 DÉPANNAGE

### Problèmes courants

#### Erreur 500 - Internal Server Error
```bash
# Vérification des logs
sudo tail -f /var/log/apache2/error.log

# Vérification des permissions
sudo chmod -R 755 /var/www/lycol
sudo chown -R www-data:www-data /var/www/lycol

# Vérification de la configuration
sudo apache2ctl configtest
```

#### Erreur de base de données
```bash
# Vérification de la connexion
mysql -u lycol_user -p lycol_db

# Vérification des permissions
mysql -u root -p -e "SHOW GRANTS FOR 'lycol_user'@'localhost';"

# Redémarrage de MariaDB
sudo systemctl restart mariadb
```

#### Problèmes de performance
```bash
# Vérification de l'utilisation mémoire
free -h

# Vérification de l'utilisation CPU
htop

# Vérification de l'utilisation disque
df -h
```

### Commandes de maintenance

#### Sauvegarde complète
```bash
#!/bin/bash
# Script de sauvegarde complète
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/lycol"

# Création du dossier de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde de la base de données
mysqldump -u lycol_user -p'mot_de_passe_securise' lycol_db > $BACKUP_DIR/lycol_db_$DATE.sql

# Sauvegarde des fichiers
tar -czf $BACKUP_DIR/lycol_files_$DATE.tar.gz /var/www/lycol

# Nettoyage des anciennes sauvegardes (garde 7 jours)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Sauvegarde terminée : $DATE"
```

#### Mise à jour de l'application
```bash
#!/bin/bash
# Script de mise à jour
cd /var/www/lycol

# Sauvegarde avant mise à jour
mysqldump -u lycol_user -p'mot_de_passe_securise' lycol_db > /var/backups/lycol_pre_update_$(date +%Y%m%d_%H%M%S).sql

# Mise à jour des dépendances
composer update --no-dev --optimize-autoloader

# Mise à jour des permissions
sudo chmod -R 755 /var/www/lycol
sudo chown -R www-data:www-data /var/www/lycol

# Redémarrage des services
sudo systemctl restart apache2

echo "Mise à jour terminée"
```

---

## 📞 SUPPORT ET MAINTENANCE

### Contacts
- **Développeur :** Expert CodeIgniter 4
- **Email :** support@lycol.com
- **Documentation :** Voir DOCUMENTATION_COMPLETE.md

### Ressources
- **CodeIgniter 4 :** https://codeigniter.com/user_guide/
- **PHP :** https://www.php.net/manual/
- **MariaDB :** https://mariadb.org/documentation/
- **Apache :** https://httpd.apache.org/docs/
- **Nginx :** https://nginx.org/en/docs/

---

*Ce guide de déploiement est maintenu à jour avec chaque version du système.*



























