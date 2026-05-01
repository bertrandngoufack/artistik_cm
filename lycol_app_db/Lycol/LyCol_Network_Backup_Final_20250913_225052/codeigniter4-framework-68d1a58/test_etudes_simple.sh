#!/bin/bash

# Script de test simplifié pour le module Etudes
# Teste les fonctionnalités principales avec cURL

BASE_URL="http://localhost:8080"
ADMIN_URL="$BASE_URL/admin/etudes"

echo "=========================================="
echo "TESTS SIMPLIFIÉS DU MODULE ÉTUDES"
echo "=========================================="
echo ""

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les résultats
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ SUCCÈS${NC} - $2"
    else
        echo -e "${RED}✗ ÉCHEC${NC} - $2"
    fi
}

# Fonction pour tester une URL
test_url() {
    local url=$1
    local description=$2
    
    echo -e "${BLUE}Test: $description${NC}"
    echo "URL: $url"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    if [ "$response" -eq "200" ]; then
        print_result 0 "$description (Status: $response)"
    else
        print_result 1 "$description (Status: $response)"
    fi
    echo ""
}

# Fonction pour tester une requête POST
test_post() {
    local url=$1
    local data=$2
    local description=$3
    
    echo -e "${BLUE}Test POST: $description${NC}"
    echo "URL: $url"
    echo "Data: $data"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" -X POST -d "$data" "$url")
    if [ "$response" -eq "302" ] || [ "$response" -eq "303" ]; then
        print_result 0 "$description (Status: $response - Redirection)"
    else
        print_result 1 "$description (Status: $response)"
    fi
    echo ""
}

echo "1. TESTS DES PAGES PRINCIPALES"
echo "=============================="

# Test de la page d'accueil
test_url "$ADMIN_URL" "Page d'accueil du module Etudes"

# Test des pages de gestion
test_url "$ADMIN_URL/cycles" "Gestion des cycles"
test_url "$ADMIN_URL/cycles/create" "Page de création de cycle"

echo ""
echo "2. TESTS DES REQUÊTES POST - CYCLES"
echo "==================================="

# Test création de cycle
test_post "$ADMIN_URL/cycles/store" \
    "name=Cycle%20Test%20cURL&code=CTEST&description=Cycle%20testé%20via%20cURL&is_active=1" \
    "Création d'un nouveau cycle via cURL"

echo ""
echo "3. TESTS D'INTÉGRATION AVEC SCOLARITÉ"
echo "====================================="

# Test de l'intégration avec le module Scolarité
test_url "$BASE_URL/admin/scolarite/students?cycle_id=2" "Filtrage des élèves par cycle"
test_url "$BASE_URL/admin/scolarite/students?class_id=1" "Filtrage des élèves par classe"

echo ""
echo "4. VÉRIFICATION DES DONNÉES EN BASE"
echo "==================================="

echo "Vérification des cycles en base de données :"
mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e "SELECT id, name, code FROM cycles ORDER BY id;" 2>/dev/null

echo ""
echo "Vérification des classes en base de données :"
mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e "SELECT id, name, code, cycle_id FROM classes ORDER BY id LIMIT 5;" 2>/dev/null

echo ""
echo "=========================================="
echo "RÉSUMÉ DES TESTS"
echo "=========================================="
echo ""

echo -e "${GREEN}Tests terminés !${NC}"
echo ""
echo "Pour tester manuellement :"
echo "1. Ouvrez votre navigateur et allez sur : $ADMIN_URL"
echo "2. Testez chaque fonctionnalité via l'interface web"
echo "3. Vérifiez que les données sont bien créées en base"
echo ""
echo "URLs importantes :"
echo "- Dashboard Etudes: $ADMIN_URL"
echo "- Gestion Cycles: $ADMIN_URL/cycles"
echo "- Gestion Classes: $ADMIN_URL/classes"
echo "- Gestion Matières: $ADMIN_URL/subjects"
echo "- Emploi du temps: $ADMIN_URL/timetable"
echo "- Assignations: $ADMIN_URL/assignments"
echo ""
echo "Intégration avec Scolarité :"
echo "- Filtrage par cycle: $BASE_URL/admin/scolarite/students?cycle_id=2"
echo "- Filtrage par classe: $BASE_URL/admin/scolarite/students?class_id=1"
