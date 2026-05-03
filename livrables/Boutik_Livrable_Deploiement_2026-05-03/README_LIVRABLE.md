# Livrable de déploiement — Boutik (Artistik)

**Référence :** `Boutik_Livrable_Deploiement_2026-05-03`  
**Application :** Boutik — gestion commerciale / POS (Laravel 9, base UltimatePOS)  
**Cible :** stack Docker Artistik — Apache 2.4 + PHP 8.4 + MariaDB 11.4, publication sous **`/boutik`**.

**Lisez d’abord :** `STRUCTURE_CIBLE_DANS_LE_DEPOT_ARTISTIK.md` pour l’arborescence attendue.

---

## Contenu de l’archive

| Dossier | Description |
|--------|-------------|
| **01_Documentation** | Audit architecture, procédure longue, livrable synthèse (Markdown). |
| **02_Paquet_Installation** | Scripts, configuration Apache, override Compose, modèle **`env.boutik.MODELE`**. |
| **03_Code_Application_Boutik** | Code source **sans** `vendor/`, `node_modules/`, caches Laravel ni fichier `.env` (secrets). |

---

## Intégration dans le dépôt Artistik

Après décompression, placer les contenus ainsi (chemins relatifs à la racine du dépôt `artistik_cm`) :

```text
docker_compose/Apache_Docker_Compose/web/Boutik/     ← contenu de 03_Code_Application_Boutik/
docker_compose/Apache_Docker_Compose/deploy/boutik/  ← contenu de 02_Paquet_Installation/
```

Les documents de **01_Documentation** peuvent être recopiés vers  
`docker_compose/Apache_Docker_Compose/web/Boutik/docs/Boutik/` (optionnel).

---

## Séquence de déploiement (ordre recommandé)

1. **Prérequis**  
   Docker Engine + Compose v2, stack Artistik construite, conteneurs `artistik-php_web` et `artistik-php_db` démarrés et sains.

2. **Fusion des sources**  
   Copier `03_Code_Application_Boutik/` et `02_Paquet_Installation/` aux emplacements ci-dessus.

3. **Variables d’installation**  
   - Copier `deploy/boutik/env.boutik.MODELE` vers `deploy/boutik/.env.boutik`.  
   - Remplacer les marqueurs `<...>` par les mots de passe et l’URL réels.  
   - Ajuster `WEB_CONTAINER`, `DB_CONTAINER` si vos noms diffèrent.

4. **Dépendances PHP (obligatoire)** — le livrable **n’inclut pas** `vendor/` :

   ```bash
   docker exec -u www-data -w /var/www/html/Boutik artistik-php_web composer install --no-dev --optimize-autoloader
   ```

   Voir aussi `03_Code_Application_Boutik/RESTAURATION_DEPENDANCES.md`.

5. **Configuration Laravel**  
   - Créer `web/Boutik/.env` à partir de `web/Boutik/.env.example`.  
   - Aligner `APP_URL`, base de données, `APP_KEY` (`php artisan key:generate`), fuseau `Africa/Douala`, etc.  
   - Vérifier que `public/.htaccess` contient **`RewriteBase /boutik/`**.

6. **Override Docker & Apache**  
   Démarrer ou mettre à jour la stack avec l’override Boutik (détail : **01_Documentation / 03_LIVRABLE_DEPLOIEMENT.md**).

7. **Exécution du script d’installation**  

   ```bash
   cd docker_compose/Apache_Docker_Compose
   bash deploy/boutik/install.sh
   ```

8. **Vérifications & post-install**  
   Connexion HTTP sur l’URL configurée (`.../boutik/login`), critères d’acceptation et compte démo : **01_Documentation / 03_LIVRABLE_DEPLOIEMENT.md**.

---

## Sécurité

- Ne pas versionner `.env.boutik` ni `.env` avec des secrets réels en production.  
- Changer tous les mots de passe par défaut avant exposition sur Internet.

---

## Documentation détaillée

| Fichier | Usage |
|---------|--------|
| `01_Documentation/03_LIVRABLE_DEPLOIEMENT.md` | **Point d’entrée opérationnel** : inventaire, étapes, critères d’acceptation. |
| `01_Documentation/02_DEPLOIEMENT_PROCEDURE.md` | Procédure longue, dépannage (PHP 8.4, CSRF, CDN, i18n). |
| `01_Documentation/01_AUDIT_ARCHITECTURE.md` | Contexte technique et licence. |
| `02_Paquet_Installation/README.md` | Rappel du paquet `deploy/boutik`. |

---

## Support technique

Les scripts supposent la **même arborescence** que le projet Artistik d’origine (`Apache_Docker_Compose` → `web/Boutik`, `deploy/boutik`). Toute autre structure impose d’adapter les chemins dans `install.sh`, `boutik.conf` et les montages Compose.
