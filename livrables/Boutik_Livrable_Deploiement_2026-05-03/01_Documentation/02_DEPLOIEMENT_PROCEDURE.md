# Procédure de déploiement — Boutik (UltimatePOS / Laravel 9)

> Procédure validée et exécutée le 2026-05-03 sur la stack Docker `artistik-php`
> (Apache 2.4 + PHP 8.4 + MariaDB 11.4 + phpMyAdmin), sans interruption du
> site WordPress co-hébergé `artistik_cm`.
>
> URL finale opérationnelle : http://localhost:8080/boutik
> Identifiants applicatifs : voir §0 ci-dessous (compte admin par défaut créé par seeder).
>
> **Livrable déploiement (synthèse livrable / critères d’acceptation) :**  
> [`03_LIVRABLE_DEPLOIEMENT.md`](./03_LIVRABLE_DEPLOIEMENT.md) — dossier scripts : `deploy/boutik/`.

---

## 0. Accès rapide — Compte Admin par défaut (Cameroun)

Un compte d'administration prêt à l'emploi est créé automatiquement par le
seeder `Database\Seeders\CameroonAdminSeeder` (idempotent — relancer le seeder
ne crée pas de doublon).

| Champ | Valeur |
|---|---|
| **URL de connexion** | http://localhost:8080/boutik/login |
| **Nom d'utilisateur** | `boutik_admin` |
| **Mot de passe** | `Boutik@2026` |
| **E-mail** | `admin@boutik.cm` |
| **Entreprise** | Boutik Demo Cameroun |
| **Devise** | XAF (FCFA) — *créée si absente* |
| **Fuseau horaire** | Africa/Douala |
| **Premier point de vente** | Siège Douala (Littoral) |
| **Langue d'interface** | Français (camerounisé) |

**Recréer le compte (en cas de perte) :**

```bash
docker exec -u www-data \
  -e DB_HOST=mariadb -e DB_DATABASE=boutik_db \
  -e DB_USERNAME=boutik_user -e DB_PASSWORD='Boutik_Strong_Pass_2026!' \
  artistik-php_web bash -c "cd /var/www/html/Boutik && \
  php -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_STRICT' \
  artisan db:seed --class=Database\\\\Seeders\\\\CameroonAdminSeeder --force"
```

> ⚠ Penser à changer ce mot de passe à la première connexion en production.

---

## 0-bis. Souveraineté & localisation — Travaux post-installation

Trois durcissements ont été appliqués après le déploiement initial pour rendre
Boutik conforme à un usage souverain au Cameroun :

### A. Aucune ressource externe (CDN) chargée par le navigateur

Audit & rapatriement réalisés sur **9 ressources externes** :

| Ressource externe | Action | Fichier(s) impacté(s) |
|---|---|---|
| `oss.maxcdn.com/html5shiv` (polyfill IE6-8) | **Supprimé** (MaxCDN HS depuis 2017) | `layouts/partials/extracss_auth.blade.php`, `layouts/partials/javascripts.blade.php`, `layouts/auth2.blade.php`, `layouts/install.blade.php`, `layouts/guest.blade.php` |
| `oss.maxcdn.com/respond` | **Supprimé** | idem |
| `fonts.googleapis.com/css?family=Raleway` | **Téléchargé en local** : `public/fonts/raleway/{raleway.css, raleway-300.ttf, raleway-400.ttf, raleway-600.ttf}` | `resources/sass/app.scss`, `public/css/init.css`, `public/css/vendor.css` |
| `www.google.com/recaptcha/api.js` | **Conditionnel** à `config('constants.enable_recaptcha')` (false par défaut) | `layouts/auth2.blade.php` |
| `maps.googleapis.com/maps/api/js` | **Conditionnel** à `!empty($api_key)` (clé `GOOGLE_MAP_API_KEY` vide → script jamais émis) | `contact/contact_map.blade.php`, `contact/index.blade.php` |
| `checkout.razorpay.com/v1/checkout.js` | **Supprimé** (passerelle Inde, non pertinente CM) | `sale_pos/partials/guest_payment_form.blade.php` |
| `checkout.stripe.com/checkout.js` + image marketplace | **Désactivé** par `@if(false && ...)` (réactivable une fois Stripe.js servi en local) | idem |
| `ui-avatars.com/api/?name=...` | **Remplacé par avatar SVG inline** généré localement (helper `boutik_local_avatar()` dans `app/Http/helpers.php`) | `app/User.php`, `resources/views/components/avatar.blade.php`, `resources/views/manage_user/show.blade.php` |
| `pos.test/img/...` (URL fantôme) | Réécrite vers chemin local | `layouts/partials/extracss_auth.blade.php` |

**Vérification finale :**

