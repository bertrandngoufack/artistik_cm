# Livrable de déploiement — Boutik

**Application :** Boutik (fork / rebrand opérationnel d’UltimatePOS sur Laravel 9)  
**Cible d’hébergement :** stack Docker **Artistik** — `artistik-php_web` (Apache 2.4 + PHP 8.4) + `artistik-php_db` (MariaDB 11.4)  
**Mode de publication :** sous-chemin HTTP **`/boutik`**, coexistence avec WordPress (`/artistik_cm/` ou racine selon votre `docker-compose`).  
**Date de référence du livrable :** 2026-05-03 (à ajuster lors d’une release interne).

---

## 1. Objet du livrable

Ce document formalise ce qui est **fourni** au client / à l’équipe d’exploitation pour **installer, vérifier et maintenir** Boutik dans l’environnement décrit. Il complète la procédure longue (`02_DEPLOIEMENT_PROCEDURE.md`) par une **vision livrable** : périmètre, inventaire, séquence opératoire, critères d’acceptation et point de contact fichiers.

---

## 2. Périmètre technique livré

| Domaine | Livré |
|---------|--------|
| Code applicatif | Répertoire `web/Boutik/` (Laravel 9, PHP ≥ 8.0) |
| Orchestration | `docker-compose.yml` principal + **override** `deploy/boutik/docker-compose.boutik.override.yml` |
| Reverse / vhost | `deploy/boutik/apache/boutik.conf` (alias `/boutik`, sécurité répertoires, PHP pour sessions) |
| Automatisation | `deploy/boutik/install.sh` + `deploy/boutik/.env.boutik` |
| Données initiales | Migrations Laravel + seeders standards + **`CameroonAdminSeeder`** (compte admin démo CM) |
| Documentation | `docs/Boutik/01_*`, `02_*`, **ce fichier `03_*`** + `deploy/boutik/README.md` |

**Hors périmètre implicite :** refonte métier complète, modules nwidart absents du dépôt (désactivés par le script), hébergement nu sans Docker (possible mais non décrit ici).

---

## 3. Inventaire du paquet `deploy/boutik/`

```
deploy/boutik/
├── README.md                        ← Point d’entrée rapide
├── .env.boutik                      ← Paramètres pour install.sh (DB, URL, flags)
├── install.sh                       ← Déploiement idempotent (exécutable)
├── docker-compose.boutik.override.yml
├── apache/
│   └── boutik.conf                  ← À monter dans le conteneur web
└── scripts/                         ← Utilitaires optionnels post-install
    ├── CameroonAdminSeeder.php      ← Copie de traçabilité (source : database/seeders/)
    ├── finalize_fr_lang.php         ← Finalisation traductions FR
    ├── sync_fr_from_en.php          ← Synchro clés FR depuis EN
    ├── restore_clean_fr.php         ← Nettoyage franglais / [EN]
    ├── fix_escapes.sh
    └── data/
        └── lang_v1_fr_patch.json    ← Données pour finalize_fr_lang.php
```

**Source de vérité applicative :** `web/Boutik/` (dont `public/`, `.env`, `database/seeders/CameroonAdminSeeder.php`).

---

## 4. Prérequis

### 4.1 Infrastructure

- Docker Engine et plugin Compose v2.
- Images et conteneurs de la stack **Artistik** construits et nommés comme dans votre `docker-compose` (par défaut : `artistik-php_web`, `artistik-php_db`).
- Ports exposés connus (ex. **8080** HTTP, **8081** phpMyAdmin si activé).

### 4.2 Accès et secrets

- Identifiants **root** MariaDB cohérents avec le `.env` du compose principal (référencés dans `.env.boutik` : `DB_ROOT_PASSWORD`).
- Décision sur URL publique : `BOUTIK_APP_URL` (ex. `http://localhost:8080/boutik` en dev).

### 4.3 Poste opérateur

- Bash, `curl`, accès Docker (utilisateur dans le groupe `docker` ou équivalent).
- **Ne pas** s’appuyer sur `php` / `composer` sur l’hôte : tout passe par le conteneur web.

---

## 5. Architecture logique (déploiement)

