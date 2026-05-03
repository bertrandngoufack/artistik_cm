# Annexe — Identifiants, URL et mots de passe (référence livrable)

> **Avertissement :** valeurs issues de l’environnement documenté pour Artistik CM / Boutik (2026). À **rotater intégralement** avant toute mise en production ou exposition réseau.

## 1. Accès applicatif Boutik

| Élément | Valeur |
|---------|--------|
| URL racine (exemple local) | `http://localhost:8080/boutik` |
| URL de connexion | `http://localhost:8080/boutik/login` |
| Nom d’utilisateur administrateur | `boutik_admin` |
| Mot de passe administrateur | `Boutik@2026` |
| E-mail admin (fiche utilisateur) | `admin@boutik.cm` |
| Entreprise démo (seeder) | Boutik Demo Cameroun |
| Fuseau horaire session / entreprise | `Africa/Douala` |
| Langue interface par défaut (seeder) | `fr` |

## 2. Base de données — compte dédié Boutik

| Élément | Valeur |
|---------|--------|
| Moteur | MariaDB 11.x (MySQL compatible) |
| Hôte (Docker, réseau interne) | `mariadb` |
| Port **externe** (hôte → conteneur, fichier `.env` compose) | `3307` (variable `DB_PORT_MAPPING`) |
| Port **interne** (Laravel dans le conteneur web) | `3306` |
| Nom de la base | `boutik_db` |
| Utilisateur applicatif | `boutik_user` |
| Mot de passe applicatif | `Boutik_Strong_Pass_2026!` |

## 3. Base de données — compte administrateur MariaDB (instance partagée)

| Élément | Valeur |
|---------|--------|
| Utilisateur | `root` |
| Mot de passe root | `Bateau123` |

Ce compte sert à créer la base `boutik_db`, l’utilisateur `boutik_user`, et à importer le dump SQL.

## 4. Cohabitation WordPress (même stack)

Le fichier `.env` du Compose principal définit également (à ne pas confondre avec Boutik) :

| Élément | Valeur |
|---------|--------|
| Base WordPress | `artistik_cm_db` (ou historique `app_database` selon migration) |
| Utilisateur applicatif WP | `app_user` |
| Mot de passe | `Bateau123` |

Laravel Boutik **ne doit pas** utiliser ces identifiants : le fichier `boutik.conf` Apache force `DB_DATABASE=boutik_db` pour les requêtes sous `/boutik`.

## 5. phpMyAdmin (si service activé dans le compose)

| Élément | Valeur |
|---------|--------|
| URL typique | `http://localhost:8081` |
| Connexion MariaDB | user `root` / mot de passe `Bateau123` (selon `.env` compose du projet) |

## 6. Fichier `.env` Laravel (référence)

Une copie commentée est fournie dans `06_LARAVEL_ENV/.env.reference_boutik`.  
Extraits critiques :

| Clé | Valeur (livrable) |
|-----|-------------------|
| `APP_URL` | `http://localhost:8080/boutik` |
| `DB_HOST` | `mariadb` |
| `DB_DATABASE` | `boutik_db` |
| `DB_USERNAME` | `boutik_user` |
| `DB_PASSWORD` | `Boutik_Strong_Pass_2026!` |
| `APP_KEY` | voir fichier de référence (à régénérer si nouvelle installation **sans** restauration du dump) |

**Important :** si vous restaurez `01_boutik_db_full_dump.sql`, conservez le même `APP_KEY` que celui ayant servi à générer les données chiffrées en session, ou planifiez une déconnexion globale et une régénération maîtrisée des secrets applicatifs.

## 7. Variables du script `install.sh`

Fichier `05_SCRIPTS/.env.boutik` :

| Variable | Rôle |
|----------|------|
| `WEB_CONTAINER` | `artistik-php_web` |
| `DB_CONTAINER` | `artistik-php_db` |
| `BOUTIK_DB_NAME` | `boutik_db` |
| `BOUTIK_DB_USER` | `boutik_user` |
| `BOUTIK_DB_PASSWORD` | `Boutik_Strong_Pass_2026!` |
| `DB_ROOT_PASSWORD` | `Bateau123` |
| `BOUTIK_APP_URL` | `http://localhost:8080/boutik` |

## 8. Conformité licence / audit

Boutik est dérivé d’**Ultimate POS**. Vérifier les obligations de licence (CodeCanyon / Envato) avant redistribution ou hébergement commercial. Voir aussi la documentation d’audit dans le dépôt : `web/Boutik/docs/Boutik/01_AUDIT_ARCHITECTURE.md` (non recopiée dans cette archive par défaut ; ajouter si besoin contractuel).