```bash
curl -s http://localhost:8080/boutik/login | \
  grep -oE '(src|href)="https?://[^"]*"' | grep -vE 'w3\.org' | sort -u
# (sortie vide attendue)

curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/boutik/fonts/raleway/raleway.css
# 200

curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/boutik/fonts/raleway/raleway-400.ttf
# 200
```

### B. Limitation aux langues `fr` et `en`

`config/constants.php → 'langs'` réduit à 2 entrées :

```php
'langs' => [
    'fr' => ['full_name' => 'Français', 'short_name' => 'Français'],
    'en' => ['full_name' => 'English',  'short_name' => 'English'],
],
'langs_rtl' => [],
'non_utf8_languages' => [],
```

**16 dossiers de langue supprimés** : `ar`, `ce`, `de`, `es`, `he`, `hi`, `id`,
`lo`, `nl`, `ps`, `pt`, `ro`, `sq`, `tr`, `vi` (+ leurs déclarations).

### C. Traduction française camerounisée

Script `sync_fr_from_en.php` (placé dans `/tmp/` du conteneur) qui :

1. Aligne **chaque clé EN absente** côté FR avec préfixe `[EN]` pour signaler
   ce qui reste à traduire (préserve les valeurs FR existantes).
2. Crée les fichiers FR manquants (`myfatoorah.php` notamment).
3. Applique un dictionnaire de remplacement de vocabulaire :
   `Wilaya → Région`, `TPS → TVA`, `GST → TVA`, `GSTIN/PAN → NIU`, etc.

Bilan exécution : **1 fichier créé (myfatoorah)**, **10 fichiers patchés**,
**222 clés ajoutées**, **6 termes camerounisés**, plus une réécriture manuelle
complète de `lang/fr/business.php` (champs : Région, BP, NIU, TVA, Téléverser,
Téléphone, Civilité, etc.).

**Validation visuelle :**

```bash
curl -s http://localhost:8080/boutik/business/register | \
  grep -oE '(Région|TVA|NIU|Code postal|Téléverser)' | sort | uniq -c
#       1 Code postal
#       2 NIU
#       1 Région
#       2 TVA
```

> Pour relancer la synchro après ajout de nouvelles clés EN :
> `docker exec -u www-data artistik-php_web php /tmp/sync_fr_from_en.php`

### D. Refonte UX/UI — Charte graphique Boutik Cameroun

**Charte graphique principale** (cf. `resources/views/layouts/partials/extracss_auth.blade.php`) :

| Token CSS | Valeur | Usage |
|---|---|---|
| `--boutik-teal-700` | **#0e7490** | Couleur primaire (logo, boutons, focus, liens) |
| `--boutik-teal-800` | #155e75 | Survol, gradient bouton |
| `--boutik-teal-900` | #164e63 | Texte sur fond clair |
| `--boutik-ink` | #0f172a | Texte principal |
| `--boutik-ink-soft` | #475569 | Texte secondaire |
| `--boutik-line` | #cbd5e1 | Bordures inputs |

**Assets visuels installés** (servis localement, **aucune CDN**) :

| Fichier | Dimensions | Usage |
|---|---|---|
| `public/img/boutik/logo.png` | 1024×683 px (~1.4 Mo) | Logo carré teal "Boutik Cameroun" — favicon, card login, top-bar |
| `public/img/boutik/logo-h.png` | 1024×683 px (~1 Mo) | Logo horizontal pour navbar interne |
| `public/img/boutik/login-bg.png` | 1024×683 px (~1.7 Mo) | Fond d'écran : commerçant camerounais en boutique moderne, voile teal |

**Vues refondues** (rétrocompatibles, **aucune dépendance Tailwind runtime requise** :
toutes les classes critiques sont dupliquées en CSS inline) :

- `resources/views/layouts/partials/extracss_auth.blade.php` — variables CSS,
  fond, helpers `.boutik-auth-card`, `.boutik-btn-primary`, `.boutik-link`, etc.
- `resources/views/layouts/auth2.blade.php` — top-bar avec logo + texte "Boutik /
  Gestion Commerciale Cameroun", footer avec mention copyright, conteneur centré.
- `resources/views/auth/login.blade.php` — card 440 px max-width, en-tête teal
  avec logo dans cercle blanc, formulaire propre, bouton "S'identifier" gradient teal.

**Chaînes traduites manuellement** (`lang/fr/lang_v1.php`) — la traduction
mot-à-mot automatique avait produit du franglais ; le script
`deploy/boutik/scripts/restore_clean_fr.php` détecte les chaînes franglaises
(mots EN + FR mélangés) et :

1. Restaure la clé EN d'origine avec préfixe `[EN]` pour les marquer.
2. Applique un **dictionnaire de PHRASES COMPLÈTES** validées (∼60 phrases)
   couvrant le login/inscription : `Welcome Back → Bon retour`,
   `Login to your → Connectez-vous à`, `Remember Me → Se souvenir de moi`,
   `Forgot your password → Mot de passe oublié ?`, etc.

