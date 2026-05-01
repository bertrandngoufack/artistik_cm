#!/bin/bash
# Script de démarrage forcé du serveur KISSAI SCHOOL sur le port 8080
# Administrateur Système Senior - Solution Définitive
echo "🚀 FORÇAGE DU SERVEUR CODEIGNITER - PORT 8080"
echo "=============================================="

# Arrêt des processus existants
echo "🛑 Arrêt des processus existants..."
pkill -f "spark serve" 2>/dev/null
pkill -f "php -S" 2>/dev/null
sleep 2

# Libération des ports
echo "🔍 Libération des ports..."
for port in 8080 8081 8080 8083 8084 8085 8086 8087; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo "⚠️  Port $port occupé, libération..."
        sudo fuser -k $port/tcp 2>/dev/null
    fi
done

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
rm -rf writable/cache/*
rm -rf writable/logs/*

# Création du script de démarrage forcé
echo "🔧 Création du script de démarrage forcé..."
cat > start_forced_8080.php << 'EOF'
<?php
// Script de démarrage forcé pour le port 8080
$host = '0.0.0.0';
$port = 8080;
$documentRoot = __DIR__ . '/public';

echo "🚀 Démarrage du serveur forcé sur $host:$port\n";
echo "📁 Document root: $documentRoot\n";
echo "🛑 Pour arrêter: Ctrl+C\n\n";

// Démarrage du serveur avec le document root spécifique
$command = "php -S $host:$port -t $documentRoot $documentRoot/index.php";
system($command);
EOF

# Démarrage du serveur forcé
echo "🚀 Démarrage du serveur forcé..."
php start_forced_8080.php

echo ""
echo "✅ Serveur arrêté"





