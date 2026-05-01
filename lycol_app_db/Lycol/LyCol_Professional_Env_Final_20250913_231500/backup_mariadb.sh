#!/bin/bash

# Script de sauvegarde optimisé pour KISSAI SCHOOL avec MariaDB
# Usage: ./backup_mariadb.sh [backup_name]

echo "💾 SAUVEGARDE KISSAI SCHOOL AVEC MARIADB"
echo "========================================="

# Configuration
BACKUP_NAME=${1:-"kissai_school"}
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PROJECT_DIR="codeigniter4-framework-68d1a58"
DB_HOST="100.69.65.33"
DB_PORT="13306"
DB_USER="root"
DB_PASS="Bateau123"
DB_NAME="lycol_db"

# Noms des fichiers de sauvegarde
PROJECT_BACKUP="${BACKUP_NAME}_project_${TIMESTAMP}.tar.gz"
DATABASE_BACKUP="${BACKUP_NAME}_database_${TIMESTAMP}.sql"
BACKUP_DIR="backups_${TIMESTAMP}"

echo "📅 Timestamp: ${TIMESTAMP}"
echo "📦 Sauvegarde projet: ${PROJECT_BACKUP}"
echo "🗄️  Sauvegarde base: ${DATABASE_BACKUP}"
echo "📁 Dossier: ${BACKUP_DIR}"
echo ""

# Créer le dossier de sauvegarde
mkdir -p "$BACKUP_DIR"
cd "$BACKUP_DIR"

echo "🔄 Début de la sauvegarde..."
echo ""

# 1. Sauvegarde du projet
echo "📦 Sauvegarde du projet..."
if [ -d "../${PROJECT_DIR}" ]; then
    tar -czf "$PROJECT_BACKUP" \
        --exclude="../${PROJECT_DIR}/.git" \
        --exclude="../${PROJECT_DIR}/writable/logs/*" \
        --exclude="../${PROJECT_DIR}/writable/cache/*" \
        --exclude="../${PROJECT_DIR}/writable/session/*" \
        --exclude="../${PROJECT_DIR}/writable/uploads/*" \
        -C .. "$PROJECT_DIR"
    
    if [ $? -eq 0 ]; then
        echo "   ✅ Projet sauvegardé: $(du -h "$PROJECT_BACKUP" | cut -f1)"
    else
        echo "   ❌ Erreur lors de la sauvegarde du projet"
        exit 1
    fi
else
    echo "   ❌ Dossier projet non trouvé: ../${PROJECT_DIR}"
    exit 1
fi

# 2. Sauvegarde de la base de données
echo ""
echo "🗄️  Sauvegarde de la base de données..."

# Test de connexion
echo "   Test de connexion à MariaDB..."
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "   ❌ Impossible de se connecter à MariaDB"
    exit 1
fi
echo "   ✅ Connexion MariaDB OK"

# Sauvegarde avec mariadb-dump
echo "   Création du dump..."
mariadb-dump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-database \
    --create-options \
    --complete-insert \
    --extended-insert \
    --set-charset \
    --default-character-set=utf8mb4 \
    "$DB_NAME" > "$DATABASE_BACKUP"

if [ $? -eq 0 ]; then
    echo "   ✅ Base de données sauvegardée: $(du -h "$DATABASE_BACKUP" | cut -f1)"
else
    echo "   ❌ Erreur lors de la sauvegarde de la base de données"
    exit 1
fi

# 3. Vérification de la sauvegarde
echo ""
echo "🔍 Vérification de la sauvegarde..."

# Vérifier le fichier de projet
if [ -f "$PROJECT_BACKUP" ] && [ -s "$PROJECT_BACKUP" ]; then
    echo "   ✅ Fichier projet: OK ($(du -h "$PROJECT_BACKUP" | cut -f1))"
else
    echo "   ❌ Fichier projet: ERREUR"
    exit 1
fi

# Vérifier le fichier de base de données
if [ -f "$DATABASE_BACKUP" ] && [ -s "$DATABASE_BACKUP" ]; then
    echo "   ✅ Fichier base de données: OK ($(du -h "$DATABASE_BACKUP" | cut -f1))"
    
    # Vérifier le contenu du dump
    TABLE_COUNT=$(grep -c "^-- Table structure for table" "$DATABASE_BACKUP")
    echo "   📊 Tables incluses: $TABLE_COUNT"
else
    echo "   ❌ Fichier base de données: ERREUR"
    exit 1
