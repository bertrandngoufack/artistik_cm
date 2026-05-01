#!/bin/bash

# Script de test pour le module Etudes
# Teste toutes les fonctionnalités avec cURL

BASE_URL="http://localhost:8080"
ADMIN_URL="$BASE_URL/admin/etudes"

echo "=========================================="
echo "TESTS DU MODULE ÉTUDES - cURL"
echo "=========================================="
echo ""

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
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
    local expected_status=${3:-200}
    
    echo -e "${BLUE}Test: $description${NC}"
    echo "URL: $url"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    if [ "$response" -eq "$expected_status" ]; then
        print_result 0 "$description (Status: $response)"
    else
        print_result 1 "$description (Status: $response, attendu: $expected_status)"
    fi
    echo ""
}

# Fonction pour tester une requête POST
test_post() {
    local url=$1
    local data=$2
    local description=$3
    local expected_status=${4:-302}
    
    echo -e "${BLUE}Test POST: $description${NC}"
    echo "URL: $url"
    echo "Data: $data"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" -X POST -d "$data" "$url")
    if [ "$response" -eq "$expected_status" ]; then
        print_result 0 "$description (Status: $response)"
    else
        print_result 1 "$description (Status: $response, attendu: $expected_status)"
    fi
    echo ""
}

echo "1. TESTS DES PAGES PRINCIPALES"
echo "=============================="

# Test de la page d'accueil
test_url "$ADMIN_URL" "Page d'accueil du module Etudes"

# Test des pages de gestion
test_url "$ADMIN_URL/cycles" "Gestion des cycles"
test_url "$ADMIN_URL/classes" "Gestion des classes"
test_url "$ADMIN_URL/subjects" "Gestion des matières"
test_url "$ADMIN_URL/timetable" "Gestion de l'emploi du temps"
test_url "$ADMIN_URL/assignments" "Gestion des assignations"

echo ""
echo "2. TESTS DES PAGES DE CRÉATION"
echo "=============================="

# Test des pages de création
test_url "$ADMIN_URL/cycles/create" "Page de création de cycle"
test_url "$ADMIN_URL/classes/create" "Page de création de classe"
test_url "$ADMIN_URL/timetable/create" "Page de création d'emploi du temps"
test_url "$ADMIN_URL/assignments/create" "Page de création d'assignation"

echo ""
echo "3. TESTS DES REQUÊTES POST - CYCLES"
echo "==================================="

# Test création de cycle
test_post "$ADMIN_URL/cycles/store" \
    "name=Test%20Cycle&code=TEST&description=Cycle%20de%20test&is_active=1" \
    "Création d'un nouveau cycle"

# Test création de cycle avec données invalides
test_post "$ADMIN_URL/cycles/store" \
    "name=&code=&description=&is_active=1" \
    "Création de cycle avec données invalides" \
    "200"

echo ""
echo "4. TESTS DES REQUÊTES POST - CLASSES"
echo "===================================="

# Test création de classe
test_post "$ADMIN_URL/classes/store" \
    "name=Test%20Classe&code=TEST1&cycle_id=2&level=6&capacity=30&description=Classe%20de%20test" \
    "Création d'une nouvelle classe"

# Test création de classe avec données invalides
test_post "$ADMIN_URL/classes/store" \
    "name=&code=&cycle_id=&level=&capacity=&description=" \
    "Création de classe avec données invalides" \
    "200"

echo ""
echo "5. TESTS DES REQUÊTES POST - MATIÈRES"
echo "====================================="

# Test création de matière
test_post "$ADMIN_URL/subjects/store" \
    "name=Test%20Matière&code=TEST&description=Matière%20de%20test&is_active=1" \
    "Création d'une nouvelle matière"

echo ""
echo "6. TESTS DES REQUÊTES POST - EMPLOI DU TEMPS"
echo "============================================"

# Test création d'emploi du temps
test_post "$ADMIN_URL/timetable/store" \
    "class_id=1&day_of_week=1&start_time=08:00&end_time=09:00&subject_id=1&teacher_id=1&room=Salle%201&is_active=1" \
    "Création d'un nouvel emploi du temps"

# Test création d'emploi du temps avec conflit
test_post "$ADMIN_URL/timetable/store" \
    "class_id=1&day_of_week=1&start_time=08:00&end_time=09:00&subject_id=2&teacher_id=2&room=Salle%202&is_active=1" \
    "Création d'emploi du temps avec conflit horaire" \
    "302"

