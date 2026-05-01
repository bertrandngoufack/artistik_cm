#!/bin/bash

PROJECT_NAME="php84"
COLOR_GREEN='\033[0;32m'
COLOR_BLUE='\033[0;34m'
COLOR_RED='\033[0;31m'
NC='\033[0m'

case "$1" in
  start)
    echo -e "${COLOR_GREEN}🚀 Démarrage PHP 8.4 Stack...${NC}"
    docker-compose up -d
    echo -e "${COLOR_BLUE}📱 Application: http://localhost:8080${NC}"
    echo -e "${COLOR_BLUE}🛢️ phpMyAdmin: http://localhost:8081${NC}"
    ;;
  stop)
    docker-compose down
    ;;
  restart)
    docker-compose restart
    ;;
  shell)
    docker exec -it ${PROJECT_NAME}_web bash
    ;;
  logs)
    docker-compose logs -f ${2:-web}
    ;;
  build)
    docker-compose build --no-cache
    ;;
  clean)
    echo -e "${COLOR_RED}⚠️ Supprime tout...${NC}"
    docker-compose down -v
    docker system prune -f
    ;;
  *)
    echo "Usage: $0 {start|stop|restart|shell|logs|build|clean}"
    ;;
esac