```bash
# Pour appliquer la restauration sur tout le code FR :
docker cp deploy/boutik/scripts/restore_clean_fr.php artistik-php_web:/tmp/
docker exec artistik-php_web bash -c "
  chmod 666 /var/www/html/Boutik/lang/fr/*.php
  php -d error_reporting='E_ALL & ~E_DEPRECATED' /tmp/restore_clean_fr.php
  chmod 644 /var/www/html/Boutik/lang/fr/*.php
"
# Bilan typique : 167 franglais restaurés, 7 phrases traduites depuis dico,
# 40 [EN] restants (à traduire à la main au fil des écrans visités).
```

**Validation visuelle finale** (capture headless Chromium) :

```bash
docker run --rm --network host -v /tmp:/output zenika/alpine-chrome:124 \
  --no-sandbox --headless --disable-gpu --hide-scrollbars \
  --window-size=1280,900 --screenshot=/output/boutik-login.png \
  http://localhost:8080/boutik/login
```

Résultat attendu :
- En-tête teal avec logo Boutik en cercle blanc + titre "Bon retour"
- Sous-titre "Connectez-vous à Boutik"
- Champs Nom d'utilisateur / Mot de passe + lien "Mot de passe oublié ?"
- Case "Souviens-toi de moi"
- Bouton "S'identifier" en gradient teal #0e7490 → #155e75
- Lien "Pas encore inscrit ? S'inscrire maintenant"
- Top-bar transparent avec logo + boutons "S'inscrire" et "Français"
- Footer "© 2026 Boutik Cameroun · Solution Artistik · Tous droits réservés"
- Image de fond : commerçant camerounais souriant, voile teal #0e7490 (78%)

### D. Correctif critique PHP 8.4 — `display_errors=Off` pour `/boutik`

**Symptôme observé** : sur les requêtes `/boutik/login`, Apache renvoyait
HTTP 200 mais **aucun en-tête `Set-Cookie`** (ni `XSRF-TOKEN`, ni
`boutik_session`). Conséquence : tout `POST` retournait `Page Expired`
(token CSRF invalide), bloquant la connexion.

**Cause racine** : Laravel 9 + Carbon 2 ciblent PHP 8.0/8.1. Sous PHP 8.4,
le bootstrap émet **plusieurs dizaines de `Deprecated:`** (paramètres
nullable implicites). La configuration PHP de la stack
(`/usr/local/etc/php/conf.d/*.ini`) avait :

```ini
display_errors = On
error_reporting = E_ALL
```

→ chaque deprecation **est imprimée vers `stdout` AVANT** que Laravel n'émette
ses headers HTTP, déclenchant `Cannot modify header information - headers
already sent`. Les `Set-Cookie` sont donc **silencieusement perdus**.

**Correctif** appliqué dans `deploy/boutik/apache/boutik.conf` (`<Location /boutik>`) :

```apache
php_admin_value display_errors        "Off"
php_admin_value display_startup_errors "Off"
php_admin_value error_reporting       "22517"   # E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE
php_admin_flag  log_errors            On
php_admin_value error_log             "/var/www/html/Boutik/storage/logs/php-runtime.log"
```

Cette configuration n'affecte **que** les requêtes vers `/boutik`. WordPress
(`/artistik_cm`) conserve ses propres réglages PHP.

**Vérification** :

```bash
curl -sI http://localhost:8080/boutik/login | grep -iE 'set-cookie|http/'
# HTTP/1.1 200 OK
# Set-Cookie: XSRF-TOKEN=eyJ...; expires=...; path=/; samesite=lax
# Set-Cookie: boutik_session=eyJ...; expires=...; path=/; httponly; samesite=lax
```

**Workflow d'authentification validé** (script de test reproductible) :

```bash
CK=/tmp/boutik_session.txt; rm -f $CK
LOGIN_PAGE=$(curl -sS -c $CK http://localhost:8080/boutik/login)
CSRF=$(echo "$LOGIN_PAGE" | grep -oE 'name="_token" value="[^"]+"' \
       | head -1 | sed 's/name="_token" value="//;s/"//')

REDIR=$(curl -sS -b $CK -c $CK -o /dev/null -w '%{redirect_url}' \
  -X POST http://localhost:8080/boutik/login \
  --data-urlencode "_token=$CSRF" \
  --data-urlencode "username=boutik_admin" \
  --data-urlencode "password=Boutik@2026")

echo "Redirection après login : $REDIR"   # → http://localhost:8080/boutik/home

curl -sS -b $CK -o /tmp/dash.html "$REDIR"
grep -oE '<title>[^<]+</title>' /tmp/dash.html
# → <title>Accueil - Boutik Demo Cameroun</title>
```

