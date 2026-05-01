#!/bin/bash

# LyCol - Script de Démarrage Serveur Réseau Automatique
# Détection automatique des IPs et démarrage sur 0.0.0.0
# Date: 13 Septembre 2025

echo "🚀 LyCol - Démarrage automatique du serveur réseau..."
echo "====================================================="

# Variables de configuration
PORT=8080
PROJECT_DIR="codeigniter4-framework-68d1a58"
PUBLIC_DIR="$PROJECT_DIR/public"

# Fonction de nettoyage
cleanup() {
    echo ""
    echo "🛑 Arrêt du serveur..."
    pkill -f "php.*:$PORT" 2>/dev/null
    echo "✅ Serveur arrêté proprement"
    exit 0
}

# Intercepter Ctrl+C
trap cleanup SIGINT SIGTERM

# Vérification des prérequis
echo "🔍 Vérification des prérequis..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé"
    exit 1
fi

if [ ! -d "$PROJECT_DIR" ]; then
    echo "❌ Dossier du projet '$PROJECT_DIR' introuvable"
    exit 1
fi

if [ ! -d "$PUBLIC_DIR" ]; then
    echo "❌ Dossier public '$PUBLIC_DIR' introuvable"
    exit 1
fi

echo "✅ Prérequis validés"

# Arrêter les serveurs existants
echo "🛑 Arrêt des serveurs existants sur le port $PORT..."
pkill -f "php.*:$PORT" 2>/dev/null
sleep 2

# Détection automatique des IPs
echo "🌐 Détection automatique des adresses IP..."
IP_ADDRESSES=$(hostname -I 2>/dev/null || ip route get 1.1.1.1 2>/dev/null | grep -oP 'src \K\S+' || echo "localhost")

echo "📍 Adresses IP détectées: $IP_ADDRESSES"

# Démarrer le serveur sur toutes les interfaces
echo "🚀 Démarrage du serveur sur 0.0.0.0:$PORT..."
cd "$PUBLIC_DIR"

# Démarrer le serveur en arrière-plan
php -S 0.0.0.0:$PORT -t . ../system/rewrite.php &
SERVER_PID=$!

# Attendre que le serveur démarre
sleep 3

# Vérifier si le serveur fonctionne
if ps -p $SERVER_PID > /dev/null; then
    echo "✅ Serveur démarré avec succès (PID: $SERVER_PID)"
    echo ""
    echo "🌍 URLs d'accès détectées automatiquement:"
    echo "   Local:    http://localhost:$PORT"
    
    # Afficher les URLs pour chaque IP détectée
    for ip in $IP_ADDRESSES; do
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "   Réseau:   http://$ip:$PORT"
        fi
    done
    
    echo ""
    echo "🔐 Pages importantes:"
    for ip in $IP_ADDRESSES; do
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "   Connexion:  http://$ip:$PORT/auth/login"
            echo "   Dashboard:  http://$ip:$PORT/admin/dashboard"
            break
        fi
    done
    
    echo ""
    echo "📊 Configuration:"
    echo "   Port d'écoute: $PORT (toutes interfaces - 0.0.0.0)"
    echo "   Répertoire:    $(pwd)"
    echo "   PHP version:   $(php -v | head -n1 | cut -d' ' -f2)"
    echo "   Auto-détection: IPs mises à jour automatiquement"
    
    echo ""
    echo "🎛️  Contrôles:"
    echo "   Ctrl+C : Arrêter le serveur"
    echo "   Le serveur s'adapte automatiquement aux changements d'IP"
    echo ""
    echo "📈 Logs du serveur:"
    echo "==================="
    
    # Attendre et afficher les logs
    wait $SERVER_PID
else
    echo "❌ Échec du démarrage du serveur"
    exit 1
fi
