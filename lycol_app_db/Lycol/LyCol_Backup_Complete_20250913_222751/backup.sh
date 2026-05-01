#!/bin/bash

# SCRIPT DE SAUVEGARDE AUTOMATIQUE - LYCCOL
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
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then
    print_error "Ce script doit être exécuté en tant que root (utilisez sudo)"
    exit 1
fi

print_message "=== SAUVEGARDE AUTOMATIQUE LYCCOL ==="
print_message "Début de la sauvegarde..."

# Création du dossier de sauvegarde
mkdir -p $BACKUP_DIR
chmod 755 $BACKUP_DIR

# 1. Sauvegarde de la base de données
print_message "Sauvegarde de la base de données..."
DB_BACKUP_FILE="$BACKUP_DIR/lycol_db_$DATE.sql"

if mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $DB_BACKUP_FILE; then
    print_success "Base de données sauvegardée: $DB_BACKUP_FILE"
    
    # Compression de la sauvegarde de la base de données
    gzip $DB_BACKUP_FILE
    print_success "Sauvegarde de la base de données compressée"
else
    print_error "Erreur lors de la sauvegarde de la base de données"
    exit 1
fi

# 2. Sauvegarde des fichiers de l'application
print_message "Sauvegarde des fichiers de l'application..."
FILES_BACKUP_FILE="$BACKUP_DIR/lycol_files_$DATE.tar.gz"

if tar -czf $FILES_BACKUP_FILE -C /var/www $APP_NAME; then
    print_success "Fichiers de l'application sauvegardés: $FILES_BACKUP_FILE"
else
    print_error "Erreur lors de la sauvegarde des fichiers"
    exit 1
fi

# 3. Sauvegarde des fichiers de configuration
print_message "Sauvegarde des fichiers de configuration..."
CONFIG_BACKUP_FILE="$BACKUP_DIR/lycol_config_$DATE.tar.gz"

# Sauvegarde des configurations système
tar -czf $CONFIG_BACKUP_FILE \
    /etc/apache2/sites-available/$APP_NAME.conf \
    /etc/apache2/sites-enabled/$APP_NAME.conf \
    /etc/cron.d/lycol \
    $APP_DIR/.env \
    $APP_DIR/app/Config/Database.php \
    $APP_DIR/app/Config/App.php

print_success "Fichiers de configuration sauvegardés: $CONFIG_BACKUP_FILE"

# 4. Sauvegarde des logs
print_message "Sauvegarde des logs..."
LOGS_BACKUP_FILE="$BACKUP_DIR/lycol_logs_$DATE.tar.gz"

if tar -czf $LOGS_BACKUP_FILE \
    $APP_DIR/writable/logs/ \
    /var/log/apache2/${APP_NAME}_*.log; then
    print_success "Logs sauvegardés: $LOGS_BACKUP_FILE"
else
    print_warning "Aucun log trouvé à sauvegarder"
fi

# 5. Création d'un rapport de sauvegarde
print_message "Création du rapport de sauvegarde..."
REPORT_FILE="$BACKUP_DIR/backup_report_$DATE.txt"

cat > $REPORT_FILE << EOF
=== RAPPORT DE SAUVEGARDE LYCCOL ===
Date: $(date)
Heure: $(date +%H:%M:%S)
Utilisateur: $(whoami)
Serveur: $(hostname)

=== FICHIERS DE SAUVEGARDE ===
Base de données: lycol_db_$DATE.sql.gz
Fichiers application: lycol_files_$DATE.tar.gz
Configuration: lycol_config_$DATE.tar.gz
Logs: lycol_logs_$DATE.tar.gz

=== TAILLES DES SAUVEGARDES ===
$(ls -lh $BACKUP_DIR/*_$DATE.* | awk '{print $5, $9}')

=== ESPACE DISQUE ===
$(df -h $BACKUP_DIR)

=== STATUT DES SERVICES ===
Apache: $(systemctl is-active apache2)
MariaDB: $(systemctl is-active mariadb)

=== INFORMATIONS DE L'APPLICATION ===
Répertoire: $APP_DIR
Base de données: $DB_NAME
Utilisateur DB: $DB_USER

=== FIN DU RAPPORT ===
EOF

print_success "Rapport de sauvegarde créé: $REPORT_FILE"

# 6. Nettoyage des anciennes sauvegardes
print_message "Nettoyage des anciennes sauvegardes (plus de $RETENTION_DAYS jours)..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "backup_report_*.txt" -mtime +$RETENTION_DAYS -delete

print_success "Anciennes sauvegardes supprimées"

# 7. Vérification de l'intégrité des sauvegardes
print_message "Vérification de l'intégrité des sauvegardes..."

# Vérification de la sauvegarde de la base de données
if gzip -t $BACKUP_DIR/lycol_db_$DATE.sql.gz; then
    print_success "Sauvegarde de la base de données valide"
else
    print_error "Sauvegarde de la base de données corrompue"
fi

# Vérification de la sauvegarde des fichiers
if tar -tzf $BACKUP_DIR/lycol_files_$DATE.tar.gz > /dev/null; then
    print_success "Sauvegarde des fichiers valide"
else
    print_error "Sauvegarde des fichiers corrompue"
fi

# 8. Affichage du résumé
print_success "=== SAUVEGARDE TERMINÉE AVEC SUCCÈS ==="
echo ""
print_message "Résumé de la sauvegarde:"
echo "  - Date: $(date)"
echo "  - Dossier: $BACKUP_DIR"
echo "  - Fichiers créés:"
ls -lh $BACKUP_DIR/*_$DATE.* | awk '{print "    " $5, $9}'
echo ""
print_message "Espace disque utilisé:"
df -h $BACKUP_DIR
echo ""
print_message "Prochaines sauvegardes:"
echo "  - Automatique: Tous les jours à 2h00"
echo "  - Rétention: $RETENTION_DAYS jours"
echo ""
print_success "Sauvegarde terminée!"

# 9. Envoi d'un email de notification (optionnel)
if command -v mail &> /dev/null; then
    print_message "Envoi de la notification par email..."
    echo "Sauvegarde LYCCOL terminée avec succès le $(date)" | mail -s "Sauvegarde LYCCOL - $(date +%Y-%m-%d)" admin@lycol.local
    print_success "Notification envoyée"
fi

exit 0



