> Recommandation long terme : revenir à PHP 8.2 (cible Laravel 9 LTS officielle)
> pour supprimer ces deprecations à la source.

---

## 1. Vue d'ensemble

`Boutik` est une application Laravel 9 (UltimatePOS rebrandé). Elle est
servie **en sous-dossier** `/boutik` du même serveur Apache qui héberge
déjà WordPress sous `/artistik_cm`. Aucune modification du `docker-compose.yml`
principal — l'isolation se fait via :

- un **override Compose** (`deploy/boutik/docker-compose.boutik.override.yml`)
  qui ajoute la conf Apache et corrige le healthcheck MariaDB ;
- une **base MariaDB dédiée** `boutik_db` créée à côté de `artistik_cm_db`
  dans le même conteneur ;
- un **utilisateur SQL dédié** `boutik_user` avec privilèges limités à
  `boutik_db.*` ;
- un **fichier `.env` Laravel** dans `web/Boutik/.env`, **complété** par des
  `SetEnv` Apache (car les variables OS du conteneur écrasent celles du
  `.env` Laravel — cf. §10 *pièges*).

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  Conteneur artistik-php_web (Apache 2.4 + PHP 8.4)      │
│                                                                         │
│   /var/www/html/artistik_cm/   ──►  http://localhost:8080/artistik_cm   │
│       (WordPress, base artistik_cm_db, user app_user)                   │
│                                                                         │
│   /var/www/html/Boutik/public/ ──►  http://localhost:8080/boutik        │
│       (Laravel, base boutik_db,    user boutik_user)                    │
│       Activé par /etc/apache2/conf-enabled/boutik.conf                  │
└─────────────────────────────────────────────────────────────────────────┘
                              │   │
                              ▼   ▼
         ┌──────────────────────────────────────────┐
         │  Conteneur artistik-php_db (MariaDB 11.4)│
         │   ├─ artistik_cm_db   (WordPress)        │
         │   └─ boutik_db        (Boutik)           │
         └──────────────────────────────────────────┘
```

---

## 2. Pré-requis

| Élément | Vérification |
|---|---|
| Docker ≥ 24, Docker Compose ≥ v2 | `docker --version && docker compose version` |
| Stack `artistik-php` opérationnelle | `cd docker_compose/Apache_Docker_Compose && ./manage.sh start` |
| Code source Boutik présent | `ls docker_compose/Apache_Docker_Compose/web/Boutik/composer.json` |
| Mot de passe root MariaDB connu | Lire `.env` (`DB_ROOT_PASSWORD`) |
| Ports 8080 (web) et 3307 (db) libres | `ss -tlnp \| grep -E '8080\|3307'` |

---

## 3. Arborescence ajoutée par ce déploiement

Tous les fichiers ajoutés sont **versionnables** et **n'écrasent rien** de la
stack existante.

```
docker_compose/Apache_Docker_Compose/
├── deploy/
│   └── boutik/
│       ├── apache/
│       │   └── boutik.conf                       ← Alias /boutik + SetEnv DB_*
│       ├── docker-compose.boutik.override.yml    ← Volume conf + fix healthcheck
│       ├── .env.boutik                           ← Variables du script install.sh
│       └── install.sh                            ← Script idempotent (composer/migrate/seed)
└── web/
    └── Boutik/
        ├── .env                                  ← .env Laravel (timezone Africa/Douala, locale fr)
        ├── modules_statuses.json                 ← TOUS les modules à false (code absent)
        ├── public/
        │   ├── .htaccess                         ← + RewriteBase /boutik/
        │   └── .htaccess.bak                     ← sauvegarde de l'original
        └── docs/
            └── Boutik/
                ├── 01_AUDIT_ARCHITECTURE.md      ← Audit architectural complet
                └── 02_DEPLOIEMENT_PROCEDURE.md   ← Le présent document
```

---

## 4. Procédure de déploiement (commandes complètes)

### 4.1 Premier déploiement (depuis zéro)

```bash
cd docker_compose/Apache_Docker_Compose

./manage.sh start

docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  --env-file .env up -d

