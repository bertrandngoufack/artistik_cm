# Index du livrable Boutik — déploiement

| Document | Description |
|----------|-------------|
| [PROCEDURE_DEPLOIEMENT_DETAILLEE.md](PROCEDURE_DEPLOIEMENT_DETAILLEE.md) | Procédure pas à pas : prérequis, Docker, Apache, base, Laravel, vérifications |
| [ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md](ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md) | **Référence unique** des URL, comptes applicatifs, MariaDB, fichiers sensibles |

## Contenu de l’archive (dossiers)

| Dossier | Rôle |
|---------|------|
| `02_SQL/` | Création de la base + dump complet `boutik_db` |
| `03_APACHE/` | Configuration d’alias `/boutik` |
| `04_DOCKER/` | Override Docker Compose |
| `05_SCRIPTS/` | `install.sh`, `.env.boutik`, scripts PHP |
| `06_LARAVEL_ENV/` | Fichier `.env` de référence |
| `07_CODE_REFERENCE/` | Extraits critiques (`.htaccess`) |
| `08_DOCUMENTATION_REPO/` | Copies des documents d’audit / procédure du dépôt `web/Boutik/docs/Boutik/` |

## Hypothèse d’environnement cible

- Stack **artistik-php** (Apache + PHP 8.4 + MariaDB 11.4) déjà décrite dans le dépôt `docker_compose/Apache_Docker_Compose/`.
- Boutik cohabite avec WordPress sur le **même** conteneur web et la **même** instance MariaDB (base dédiée `boutik_db`).

## Sécurité (production)

Les mots de passe figurant dans ce livrable correspondent à un **environnement de démonstration / laboratoire**. En production : les remplacer tous, régénérer `APP_KEY`, désactiver `APP_DEBUG`, durcir les accès MariaDB et les secrets dans un coffre (Vault, Ansible Vault, etc.).
