#!/bin/bash

# 🚀 SCRIPT DE RESTAURATION - KISSAI SCHOOL
# Date: 23 Août 2025
# Version: CodeIgniter 4 - Module Études Finalisé

echo "=== RESTAURATION KISSAI SCHOOL ==="
echo "Date: $(date)"
echo ""

# Variables
BACKUP_DIR="$(dirname "$0")"
PROJECT_NAME="codeigniter4-framework-68d1a58"
DB_HOST="100.69.65.33"
DB_PORT="13306"
DB_NAME="lycol_db"
DB_USER="root"
DB_PASS="Bateau123"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_status() {
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

# Vérification des fichiers de sauvegarde
print_status "Vérification des fichiers de sauvegarde..."

if [ ! -f "$BACKUP_DIR/codeigniter4_project.tar.gz" ]; then
    print_error "Fichier codeigniter4_project.tar.gz introuvable!"
    exit 1
fi

if [ ! -f "$BACKUP_DIR/database_backup.sql" ]; then
    print_error "Fichier database_backup.sql introuvable!"
    exit 1
fi

print_success "Fichiers de sauvegarde trouvés"

# 1. RESTAURATION DU CODE SOURCE
print_status "Restauration du code source..."

# Vérifier si le projet existe déjà
if [ -d "$PROJECT_NAME" ]; then
    print_warning "Le projet $PROJECT_NAME existe déjà. Sauvegarde de l'ancienne version..."
    mv "$PROJECT_NAME" "${PROJECT_NAME}_backup_$(date +%Y%m%d_%H%M%S)"
fi

# Extraire le projet
tar -xzf "$BACKUP_DIR/codeigniter4_project.tar.gz"
if [ $? -eq 0 ]; then
    print_success "Code source restauré avec succès"
else
    print_error "Erreur lors de l'extraction du code source"
    exit 1
fi

# 2. INSTALLATION DES DÉPENDANCES
print_status "Installation des dépendances Composer..."

cd "$PROJECT_NAME"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
    if [ $? -eq 0 ]; then
        print_success "Dépendances installées"
    else
        print_warning "Erreur lors de l'installation des dépendances"
    fi
else
    print_warning "Composer non trouvé. Veuillez l'installer manuellement."
fi

# 3. CONFIGURATION DE L'ENVIRONNEMENT
print_status "Configuration de l'environnement..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "Fichier .env créé à partir de .env.example"
    else
        print_warning "Fichier .env.example non trouvé. Création manuelle requise."
    fi
fi

# 4. RESTAURATION DE LA BASE DE DONNÉES
print_status "Restauration de la base de données..."

# Vérifier la connexion à la base de données
if command -v mysql &> /dev/null; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null
    if [ $? -eq 0 ]; then
        print_status "Connexion à la base de données réussie"
        
        # Restaurer la base de données
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_DIR/database_backup.sql"
        if [ $? -eq 0 ]; then
            print_success "Base de données restaurée avec succès"
        else
            print_error "Erreur lors de la restauration de la base de données"
        fi
    else
        print_error "Impossible de se connecter à la base de données"
        print_status "Vérifiez les paramètres de connexion:"
        echo "  Host: $DB_HOST"
        echo "  Port: $DB_PORT"
        echo "  Database: $DB_NAME"
        echo "  User: $DB_USER"
    fi
else
    print_warning "MySQL client non trouvé. Restauration manuelle requise."
fi

# 5. CONFIGURATION DES PERMISSIONS
print_status "Configuration des permissions..."

# Permissions pour les dossiers writable
chmod -R 755 writable/
chmod -R 777 writable/cache/
chmod -R 777 writable/logs/
chmod -R 777 writable/session/
chmod -R 777 writable/uploads/

print_success "Permissions configurées"

# 6. VÉRIFICATION FINALE
print_status "Vérification finale..."

# Vérifier que les fichiers essentiels existent
if [ -f "app/Config/Routes.php" ]; then
    print_success "Routes configurées"
else
    print_error "Fichier Routes.php manquant"
fi

if [ -f "public/index.php" ]; then
    print_success "Point d'entrée trouvé"
else
    print_error "Point d'entrée manquant"
fi

# 7. INSTRUCTIONS FINALES
echo ""
echo "=== RESTAURATION TERMINÉE ==="
echo ""
print_status "Instructions pour démarrer l'application:"
echo ""
echo "1. Aller dans le dossier du projet:"
echo "   cd $PROJECT_NAME"
echo ""
echo "2. Configurer le fichier .env avec vos paramètres:"
echo "   nano .env"
echo ""
echo "3. Démarrer le serveur:"
echo "   php spark serve --port=8080 --host=0.0.0.0"
echo ""
echo "4. Accéder à l'application:"
echo "   http://localhost:8080"
echo ""
print_success "Restauration terminée avec succès! 🎉"
echo ""


