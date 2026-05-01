#!/bin/bash
set -e

echo "🚀 Démarrage de PHP 8.4 Stack..."

# Vérifier si composer.json existe
if [ -f /var/www/html/composer.json ] && [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "📦 Installation des dépendances Composer..."
    cd /var/www/html && composer install --no-interaction --optimize-autoloader --no-dev
fi

# Créer un index.php par défaut
if [ ! -f /var/www/html/index.php ] && [ ! -f /var/www/html/index.html ]; then
    echo "ℹ️ Création d'un index.php par défaut..."
    cat > /var/www/html/index.php << 'PHPEOF'
<?php
phpinfo();
?>
PHPEOF
fi

# Permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "✅ Stack PHP 8.4 prête !"

exec "$@"