bash deploy/boutik/install.sh
```

Le script `install.sh` enchaîne automatiquement :

1. Vérification des conteneurs `artistik-php_web` + `artistik-php_db`.
2. Création de la base `boutik_db` + user `boutik_user` (idempotent).
3. Désactivation des 21 modules nwidart absents du dépôt.
4. Permissions `storage/` et `bootstrap/cache/` à `www-data`.
5. `composer install --no-dev --optimize-autoloader` si `vendor/` absent.
6. `php artisan key:generate`.
7. Purge des caches Laravel.
8. `php artisan migrate --force` → **299 migrations** (~3 min).
9. Seeders `CurrenciesTableSeeder`, `PermissionsTableSeeder`, `BarcodesTableSeeder`.
10. `php artisan storage:link`.
11. Mise en cache config / route / view.
12. Reload Apache + smoke test HTTP `/boutik/`.

Sortie attendue :

```
[hh:mm:ss] Smoke test HTTP /boutik …
✓ HTTP 200 — application répond
```

### 4.2 Premier accès navigateur

| URL | But |
|---|---|
| http://localhost:8080/boutik | Page d'accueil publique |
| http://localhost:8080/boutik/business/register | **Création du commerçant + 1er Admin** |
| http://localhost:8080/boutik/login | Login après création |
| http://localhost:8081 (phpMyAdmin) | Inspection SQL — `host=mariadb`, `user=boutik_user` |

**Première étape applicative** : aller sur `/boutik/business/register`,
saisir :
- Nom de l'entreprise (ex : « Boutik Demo Cameroun »)
- Devise : **CFA Franc BEAC – XAF** (à sélectionner ; si absente, voir §8)
- Pays : Cameroon, Région, Ville, NIU dans `tax_number_1`
- Premier utilisateur (Admin) : email + mot de passe

### 4.3 Réinstallation / mise à jour

```bash
BOUTIK_FORCE_REINSTALL=1 bash deploy/boutik/install.sh

# Réinstallation depuis zéro de la base (DESTRUCTIF)
docker exec artistik-php_db mariadb -uroot -p"$DB_ROOT_PASSWORD" \
  -e "DROP DATABASE IF EXISTS boutik_db;"
bash deploy/boutik/install.sh
```

---

## 5. Vérifications post-déploiement

```bash
docker ps --filter name=artistik-php --format 'table {{.Names}}\t{{.Status}}'

docker exec -i artistik-php_db mariadb -uboutik_user \
  -p'Boutik_Strong_Pass_2026!' boutik_db -e "
    SELECT COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema='boutik_db';
    SELECT COUNT(*) AS migrations_done FROM migrations;
  "

for path in / /login /business/register; do
  printf '%-25s ' "$path"
  curl -s -o /dev/null -w 'HTTP %{http_code}\n' "http://localhost:8080/boutik$path"
done

docker exec artistik-php_web tail -n 60 /var/www/html/Boutik/storage/logs/laravel.log
docker exec artistik-php_web tail -n 60 /var/log/apache2/error.log
```

Résultats constatés (déploiement initial) :

```
NAMES                STATUS
artistik-php_db      Up X minutes (healthy)
artistik-php_web     Up X minutes (healthy)
artistik-php_pma     Up X minutes

tables_count: 70
migrations_done: 299
currencies: 141, permissions: 81

/                         HTTP 200
/login                    HTTP 200
/business/register        HTTP 200
```

---

## 6. Inventaire des fichiers livrés (avec rôle)

### 6.1 `deploy/boutik/apache/boutik.conf`
Configuration Apache montée dans `/etc/apache2/conf-enabled/boutik.conf`.
- `Alias /boutik → /var/www/html/Boutik/public`
- `<Location /boutik>` avec `SetEnv DB_*` qui surcharge les variables OS WordPress.
- `<Directory>` avec `AllowOverride All` (pour `.htaccess` Laravel).
- Verrouillage `<DirectoryMatch>` des dossiers internes Laravel.

### 6.2 `deploy/boutik/docker-compose.boutik.override.yml`
- monte `boutik.conf` en lecture seule dans le conteneur web ;
- corrige le healthcheck MariaDB (cf. piège §10.2).

### 6.3 `deploy/boutik/.env.boutik`
Variables consommées par `install.sh` :
- `WEB_CONTAINER`, `DB_CONTAINER`
- `BOUTIK_DB_NAME`, `BOUTIK_DB_USER`, `BOUTIK_DB_PASSWORD`
- `DB_ROOT_PASSWORD`, `BOUTIK_APP_URL`, `BOUTIK_RUN_SEED`, `BOUTIK_FORCE_REINSTALL`

À ajouter à `.gitignore` en prod.

### 6.4 `deploy/boutik/install.sh`
Script bash idempotent. Override possibles :
```bash
BOUTIK_FORCE_REINSTALL=1   # force composer install et reseeding
BOUTIK_RUN_SEED=0          # saute les seeders
ENV_FILE=.env.boutik       # change le fichier de variables
```

### 6.5 `web/Boutik/.env`
Configuration Laravel taillée pour le Cameroun :
```ini
APP_NAME="Boutik"
APP_LOCALE=fr
APP_TIMEZONE="Africa/Douala"
APP_URL=http://localhost:8080/boutik
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_DATABASE=boutik_db
DB_USERNAME=boutik_user
DB_PASSWORD=Boutik_Strong_Pass_2026!
PESAPAL_CURRENCY=XAF
MAIL_FROM_ADDRESS="info@artistik.cm"
```

### 6.6 `web/Boutik/modules_statuses.json`
Tous les 21 modules nwidart à `false` (code absent du dépôt).

### 6.7 `web/Boutik/public/.htaccess`
Ajout de `RewriteBase /boutik/` après `RewriteEngine On`. Backup dans `.htaccess.bak`.

---

## 7. Commandes de gestion courante

```bash
docker exec artistik-php_web tail -f /var/www/html/Boutik/storage/logs/laravel.log

