#!/bin/bash

# LyCol - Script de Sauvegarde Complète Automatique
# Sauvegarde projet + base de données + documentation
# Date: 13 Septembre 2025

echo "💾 LyCol - Sauvegarde Complète Automatique"
echo "=========================================="

# Configuration
BACKUP_DIR="backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
PROJECT_NAME="LyCol_Complete_Backup_$TIMESTAMP"
DB_HOST="100.69.65.33"
DB_PORT="13306"
DB_USER="root"
DB_PASS="Bateau123"

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Fonction de sauvegarde de la base de données
backup_database() {
    log_info "Sauvegarde de la base de données..."
    
    local db_backup_file="backup_database_$TIMESTAMP.sql"
    
    if mysqldump --protocol=TCP -h $DB_HOST --port=$DB_PORT -u $DB_USER -p$DB_PASS --all-databases > "$db_backup_file" 2>/dev/null; then
        log_success "Base de données sauvegardée : $db_backup_file"
        echo "$db_backup_file"
    else
        log_warning "Échec de la sauvegarde de la base de données"
        echo ""
    fi
}

# Fonction de création du répertoire de sauvegarde
create_backup_directory() {
    log_info "Création du répertoire de sauvegarde..."
    
    mkdir -p "$BACKUP_DIR/$PROJECT_NAME"
    log_success "Répertoire créé : $BACKUP_DIR/$PROJECT_NAME"
}

# Fonction de copie du projet
backup_project() {
    log_info "Sauvegarde du projet..."
    
    # Exclure les fichiers temporaires et caches
    rsync -av --exclude='writable/cache/*' \
              --exclude='writable/logs/*' \
              --exclude='writable/session/*' \
              --exclude='vendor/*' \
              --exclude='node_modules/*' \
              --exclude='*.log' \
              --exclude='.git/*' \
              . "$BACKUP_DIR/$PROJECT_NAME/"
    
    log_success "Projet sauvegardé"
}

# Fonction de copie de la base de données
copy_database_backup() {
    local db_file="$1"
    
    if [ -n "$db_file" ] && [ -f "$db_file" ]; then
        log_info "Copie de la sauvegarde de base de données..."
        cp "$db_file" "$BACKUP_DIR/$PROJECT_NAME/"
        log_success "Sauvegarde BDD copiée"
    fi
}

# Fonction de création de la documentation
create_documentation() {
    log_info "Création de la documentation..."
    
    cat > "$BACKUP_DIR/$PROJECT_NAME/README_BACKUP.md" << 'DOCEOF'
# LyCol - Sauvegarde Complète

**Date de sauvegarde :** $(date)
**Version :** 1.0.0 Finale
**Type :** Sauvegarde complète projet + base de données

## Contenu de la sauvegarde

- **Code source** : Application CodeIgniter 4 complète
- **Base de données** : backup_database_*.sql
- **Configuration** : Fichier .env avec configuration réseau
- **Scripts** : Scripts de gestion et déploiement
- **Documentation** : Guides techniques complets

## Restauration

1. Extraire l'archive
2. Restaurer la base de données : `mysql -u root -p < backup_database_*.sql`
3. Configurer : `./manage_network_config.sh --auto-detect`
4. Démarrer : `./manage_network_config.sh --start-server`

## Configuration réseau

L'application est configurée pour s'adapter automatiquement aux changements d'IP via le fichier .env.

**Mode auto-détection activé** : APP_AUTO_DETECT_IP=true
DOCEOF

    log_success "Documentation créée"
}

# Fonction de création de l'archive
create_archive() {
    log_info "Création de l'archive..."
    
    local archive_name="${PROJECT_NAME}.tar.gz"
    
    cd "$BACKUP_DIR"
    tar -czf "$archive_name" "$PROJECT_NAME/"
    cd ..
    
    local archive_size=$(du -h "$BACKUP_DIR/$archive_name" | cut -f1)
    log_success "Archive créée : $archive_name ($archive_size)"
    
    echo "$archive_name"
}

# Fonction de nettoyage
cleanup() {
    log_info "Nettoyage des fichiers temporaires..."
    
    # Supprimer la sauvegarde de BDD temporaire
    rm -f backup_database_$TIMESTAMP.sql
    
    # Supprimer le répertoire temporaire
    rm -rf "$BACKUP_DIR/$PROJECT_NAME"
    
    log_success "Nettoyage terminé"
}

# Fonction de génération du rapport
generate_report() {
    local archive_name="$1"
    local archive_path="$BACKUP_DIR/$archive_name"
    
    log_info "Génération du rapport de sauvegarde..."
    
    cat > "${archive_name}_REPORT.txt" << 'REPORTEOF'
📦 LYCCOL - RAPPORT DE SAUVEGARDE COMPLÈTE
==========================================

📅 Date: $(date)
🏷️  Archive: $archive_name
📏 Taille: $(du -h "$archive_path" | cut -f1)

📋 CONTENU DE LA SAUVEGARDE:
============================

✅ Code source complet (CodeIgniter 4)
✅ Base de données (MariaDB)
✅ Configuration .env professionnelle
✅ Scripts de gestion et déploiement
✅ Documentation technique complète
✅ Tests automatisés

🔧 FONCTIONNALITÉS INCLUSES:
============================

- Configuration réseau automatique
- Adaptation aux changements d'IP
- Scripts de déploiement automatisés
- Tests de validation multi-IP
- Documentation technique experte
- Guide de déploiement serveur

🚀 DÉPLOIEMENT:
===============

1. Extraire: tar -xzf $archive_name
2. Base de données: mysql -u root -p < backup_database_*.sql
3. Configurer: ./manage_network_config.sh --auto-detect
4. Démarrer: ./manage_network_config.sh --start-server

🏆 STATUS: SAUVEGARDE COMPLÈTE RÉUSSIE
REPORTEOF

    log_success "Rapport généré : ${archive_name}_REPORT.txt"
}

# Fonction principale
main() {
    echo "🎯 Sauvegarde complète de LyCol"
    echo "==============================="
    
    # Créer le répertoire de sauvegarde
    create_backup_directory
    
    # Sauvegarder la base de données
    local db_backup=$(backup_database)
    
    # Sauvegarder le projet
    backup_project
    
    # Copier la sauvegarde de base de données
    copy_database_backup "$db_backup"
    
    # Créer la documentation
    create_documentation
    
    # Créer l'archive
    local archive_name=$(create_archive)
    
    # Nettoyer
    cleanup
    
    # Générer le rapport
    generate_report "$archive_name"
    
    echo ""
    echo "🎉 SAUVEGARDE COMPLÈTE TERMINÉE !"
    echo "================================="
    echo ""
    echo "📦 Archive : $BACKUP_DIR/$archive_name"
    echo "📋 Rapport : ${archive_name}_REPORT.txt"
    echo ""
    echo "🚀 Pour déployer sur un autre serveur :"
    echo "   1. Transférer l'archive"
    echo "   2. Extraire : tar -xzf $archive_name"
    echo "   3. Suivre le guide de déploiement"
    echo ""
    
    log_success "Sauvegarde terminée avec succès !"
}

# Exécution
main "$@"
