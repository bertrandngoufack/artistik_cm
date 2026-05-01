#!/bin/bash
set -e

echo "🚀 Démarrage de PHP 8.4 Stack..."

# Vérifier si composer.json existe et installer les dépendances
if [ -f /var/www/html/composer.json ] && [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "📦 Installation des dépendances Composer..."
    cd /var/www/html && composer install --no-interaction --optimize-autoloader --no-dev
fi

# Créer un fichier index.php par défaut si aucun fichier n'existe
if [ ! -f /var/www/html/index.php ] && [ ! -f /var/www/html/index.html ]; then
    echo "ℹ️ Création d'un index.php par défaut..."
    cat > /var/www/html/index.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>PHP 8.4 Stack</title>
    <style>
        body { font-family: monospace; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #4CAF50; }
        .info { background: #e7f3ff; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Stack PHP 8.4 fonctionnelle !</h1>
        <div class="info">
            <strong>Informations système :</strong><br>
            PHP Version : <?php echo phpversion(); ?><br>
            Serveur : <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
            Extensions chargées : <?php echo implode(', ', get_loaded_extensions()); ?>
        </div>
        
        <?php
        // Test connexion à MariaDB
        $db_host = getenv('DB_HOST');
        $db_name = getenv('DB_NAME');
        $db_user = getenv('DB_USER');
        $db_pass = getenv('DB_PASSWORD');
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            echo '<div class="info success">✅ Connexion à MariaDB réussie !</div>';
        } catch (PDOException $e) {
            echo '<div class="info error">❌ Erreur DB : ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <div class="info">
            <strong>🛠️ Outils disponibles :</strong><br>
            • Composer : <?php echo shell_exec('composer --version 2>&1'); ?><br>
            • WP-CLI : <?php echo shell_exec('wp --version 2>&1'); ?>
        </div>
    </div>
</body>
</html>
EOF
fi

# Ajuster les permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "✅ Stack PHP 8.4 prête !"

exec "$@"