```text
                    ┌─────────────────────────────────────┐
 Client (navigateur)│  http://hôte:8080/boutik/*        │
                    └─────────────────┬───────────────────┘
                                      │
                    ┌─────────────────▼───────────────────┐
                    │  artistik-php_web (Apache)          │
                    │  Alias /boutik → .../Boutik/public  │
                    │  boutik.conf : SetEnv DB_*, PHP     │
                    └─────────────────┬───────────────────┘
                                      │
         ┌────────────────────────────┼────────────────────────────┐
         │                            │                            │
         ▼                            ▼                            ▼
  /var/www/html/Boutik         WordPress (autre alias)      Fichiers statiques
  (Laravel, .env, storage)     /artistik_cm/ ...             montés volume
         │
         └────────────────────────► mariadb : boutik_db
                                     (utilisateur boutik_user)
```

---

## 6. Procédure de déploiement (séquence officielle)

Les étapes sont **ordonnées** ; les numéros servent de référence dans les comptes rendus d’installation.

### Étape 1 — Recevoir et placer les sources

- S’assurer que le dépôt contient `web/Boutik/` complet et `deploy/boutik/`.
- Vérifier que `web/Boutik/public/.htaccess` contient la directive **`RewriteBase /boutik/`** (obligatoire pour le routage Laravel en sous-dossier).

### Étape 2 — Variables `deploy/boutik/.env.boutik`

1. Copier ou éditer `.env.boutik`.
2. Définir au minimum :
   - `BOUTIK_DB_NAME`, `BOUTIK_DB_USER`, `BOUTIK_DB_PASSWORD`
   - `DB_ROOT_PASSWORD` (aligné sur MariaDB du compose principal)
   - `BOUTIK_APP_URL`
   - `WEB_CONTAINER`, `DB_CONTAINER` si vos noms diffèrent
3. Optionnel : `BOUTIK_RUN_SEED=0` pour sauter les seeders (rare) ; `BOUTIK_RUN_CAMEROON_SEED=0` pour ne pas créer le compte démo admin.

### Étape 3 — Fichier `web/Boutik/.env` (Laravel)

- Le `.env` du projet doit refléter la même base, user, URL, `APP_KEY`, timezone Cameroun si souhaité (`Africa/Douala`), locale `fr`.
- **Important :** sous requêtes HTTP, les variables **`SetEnv`** dans `boutik.conf` corrigent le problème des variables d’environnement OS du conteneur (WordPress) qui écrasent la connexion Laravel. Pour **CLI** (`artisan`), utiliser la même technique que `install.sh` (passer `-e DB_*` à `docker exec`).

### Étape 4 — Démarrer la stack avec l’override Boutik

Depuis `docker_compose/Apache_Docker_Compose/` :

```bash
docker compose \
  -f docker-compose.yml \
  -f deploy/boutik/docker-compose.boutik.override.yml \
  up -d
```

Vérifier que `artistik-php_web` et `artistik-php_db` sont **healthy** (healthcheck MariaDB corrigé dans l’override).

### Étape 5 — Conf Apache dans le conteneur

- L’override doit monter `apache/boutik.conf` vers `/etc/apache2/conf-enabled/boutik.conf` (ou mécanisme équivalent dans votre stack).
- Contrôle :

```bash
docker exec artistik-php_web test -f /etc/apache2/conf-enabled/boutik.conf && echo OK
```

### Étape 6 — Exécuter le script d’installation

Depuis le même répertoire compose :

```bash
bash deploy/boutik/install.sh
```

Le script enchaîne (résumé) : vérification conteneurs → création base + user MariaDB → normalisation `modules_statuses.json` → permissions `storage` / `bootstrap/cache` → `composer install` si besoin → `key:generate` → migrations → seeders (`Currencies`, `Permissions`, `Barcodes`) → **`CameroonAdminSeeder`** → `storage:link` → caches Laravel → graceful Apache → smoke test HTTP.

**Forcer une réinstallation complète des dépendances :**

```bash
BOUTIK_FORCE_REINSTALL=1 bash deploy/boutik/install.sh
```

### Étape 7 — Vérifications fonctionnelles minimales

