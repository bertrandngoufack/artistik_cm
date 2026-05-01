#!/bin/bash

# LyCol - Script de Démarrage Serveur Réseau
echo "🚀 LyCol - Démarrage du serveur réseau sur toutes les interfaces..."

# Configuration
PORT=8080
PROJECT_DIR="codeigniter4-framework-68d1a58"

# Arrêter les serveurs existants
echo "🛑 Arrêt des serveurs existants..."
pkill -f "php.*:$PORT" 2>/dev/null
sleep 2

# Obtenir les IPs
IP_ADDRESSES=$(hostname -I)
echo "📍 Adresses IP: $IP_ADDRESSES"

# Démarrer le serveur
echo "🚀 Démarrage sur 0.0.0.0:$PORT..."
cd "$PROJECT_DIR/public"
php -S 0.0.0.0:$PORT -t . ../system/rewrite.php &

echo "✅ Serveur démarré!"
echo "🌍 Accès local: http://localhost:$PORT"
for ip in $IP_ADDRESSES; do
    if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "🌐 Accès réseau: http://$ip:$PORT"
        break
    fi
done

wait
