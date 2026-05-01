#!/bin/bash

# Script pour forcer le serveur CodeIgniter sur le port 8080
# Solution définitive - Administrateur Système Senior

echo "🚀 FORÇAGE DU SERVEUR CODEIGNITER - PORT 8080"
echo "=============================================="

# Arrêter tous les processus existants
echo "🛑 Arrêt des processus existants..."
pkill -f "spark serve" 2>/dev/null
pkill -f "php -S" 2>/dev/null
sleep 2

# Libérer tous les ports
echo "🔍 Libération des ports..."
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

# Créer un script PHP temporaire pour forcer le port
echo "🔧 Création du script de démarrage forcé..."
cat > force_port_8082.php << 'EOF'
<?php
// Script pour forcer le serveur sur le port 8080
$port = 8082;
$host = '0.0.0.0';

// Vérifier si le port est libre
$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("Impossible de créer le socket\n");
}

$result = @socket_bind($socket, $host, $port);
if ($result === false) {
    die("Port $port déjà occupé\n");
}

socket_close($socket);

// Démarrer le serveur avec le port forcé
$command = "php -S $host:$port -t public/ public/index.php";
echo "🚀 Démarrage du serveur sur le port $port...\n";
echo "📡 URL: http://localhost:$port\n";
echo "🛑 Pour arrêter: Ctrl+C\n\n";

passthru($command);
EOF

# Exécuter le script
echo "🚀 Démarrage du serveur forcé..."
php force_port_8082.php

echo ""
echo "✅ Serveur arrêté"





