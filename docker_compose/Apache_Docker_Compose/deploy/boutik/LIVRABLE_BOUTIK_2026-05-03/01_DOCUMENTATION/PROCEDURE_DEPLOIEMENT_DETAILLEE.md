# Procédure de déploiement détaillée — Boutik (stack Artistik / Docker)

**Version du document :** 1.0 — 2026-05-03  
**Public visé :** intégrateur, administrateur système, DevOps.  
**Identifiants et secrets :** voir [ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md](ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md).

---

## 1. Vue d’ensemble

Boutik est une application **Laravel 9** (base **Ultimate POS**, rebrand **Artistik**) servie en **sous-chemin** `/boutik` sur le même serveur Apache que WordPress (`/artistik_cm`). La base de données **`boutik_db`** est isolée sur l’instance **MariaDB** partagée.

**Composants livrés dans l’archive :**

- Scripts SQL (création + dump).
- Configuration Apache `boutik.conf`.
- Override Docker Compose.
- Script `install.sh` et variables `.env.boutik`.
- Référence `.env` Laravel et `.htaccess` public.

**Code source applicatif :** à prendre dans le dépôt :  
`docker_compose/Apache_Docker_Compose/web/Boutik/` (non inclus intégralement dans l’archive pour limiter la taille ; exécuter `composer install` sur site).

---

## 2. Prérequis

| Élément | Détail |
|---------|--------|
| Docker & Docker Compose | V2 recommandé |
| Images | PHP 8.4 + Apache + MariaDB 11.4 (voir `docker-compose.yml` du projet) |
| Ports | HTTP **8080** (paramétrable), MariaDB hôte **3307** par défaut, phpMyAdmin **8081** si activé |
| Espace disque | Prévoir ≥ 2 Go pour vendor + fichiers upload futurs |
| Droits | Accès shell ; droits `docker` pour l’utilisateur qui déploie |

---

## 3. Récupération du dépôt et positionnement

```bash
git clone <URL_DU_DEPOT> artistik_cm
cd artistik_cm/docker_compose/Apache_Docker_Compose
```

Vérifier la présence de :

- `web/Boutik/composer.json`
- `web/Boutik/artisan`
- `deploy/boutik/` (cette livraison peut être fusionnée ou déjà présente)

---

## 4. Préparer l’environnement Docker avec l’override Boutik

Depuis `docker_compose/Apache_Docker_Compose` :

```bash
docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  up -d
```

**Effets de l’override :**

- Monte `03_APACHE/boutik.conf` (copié depuis le livrable vers `deploy/boutik/apache/boutik.conf` dans le dépôt) en `/etc/apache2/conf-enabled/boutik.conf`.
- Corrige le **healthcheck** du service `mariadb` (authentification root avec `MYSQL_ROOT_PASSWORD`).

Attendre le statut **healthy** pour `artistik-php_db` et un conteneur web **artistik-php_web** opérationnel.

---

## 5. Base de données : deux scénarios

### 5.A Restauration depuis le livrable SQL (recommandé pour reproduire l’environnement)

1. Copier `02_SQL/*.sql` sur la machine hôte ou les injecter via `docker exec`.
2. Exécuter **d’abord** `00_create_database_et_utilisateur.sql` en `root` MariaDB.
3. Importer **`01_boutik_db_full_dump.sql`** dans `boutik_db` :

```bash
docker exec -i artistik-php_db mariadb -uroot -p'Bateau123' < 02_SQL/00_create_database_et_utilisateur.sql
docker exec -i artistik-php_db mariadb -uroot -p'Bateau123' boutik_db < 02_SQL/01_boutik_db_full_dump.sql
```

*(Adapter mot de passe root si nécessaire — voir annexe.)*

### 5.B Installation vide (migrations + seeders uniquement)

Ne pas importer le dump. Après création de la base et de l’utilisateur (`00_...sql`), suivre la section 7 avec `install.sh` qui exécute `migrate` / `db:seed` / `CameroonAdminSeeder`.

---

## 6. Configuration Laravel (`web/Boutik/.env`)

1. Copier `06_LARAVEL_ENV/.env.reference_boutik` vers `web/Boutik/.env`.
2. Vérifier **`APP_URL`** (URL publique réelle + chemin `/boutik`).
3. Conserver **`APP_KEY`** cohérent avec le dump si vous avez restauré les données (sinon `php artisan key:generate` sur une install vierge).
4. Vérifier **`DB_*`** (hôte `mariadb` depuis le conteneur web ; identifiants dans l’annexe).

