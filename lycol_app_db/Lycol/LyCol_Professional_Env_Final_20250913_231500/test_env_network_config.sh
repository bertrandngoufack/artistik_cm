#!/bin/bash

# LyCol - Test Configuration .env Réseau
# Validation automatique de la configuration professionnelle
# Date: 13 Septembre 2025

echo "🧪 Test Configuration .env Réseau - LyCol"
echo "=========================================="

# Fonction de test d'une IP
test_ip_configuration() {
    local ip="$1"
    local port="8080"
    local url="http://$ip:$port"
    
    echo "🔍 Test IP: $ip"
    
    # Test page d'accueil
    if curl -s -o /dev/null -w "%{http_code}" "$url/" | grep -q "200"; then
        echo "  ✅ Page d'accueil: OK"
    else
        echo "  ❌ Page d'accueil: ÉCHEC"
        return 1
    fi
    
    # Test configuration .env via page de connexion
    local login_content=$(curl -s "$url/auth/login")
    
    # Vérifier que les assets utilisent la bonne IP
    if echo "$login_content" | grep -q "href=\"http://$ip:$port/assets/"; then
        echo "  ✅ Configuration .env: Assets utilisent l'IP $ip"
    else
        echo "  ❌ Configuration .env: Assets n'utilisent pas l'IP $ip"
        echo "  📝 URLs détectées:"
        echo "$login_content" | grep -o 'href="[^"]*assets[^"]*"' | head -2 | sed 's/^/    /'
        return 1
    fi
    
    # Test ressources CSS
    if curl -s -o /dev/null -w "%{http_code}" "$url/assets/bulma/css/bulma.min.css" | grep -q "200"; then
        echo "  ✅ CSS Bulma: OK"
    else
        echo "  ❌ CSS Bulma: ÉCHEC"
    fi
    
    if curl -s -o /dev/null -w "%{http_code}" "$url/assets/fontawesome/css/all.min.css" | grep -q "200"; then
        echo "  ✅ CSS FontAwesome: OK"
    else
        echo "  ❌ CSS FontAwesome: ÉCHEC"
    fi
    
    # Test JavaScript
    if curl -s -o /dev/null -w "%{http_code}" "$url/assets/bulma/js/bulma.js" | grep -q "200"; then
        echo "  ✅ JavaScript Bulma: OK"
    else
        echo "  ❌ JavaScript Bulma: ÉCHEC"
    fi
    
    # Test authentification
    if curl -s -o /dev/null -w "%{http_code}" "$url/auth/login" | grep -q "200"; then
        echo "  ✅ Authentification: OK"
    else
        echo "  ❌ Authentification: ÉCHEC"
    fi
    
    echo ""
    return 0
}

# Fonction de test de la configuration .env
test_env_config() {
    echo "📋 Vérification du fichier .env:"
    
    if [ ! -f ".env" ]; then
        echo "  ❌ Fichier .env introuvable"
        return 1
    fi
    
    local auto_detect=$(grep "APP_AUTO_DETECT_IP" .env 2>/dev/null | cut -d'=' -f2)
    local base_url=$(grep "APP_BASE_URL" .env 2>/dev/null | cut -d'=' -f2)
    
    echo "  📝 APP_AUTO_DETECT_IP: ${auto_detect:-non défini}"
    echo "  📝 APP_BASE_URL: ${base_url:-non définie}"
    
    if [ "$auto_detect" = "true" ]; then
        echo "  ✅ Mode auto-détection activé"
    else
        echo "  ⚠️  Mode auto-détection désactivé"
    fi
    
    echo ""
}

# Obtenir toutes les IPs disponibles
get_server_ips() {
    hostname -I 2>/dev/null | tr ' ' '\n' | grep -E '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' | sort -u
}

# Fonction principale
main() {
    echo "📍 Détection des IPs du serveur..."
    local ips=$(get_server_ips)
    
    if [ -z "$ips" ]; then
        echo "❌ Aucune IP détectée"
        exit 1
    fi
    
    echo "IPs détectées: $(echo $ips | tr '\n' ' ')"
    echo ""
    
    # Test de la configuration .env
    test_env_config
    
    # Test de chaque IP
    local success_count=0
    local total_count=0
    
    for ip in $ips; do
        if test_ip_configuration "$ip"; then
            success_count=$((success_count + 1))
        fi
        total_count=$((total_count + 1))
    done
    
    # Résumé final
    echo "🎯 Résumé des tests:"
    echo "========================"
    echo "IPs testées: $total_count"
    echo "IPs fonctionnelles: $success_count"
    
    if [ $success_count -eq $total_count ]; then
        echo "✅ Configuration .env parfaite - Toutes les IPs fonctionnent"
        echo "🏆 L'application s'adapte automatiquement aux changements d'IP"
        return 0
    else
        echo "⚠️  Certaines IPs ont des problèmes"
        return 1
    fi
}

# Exécution
main "$@"
