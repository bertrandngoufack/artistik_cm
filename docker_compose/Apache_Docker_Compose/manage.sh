#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

ENV_FILE="${ENV_FILE:-.env}"
if [ -f "$ENV_FILE" ]; then
  set -a
  # shellcheck source=/dev/null
  source "$ENV_FILE"
  set +a
elif [ -f ".env.example" ] && { [ "${1:-}" = "start" ] || [ "${1:-}" = "start-tools" ] || [ "${1:-}" = "build" ]; }; then
  echo "Fichier ${ENV_FILE} absent. Créez-le : cp .env.example .env puis adaptez les mots de passe."
  exit 1
fi

COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-artistik-php}"
HTTP_PORT="${HTTP_PORT:-8080}"
PMA_PORT="${PMA_PORT:-8081}"
PROJECT_WEB="${COMPOSE_PROJECT_NAME}_web"

COLOR_GREEN='\033[0;32m'
COLOR_BLUE='\033[0;34m'
COLOR_RED='\033[0;31m'
NC='\033[0m'

compose() {
  if [ -f "$ENV_FILE" ]; then
    docker compose --env-file "$ENV_FILE" "$@"
  else
    docker compose "$@"
  fi
}

case "${1:-}" in
  start)
    echo -e "${COLOR_GREEN}Démarrage de la stack (projet: ${COMPOSE_PROJECT_NAME})…${NC}"
    compose up -d web mariadb
    echo -e "${COLOR_BLUE}Application : http://localhost:${HTTP_PORT}${NC}"
    echo -e "${COLOR_BLUE}phpMyAdmin (optionnel) : docker compose --profile tools up -d → http://localhost:${PMA_PORT}${NC}"
    ;;
  start-tools)
    echo -e "${COLOR_GREEN}Démarrage avec phpMyAdmin…${NC}"
    compose --profile tools up -d
    echo -e "${COLOR_BLUE}Application : http://localhost:${HTTP_PORT}${NC}"
    echo -e "${COLOR_BLUE}phpMyAdmin : http://localhost:${PMA_PORT}${NC}"
    ;;
  stop)
    compose down
    ;;
  restart)
    compose restart
    ;;
  shell)
    docker exec -it "$PROJECT_WEB" bash
    ;;
  logs)
    compose logs -f "${2:-web}"
    ;;
  build)
    compose build --no-cache
    ;;
  config)
    compose config
    ;;
  clean)
    echo -e "${COLOR_RED}Arrêt des services et suppression des volumes nommés du projet…${NC}"
    compose down -v
    docker system prune -f
    ;;
  *)
    echo "Usage: $0 {start|start-tools|stop|restart|shell|logs|build|config|clean}"
    echo "Variables : fichier ${ENV_FILE} (exemple : cp .env.example .env)"
    exit 1
    ;;
esac
