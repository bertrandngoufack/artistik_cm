# Scripts SQL — Boutik

## Fichiers

| Fichier | Usage |
|---------|--------|
| `00_create_database_et_utilisateur.sql` | Crée `boutik_db` et l’utilisateur `boutik_user` avec les droits nécessaires. |
| `01_boutik_db_full_dump.sql` | Export **complet** (structure + données) de la base au moment du livrable. |

## Ordre d’exécution recommandé

### Sur une instance MariaDB **vide** (nouveau serveur)

1. Se connecter en `root` (ou équivalent).
2. Exécuter `00_create_database_et_utilisateur.sql`.
3. Importer le dump :
   ```bash
   mariadb -h HOST -P PORT -uroot -p boutik_db < 01_boutik_db_full_dump.sql
   ```
   Ou, si la base est vide et que le dump contient déjà les `CREATE TABLE` :
   ```bash
   mariadb -h HOST -P PORT -uroot -p < 01_boutik_db_full_dump.sql
   ```
   (en pratique le dump cible `boutik_db` ; assurez-vous que la base existe avant import si besoin).

### Sur l’environnement Docker documenté

```bash
docker exec -i artistik-php_db mariadb -uroot -p'Bateau123' < 00_create_database_et_utilisateur.sql
docker exec -i artistik-php_db mariadb -uroot -p'Bateau123' boutik_db < 01_boutik_db_full_dump.sql
```

(Remplacer le mot de passe root si vous l’avez modifié.)

## Précautions

- **Sauvegarde** : toute réimport écrase les données existantes de `boutik_db`.
- **Charset** : conserver `utf8mb4` / `utf8mb4_unicode_ci` pour éviter des régressions sur les emojis et caractères étendus.
- **Version** : dump généré avec MariaDB 11.4 ; importer de préférence sur MariaDB 10.6+ ou MySQL 8.x compatible.

## Alternative sans dump

Si vous ne restituez pas `01_boutik_db_full_dump.sql`, déployer le code Laravel puis :

```bash
php artisan migrate --force
php artisan db:seed --force
docker exec ... php artisan db:seed --class=Database\\Seeders\\CameroonAdminSeeder --force
```

(voir `05_SCRIPTS/install.sh` pour l’automatisation complète.)
