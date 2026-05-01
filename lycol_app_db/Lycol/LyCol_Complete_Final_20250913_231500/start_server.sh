#!/bin/bash

# Script de démarrage du serveur LyCol
# KISSAI SCHOOL - Système de Gestion Scolaire

echo "🚀 Démarrage du serveur LyCol..."
echo "=================================="

# Arrêter tous les processus PHP existants
echo "🛑 Arrêt des processus existants..."
pkill -f "php.*serve" 2>/dev/null
pkill -f "php.*-S" 2>/dev/null

# Attendre un peu
sleep 2

# Vérifier que le port 8080 est libre
if netstat -tlnp 2>/dev/null | grep -q ":8080"; then
    echo "❌ Le port 8080 est déjà utilisé"
    echo "🔍 Processus utilisant le port 8080:"
    netstat -tlnp 2>/dev/null | grep ":8080"
    echo "🛑 Arrêt forcé des processus sur le port 8080..."
    sudo pkill -f ".*:8080" 2>/dev/null
    sleep 2
fi

# Démarrer le serveur
echo "✅ Démarrage du serveur sur http://localhost:8080"
echo "📊 Module Statistiques: http://localhost:8080/admin/statistiques"
echo "💰 Module Economat: http://localhost:8080/admin/economat"
echo "🎓 Module Scolarité: http://localhost:8080/admin/scolarite"
echo "📚 Module Études: http://localhost:8080/admin/etudes"
echo "📝 Module Examens: http://localhost:8080/admin/examens"
echo "👨‍🏫 Module Enseignants: http://localhost:8080/admin/enseignants"
echo ""
echo "🔄 Pour arrêter le serveur: Ctrl+C"
echo ""

# Démarrer le serveur PHP
php -S localhost:8080 -t public/

echo ""
echo "✅ Serveur arrêté"






