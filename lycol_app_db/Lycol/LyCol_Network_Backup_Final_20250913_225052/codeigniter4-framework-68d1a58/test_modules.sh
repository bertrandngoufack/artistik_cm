#!/bin/bash

# Script de test des modules LyCol
# KISSAI SCHOOL - Système de Gestion Scolaire

echo "🧪 TEST DES MODULES LYSCOL"
echo "==========================="
echo ""

# Configuration
BASE_URL="http://localhost:8080"
TIMEOUT=10

# Fonction de test
test_module() {
    local module_name=$1
    local url=$2
    local expected_code=${3:-200}
    
    echo -n "🔍 Test du module $module_name... "
    
    # Test avec curl
    response_code=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout $TIMEOUT "$url" 2>/dev/null)
    
    if [ "$response_code" = "$expected_code" ]; then
        echo "✅ OK ($response_code)"
        return 0
    else
        echo "❌ ERREUR ($response_code)"
        return 1
    fi
}

# Tests des modules principaux
echo "📊 TESTS DES MODULES PRINCIPAUX"
echo "-------------------------------"

test_module "Statistiques" "$BASE_URL/admin/statistiques"
test_module "Economat" "$BASE_URL/admin/economat"
test_module "Scolarité" "$BASE_URL/admin/scolarite"
test_module "Études" "$BASE_URL/admin/etudes"
test_module "Examens" "$BASE_URL/admin/examens"
test_module "Enseignants" "$BASE_URL/admin/enseignants"

echo ""
echo "🔧 TESTS DES MODULES AVANCÉS"
echo "----------------------------"

test_module "Bibliothèque" "$BASE_URL/admin/bibliotheque"
test_module "Messagerie" "$BASE_URL/admin/messagerie"
test_module "Sécurité" "$BASE_URL/admin/securite"
test_module "Configuration" "$BASE_URL/admin/configuration"

echo ""
echo "🏠 TESTS DES PAGES DE BASE"
echo "-------------------------"

test_module "Accueil" "$BASE_URL/"
test_module "Admin" "$BASE_URL/admin"
test_module "Authentification" "$BASE_URL/auth/login"

echo ""
echo "📋 RÉSUMÉ DES TESTS"
echo "==================="

# Compter les succès et échecs
success_count=0
total_count=0

for module in "Statistiques" "Economat" "Scolarité" "Études" "Examens" "Enseignants" "Bibliothèque" "Messagerie" "Sécurité" "Configuration" "Accueil" "Admin" "Authentification"; do
    total_count=$((total_count + 1))
    if test_module "$module" "$BASE_URL/admin/${module,,}" >/dev/null 2>&1; then
        success_count=$((success_count + 1))
    fi
done

echo ""
echo "📊 RÉSULTATS FINAUX"
echo "==================="
echo "✅ Modules fonctionnels: $success_count/$total_count"
echo "📈 Taux de réussite: $((success_count * 100 / total_count))%"

if [ $success_count -eq $total_count ]; then
    echo "🎉 TOUS LES MODULES FONCTIONNENT PARFAITEMENT !"
    exit 0
else
    echo "⚠️  Certains modules nécessitent une attention"
    exit 1
fi






