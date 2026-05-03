# Déploiement Artistik CM (WordPress) — SQL + dossier `web/artistik_cm`

Ce dossier permet de régénérer **l’archive de la base** et **l’archive des fichiers** pour votre hébergeur.

## Vérifications rapides (avant mise en ligne)

- **Réponse HTTP** : la racine WP répond (`200`), les permaliens fonctionnent après création `.htaccess` prod.
- **URL en base** : actuellement le dump contient `siteurl` / `home` en `http://localhost:8080/artistik_cm` — vous devrez les remplacer par votre domaine (voir étape 4).
- **`.htaccess`** : en développement le fichier `web/artistik_cm/.htaccess` peut cibler le sous-dossier `/artistik_cm/`. À la racine du domaine en production, utiliser `.htaccess.production-root.example` du même répertoire.
- **Secrets** : `wp-config.php` contient les clés `AUTH_KEY`, etc. Dupliquez le fichier puis **regénérez de nouvelles clés salées sur** [wordpress.org secret-key](https://api.wordpress.org/secret-key/1.1/salt/) pour la prod.
- **Variables d’environnement** : le `wp-config.php` utilise `getenv('DB_*')`. Sur un hébergeur mutualisé, définissez les variables système compatibles avec PHP-FPM/Apache ou remplacez par des constantes explicites `DB_HOST`, etc.

### Nom de base exportée

La base utilisée dans le dump est celle configurée dans le conteneur **MariaDB au moment du dump** (`MYSQL_DATABASE`). Si votre fichier `.env` indique par exemple `DB_NAME=artistik_cm_db` mais que le volume MariaDB existant contient encore `app_database`, c’est **`app_database` qui sera exportée**. Vérifiez avec :

```bash
docker compose exec mariadb printenv MYSQL_DATABASE
```

À l’import chez l’hébergeur, créez une base vide puis importez le `.sql.gz` dedans : le fichier **n’embarque pas** de ligne `CREATE DATABASE` / `USE`.

## Produire ou reproduire les paquets localement

```bash
cd docker_compose/Apache_Docker_Compose
chmod +x deploy/export-for-hosting.sh
./deploy/export-for-hosting.sh
```

Les sorties sont dans `deploy/artifacts/` :

| Fichier | Contenu |
|--------|---------|
| `artistik_cm_<horodatage>_full.sql.gz` | Dump MariaDB/MySQL compacté |
| `artistik_cm_files_<horodatage>.tar.gz` | Dossier `artistik_cm/` (sans quelques dossiers régénérables) |
| `SHA256_<horodatage>.txt` | Empreintes des deux archives |

**Ne pas** publier ces archives sur Internet ; ajoutez-les hors Git si elles contiennent des données personnelles (RGPD).

## Étapes côté hébergeur

1. **Créer** une base MySQL/MariaDB + utilisateur avec tous les droits sur cette base.
2. **Importer** : téléverser `.sql.gz` puis : `gunzip -c artistik_cm_*_full.sql.gz | mysql -u USER -p NOM_BASE` (adapté selon votre hébergement/phpMyAdmin).
3. **Déployer fichiers** : extraire `artistik_cm_files_*.tar.gz` sous le répertoire public (souvent `public_html/` ou sous-dossier). Le document root doit pointer vers **le dossier qui contient** `wp-config.php` et `wp-content/` (ici `artistik_cm/` lui-même).
4. **Mettre les URLs WP** après import (domaine HTTPS) :
   - soit variables `ARTISTIK_CM_SITE_URL` / `WP_PUBLIC_URL` alignées comme dans votre `wp-config.php` ;
   - soit mise à jour en base :
     ```sql
     UPDATE wp_options SET option_value='https://votredomaine.tld/artistik_cm' WHERE option_name IN ('siteurl','home');
     ```
     (ajuster le chemin si le site est à la racine : `https://votredomaine.tld` sans sous-dossier.)
   - soit **WP-CLI** :  
     `wp search-replace 'http://localhost:8080/artistik_cm' 'https://votredomaine.tld/artistik_cm' --dry-run`
5. **`wp-config.php`** : même préfixe de tables (`wp_` tel qu’en base), identifiants base hébergement.
6. TranslatePress : vérifiez les langues sous **Réglages → TranslatePress** et les permaliens (réécritures Apache actives).

## Contenu exclus de l’archive fichiers volontairement

Les répertoires suivants peuvent être recréés sur le serveur et ne sont généralement pas nécessaires pour un premier déploiement :

- `wp-content/cache/`
- `wp-content/upgrade/`
- sauvegardes typiques : `wp-content/updraft`, `backupwordpress-*`, `ai1wm-backups`

Le dossier `wp-content/uploads/` est inclus (médiathèque).