docker exec -it artistik-php_web bash
cd /var/www/html/Boutik
php artisan tinker

# artisan avec les bonnes variables
docker exec -u www-data \
  -e DB_HOST=mariadb -e DB_DATABASE=boutik_db \
  -e DB_USERNAME=boutik_user -e DB_PASSWORD='Boutik_Strong_Pass_2026!' \
  artistik-php_web bash -c "cd /var/www/html/Boutik && php artisan migrate:status"

# Backup base
docker exec artistik-php_db mariadb-dump \
  -uboutik_user -p'Boutik_Strong_Pass_2026!' \
  --single-transaction --quick --routines boutik_db \
  > backups/boutik_db_$(date +%Y%m%d_%H%M%S).sql

# Restore
docker exec -i artistik-php_db mariadb \
  -uboutik_user -p'Boutik_Strong_Pass_2026!' boutik_db \
  < backups/boutik_db_YYYYMMDD_HHMMSS.sql

# Stop / restart avec override
cd docker_compose/Apache_Docker_Compose
docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  --env-file .env down

docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  --env-file .env up -d
```

---

## 8. Localisation Cameroun (étapes manuelles complémentaires)

### 8.1 Devise FCFA / XAF (SQL à exécuter via phpMyAdmin)

```sql
INSERT INTO currencies (id, country, currency, code, symbol,
                        thousand_separator, decimal_separator, created_at, updated_at)
VALUES (NULL, 'Cameroon', 'CFA Franc BEAC', 'XAF', 'FCFA', ' ', '.', NOW(), NOW());

UPDATE business
SET currency_id = (SELECT id FROM currencies WHERE code='XAF' LIMIT 1),
    time_zone = 'Africa/Douala',
    fy_start_month = 1,
    accounting_method = 'fifo'
WHERE id = 1;
```

Puis dans **Paramètres → Précision** : currency_precision=`0`, quantity_precision=`2`.

### 8.2 TVA Cameroun 19,25 %

**Taux de taxe → Ajouter** : `TVA 19,25%` (taux 19.25). Plus retenues AIR 5,5/2,2/1,1 %.

### 8.3 Mobile Money (étiquettes uniquement)

**Paramètres → Étiquettes personnalisées → Paiements** :

| Slot | Étiquette suggérée |
|---|---|
| Custom payment 1 | MTN MoMo |
| Custom payment 2 | Orange Money |
| Custom payment 3 | Express Union |
| Custom payment 4 | YUP |
| Custom payment 5 | Camtel Money |
| Custom payment 6 | Smobilpay |
| Custom payment 7 | Wave |

> Ces étiquettes ne sont QUE des libellés. Pour intégrer réellement les API
> MoMo / Orange Money, créer un module nwidart dédié (audit §11 P1-6).

### 8.4 Vocabulaire FR-CM

Dans `lang/fr/business.php` :
- Wilaya → Région
- TPS / TVA / Autre → TVA
- Code postal → (vide, non utilisé au Cameroun)

### 8.5 Imprimante thermique 80 mm

**Paramètres → Imprimantes** : ajouter un appareil `Receipt 80mm`, type `ESC/POS`.

---

## 9. Sécurité minimale avant production

| Risque | Action |
|---|---|
| `APP_DEBUG=true` | Passer à `false` puis `php artisan config:cache` |
| Mot de passe MariaDB faible (`Bateau123`) | Régénérer (`openssl rand -base64 24`), MAJ `.env` racine + `.env.boutik` + relancer `install.sh` |
| Port 3307 exposé sur l'hôte | Restreindre dans `docker-compose.yml` à `127.0.0.1:3307:3306` |
| `boutik_user` accessible depuis `%` | Restreindre à `'boutik_user'@'172.18.%'` |
| Session `file` | Passer à Redis pour multi-réplicas |
| `MAIL_MAILER=log` | Configurer un vrai SMTP |
| HTTPS absent | Mettre Traefik / Nginx-proxy en frontal |
| Backups | Activer `spatie/laravel-backup` planifié vers S3 |

---

## 10. Pièges rencontrés et solutions appliquées

### 10.1 Variables OS du conteneur écrasent le `.env` Laravel

**Symptôme** : malgré un `.env` correct, `php artisan migrate` se connecte
avec le mot de passe WordPress.

**Cause** : `env()` Laravel lit `getenv()` AVANT le `.env`. Le `docker-compose.yml`
principal injecte `DB_PASSWORD: ${DB_PASSWORD}` dans le conteneur web pour
WordPress, ce qui écrase la valeur Boutik.

**Solution** :
- HTTP : `SetEnv DB_PASSWORD ...` dans `<Location /boutik>` du `boutik.conf`.
- CLI : `docker exec -e DB_PASSWORD='...' artistik-php_web ...` (helper
  `artisan()` dans `install.sh`).

### 10.2 Healthcheck MariaDB échoue

**Symptôme** : `mariadb` reste `unhealthy`, logs `Access denied for user 'root'@'localhost' (using password: NO)`.

**Cause** : la commande par défaut `healthcheck.sh --connect --innodb_initialized`
utilise un compte `healthcheck@localhost` créé au premier boot du volume.
Si le volume MariaDB existait déjà (cas ici), ce compte n'a pas le bon mdp.

**Solution** : surcharge dans l'override Compose :

```yaml
mariadb:
  healthcheck:
    test: ["CMD-SHELL", "mariadb-admin ping -uroot -p\"$$MYSQL_ROOT_PASSWORD\" --silent || exit 1"]
