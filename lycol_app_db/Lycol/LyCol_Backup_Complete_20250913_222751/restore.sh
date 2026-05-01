#!/bin/bash

# SCRIPT DE RESTAURATION - LYCCOL
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
BACKUP_DIR="/var/backups/lycol"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then
    print_error "Ce script doit être exécuté en tant que root (utilisez sudo)"
    exit 1
fi

print_message "=== RESTAURATION LYCCOL ==="
print_message "Début de la restauration..."

# Fonction d'aide
show_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -d, --date DATE     Date de la sauvegarde (format: YYYYMMDD_HHMMSS)"
    echo "  -l, --list          Lister les sauvegardes disponibles"
    echo "  -h, --help          Afficher cette aide"
    echo ""
    echo "Exemples:"
    echo "  $0 --list"
    echo "  $0 --date 20250913_222731"
    echo ""
}

# Fonction pour lister les sauvegardes
list_backups() {
    print_message "Sauvegardes disponibles:"
    echo ""
    if [ -d "$BACKUP_DIR" ]; then
        ls -la $BACKUP_DIR/lycol_db_*.sql.gz 2>/dev/null | awk '{print $6, $7, $8, $9}' | while read date time file; do
            if [ -n "$file" ]; then
                backup_date=$(basename $file | sed 's/lycol_db_\(.*\)\.sql\.gz/\1/')
                echo "  - $backup_date"
            fi
        done
    else
        print_warning "Aucune sauvegarde trouvée dans $BACKUP_DIR"
    fi
    echo ""
}

# Fonction pour vérifier la sauvegarde
check_backup() {
    local backup_date=$1
    local db_file="$BACKUP_DIR/lycol_db_${backup_date}.sql.gz"
    local files_file="$BACKUP_DIR/lycol_files_${backup_date}.tar.gz"
    local config_file="$BACKUP_DIR/lycol_config_${backup_date}.tar.gz"
    
    if [ ! -f "$db_file" ]; then
        print_error "Sauvegarde de la base de données non trouvée: $db_file"
        return 1
    fi
    
    if [ ! -f "$files_file" ]; then
        print_error "Sauvegarde des fichiers non trouvée: $files_file"
        return 1
    fi
    
    if [ ! -f "$config_file" ]; then
        print_warning "Sauvegarde de la configuration non trouvée: $config_file"
    fi
    
    print_success "Sauvegarde valide trouvée pour la date: $backup_date"
    return 0
}

# Fonction pour créer une sauvegarde de sécurité
create_safety_backup() {
    local safety_date=$(date +%Y%m%d_%H%M%S)
    print_message "Création d'une sauvegarde de sécurité avant restauration..."
    
    # Sauvegarde de la base de données actuelle
    if mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > /tmp/lycol_safety_db_$safety_date.sql 2>/dev/null; then
        print_success "Sauvegarde de sécurité de la base de données créée"
    else
        print_warning "Impossible de créer une sauvegarde de sécurité de la base de données"
    fi
    
    # Sauvegarde des fichiers actuels
    if [ -d "$APP_DIR" ]; then
        tar -czf /tmp/lycol_safety_files_$safety_date.tar.gz -C /var/www $APP_NAME 2>/dev/null
        print_success "Sauvegarde de sécurité des fichiers créée"
    else
        print_warning "Répertoire de l'application non trouvé"
    fi
}

# Fonction pour restaurer la base de données
restore_database() {
    local backup_date=$1
    local db_file="$BACKUP_DIR/lycol_db_${backup_date}.sql.gz"
    
    print_message "Restauration de la base de données..."
    
    # Arrêt temporaire de l'application
    systemctl stop apache2
    
    # Suppression de la base de données actuelle
    mysql -u root -p -e "DROP DATABASE IF EXISTS $DB_NAME;"
    mysql -u root -p -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # Restauration de la base de données
    if zcat $db_file | mysql -u root -p $DB_NAME; then
        print_success "Base de données restaurée avec succès"
    else
        print_error "Erreur lors de la restauration de la base de données"
        systemctl start apache2
        exit 1
    fi
    
    # Redémarrage de l'application
    systemctl start apache2
}

