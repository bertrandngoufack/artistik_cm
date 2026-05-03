# Livrable déploiement — Boutik (stack Artistik / Docker)

Ce répertoire constitue le **paquet d’installation** prêt à l’emploi pour publier l’application **Boutik** (Laravel 9, base UltimatePOS) sur la stack Apache + PHP + MariaDB du projet `Apache_Docker_Compose`, **en sous-chemin `/boutik`**, sans remplacer le site WordPress existant.

## Documents de référence (dans le dépôt)

| Document | Emplacement | Rôle |
|----------|-------------|------|
| **Livrable déploiement (synthèse professionnelle)** | `web/Boutik/docs/Boutik/03_LIVRABLE_DEPLOIEMENT.md` | Vue d’ensemble, inventaire, procédure numérotée, critères d’acceptation |
| Procédure détaillée & dépannage | `web/Boutik/docs/Boutik/02_DEPLOIEMENT_PROCEDURE.md` | Audit post-install, pièges PHP 8.4, CDN, i18n, etc. |
| Audit architecture | `web/Boutik/docs/Boutik/01_AUDIT_ARCHITECTURE.md` | Contexte technique et licence |

## Démarrage rapide (résumé)

1. Démarrer la stack Docker (`manage.sh start` ou équivalent depuis `Apache_Docker_Compose`).
2. Appliquer l’override compose qui monte `apache/boutik.conf` et corrige le healthcheck MariaDB :  
   `docker compose -f docker-compose.yml -f deploy/boutik/docker-compose.boutik.override.yml up -d`
3. Copier `env.boutik.MODELE` vers `.env.boutik` et renseigner les secrets (mots de passe base, URL publique).
4. Exécuter :  
   `bash deploy/boutik/install.sh`
5. Ouvrir l’URL indiquée (ex. `http://localhost:8080/boutik/login`) et se connecter avec le compte créé par `CameroonAdminSeeder` (voir le livrable § accès).

## Contenu de ce dossier

| Élément | Description |
|---------|-------------|
| `install.sh` | Installation idempotente (base, Composer, migrations, seeders, caches) |
| `env.boutik.MODELE` | Modèle → copier en `.env.boutik` (variables pour `install.sh`) |
| `docker-compose.boutik.override.yml` | Montage `boutik.conf`, healthcheck MariaDB |
| `apache/boutik.conf` | Alias `/boutik`, `SetEnv` DB, réglages PHP (sessions / CSRF) |
| `scripts/` | Outils optionnels post-install (i18n FR, seeder copié, etc.) |

Pour le détail opérationnel, variables, ordre des étapes et check-list d’acceptation, consulter **`03_LIVRABLE_DEPLOIEMENT.md`**.