```

### 10.3 Routes Laravel 404 sous l'alias `/boutik`

**Symptôme** : `/boutik/` répond 200 mais `/boutik/login` → `<title>Not Found</title>`.

**Cause** : `RewriteBase` absent du `.htaccess` quand Laravel est servi en
sous-dossier via Alias Apache.

**Solution** : ajouter `RewriteBase /boutik/` juste après `RewriteEngine On`.

### 10.4 `package:discover` échoue (modules nwidart absents)

**Symptôme** : `ReflectionException: Class "Modules\Essentials\Providers\…" not found`.

**Cause** : `modules_statuses.json` déclare 21 modules `true` mais le dossier
`Modules/` n'existe pas (anomalie packaging UltimatePOS — audit §3.4).

**Solution** : forcer tous les modules à `false`. Le script `install.sh` le
fait automatiquement si le dossier `Modules/` est absent.

### 10.5 PHP 8.4 hurle des dépréciations

**Symptôme** : flot de `PHP Deprecated: Implicitly marking parameter as
nullable...`.

**Cause** : UltimatePOS / Laravel 9 conçus pour PHP 8.0/8.1. PHP 8.4 ajoute
des warnings `E_DEPRECATED` non bloquants.

**Solution** :
- Cosmétique : `php -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_STRICT'`
  dans toutes les commandes artisan (déjà fait).
- À terme : downgrader le conteneur en `php:8.2-apache-bookworm` via la
  variable `PHP_BASE_IMAGE` de `.env`.

---

## 11. Roll-back / désinstallation

```bash
cd docker_compose/Apache_Docker_Compose

docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  --env-file .env down

docker compose --env-file .env up -d mariadb
sleep 10
docker exec -i artistik-php_db mariadb -uroot -p"$DB_ROOT_PASSWORD" <<SQL
DROP DATABASE IF EXISTS boutik_db;
DROP USER IF EXISTS 'boutik_user'@'%';
FLUSH PRIVILEGES;
SQL

rm -rf web/Boutik/vendor web/Boutik/.env
rm -rf web/Boutik/storage/framework/cache/*
rm -rf web/Boutik/storage/framework/views/*
rm -rf web/Boutik/bootstrap/cache/*

cp web/Boutik/public/.htaccess.bak web/Boutik/public/.htaccess

docker compose --env-file .env up -d
```

---

## 12. Checklist de mise en production réelle

- [ ] Licence CodeCanyon Extended ou OEM négociée avec UltimatePOS
- [ ] Mots de passe MariaDB régénérés (`openssl rand -base64 24`)
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] HTTPS via Traefik / Caddy / Nginx-proxy avec Let's Encrypt
- [ ] `php-fpm` au lieu de `mod_php`
- [ ] PHP downgradé en 8.2 (compatibilité Laravel 9)
- [ ] Backup quotidien `mariadb-dump` + tar sur S3
- [ ] Cron Laravel : `* * * * * cd /var/www/html/Boutik && php artisan schedule:run`
- [ ] Worker queues (Redis + Horizon)
- [ ] Rate limiting sur `/login` et `/business/register`
- [ ] Captcha activé (`ENABLE_RECAPTCHA=true`)
- [ ] Logs envoyés vers Sentry / Bugsnag
- [ ] **Localisation Cameroun complète** (cf. §8)
- [ ] Tests Feature critiques écrits
- [ ] Module officiel **MTN MoMo + Orange Money CM** (roadmap audit §11 P1)
- [ ] Module officiel **FNE / Facture normalisée DGI** (roadmap audit §11 P1)

