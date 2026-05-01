#!/bin/bash
# Script de démarrage simple du serveur KISSAI SCHOOL sur le port 8080
echo "🚀 DÉMARRAGE DU SERVEUR KISSAI SCHOOL - PORT 8080"
echo "=================================================="

# Arrêt des processus existants
echo "🛑 Arrêt des processus PHP existants..."
pkill -f "spark serve" 2>/dev/null
pkill -f "php -S" 2>/dev/null
sleep 2

# Libération du port 8080
echo "🔍 Libération du port 8080..."
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "⚠️  Port 8080 occupé, libération..."
    sudo fuser -k 8080/tcp 2>/dev/null
fi

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
rm -rf writable/cache/*
rm -rf writable/logs/*

# Démarrage du serveur sur le port 8080
echo "🚀 Démarrage du serveur sur le port 8080..."
echo "📡 URL: http://localhost:8080"
echo "🛑 Pour arrêter: Ctrl+C"
echo ""

# Utilisation du serveur PHP avec le router personnalisé
php -S 0.0.0.0:8080 -t public/ public/router.php

echo ""
echo "✅ Serveur arrêté"
