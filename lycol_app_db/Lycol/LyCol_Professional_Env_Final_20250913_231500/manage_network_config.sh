#!/bin/bash

# LyCol - Gestionnaire de Configuration Réseau Intelligent
# Gestion professionnelle des IPs via fichier .env
# Date: 13 Septembre 2025

SCRIPT_NAME="LyCol Network Manager"
ENV_FILE=".env"
ENV_TEMPLATE=".env.network.template"
PROJECT_DIR="codeigniter4-framework-68d1a58"

echo "🔧 $SCRIPT_NAME"
echo "=================================="

# Fonction d'aide
show_help() {
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  --auto-detect    Active l'auto-détection d'IP"
    echo "  --set-ip IP      Configure une IP fixe"
    echo "  --show-config    Affiche la configuration actuelle"
    echo "  --reset          Remet la configuration par défaut"
    echo "  --start-server   Démarre le serveur avec la config"
    echo "  --help           Affiche cette aide"
    echo ""
    echo "Exemples:"
    echo "  $0 --auto-detect"
    echo "  $0 --set-ip 192.168.1.50"
    echo "  $0 --start-server"
}

# Fonction de détection d'IP
detect_server_ips() {
    local ips=$(hostname -I 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' | sort -u)
    echo "$ips"
}

# Fonction de configuration auto-détection
configure_auto_detect() {
    echo "🎯 Configuration de l'auto-détection d'IP..."
    
    # Créer le .env s'il n'existe pas
    if [ ! -f "$ENV_FILE" ]; then
        if [ -f "$ENV_TEMPLATE" ]; then
            cp "$ENV_TEMPLATE" "$ENV_FILE"
            echo "✅ Fichier .env créé depuis le template"
        else
            create_default_env
        fi
    fi
    
    # Activer l'auto-détection
    if grep -q "APP_AUTO_DETECT_IP" "$ENV_FILE"; then
        sed -i 's/APP_AUTO_DETECT_IP=.*/APP_AUTO_DETECT_IP=true/' "$ENV_FILE"
    else
        echo "APP_AUTO_DETECT_IP=true" >> "$ENV_FILE"
    fi
    
    # Vider APP_BASE_URL pour forcer l'auto-détection
    if grep -q "APP_BASE_URL" "$ENV_FILE"; then
        sed -i 's/APP_BASE_URL=.*/APP_BASE_URL=/' "$ENV_FILE"
    else
        echo "APP_BASE_URL=" >> "$ENV_FILE"
    fi
    
    echo "✅ Auto-détection d'IP activée dans $ENV_FILE"
    echo ""
    echo "📍 IPs détectées sur ce serveur:"
    detect_server_ips | while read ip; do
        echo "   - http://$ip:8080"
    done
}

# Fonction de configuration IP fixe
configure_fixed_ip() {
    local fixed_ip="$1"
    
    echo "🎯 Configuration IP fixe: $fixed_ip"
    
    # Créer le .env s'il n'existe pas
    if [ ! -f "$ENV_FILE" ]; then
        create_default_env
    fi
    
    # Désactiver l'auto-détection
    if grep -q "APP_AUTO_DETECT_IP" "$ENV_FILE"; then
        sed -i 's/APP_AUTO_DETECT_IP=.*/APP_AUTO_DETECT_IP=false/' "$ENV_FILE"
    else
        echo "APP_AUTO_DETECT_IP=false" >> "$ENV_FILE"
    fi
    
    # Configurer l'IP fixe
    local base_url="http://$fixed_ip:8080/"
    if grep -q "APP_BASE_URL" "$ENV_FILE"; then
        sed -i "s|APP_BASE_URL=.*|APP_BASE_URL=$base_url|" "$ENV_FILE"
    else
        echo "APP_BASE_URL=$base_url" >> "$ENV_FILE"
    fi
    
    # Mettre à jour APP_HOST
    if grep -q "APP_HOST" "$ENV_FILE"; then
        sed -i "s/APP_HOST=.*/APP_HOST=$fixed_ip/" "$ENV_FILE"
    else
        echo "APP_HOST=$fixed_ip" >> "$ENV_FILE"
    fi
    
    echo "✅ IP fixe configurée: $base_url"
}

# Fonction de création du .env par défaut
create_default_env() {
    cat > "$ENV_FILE" << 'ENVEOF'
# Configuration LyCol générée automatiquement
CI_ENVIRONMENT=development
CI_DEBUG=true
APP_AUTO_DETECT_IP=true
APP_HOST=localhost
APP_PORT=8080
APP_PROTOCOL=http
APP_BASE_URL=
ENVEOF
    echo "✅ Fichier .env par défaut créé"
}

# Fonction d'affichage de la configuration
show_config() {
    echo "📋 Configuration actuelle:"
    echo ""
    
    if [ ! -f "$ENV_FILE" ]; then
        echo "❌ Fichier .env introuvable"
        return 1
    fi
    
    local auto_detect=$(grep "APP_AUTO_DETECT_IP" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2)
    local base_url=$(grep "APP_BASE_URL" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2)
    local host=$(grep "APP_HOST" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2)
    local port=$(grep "APP_PORT" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2)
    
    echo "Mode auto-détection: ${auto_detect:-non défini}"
    echo "URL de base: ${base_url:-non définie}"
    echo "Host: ${host:-non défini}"
    echo "Port: ${port:-non défini}"
    echo ""
    
    if [ "$auto_detect" = "true" ]; then
        echo "🌐 IPs disponibles pour l'auto-détection:"
        detect_server_ips | while read ip; do
            echo "   - http://$ip:8080"
        done
    fi
}

# Fonction de démarrage du serveur
start_server() {
    echo "🚀 Démarrage du serveur avec configuration .env..."
    
    if [ ! -d "$PROJECT_DIR" ]; then
        echo "❌ Dossier projet '$PROJECT_DIR' introuvable"
        exit 1
    fi
    
    # Afficher la config avant de démarrer
    show_config
    echo ""
    
    # Arrêter les serveurs existants
    pkill -f "php.*:8080" 2>/dev/null
    sleep 2
    
    # Démarrer le serveur
    echo "🌟 Serveur démarré sur 0.0.0.0:8080"
    echo "📍 Configuration gérée automatiquement par .env"
    echo "🎛️  Ctrl+C pour arrêter"
    echo ""
    
    cd "$PROJECT_DIR/public"
    php -S 0.0.0.0:8080 -t . ../system/rewrite.php
}

# Fonction de reset
reset_config() {
    echo "🔄 Remise à zéro de la configuration..."
    if [ -f "$ENV_FILE" ]; then
        mv "$ENV_FILE" "$ENV_FILE.backup.$(date +%s)"
        echo "✅ Ancien .env sauvegardé"
    fi
    configure_auto_detect
}

# Traitement des arguments
case "${1:-}" in
    --auto-detect)
        configure_auto_detect
        ;;
    --set-ip)
        if [ -z "$2" ]; then
            echo "❌ IP manquante. Usage: $0 --set-ip 192.168.1.50"
            exit 1
        fi
        configure_fixed_ip "$2"
        ;;
    --show-config)
        show_config
        ;;
    --reset)
        reset_config
        ;;
    --start-server)
        start_server
        ;;
    --help|*)
        show_help
        ;;
esac
