#!/usr/bin/env bash
#
# Archive SQL + dossier WordPress pour hébergement mutualisé / VPS.
# Prérequis : Docker Compose avec le service mariadb aligné sur la base réelle utilisée par WP (voir README-DEPLOY-FR.md).
#
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
OUT="$ROOT/deploy/artifacts"
mkdir -p "$OUT"
STAMP="$(date +%Y%m%d_%H%M%S)"
SQL_RAW="$OUT/artistik_cm_${STAMP}_full.sql"

echo "→ Export MariaDB → $SQL_RAW"
docker compose exec -T mariadb sh -lc '
  exec mariadb-dump -h127.0.0.1 \
    -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
    --single-transaction \
    --quick \
    --skip-lock-tables \
    --skip-add-locks
' >"$SQL_RAW"

echo "→ Compression gzip → ${SQL_RAW}.gz"
gzip -f "$SQL_RAW"

TGZ="$OUT/artistik_cm_files_${STAMP}.tar.gz"
echo "→ Archive fichiers web/artistik_cm → $TGZ"
tar -czf "$TGZ" \
  -C "$ROOT/web" \
  --exclude='artistik_cm/wp-content/cache' \
  --exclude='artistik_cm/wp-content/upgrade' \
  --exclude='artistik_cm/wp-content/backupwordpress-*' \
  --exclude='artistik_cm/wp-content/updraft' \
  --exclude='artistik_cm/wp-content/ai1wm-backups' \
  artistik_cm

echo "→ Sommes SHA256"
( cd "$OUT" && sha256sum "$(basename "${SQL_RAW}.gz")" "$(basename "$TGZ")" > "SHA256_${STAMP}.txt" )

ls -lh "$OUT/artistik_cm_${STAMP}_full.sql.gz" "$TGZ" "$OUT/SHA256_${STAMP}.txt"
echo "Terminé. Stocker ces fichiers hors dépôt public et transmettre de façon sécurisée."
