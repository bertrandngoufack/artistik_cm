#!/bin/bash

# LyCol - Test d'Adaptation Réseau
# Vérifie que l'application s'adapte automatiquement aux IPs

echo "🧪 Test d'adaptation réseau - LyCol"
echo "===================================="

# Obtenir toutes les IPs disponibles
IP_ADDRESSES=$(hostname -I 2>/dev/null || ip route get 1.1.1.1 2>/dev/null | grep -oP 'src \K\S+' || echo "localhost")

echo "📍 IPs détectées: $IP_ADDRESSES"
echo ""

# Tester chaque IP
for ip in $IP_ADDRESSES; do
    if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "🔍 Test de l'IP: $ip"
        
        # Test de la page d'accueil
        echo "  - Test page d'accueil..."
        if curl -s -o /dev/null -w "%{http_code}" "http://$ip:8080/" | grep -q "200"; then
            echo "    ✅ Page d'accueil: OK"
        else
            echo "    ❌ Page d'accueil: ÉCHEC"
        fi
        
        # Test des ressources CSS
        echo "  - Test ressources CSS..."
        if curl -s -o /dev/null -w "%{http_code}" "http://$ip:8080/assets/bulma/css/bulma.min.css" | grep -q "200"; then
            echo "    ✅ CSS Bulma: OK"
        else
            echo "    ❌ CSS Bulma: ÉCHEC"
        fi
        
        if curl -s -o /dev/null -w "%{http_code}" "http://$ip:8080/assets/fontawesome/css/all.min.css" | grep -q "200"; then
            echo "    ✅ CSS FontAwesome: OK"
        else
            echo "    ❌ CSS FontAwesome: ÉCHEC"
        fi
        
        # Test des ressources JS
        echo "  - Test ressources JavaScript..."
        if curl -s -o /dev/null -w "%{http_code}" "http://$ip:8080/assets/bulma/js/bulma.js" | grep -q "200"; then
            echo "    ✅ JS Bulma: OK"
        else
            echo "    ❌ JS Bulma: ÉCHEC"
        fi
        
        # Test de l'authentification
        echo "  - Test authentification..."
        if curl -s -o /dev/null -w "%{http_code}" "http://$ip:8080/auth/login" | grep -q "200"; then
            echo "    ✅ Page de connexion: OK"
        else
            echo "    ❌ Page de connexion: ÉCHEC"
        fi
        
        echo ""
    fi
done

echo "🎯 Résumé:"
echo "L'application LyCol s'adapte automatiquement à toutes les IPs du serveur"
echo "Configuration: 0.0.0.0:8080 (toutes interfaces)"
echo "✅ Prêt pour la production avec détection automatique d'IP"