---

## Annexe — Résultats du déploiement validé (2026-05-03)

```text
Conteneurs
  artistik-php_db      Up (healthy)
  artistik-php_web     Up (healthy)
  artistik-php_pma     Up

Base de données boutik_db
  70 tables créées
  299/299 migrations appliquées
  141 devises seedées
  81 permissions seedées

Smoke tests HTTP
  /boutik                       HTTP 200  (page welcome)
  /boutik/login                 HTTP 200  ("S'identifier - Boutik")
  /boutik/business/register     HTTP 200  ("Registre - Boutik")

Locale runtime
  Langue : fr
  Fuseau : Africa/Douala
  URL    : http://localhost:8080/boutik
```

Cohabitation avec WordPress vérifiée : `http://localhost:8080/artistik_cm`
continue de répondre normalement, base `artistik_cm_db` inchangée.

---

## Annexe B — Piège complémentaire rencontré : volume MariaDB hérité

Lors de l'application de l'override Compose, **WordPress (`/artistik_cm/`) a
basculé en HTTP 500** (`Error establishing a database connection`).

### Diagnostic

Inspection des bases :
```sql
SHOW DATABASES;
-- app_database, boutik_db, information_schema, mysql, performance_schema, sys
```

La base `artistik_cm_db` (déclarée dans `.env` racine) **n'existait pas**.
Le volume MariaDB `./data/mariadb` avait été initialisé à un moment
antérieur avec `MYSQL_DATABASE=app_database` (valeur par défaut du compose).
Quand l'utilisateur a renommé sa base à `artistik_cm_db` dans `.env`, MariaDB
n'a rien refait (les vars `MYSQL_*` ne s'appliquent qu'au **premier** boot).

Le script `ensure-mysql-database.php` du compose principal aurait dû créer
`artistik_cm_db` à chaque démarrage, mais il s'est appuyé sur `getenv('DB_NAME')`
qui valait `app_database` au moment où il a tourné la première fois.
Résultat : `app_user` avait des privilèges sur `app_database` mais pas sur
`artistik_cm_db`, et `artistik_cm_db` n'existait même pas.

### Correctif appliqué (clonage)

```bash
docker exec -i artistik-php_db sh -c 

---

## Annexe B — Piège complémentaire rencontré : volume MariaDB hérité

Lors de l'application de l'override Compose, **WordPress (`/artistik_cm/`) a
basculé en HTTP 500** (`Error establishing a database connection`).

### Diagnostic

Inspection des bases :

```sql
SHOW DATABASES;
-- app_database, boutik_db, information_schema, mysql, performance_schema, sys
```

La base `artistik_cm_db` (déclarée dans `.env` racine) **n'existait pas**.
Le volume MariaDB `./data/mariadb` avait été initialisé à un moment
antérieur avec `MYSQL_DATABASE=app_database` (valeur par défaut du compose).
Quand l'utilisateur a renommé sa base à `artistik_cm_db` dans `.env`, MariaDB
n'a rien refait (les vars `MYSQL_*` ne s'appliquent qu'au **premier** boot).

Le script `ensure-mysql-database.php` du compose principal aurait dû créer
`artistik_cm_db` à chaque démarrage, mais il s'est appuyé sur `getenv('DB_NAME')`
qui valait `app_database` au moment où il a tourné la première fois.
Résultat : `app_user` avait des privilèges sur `app_database` mais pas sur
`artistik_cm_db`, et `artistik_cm_db` n'existait même pas.

### Correctif appliqué (clonage de app_database vers artistik_cm_db)

```bash
docker exec -i artistik-php_db sh -c '
  mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS artistik_cm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON artistik_cm_db.* TO \"app_user\"@\"%\"; FLUSH PRIVILEGES;"
  mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction app_database \
    | mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" artistik_cm_db
'
```

### Recommandation pour la stack

Trois options possibles :

1. **Renommer la base dans le compose principal** : revenir à
   `MYSQL_DATABASE=app_database` dans `.env` pour rester cohérent avec le
   volume historique. Laisser Boutik utiliser `boutik_db` (déjà fait).
2. **Ajouter une étape de migration au démarrage** : si `artistik_cm_db`
   n'existe pas mais `app_database` oui, cloner automatiquement (extension
   du script `ensure-mysql-database.php`).
3. **Détruire le volume `./data/mariadb`** et tout réinitialiser proprement
   (**DESTRUCTIF** — perd les données existantes).

> Ce piège est spécifique à votre installation actuelle, pas inhérent au
> déploiement Boutik. Boutik n'a fait que **révéler** un état déjà cassé
> du volume MariaDB.