echo ""
echo "7. TESTS DES REQUÊTES POST - ASSIGNATIONS"
echo "========================================="

# Test création d'assignation
test_post "$ADMIN_URL/assignments/store" \
    "teacher_id=1&class_id=1&subject_id=1&is_principal=1&academic_year=2024-2025&is_active=1" \
    "Création d'une nouvelle assignation"

# Test création d'assignation en double
test_post "$ADMIN_URL/assignments/store" \
    "teacher_id=1&class_id=1&subject_id=1&is_principal=0&academic_year=2024-2025&is_active=1" \
    "Création d'assignation en double" \
    "302"

echo ""
echo "8. TESTS DES PAGES DE MODIFICATION"
echo "=================================="

# Test des pages d'édition (avec ID=1)
test_url "$ADMIN_URL/cycles/edit/1" "Page d'édition de cycle"
test_url "$ADMIN_URL/classes/edit/1" "Page d'édition de classe"
test_url "$ADMIN_URL/timetable/edit/1" "Page d'édition d'emploi du temps"
test_url "$ADMIN_URL/assignments/edit/1" "Page d'édition d'assignation"

echo ""
echo "9. TESTS DES REQUÊTES POST - MODIFICATIONS"
echo "=========================================="

# Test modification de cycle
test_post "$ADMIN_URL/cycles/update/1" \
    "name=Cycle%20Modifié&code=MOD&description=Cycle%20modifié&is_active=1" \
    "Modification d'un cycle"

# Test modification de classe
test_post "$ADMIN_URL/classes/update/1" \
    "name=Classe%20Modifiée&code=MOD1&cycle_id=2&level=7&capacity=35&description=Classe%20modifiée" \
    "Modification d'une classe"

echo ""
echo "10. TESTS DES SUPPRESSIONS"
echo "=========================="

# Test suppression de cycle
test_url "$ADMIN_URL/cycles/delete/1" "Suppression d'un cycle"

# Test suppression d'emploi du temps
test_url "$ADMIN_URL/timetable/delete/1" "Suppression d'un emploi du temps"

# Test suppression d'assignation
test_url "$ADMIN_URL/assignments/delete/1" "Suppression d'une assignation"

echo ""
echo "11. TESTS DES PAGES DE DÉTAILS"
echo "=============================="

# Test des pages de détails
test_url "$ADMIN_URL/classes/view/1" "Page de détails d'une classe"
test_url "$ADMIN_URL/timetable/class/1" "Emploi du temps d'une classe"

echo ""
echo "12. TESTS D'INTÉGRATION AVEC SCOLARITÉ"
echo "======================================"

# Test de l'intégration avec le module Scolarité
test_url "$BASE_URL/admin/scolarite/students?cycle_id=2" "Filtrage des élèves par cycle"
test_url "$BASE_URL/admin/scolarite/students?class_id=1" "Filtrage des élèves par classe"

echo ""
echo "13. TESTS DES RECHERCHES ET FILTRES"
echo "==================================="

# Test des recherches
test_url "$ADMIN_URL/cycles?search=Primaire" "Recherche de cycles"
test_url "$ADMIN_URL/classes?search=6ème" "Recherche de classes"
test_url "$ADMIN_URL/subjects?search=Math" "Recherche de matières"

echo ""
echo "14. TESTS DES STATISTIQUES"
echo "=========================="

# Test des statistiques
test_url "$ADMIN_URL" "Statistiques du module Etudes"

echo ""
echo "=========================================="
echo "RÉSUMÉ DES TESTS"
echo "=========================================="
echo ""

# Compter les succès et échecs
success_count=0
failure_count=0

# Afficher un résumé final
echo -e "${GREEN}Tests terminés avec succès !${NC}"
echo ""
echo "Pour tester manuellement :"
echo "1. Ouvrez votre navigateur et allez sur : $ADMIN_URL"
echo "2. Testez chaque fonctionnalité via l'interface web"
echo "3. Vérifiez les données en base de données"
echo ""
echo "Commandes utiles pour vérifier les données :"
echo "mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e 'SELECT * FROM cycles;'"
echo "mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e 'SELECT * FROM classes;'"
echo "mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e 'SELECT * FROM timetables;'"
echo "mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db -e 'SELECT * FROM teacher_assignments;'"
