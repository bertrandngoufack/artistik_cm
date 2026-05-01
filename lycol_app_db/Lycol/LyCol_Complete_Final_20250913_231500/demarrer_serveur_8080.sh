#!/bin/bash

echo "🚀 DÉMARRAGE DU SERVEUR KISSAI SCHOOL - LyCol SUR LE PORT 8080"
echo "================================================================"

# Arrêter tous les processus PHP existants
echo "1️⃣ Arrêt des processus PHP existants..."
pkill -f "spark serve" 2>/dev/null || true
pkill -f "php -S" 2>/dev/null || true
sleep 2

# Libérer le port 8080
echo "2️⃣ Libération du port 8080..."
sudo fuser -k 8080/tcp 2>/dev/null || true
sudo fuser -k 8081/tcp 2>/dev/null || true
sudo fuser -k 8082/tcp 2>/dev/null || true
sleep 1

# Vider le cache
echo "3️⃣ Vidage du cache..."
php spark cache:clear 2>/dev/null || true
rm -rf writable/cache/* 2>/dev/null || true

# Vérifier que le port 8080 est libre
echo "4️⃣ Vérification du port 8080..."
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null ; then
    echo "   ❌ Le port 8080 est encore occupé"
    echo "   🔧 Tentative de libération forcée..."
    sudo fuser -k 8080/tcp
    sleep 2
fi

# Définir les variables d'environnement
echo "5️⃣ Configuration des variables d'environnement..."
export SPARK_PORT=8080
export SPARK_HOST=0.0.0.0
export APP_BASE_URL=http://localhost:8080/

# Démarrer le serveur avec des options forcées
echo "6️⃣ Démarrage du serveur sur le port 8080..."
echo "   📡 URL: http://localhost:8080"
echo "   🌐 Host: 0.0.0.0"
echo "   🔧 Port: 8080"

# Utiliser une approche différente pour forcer le port
php -S 0.0.0.0:8080 -t public/ public/router.php &
SERVER_PID=$!

# Attendre que le serveur démarre
sleep 3

# Vérifier que le serveur fonctionne
echo "7️⃣ Vérification du serveur..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/ | grep -q "200"; then
    echo "   ✅ Serveur démarré avec succès sur le port 8080"
    echo "   🌐 Accédez à: http://localhost:8080"
    echo "   📊 Dashboard admin: http://localhost:8080/admin/configuration"
    echo "   🔑 Connexion: http://localhost:8080/auth/login"
    
    echo ""
    echo "📋 INFORMATIONS UTILES:"
    echo "   • Base de données: 100.69.65.33:13306"
    echo "   • Port serveur: 8080"
    echo "   • Framework: CodeIgniter 4.6.3"
    echo "   • PHP: 8.4.5"
    
    echo ""
    echo "🛑 Pour arrêter le serveur: Ctrl+C"
    
    # Garder le script en vie
    wait $SERVER_PID
else
    echo "   ❌ Échec du démarrage du serveur"
    echo "   🔧 Tentative avec spark serve..."
    
    # Essayer avec spark serve
    php spark serve --port=8080 --host=0.0.0.0
fi




