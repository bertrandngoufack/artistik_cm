#!/bin/bash

# Script de restauration pour KISSAI SCHOOL
# Usage: ./restore_backup.sh [backup_date]

echo "🔄 SCRIPT DE RESTAURATION KISSAI SCHOOL"
echo "======================================="

# Vérifier si une date de sauvegarde est fournie
if [ -z "$1" ]; then
    echo "❌ Erreur: Veuillez spécifier la date de sauvegarde (format: YYYYMMDD_HHMMSS)"
    echo "Usage: ./restore_backup.sh 20250824_185425"
    exit 1
fi

BACKUP_DATE=$1
PROJECT_BACKUP="kissai_school_backup_${BACKUP_DATE}.tar.gz"
DATABASE_BACKUP="lycol_database_backup_${BACKUP_DATE}.sql"

echo "📅 Date de sauvegarde: ${BACKUP_DATE}"
echo "📦 Sauvegarde projet: ${PROJECT_BACKUP}"
echo "🗄️  Sauvegarde base: ${DATABASE_BACKUP}"
echo ""

# Vérifier l'existence des fichiers de sauvegarde
if [ ! -f "$PROJECT_BACKUP" ]; then
    echo "❌ Erreur: Fichier de sauvegarde projet non trouvé: ${PROJECT_BACKUP}"
    exit 1
fi

if [ ! -f "$DATABASE_BACKUP" ]; then
    echo "❌ Erreur: Fichier de sauvegarde base de données non trouvé: ${DATABASE_BACKUP}"
    exit 1
fi

echo "✅ Fichiers de sauvegarde trouvés"
echo ""

# Demander confirmation
read -p "⚠️  ATTENTION: Cette opération va écraser les données actuelles. Continuer ? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Restauration annulée"
    exit 1
fi

echo ""
echo "🔄 Début de la restauration..."
echo ""

# 1. Restauration du projet
echo "📦 Restauration du projet..."
if [ -d "codeigniter4-framework-68d1a58" ]; then
    echo "   Sauvegarde de l'ancien projet..."
    mv codeigniter4-framework-68d1a58 "codeigniter4-framework-68d1a58_backup_$(date +%Y%m%d_%H%M%S)"
fi

echo "   Extraction de la sauvegarde..."
tar -xzf "$PROJECT_BACKUP"
if [ $? -eq 0 ]; then
    echo "   ✅ Projet restauré avec succès"
else
    echo "   ❌ Erreur lors de la restauration du projet"
    exit 1
fi

# 2. Restauration de la base de données
echo ""
echo "🗄️  Restauration de la base de données..."

# Configuration de la base de données
DB_HOST="100.69.65.33"
DB_PORT="13306"
DB_USER="root"
DB_PASS="Bateau123"
DB_NAME="lycol_db"

echo "   Connexion à la base de données..."
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo "   Base de données recréée"
else
    echo "   ❌ Erreur lors de la recréation de la base de données"
    exit 1
fi

echo "   Import des données..."
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DATABASE_BACKUP"

if [ $? -eq 0 ]; then
    echo "   ✅ Base de données restaurée avec succès"
else
    echo "   ❌ Erreur lors de l'import de la base de données"
    exit 1
fi

# 3. Configuration des permissions
echo ""
echo "🔧 Configuration des permissions..."
cd codeigniter4-framework-68d1a58
chmod -R 755 writable/
chmod -R 755 app/
echo "   ✅ Permissions configurées"

# 4. Vérification finale
echo ""
echo "🔍 Vérification finale..."

# Vérifier que le serveur peut démarrer
echo "   Test de démarrage du serveur..."
timeout 10s php spark serve --port=8084 --host=0.0.0.0 > /dev/null 2>&1 &
SERVER_PID=$!
sleep 3

if kill -0 $SERVER_PID 2>/dev/null; then
    echo "   ✅ Serveur peut démarrer"
    kill $SERVER_PID 2>/dev/null
else
    echo "   ⚠️  Problème avec le démarrage du serveur"
fi

# Vérifier la connexion à la base de données
echo "   Test de connexion à la base de données..."
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; SELECT COUNT(*) as total_students FROM students;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "   ✅ Connexion à la base de données OK"
else
    echo "   ❌ Problème de connexion à la base de données"
fi

echo ""
echo "🎉 RESTAURATION TERMINÉE AVEC SUCCÈS !"
echo "======================================"
echo ""
echo "📋 Récapitulatif :"
echo "   - Projet restauré: $(pwd)"
echo "   - Base de données: $DB_NAME"
echo "   - Serveur: http://localhost:8080"
echo ""
echo "🚀 Pour démarrer le serveur :"
echo "   cd $(pwd)"
echo "   php spark serve --port=8080 --host=0.0.0.0"
echo ""
echo "📝 Notes :"
echo "   - L'ancien projet a été sauvegardé avec l'extension _backup"
echo "   - Vérifiez les paramètres de connexion dans app/Config/Database.php"
echo "   - Testez l'application avant de supprimer les sauvegardes"
