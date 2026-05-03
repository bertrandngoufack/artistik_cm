#!/bin/bash
# Corrige le double-échappement \\" dans les fichiers lang FR
for f in /var/www/html/Boutik/lang/fr/*.php; do
  if grep -q '\\\\"' "$f" 2>/dev/null; then
    sed -i 's|\\\\"|\\"|g' "$f"
    echo "Patched escapes: $f"
  fi
done
echo "Done"
