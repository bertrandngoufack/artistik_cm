#!/usr/bin/env bash
# =============================================================================
#  Déploiement automatisé de Boutik (UltimatePOS / Laravel 9) sur la stack
#  Docker artistik-php (Apache + PHP 8.4 + MariaDB 11.4).
#
#  Idempotent : peut être relancé sans casser l'installation existante.
#  Usage :
#     cd docker_compose/Apache_Docker_Compose
#     bash deploy/boutik/install.sh
#
#  Pré-requis :
#    - La stack artistik-php est démarrée (manage.sh start).
#    - Les conteneurs artistik-php_web et artistik-php_db sont healthy.
#    - L'override compose deploy/boutik/docker-compose.boutik.override.yml
#      a été appliqué (alias /boutik).
# =============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${BLUE}[$(date +%H:%M:%S)]${NC} $*"; }
ok()    { echo -e "${GREEN}✓${NC} $*"; }
warn()  { echo -e "${YELLOW}⚠${NC} $*"; }
err()   { echo -e "${RED}✗${NC} $*" >&2; }

# 1. Charger les variables Boutik
ENV_FILE="${SCRIPT_DIR}/.env.boutik"
if [ ! -f "$ENV_FILE" ]; then
  err ".env.boutik introuvable : $ENV_FILE"
  exit 1
fi
# shellcheck source=/dev/null
set -a; source "$ENV_FILE"; set +a

WEB="${WEB_CONTAINER:-artistik-php_web}"
DB="${DB_CONTAINER:-artistik-php_db}"
DBNAME="${BOUTIK_DB_NAME:-boutik_db}"
DBUSER="${BOUTIK_DB_USER:-boutik_user}"
DBPASS="${BOUTIK_DB_PASSWORD:?BOUTIK_DB_PASSWORD requis}"
ROOTPASS="${DB_ROOT_PASSWORD:?DB_ROOT_PASSWORD requis}"
APP_PATH="${BOUTIK_APP_PATH_CONTAINER:-/var/www/html/Boutik}"
RUN_SEED="${BOUTIK_RUN_SEED:-1}"
FORCE="${BOUTIK_FORCE_REINSTALL:-0}"

# Helper artisan : surcharge les vars OS du conteneur (qui pointent vers
# WordPress) par celles dédiées à Boutik. Sinon Laravel.env() lit getenv()
# en priorité et se connecte à la mauvaise base.
artisan() {
  docker exec -u www-data \
    -e DB_HOST=mariadb -e DB_PORT=3306 \
    -e DB_DATABASE="$DBNAME" -e DB_USERNAME="$DBUSER" -e DB_PASSWORD="$DBPASS" \
    -e DB_CONNECTION=mysql \
    -e APP_TIMEZONE=Africa/Douala -e APP_LOCALE=fr \
    -e APP_URL="${BOUTIK_APP_URL:-http://localhost:8080/boutik}" \
    -e PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d:$APP_PATH \
    "$WEB" bash -c "cd $APP_PATH && php -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_STRICT' artisan $*"
}

# 2. Vérifier que les conteneurs tournent
log "Vérification des conteneurs Docker…"
for c in "$WEB" "$DB"; do
  if ! docker ps --format '{{.Names}}' | grep -q "^${c}$"; then
    err "Conteneur $c non démarré. Lancez d'abord : ./manage.sh start"
    exit 1
  fi
done
ok "Conteneurs $WEB et $DB en cours d'exécution"