| ID | Contrôle | Résultat attendu |
|----|----------|------------------|
| V1 | `curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/boutik/` | 200 ou 302 |
| V2 | Page `/boutik/login` s’affiche sans erreur PHP visible | OK |
| V3 | POST login (CSRF) : pas de « Page Expired » persistant | Session cookie présent (grâce à `boutik.conf` PHP) |
| V4 | Connexion avec compte seeder | Accès back-office |
| V5 | Cohérence devise / fuseau (interface) | FCFA / Africa/Douala si seeder appliqué |

### Étape 8 — (Optionnel) Finalisation i18n / charte

- Traductions FR : `deploy/boutik/scripts/finalize_fr_lang.php` (voir en-tête du script et `BOUTIK_FR_PATCH_JSON`).
- Détails CDN, langues restreintes, charte `#0e7490` : `02_DEPLOIEMENT_PROCEDURE.md`.

---

## 7. Accès par défaut après `CameroonAdminSeeder`

| Élément | Valeur (à changer en production) |
|---------|-----------------------------------|
| URL | `{BOUTIK_APP_URL}/login` |
| Utilisateur | `boutik_admin` |
| Mot de passe | `Boutik@2026` |
| E-mail | `admin@boutik.cm` |

Relancer uniquement le seeder (si besoin) : voir commande dans `02_DEPLOIEMENT_PROCEDURE.md` § 0.

---

## 8. Cohérence des secrets (check-list)

- [ ] `BOUTIK_DB_PASSWORD` dans `.env.boutik` = mot de passe accordé dans MariaDB pour `boutik_user`
- [ ] Même couple user/mot de passe dans `web/Boutik/.env` (`DB_USERNAME` / `DB_PASSWORD`)
- [ ] Ligne `SetEnv DB_PASSWORD` dans `apache/boutik.conf` alignée (sinon HTTP utilise une autre base)
- [ ] `DB_ROOT_PASSWORD` = root réel du conteneur MariaDB
- [ ] Mots de passe par défaut **changés** avant mise en production

---

## 9. Exploitation courante

| Besoin | Commande / emplacement |
|--------|-------------------------|
| Logs Laravel | `web/Boutik/storage/logs/laravel.log` (dans le conteneur : `/var/www/html/Boutik/storage/logs/`) |
| Logs PHP dédiés `/boutik` | `storage/logs/php-runtime.log` (cf. `boutik.conf`) |
| Logs Apache | `/var/log/apache2/error.log` dans le conteneur web |
| Shell applicatif | `docker exec -it artistik-php_web bash` puis `cd /var/www/html/Boutik` |
| Vider caches Laravel | `docker exec -u www-data -e DB_*… artistik-php_web bash -c 'cd /var/www/html/Boutik && php artisan config:clear && php artisan cache:clear && php artisan view:clear'` (répliquer les `-e` comme dans `install.sh`) |

---

## 10. Désinstallation / rollback (niveau applicatif)

1. Supprimer la base `boutik_db` (ou restaurer un dump antérieur) depuis MariaDB.
2. Retirer l’override compose ou le fichier `boutik.conf` et recharger Apache.
3. Conserver ou archiver `web/Boutik/.env` et les volumes de stockage si audit / reprise nécessaire.

Le détail des pièges (WordPress, healthcheck, modules manquants, PHP 8.4) est dans **`02_DEPLOIEMENT_PROCEDURE.md`**.

---

## 11. Critères d’acceptation du livrable

Le déploiement est **accepté** lorsque :

1. La procédure des **étapes 1 à 7** est réalisable sans contournement non documenté.
2. **`install.sh`** se termine avec message de succès et smoke test HTTP non nul.
3. Un utilisateur peut **se connecter** et accéder au tableau de bord après création du compte seeder.
4. Les documents **`03_LIVRABLE_DEPLOIEMENT.md`**, **`deploy/boutik/README.md`** et **`02_DEPLOIEMENT_PROCEDURE.md`** sont présents et cohérents avec les chemins réels du dépôt.

---

## 12. Références croisées

| Sujet | Document |
|-------|----------|
| Architecture, modules, risques licence | `docs/Boutik/01_AUDIT_ARCHITECTURE.md` |
| Dépannage fin (419 CSRF, CDN, i18n, healthcheck) | `docs/Boutik/02_DEPLOIEMENT_PROCEDURE.md` |
| Point d’entrée dossier scripts | `deploy/boutik/README.md` |

---

*Document : livrable de déploiement — Boutik — usage interne / remise client technique.*