**Réécriture Apache :** le fichier `boutik.conf` force des variables `SetEnv` pour les requêtes `/boutik` afin d’écraser les variables WordPress du conteneur — condition **nécessaire** au bon fonctionnement.

---

## 7. Fichier `.htaccess` du public Laravel

Vérifier que `web/Boutik/public/.htaccess` contient :

```apache
RewriteBase /boutik/
```

Référence fournie : `07_CODE_REFERENCE/public_htaccess.reference`.

---

## 8. Script dédié `install.sh`

Le livrable fournit `05_SCRIPTS/install.sh` (copie identique à celle du dépôt sous `deploy/boutik/install.sh`).

**Usage typique :**

```bash
cd docker_compose/Apache_Docker_Compose
chmod +x deploy/boutik/install.sh
bash deploy/boutik/install.sh
```

**Prérequis du script :**

- Fichier `deploy/boutik/.env.boutik` (ou copie depuis `05_SCRIPTS/.env.boutik` du livrable).
- Conteneurs `artistik-php_web` et `artistik-php_db` démarrés.

**Actions principales :**

- Création idempotente de `boutik_db` et `boutik_user`.
- Vérification des modules Laravel manquants (`modules_statuses.json`).
- `composer install` si nécessaire.
- `php artisan migrate` / `db:seed` selon variables.
- Option **`BOUTIK_RUN_CAMEROON_SEED=1`** : compte `boutik_admin` / entreprise démo Cameroun.

---

## 9. Vérifications post-déploiement

| Contrôle | Commande / attente |
|----------|---------------------|
| HTTP racine Boutik | `curl -sI http://localhost:8080/boutik` → redirection ou 200 |
| Page login | `curl -sI http://localhost:8080/boutik/login` → **200** |
| Connexion applicative | URL login + utilisateur `boutik_admin` (annexe) |
| Cookie session | Pas de sortie PHP avant en-têtes (corrigé par `boutik.conf`) |
| Permissions Laravel | `storage/` et `bootstrap/cache/` inscriptibles par `www-data` |

---

## 10. Exploitation et sauvegardes

- **Sauvegardes SQL :** planifier un `mariadb-dump` périodique de `boutik_db`.
- **Fichiers :** sauvegarder `web/Boutik/storage/app` (uploads éventuels).
- **Journaux :** `web/Boutik/storage/logs/laravel-*.log` et `php-runtime.log` (chemin défini dans `boutik.conf`).

---

## 11. Dépannage (extraits)

| Symptôme | Cause fréquente | Action |
|----------|-----------------|--------|
| `419 Page Expired` après POST login | Sortie PHP (warnings) avant `Set-Cookie` | Vérifier directives `php_admin_value` dans `boutik.conf` |
| `Access denied` MySQL pour `boutik_user` | Variables OS WordPress écrasant `.env` en CLI / HTTP | Vérifier `SetEnv` dans `boutik.conf` ; utiliser `artisan()` du `install.sh` avec `-e DB_*` |
| 404 sur routes Laravel | `RewriteBase` absent ou faux | Contrôler `.htaccess` |
| Healthcheck MariaDB unhealthy | Sonde root sans mot de passe | Appliquer l’override `docker-compose.boutik.override.yml` |

---

## 12. Conformité et licence

Le logiciel d’origine **Ultimate POS** est soumis aux conditions d’**Envato / CodeCanyon**. L’usage en production nécessite une licence valide et le respect des termes du vendeur. Le présent livrable documente un déploiement technique ; il ne remplace pas une due diligence juridique.

---

## 13. Contacts secrets (récapitulatif)

Tous les mots de passe et URLs précis sont centralisés dans **ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md**. Toute rotation de secret doit être reflétée à trois endroits minimum :

1. `web/Boutik/.env`
2. `deploy/boutik/apache/boutik.conf` (directives `SetEnv` pour `DB_PASSWORD`, etc.)
3. `deploy/boutik/.env.boutik` (pour `install.sh` et création SQL)

Puis redémarrer / recharger Apache dans le conteneur web si besoin :

```bash
docker exec artistik-php_web apachectl graceful
```

---

*Fin du document.*