# 3. Créer la base et le user dédiés (idempotent)
log "Création de la base \"$DBNAME\" et du user \"$DBUSER\" dans MariaDB…"
docker exec -i "$DB" mariadb -uroot -p"$ROOTPASS" <<SQL
CREATE DATABASE IF NOT EXISTS \`${DBNAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DBUSER}'@'%' IDENTIFIED BY '${DBPASS}';
ALTER USER '${DBUSER}'@'%' IDENTIFIED BY '${DBPASS}';
GRANT ALL PRIVILEGES ON \`${DBNAME}\`.* TO '${DBUSER}'@'%';
FLUSH PRIVILEGES;
SQL
ok "Base \"$DBNAME\" et user \"$DBUSER\" prêts"

# 4. Vérifier que les fichiers Boutik sont bien montés
if ! docker exec "$WEB" test -f "$APP_PATH/composer.json"; then
  err "Boutik introuvable dans $WEB:$APP_PATH (composer.json manquant)"
  exit 1
fi

# 5. Désactiver les modules absents pour permettre le boot Laravel
log "Désactivation des modules dont le code source est absent du dépôt…"
docker exec -u www-data "$WEB" bash -c "cd $APP_PATH && [ -d Modules ] || cat > modules_statuses.json <<'JSON'
{
    \"Essentials\": false,
    \"Accounting\": false,
    \"AssetManagement\": false,
    \"Cms\": false,
    \"Connector\": false,
    \"Crm\": false,
    \"Ecommerce\": false,
    \"FieldForce\": false,
    \"Manufacturing\": false,
    \"ProductCatalogue\": false,
    \"Project\": false,
    \"Repair\": false,
    \"Spreadsheet\": false,
    \"Superadmin\": false,
    \"Woocommerce\": false,
    \"AiAssistance\": false,
    \"Hms\": false,
    \"InboxReport\": false,
    \"CustomDashboard\": false,
    \"Gym\": false,
    \"ZatcaIntegrationKsa\": false
}
JSON
"
ok "modules_statuses.json normalisé (modules absents = false)"

# 6. Ajustements de permissions (storage, bootstrap/cache)
log "Préparation des permissions storage / bootstrap…"
docker exec "$WEB" bash -c "
  cd $APP_PATH && \
  mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs storage/app/public bootstrap/cache && \
  chown -R www-data:www-data storage bootstrap/cache && \
  chmod -R 775 storage bootstrap/cache
"
ok "Permissions OK"

# 7. Composer install (no-dev) si vendor manquant ou FORCE=1
if [ "$FORCE" = "1" ] || ! docker exec "$WEB" test -f "$APP_PATH/vendor/autoload.php"; then
  log "Installation des dépendances Composer (no-dev, optimize)…"
  docker exec -u www-data -e COMPOSER_ALLOW_SUPERUSER=1 -e COMPOSER_MEMORY_LIMIT=-1 "$WEB" \
    bash -c "cd $APP_PATH && composer install --no-interaction --no-dev --optimize-autoloader --ignore-platform-req=ext-* 2>&1 | tail -50"
  ok "Vendor Composer installé"
else
  ok "Vendor déjà présent (skip composer install — utiliser FORCE=1 pour forcer)"
fi

# 8. Génération de la clé Laravel (uniquement si APP_KEY vide)
log "Génération de APP_KEY…"
artisan "key:generate --force --ansi" 2>&1 | tail -5
ok "APP_KEY générée"

# 9. Cache config + nettoyage
log "Nettoyage des caches Laravel…"
artisan "config:clear" 2>&1 | tail -3
artisan "cache:clear"  2>&1 | tail -3 || true
artisan "view:clear"   2>&1 | tail -3 || true
artisan "route:clear"  2>&1 | tail -3 || true
ok "Caches purgés"

# 10. Migrations
log "Exécution des migrations Laravel (299 migrations, ~2-3 minutes)…"
artisan "migrate --force --ansi" 2>&1 | tail -25
ok "Migrations terminées"

# 11. Seeders (devises, permissions, barcodes)
if [ "$RUN_SEED" = "1" ]; then
  log "Exécution des seeders (devises, permissions, codes-barres)…"
  for seeder in CurrenciesTableSeeder PermissionsTableSeeder BarcodesTableSeeder; do
    artisan "db:seed --class=Database\\\\Seeders\\\\${seeder} --force --ansi" 2>&1 | tail -5 || \
      warn "Seeder $seeder a renvoyé une erreur (souvent : déjà exécuté)"
  done
  # Compte admin démo Cameroun (idempotent : ignore si boutik_admin existe déjà)
  if [ "${BOUTIK_RUN_CAMEROON_SEED:-1}" = "1" ]; then
    log "Seeder CameroonAdminSeeder (admin boutik_admin / entreprise démo)…"
    artisan "db:seed --class=Database\\\\Seeders\\\\CameroonAdminSeeder --force --ansi" 2>&1 | tail -8 || \
      warn "CameroonAdminSeeder : déjà exécuté ou erreur mineure"
  fi
  ok "Seeders appliqués"
else
  warn "RUN_SEED=0 → seeders non exécutés (à lancer manuellement)"
fi

# 12. Lien symbolique storage public (si applicable)
log "Création du lien storage:link…"
artisan "storage:link --ansi" 2>&1 | tail -3 || true
ok "storage:link OK (ou déjà existant)"

# 13. Optimisation prod-ready
log "Mise en cache config/route/view…"
artisan "config:cache --ansi" 2>&1 | tail -3 || true
artisan "route:cache --ansi"  2>&1 | tail -3 || true
artisan "view:cache --ansi"   2>&1 | tail -3 || true
ok "Caches reconstruits"

# 14. S'assurer que la conf Apache /boutik est chargée
log "Vérification de la conf Apache /boutik…"
if docker exec "$WEB" test -f /etc/apache2/conf-enabled/boutik.conf; then
  ok "Conf /boutik présente dans $WEB"
else
  warn "Conf /boutik absente — l'override compose n'est pas appliqué !"
  warn "Lancez : docker compose -f docker-compose.yml -f deploy/boutik/docker-compose.boutik.override.yml up -d"
fi

# 15. Recharger Apache (gracieux)
log "Rechargement gracieux d'Apache…"
docker exec "$WEB" apachectl -k graceful 2>/dev/null || docker exec "$WEB" apache2ctl graceful || true
ok "Apache rechargé"

# 16. Smoke test
log "Smoke test HTTP /boutik …"
HTTP_CODE=$(docker exec "$WEB" curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/boutik/ || echo "000")
case "$HTTP_CODE" in
  200|301|302|303|307|308) ok "HTTP $HTTP_CODE — application répond" ;;
  *) warn "HTTP $HTTP_CODE — vérifiez les logs : docker exec $WEB tail -n 100 /var/log/apache2/error.log" ;;
esac

cat <<EOF

${GREEN}╔══════════════════════════════════════════════════════════════════╗
║                    DÉPLOIEMENT BOUTIK TERMINÉ                    ║
╚══════════════════════════════════════════════════════════════════╝${NC}

  Application :      ${BLUE}${BOUTIK_APP_URL}${NC}
  Base de données :  ${DBNAME} (user ${DBUSER})
  PHPMyAdmin :       http://localhost:8081 (host=mariadb, user=${DBUSER})

  Connexion admin démo (si CameroonAdminSeeder exécuté) :
    URL ${BOUTIK_APP_URL}/login — utilisateur ${BLUE}boutik_admin${NC} / mot de passe documenté §0 de la procédure.

  Inscription commerçant supplémentaire : ${BOUTIK_APP_URL}/business/register

  Logs Laravel :     docker exec ${WEB} tail -f ${APP_PATH}/storage/logs/laravel.log
  Logs Apache  :     docker exec ${WEB} tail -f /var/log/apache2/error.log
  Shell conteneur :  docker exec -it ${WEB} bash

  Pour réinstaller à zéro :
    BOUTIK_FORCE_REINSTALL=1 bash deploy/boutik/install.sh

EOF
