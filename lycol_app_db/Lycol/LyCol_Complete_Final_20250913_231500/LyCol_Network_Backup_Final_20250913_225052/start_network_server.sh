#!/bin/bash

# LyCol - Script de Démarrage Serveur Réseau
# Configuration pour accès via toutes les interfaces réseau
# Date: 13 Septembre 2025

echo "🚀 LyCol - Démarrage du serveur réseau..."
echo "=========================================="

# Variables de configuration
PORT=8080
PROJECT_DIR="codeigniter4-framework-68d1a58"
PUBLIC_DIR="$PROJECT_DIR/public"

# Vérification des prérequis
echo "🔍 Vérification des prérequis..."

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé ou n'est pas dans le PATH"
    exit 1
fi

echo "✅ PHP version: $(php -v | head -n1)"

# Vérifier si le dossier du projet existe
if [ ! -d "$PROJECT_DIR" ]; then
    echo "❌ Dossier du projet '$PROJECT_DIR' introuvable"
    echo "💡 Assurez-vous d'être dans le bon répertoire"
    exit 1
fi

echo "✅ Dossier du projet trouvé: $PROJECT_DIR"

# Vérifier si le dossier public existe
if [ ! -d "$PUBLIC_DIR" ]; then
    echo "❌ Dossier public '$PUBLIC_DIR' introuvable"
    exit 1
fi

echo "✅ Dossier public trouvé: $PUBLIC_DIR"

# Arrêter les serveurs existants sur le port
echo "🛑 Arrêt des serveurs existants sur le port $PORT..."
pkill -f "php.*:$PORT" 2>/dev/null
pkill -f "php.*serve.*$PORT" 2>/dev/null
sleep 2

# Vérifier si le port est libre
if netstat -tlnp 2>/dev/null | grep -q ":$PORT "; then
    echo "⚠️  Le port $PORT est encore occupé"
    echo "🔄 Tentative de libération..."
    sudo lsof -ti:$PORT | xargs sudo kill -9 2>/dev/null
    sleep 2
fi

# Obtenir l'adresse IP du serveur
echo "🌐 Détection des adresses IP..."
IP_ADDRESSES=$(hostname -I)
echo "📍 Adresses IP disponibles: $IP_ADDRESSES"

# Démarrer le serveur sur toutes les interfaces
echo "🚀 Démarrage du serveur sur 0.0.0.0:$PORT..."
cd "$PUBLIC_DIR"

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

# Démarrer le serveur en arrière-plan et capturer le PID
php -S 0.0.0.0:$PORT -t . ../system/rewrite.php &
SERVER_PID=$!

# Attendre que le serveur démarre
sleep 3

# Vérifier si le serveur fonctionne
if ps -p $SERVER_PID > /dev/null; then
    echo "✅ Serveur démarré avec succès (PID: $SERVER_PID)"
    echo ""
    echo "🌍 URLs d'accès:"
    echo "   Local:    http://localhost:$PORT"
    
    # Afficher les URLs pour chaque IP
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
    echo "📊 Informations système:"
    echo "   Port d'écoute: $PORT (toutes interfaces)"
    echo "   Répertoire:    $(pwd)"
    echo "   PHP version:   $(php -v | head -n1 | cut -d' ' -f2)"
    
    echo ""
    echo "🎛️  Contrôles disponibles:"
    echo "   Ctrl+C : Arrêter le serveur"
    echo ""
    echo "📈 Logs du serveur:"
    echo "==================="
    
    # Attendre et afficher les logs
    wait $SERVER_PID
else
    echo "❌ Échec du démarrage du serveur"
    exit 1
fi
