# Restauration des dépendances — Boutik (hors archive)

Ce répertoire est livré **sans** le dossier `vendor/` (Composer) ni `node_modules/` (npm), afin de limiter la taille de l’archive et éviter les binaires obsolètes.

## 1. Dépendances PHP (obligatoire)

Sur la machine hôte **ou** dans le conteneur `artistik-php_web` (recommandé) :

```bash
docker exec -u www-data -w /var/www/html/Boutik artistik-php_web \
  composer install --no-dev --optimize-autoloader
```

Pour un environnement de développement, omettre `--no-dev`.

## 2. Fichier `.env`

Copier `.env.example` vers `.env`, puis configurer au minimum :

- `APP_KEY` — générer avec `php artisan key:generate`
- `APP_URL` — ex. `http://localhost:8080/boutik`
- `DB_*` — alignés sur la base `boutik_db` et l’utilisateur créé par `install.sh`
- `APP_LOCALE=fr`, fuseau `Africa/Douala` si besoin

## 3. Droits d’écriture Laravel

```bash
docker exec -u root artistik-php_web chown -R www-data:www-data /var/www/html/Boutik/storage /var/www/html/Boutik/bootstrap/cache
```

## 4. Front-end (facultatif)

Si vous modifiez les assets front : `npm ci && npm run build` (voir la documentation du projet d’origine). Le déploiement standard Docker utilise les CSS/JS déjà compilés dans `public/` lorsque présents.
