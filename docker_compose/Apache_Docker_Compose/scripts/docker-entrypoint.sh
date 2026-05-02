#!/bin/bash
set -euo pipefail

export APACHE_DOCUMENT_ROOT="${APACHE_DOCUMENT_ROOT:-/var/www/html}"
export APACHE_SERVER_ADMIN="${APACHE_SERVER_ADMIN:-webmaster@localhost}"
export APACHE_ERROR_DOCUMENT_404="${APACHE_ERROR_DOCUMENT_404:-/index.php}"

TEMPLATE="${APACHE_VHOST_TEMPLATE:-/usr/local/share/php-stack/apache/vhost.conf.template}"
if [ ! -f "$TEMPLATE" ]; then
  echo "❌ Gabarit Apache introuvable : $TEMPLATE"
  exit 1
fi

echo "📝 Génération du vhost (DocumentRoot=${APACHE_DOCUMENT_ROOT})..."
envsubst '$APACHE_DOCUMENT_ROOT $APACHE_SERVER_ADMIN $APACHE_ERROR_DOCUMENT_404' < "$TEMPLATE" > /etc/apache2/sites-available/000-default.conf

echo "📝 Application des paramètres PHP runtime..."
cat > /usr/local/etc/php/conf.d/zz-runtime.ini <<EOF
memory_limit = ${PHP_MEMORY_LIMIT:-256M}
max_execution_time = ${PHP_MAX_EXECUTION_TIME:-300}
max_input_time = ${PHP_MAX_INPUT_TIME:-300}
upload_max_filesize = ${PHP_UPLOAD_MAX_SIZE:-64M}
post_max_size = ${PHP_POST_MAX_SIZE:-64M}
max_input_vars = ${PHP_MAX_INPUT_VARS:-3000}
date.timezone = ${PHP_DATE_TIMEZONE:-UTC}
display_errors = ${PHP_DISPLAY_ERRORS:-On}
variables_order = EGPCS
error_reporting = ${PHP_ERROR_REPORTING:-E_ALL}
log_errors = ${PHP_LOG_ERRORS:-On}
error_log = /var/log/php_errors.log
file_uploads = On
allow_url_fopen = On
opcache.enable = 1
opcache.memory_consumption = ${PHP_OPCACHE_MEMORY_CONSUMPTION:-256}
opcache.interned_strings_buffer = ${PHP_OPCACHE_INTERNED_STRINGS_BUFFER:-16}
opcache.max_accelerated_files = ${PHP_OPCACHE_MAX_ACCELERATED_FILES:-10000}
opcache.revalidate_freq = ${PHP_OPCACHE_REVALIDATE_FREQ:-2}
opcache.fast_shutdown = 1
opcache.enable_cli = 1
apc.enabled = 1
apc.shm_size = 128M
EOF

if [ "${ENSURE_MYSQL_DATABASE:-1}" = "1" ] && [ -n "${DB_NAME:-}" ] && [ -n "${DB_ROOT_PASSWORD:-}" ]; then
  echo "🗄️ Vérification / création de la base MariaDB (${DB_NAME})…"
  php /usr/local/share/php-stack/bin/ensure-mysql-database.php
fi

if [ "${COMPOSER_INSTALL_ON_START:-1}" = "1" ] && [ -f /var/www/html/composer.json ] && [ ! -f /var/www/html/vendor/autoload.php ]; then
  echo "📦 Installation des dépendances Composer..."
  COMPOSER_ARGS=(install --no-interaction --optimize-autoloader)
  if [ "${COMPOSER_INSTALL_DEV:-0}" != "1" ]; then
    COMPOSER_ARGS+=(--no-dev)
  fi
  (cd /var/www/html && composer "${COMPOSER_ARGS[@]}")
fi

if [ "${CREATE_DEFAULT_INDEX:-1}" = "1" ] && [ ! -f /var/www/html/index.php ] && [ ! -f /var/www/html/index.html ]; then
  echo "ℹ️ Création d'un index.php par défaut..."
  cat > /var/www/html/index.php << 'PHPEOF'
<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Stack PHP (Docker)</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 2rem; background: #f6f7f9; }
    .box { max-width: 52rem; margin: 0 auto; background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    h1 { color: #1a7f37; }
    pre { background: #f0f3f8; padding: 1rem; overflow: auto; border-radius: 6px; }
    .ok { color: #137333; }
    .err { color: #c5221f; }
  </style>
</head>
<body>
  <div class="box">
    <h1>Stack PHP opérationnelle</h1>
    <p><strong>PHP</strong> : <?php echo htmlspecialchars(phpversion(), ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>DocumentRoot</strong> : <?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <pre><?php
    $host = getenv('DB_HOST') ?: 'mariadb';
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASSWORD') ?: '';
    try {
      $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name),
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );
      echo '<span class="ok">Connexion MariaDB / MySQL : OK</span>';
    } catch (Throwable $e) {
      echo '<span class="err">Base de données : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</span>';
    }
    ?></pre>
  </div>
</body>
</html>
PHPEOF
fi

chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "✅ Démarrage Apache…"
exec "$@"
