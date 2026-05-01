#!/bin/bash

# Script de démarrage du serveur KISSAI SCHOOL sur le port 8080
# Administrateur Système Senior - Solution Définitive

echo "🚀 DÉMARRAGE DU SERVEUR KISSAI SCHOOL - PORT 8080"
echo "=================================================="

# Arrêter tous les processus PHP existants
echo "🛑 Arrêt des processus PHP existants..."
pkill -f "spark serve" 2>/dev/null
pkill -f "php -S" 2>/dev/null
sleep 2

# Vérifier que les ports sont libres
echo "🔍 Vérification des ports..."
for port in 8080 8081 8080 8083 8084 8085 8086 8087; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo "⚠️  Port $port occupé, libération..."
        sudo fuser -k $port/tcp 2>/dev/null
    fi
done

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
rm -rf writable/cache/*
rm -rf writable/logs/*

# Démarrer le serveur avec PHP intégré sur le port 8080
echo "🚀 Démarrage du serveur sur le port 8080..."
echo "📡 URL: http://localhost:8080"
echo "🛑 Pour arrêter: Ctrl+C"
echo ""

# Démarrer le serveur PHP avec le router personnalisé
php -S localhost:8080 -t public/ public/router.php

echo ""
echo "✅ Serveur arrêté"
