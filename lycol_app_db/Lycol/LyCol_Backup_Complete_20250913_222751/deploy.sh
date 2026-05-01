#!/bin/bash

# SCRIPT DE DÉPLOIEMENT AUTOMATIQUE - LYCCOL
# Version: 1.0.0
# Date: 13 Septembre 2025

set -e  # Arrêter le script en cas d'erreur

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Configuration
APP_NAME="lycol"
APP_DIR="/var/www/$APP_NAME"
DB_NAME="lycol_db"
DB_USER="lycol_user"
DB_PASS="mot_de_passe_securise"
WEB_USER="www-data"
WEB_GROUP="www-data"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then
    print_error "Ce script doit être exécuté en tant que root (utilisez sudo)"
    exit 1
fi

print_message "=== DÉPLOIEMENT AUTOMATIQUE LYCCOL ==="
print_message "Début du déploiement..."

# 1. Mise à jour du système
print_message "Mise à jour du système..."
apt update && apt upgrade -y

# 2. Installation des dépendances
print_message "Installation des dépendances..."

# Installation de PHP 8.4
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-intl php8.4-curl php8.4-zip php8.4-gd php8.4-xml php8.4-json

# Installation de MariaDB
apt install -y mariadb-server mariadb-client

# Installation d'Apache
apt install -y apache2

# Installation de Composer
if ! command -v composer &> /dev/null; then
    print_message "Installation de Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Installation de Git
apt install -y git

print_success "Dépendances installées avec succès"

# 3. Configuration de MariaDB
print_message "Configuration de MariaDB..."

# Démarrage de MariaDB
systemctl start mariadb
systemctl enable mariadb

# Configuration sécurisée de MariaDB
print_warning "Configuration de MariaDB - Veuillez suivre les instructions:"
mysql_secure_installation

# Création de la base de données
print_message "Création de la base de données..."
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

print_success "Base de données configurée"

# 4. Importation de la base de données
print_message "Importation de la base de données..."
if [ -f "backup_all_databases_20250913_222731.sql" ]; then
    mysql -u root -p $DB_NAME < backup_all_databases_20250913_222731.sql
    print_success "Base de données importée"
else
    print_error "Fichier de sauvegarde de la base de données non trouvé!"
    exit 1
fi

# 5. Création du répertoire de l'application
print_message "Création du répertoire de l'application..."
mkdir -p $APP_DIR
chown -R $WEB_USER:$WEB_GROUP $APP_DIR
chmod -R 755 $APP_DIR

# 6. Copie des fichiers de l'application
print_message "Copie des fichiers de l'application..."
cp -r . $APP_DIR/
chown -R $WEB_USER:$WEB_GROUP $APP_DIR

# 7. Installation des dépendances Composer
print_message "Installation des dépendances Composer..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader

# 8. Configuration des permissions
print_message "Configuration des permissions..."
chmod -R 775 $APP_DIR/writable
chown -R $WEB_USER:$WEB_GROUP $APP_DIR/writable
chmod -R 755 $APP_DIR/public
chown -R $WEB_USER:$WEB_GROUP $APP_DIR/public

# 9. Configuration d'Apache
print_message "Configuration d'Apache..."

# Activation des modules requis
a2enmod rewrite
a2enmod ssl

# Création du Virtual Host
cat > /etc/apache2/sites-available/$APP_NAME.conf << EOF
<VirtualHost *:80>
    ServerName $APP_NAME.local
    ServerAlias www.$APP_NAME.local
    DocumentRoot $APP_DIR/public
    
    <Directory $APP_DIR/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${APP_NAME}_access.log combined
</VirtualHost>
EOF

# Activation du site
a2ensite $APP_NAME.conf
a2dissite 000-default.conf

# 10. Configuration de l'application
print_message "Configuration de l'application..."

# Configuration de la base de données
cat > $APP_DIR/app/Config/Database.php << EOF
<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string \$filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string \$defaultGroup = 'default';

    public array \$default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => '$DB_USER',
        'password' => '$DB_PASS',
        'database' => '$DB_NAME',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    public array \$tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => 'ci4',
        'password'    => 'ci4',
        'database'    => 'ci4',
        'DBDriver'    => 'MySQLi',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => (ENVIRONMENT !== 'production'),
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            \$this->defaultGroup = 'tests';
        }
    }
}
EOF

# Configuration de l'application
cat > $APP_DIR/.env << EOF
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'http://$APP_NAME.local/'
app.forceGlobalSecureRequests = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = localhost
database.default.database = $DB_NAME
database.default.username = $DB_USER
database.default.password = $DB_PASS
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
EOF

# 11. Configuration du firewall
print_message "Configuration du firewall..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# 12. Redémarrage des services
print_message "Redémarrage des services..."
systemctl restart apache2
systemctl restart mariadb

# 13. Configuration des tâches cron
print_message "Configuration des tâches cron..."
cat > /etc/cron.d/lycol << EOF
# Sauvegarde quotidienne de la base de données
0 2 * * * root mysqldump -u $DB_USER -p'$DB_PASS' $DB_NAME > /var/backups/lycol_\$(date +\%Y\%m\%d).sql

# Nettoyage des logs (hebdomadaire)
0 3 * * 0 root find $APP_DIR/writable/logs -name "*.log" -mtime +30 -delete
EOF

# 14. Création du dossier de sauvegarde
mkdir -p /var/backups
chown root:root /var/backups
chmod 755 /var/backups

# 15. Tests de déploiement
print_message "Tests de déploiement..."

# Test de la configuration Apache
if apache2ctl configtest; then
    print_success "Configuration Apache valide"
else
    print_error "Erreur dans la configuration Apache"
    exit 1
fi

# Test de la base de données
if mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT 1;" > /dev/null 2>&1; then
    print_success "Connexion à la base de données réussie"
else
    print_error "Erreur de connexion à la base de données"
    exit 1
fi

# Test de l'application
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
    print_success "Application accessible"
else
    print_warning "Application non accessible via localhost"
fi

# 16. Affichage des informations de déploiement
print_success "=== DÉPLOIEMENT TERMINÉ AVEC SUCCÈS ==="
echo ""
print_message "Informations de déploiement:"
echo "  - Application: $APP_NAME"
echo "  - Répertoire: $APP_DIR"
echo "  - Base de données: $DB_NAME"
echo "  - Utilisateur DB: $DB_USER"
echo "  - URL: http://$APP_NAME.local"
echo ""
print_message "Prochaines étapes:"
echo "  1. Ajoutez '$APP_NAME.local' à votre fichier /etc/hosts"
echo "  2. Accédez à http://$APP_NAME.local"
echo "  3. Connectez-vous avec les identifiants par défaut"
echo "  4. Configurez SSL avec: certbot --apache -d $APP_NAME.local"
echo ""
print_message "Fichiers de configuration:"
echo "  - Apache: /etc/apache2/sites-available/$APP_NAME.conf"
echo "  - Base de données: $APP_DIR/app/Config/Database.php"
echo "  - Application: $APP_DIR/.env"
echo ""
print_message "Logs:"
echo "  - Apache: /var/log/apache2/${APP_NAME}_error.log"
echo "  - Application: $APP_DIR/writable/logs/"
echo ""
print_success "Déploiement terminé!"



