fi

# 4. Création du fichier de métadonnées
echo ""
echo "📝 Création des métadonnées..."

cat > "backup_info.txt" << EOF
SAUVEGARDE KISSAI SCHOOL
========================

Date: $(date)
Timestamp: ${TIMESTAMP}
Version: MariaDB 12.0.2

FICHIERS:
- Projet: ${PROJECT_BACKUP}
- Base de données: ${DATABASE_BACKUP}

CONFIGURATION:
- Serveur: ${DB_HOST}:${DB_PORT}
- Base: ${DB_NAME}
- Tables: ${TABLE_COUNT}

STATISTIQUES:
- Taille projet: $(du -h "$PROJECT_BACKUP" | cut -f1)
- Taille base: $(du -h "$DATABASE_BACKUP" | cut -f1)
- Total: $(du -sh . | cut -f1)

RESTAURATION:
Pour restaurer cette sauvegarde, utilisez:
./restore_backup.sh ${TIMESTAMP}

EOF

echo "   ✅ Métadonnées créées"

# 5. Création d'un script de restauration spécifique
echo ""
echo "🔧 Création du script de restauration..."

cat > "restore_this_backup.sh" << 'EOF'
#!/bin/bash

# Script de restauration pour cette sauvegarde spécifique
# Usage: ./restore_this_backup.sh

echo "🔄 RESTAURATION DE LA SAUVEGARDE"
echo "================================"

# Configuration
DB_HOST="100.69.65.33"
DB_PORT="13306"
DB_USER="root"
DB_PASS="Bateau123"
DB_NAME="lycol_db"

# Fichiers de sauvegarde
PROJECT_BACKUP="'$PROJECT_BACKUP'"
DATABASE_BACKUP="'$DATABASE_BACKUP'"

echo "📦 Restauration du projet..."
if [ -d "../codeigniter4-framework-68d1a58" ]; then
    echo "   Sauvegarde de l'ancien projet..."
    mv ../codeigniter4-framework-68d1a58 "../codeigniter4-framework-68d1a58_backup_$(date +%Y%m%d_%H%M%S)"
fi

tar -xzf "$PROJECT_BACKUP" -C ..
if [ $? -eq 0 ]; then
    echo "   ✅ Projet restauré"
else
    echo "   ❌ Erreur lors de la restauration du projet"
    exit 1
fi

echo ""
echo "🗄️  Restauration de la base de données..."
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo "   Base de données recréée"
else
    echo "   ❌ Erreur lors de la recréation de la base de données"
    exit 1
fi

mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DATABASE_BACKUP"

if [ $? -eq 0 ]; then
    echo "   ✅ Base de données restaurée"
else
    echo "   ❌ Erreur lors de l'import de la base de données"
    exit 1
fi

echo ""
echo "🔧 Configuration des permissions..."
chmod -R 755 ../codeigniter4-framework-68d1a58/writable/
chmod -R 755 ../codeigniter4-framework-68d1a58/app/

echo ""
echo "🎉 RESTAURATION TERMINÉE AVEC SUCCÈS !"
echo "======================================"
echo ""
echo "📋 Récapitulatif :"
echo "   - Projet restauré: ../codeigniter4-framework-68d1a58"
echo "   - Base de données: $DB_NAME"
echo "   - Serveur: http://localhost:8080"
echo ""
echo "🚀 Pour démarrer le serveur :"
echo "   cd ../codeigniter4-framework-68d1a58"
echo "   php spark serve --port=8080 --host=0.0.0.0"
EOF

chmod +x "restore_this_backup.sh"
echo "   ✅ Script de restauration créé"

# 6. Résumé final
echo ""
echo "🎉 SAUVEGARDE TERMINÉE AVEC SUCCÈS !"
echo "===================================="
echo ""
echo "📁 Dossier de sauvegarde: $(pwd)"
echo "📦 Projet: ${PROJECT_BACKUP}"
echo "🗄️  Base de données: ${DATABASE_BACKUP}"
echo "📝 Métadonnées: backup_info.txt"
echo "🔧 Script de restauration: restore_this_backup.sh"
echo ""
echo "💾 Taille totale: $(du -sh . | cut -f1)"
echo ""
echo "📋 Pour restaurer cette sauvegarde :"
echo "   cd $(pwd)"
echo "   ./restore_this_backup.sh"
echo ""
echo "📋 Pour lister toutes les sauvegardes :"
echo "   ls -la ../backups_*"









