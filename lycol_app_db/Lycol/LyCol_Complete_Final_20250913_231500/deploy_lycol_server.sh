#!/bin/bash

# LyCol - Script de Déploiement Automatique Serveur
# Déploiement professionnel avec configuration .env
# Date: 13 Septembre 2025

echo "🚀 LyCol - Déploiement Automatique Serveur"
echo "=========================================="

# Configuration
PROJECT_NAME="LyCol"
ARCHIVE_NAME="LyCol_Complete_Final_20250913_231500.tar.gz"
DB_BACKUP="backup_database_final_20250913_231500.sql"
INSTALL_DIR="/opt/lycol"
SERVICE_USER="lycol"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage des messages
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Fonction de vérification des prérequis
check_prerequisites() {
    log_info "Vérification des prérequis..."
    
    # Vérifier PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP n'est pas installé"
        return 1
    fi
    
    # Vérifier MariaDB/MySQL
    if ! command -v mysql &> /dev/null; then
        log_error "MariaDB/MySQL n'est pas installé"
        return 1
    fi
    
    # Vérifier Git
    if ! command -v git &> /dev/null; then
        log_error "Git n'est pas installé"
        return 1
    fi
    
    log_success "Tous les prérequis sont installés"
    return 0
}

# Fonction d'installation des prérequis
install_prerequisites() {
    log_info "Installation des prérequis..."
    
    # Détecter la distribution
    if [ -f /etc/debian_version ]; then
        DISTRO="debian"
    elif [ -f /etc/redhat-release ]; then
        DISTRO="redhat"
    else
        log_error "Distribution non supportée"
        return 1
    fi
    
    # Installation selon la distribution
    if [ "$DISTRO" = "debian" ]; then
        sudo apt update
        sudo apt install -y php8.4 php8.4-cli php8.4-mysql php8.4-gd php8.4-curl php8.4-zip php8.4-mbstring php8.4-xml mariadb-server mariadb-client git curl wget unzip
    elif [ "$DISTRO" = "redhat" ]; then
        sudo dnf install -y php84 php84-cli php84-mysql php84-gd php84-curl php84-zip php84-mbstring php84-xml mariadb-server mariadb git curl wget unzip
        sudo systemctl enable mariadb
        sudo systemctl start mariadb
    fi
    
    log_success "Prérequis installés"
}

# Fonction de configuration de la base de données
setup_database() {
    log_info "Configuration de la base de données..."
    
    # Démarrer MariaDB si nécessaire
    sudo systemctl start mariadb 2>/dev/null || true
    
    # Créer la base de données
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS lyscol CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
    
    # Créer l'utilisateur
    sudo mysql -e "CREATE USER IF NOT EXISTS 'lycol_user'@'localhost' IDENTIFIED BY 'LyCol2025!';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON lyscol.* TO 'lycol_user'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    
    log_success "Base de données configurée"
}

# Fonction d'extraction et installation
install_application() {
    log_info "Installation de l'application..."
    
    # Créer le répertoire d'installation
    sudo mkdir -p $INSTALL_DIR
    sudo chown $USER:$USER $INSTALL_DIR
    
    # Extraire l'archive
    if [ -f "$ARCHIVE_NAME" ]; then
        tar -xzf "$ARCHIVE_NAME" -C $INSTALL_DIR
        log_success "Archive extraite"
    else
        log_error "Archive $ARCHIVE_NAME introuvable"
        return 1
    fi
    
    # Aller dans le répertoire du projet
    cd $INSTALL_DIR/codeigniter4-framework-68d1a58
    
    # Rendre les scripts exécutables
    chmod +x *.sh
    
    # Configurer les permissions
    chmod -R 755 app/
    chmod -R 755 public/
    
    log_success "Application installée"
}

# Fonction de restauration de la base de données
restore_database() {
    log_info "Restauration de la base de données..."
    
    if [ -f "$DB_BACKUP" ]; then
        mysql -u lycol_user -p'LyCol2025!' lyscol < "$DB_BACKUP"
        log_success "Base de données restaurée"
    else
        log_warning "Sauvegarde de base de données introuvable, création d'une base vide"
        # Créer les tables de base si nécessaire
    fi
}

# Fonction de configuration de l'application
configure_application() {
    log_info "Configuration de l'application..."
    
    # Configuration réseau automatique
    ./manage_network_config.sh --auto-detect
    
    # Mettre à jour la configuration de la base de données
    sed -i 's/database.default.hostname=100.69.65.33/database.default.hostname=localhost/' .env
    sed -i 's/database.default.port=13306/database.default.port=3306/' .env
    sed -i 's/database.default.username=root/database.default.username=lycol_user/' .env
    sed -i 's/database.default.password=Bateau123/database.default.password=LyCol2025!/' .env
    
    log_success "Application configurée"
}

# Fonction de test de l'installation
test_installation() {
    log_info "Test de l'installation..."
    
    # Test de la configuration
    ./test_env_network_config.sh
    
    if [ $? -eq 0 ]; then
        log_success "Tests réussis"
        return 0
    else
        log_error "Tests échoués"
        return 1
    fi
}

# Fonction de création du service systemd
create_systemd_service() {
    log_info "Création du service systemd..."
    
    sudo tee /etc/systemd/system/lycol.service > /dev/null << 'SERVICE_EOF'
[Unit]
Description=LyCol School Management System
After=network.target mariadb.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/opt/lycol/codeigniter4-framework-68d1a58
ExecStart=/usr/bin/php -S 0.0.0.0:8080 -t public/ ../system/rewrite.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
SERVICE_EOF

    sudo systemctl daemon-reload
    sudo systemctl enable lycol
    log_success "Service systemd créé"
}

# Fonction principale
main() {
    echo "🎯 Déploiement de $PROJECT_NAME"
    echo "================================"
    
    # Vérifier les prérequis
    if ! check_prerequisites; then
        log_info "Installation des prérequis..."
        install_prerequisites
    fi
    
    # Configuration de la base de données
    setup_database
    
    # Installation de l'application
    install_application
    
    # Restauration de la base de données
    restore_database
    
    # Configuration de l'application
    configure_application
    
    # Test de l'installation
    if test_installation; then
        log_success "Installation réussie !"
        
        # Créer le service systemd
        create_systemd_service
        
        echo ""
        echo "🎉 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS !"
        echo "===================================="
        echo ""
        echo "📋 Informations de connexion :"
        echo "   - URL : http://$(hostname -I | awk '{print $1}'):8080"
        echo "   - Base de données : lyscol"
        echo "   - Utilisateur BDD : lycol_user"
        echo "   - Mot de passe BDD : LyCol2025!"
        echo ""
        echo "🚀 Commandes utiles :"
        echo "   - Démarrer : sudo systemctl start lycol"
        echo "   - Arrêter : sudo systemctl stop lycol"
        echo "   - Status : sudo systemctl status lycol"
        echo "   - Logs : sudo journalctl -u lycol -f"
        echo ""
        echo "🔧 Configuration :"
        echo "   - Fichier : $INSTALL_DIR/codeigniter4-framework-68d1a58/.env"
        echo "   - Scripts : $INSTALL_DIR/codeigniter4-framework-68d1a58/"
        echo ""
        
        # Démarrer le service
        sudo systemctl start lycol
        log_success "Service démarré automatiquement"
        
    else
        log_error "Installation échouée"
        exit 1
    fi
}

# Exécution
main "$@"