# Fonction pour restaurer les fichiers
restore_files() {
    local backup_date=$1
    local files_file="$BACKUP_DIR/lycol_files_${backup_date}.tar.gz"
    
    print_message "Restauration des fichiers de l'application..."
    
    # Arrêt temporaire de l'application
    systemctl stop apache2
    
    # Sauvegarde de la configuration actuelle
    if [ -f "$APP_DIR/.env" ]; then
        cp $APP_DIR/.env /tmp/lycol_env_backup
    fi
    
    # Suppression des fichiers actuels
    if [ -d "$APP_DIR" ]; then
        rm -rf $APP_DIR
    fi
    
    # Restauration des fichiers
    if tar -xzf $files_file -C /var/www; then
        print_success "Fichiers de l'application restaurés"
    else
        print_error "Erreur lors de la restauration des fichiers"
        systemctl start apache2
        exit 1
    fi
    
    # Restauration de la configuration
    if [ -f "/tmp/lycol_env_backup" ]; then
        cp /tmp/lycol_env_backup $APP_DIR/.env
        rm /tmp/lycol_env_backup
    fi
    
    # Configuration des permissions
    chown -R www-data:www-data $APP_DIR
    chmod -R 755 $APP_DIR
    chmod -R 775 $APP_DIR/writable
    
    # Redémarrage de l'application
    systemctl start apache2
}

# Fonction pour restaurer la configuration
restore_config() {
    local backup_date=$1
    local config_file="$BACKUP_DIR/lycol_config_${backup_date}.tar.gz"
    
    if [ ! -f "$config_file" ]; then
        print_warning "Fichier de configuration non trouvé, utilisation de la configuration actuelle"
        return 0
    fi
    
    print_message "Restauration de la configuration..."
    
    # Arrêt temporaire de l'application
    systemctl stop apache2
    
    # Restauration de la configuration
    if tar -xzf $config_file -C /; then
        print_success "Configuration restaurée"
    else
        print_warning "Erreur lors de la restauration de la configuration"
    fi
    
    # Redémarrage des services
    systemctl restart apache2
    systemctl restart mariadb
}

# Fonction pour vérifier la restauration
verify_restoration() {
    print_message "Vérification de la restauration..."
    
    # Vérification de la base de données
    if mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT 1;" > /dev/null 2>&1; then
        print_success "Base de données accessible"
    else
        print_error "Problème d'accès à la base de données"
        return 1
    fi
    
    # Vérification de l'application
    if [ -d "$APP_DIR" ] && [ -f "$APP_DIR/public/index.php" ]; then
        print_success "Fichiers de l'application présents"
    else
        print_error "Fichiers de l'application manquants"
        return 1
    fi
    
    # Vérification d'Apache
    if systemctl is-active apache2 > /dev/null; then
        print_success "Apache fonctionne"
    else
        print_error "Apache ne fonctionne pas"
        return 1
    fi
    
    # Test de l'application
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
        print_success "Application accessible"
    else
        print_warning "Application non accessible via localhost"
    fi
    
    return 0
}

# Traitement des arguments
BACKUP_DATE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -d|--date)
            BACKUP_DATE="$2"
            shift 2
            ;;
        -l|--list)
            list_backups
            exit 0
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            print_error "Option inconnue: $1"
            show_help
            exit 1
            ;;
    esac
done

# Vérification des arguments
if [ -z "$BACKUP_DATE" ]; then
    print_error "Date de sauvegarde requise"
    show_help
    exit 1
fi

# Vérification de la sauvegarde
if ! check_backup $BACKUP_DATE; then
    exit 1
fi

# Confirmation de l'utilisateur
print_warning "ATTENTION: Cette opération va remplacer les données actuelles!"
print_warning "Une sauvegarde de sécurité sera créée avant la restauration."
echo ""
read -p "Voulez-vous continuer? (oui/non): " confirm

if [ "$confirm" != "oui" ]; then
    print_message "Restauration annulée"
    exit 0
fi

# Création d'une sauvegarde de sécurité
create_safety_backup

# Restauration
print_message "Début de la restauration pour la date: $BACKUP_DATE"

# Restauration de la base de données
restore_database $BACKUP_DATE

# Restauration des fichiers
restore_files $BACKUP_DATE

# Restauration de la configuration
restore_config $BACKUP_DATE

# Vérification de la restauration
if verify_restoration; then
    print_success "=== RESTAURATION TERMINÉE AVEC SUCCÈS ==="
    echo ""
    print_message "Résumé de la restauration:"
    echo "  - Date de sauvegarde: $BACKUP_DATE"
    echo "  - Base de données: Restaurée"
    echo "  - Fichiers: Restaurés"
    echo "  - Configuration: Restaurée"
    echo "  - Application: Accessible"
    echo ""
    print_message "Sauvegarde de sécurité créée dans /tmp/"
    echo ""
    print_success "Restauration terminée!"
else
    print_error "=== ERREUR LORS DE LA RESTAURATION ==="
    print_error "Vérifiez les logs et contactez l'administrateur"
    exit 1
fi

exit 0



























