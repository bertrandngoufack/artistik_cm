#!/bin/bash
# Script de démarrage définitif du serveur KISSAI SCHOOL sur le port 8080
# Administrateur Système Senior - Solution Définitive
echo "🚀 DÉMARRAGE DÉFINITIF DU SERVEUR KISSAI SCHOOL - PORT 8080"
echo "============================================================="

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
    sleep 1
fi

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
rm -rf writable/cache/*
rm -rf writable/logs/*

# Vérification de la configuration
echo "⚙️  Vérification de la configuration..."
if grep -q "baseURL.*8080" app/Config/App.php; then
    echo "✅ Configuration baseURL correcte (port 8080)"
else
    echo "❌ Configuration baseURL incorrecte, correction..."
    sed -i 's/baseURL.*=.*/baseURL = '\''http:\/\/localhost:8080\/'\'';/' app/Config/App.php
fi

# Démarrage du serveur avec spark serve
echo "🚀 Démarrage du serveur avec spark serve..."
echo "📡 URL: http://localhost:8080"
echo "🛑 Pour arrêter: Ctrl+C"
echo ""

# Forcer le port 8080 avec des variables d'environnement
export CI_ENVIRONMENT=development
export SPARK_PORT=8080
export SPARK_HOST=0.0.0.0

# Démarrage du serveur
php spark serve --port=8080 --host=0.0.0.0

echo ""
echo "✅ Serveur arrêté"





