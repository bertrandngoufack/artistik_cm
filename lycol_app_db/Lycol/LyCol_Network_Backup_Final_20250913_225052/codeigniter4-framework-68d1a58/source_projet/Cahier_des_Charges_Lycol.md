## Cahier des charges — Solution de gestion scolaire « Lycol »

Version: 1.0  
Date: 2025-08-22

### 1. Contexte et objectif
« Nous venons vous donner confiance ». Lycol est une solution adaptée au système éducatif camerounais permettant la gestion intégrale des établissements: Maternelle, Primaire, Secondaire, Universitaire et Centres de formation professionnelle. Quelle que soit la taille de l’établissement, le résultat attendu est le même: une gestion fiable, moderne, sécurisée et interopérable.

### 2. Périmètre fonctionnel (modules)
Les modules ci-dessous sont à livrer, activables/désactivables depuis la console d’administration. Les intitulés reprennent les plaquettes fournies (Economat, Scolarité, Études, Examens, Statistiques, Discipline, Publipostage/Messagerie, Bibliothèque, Personnel, Sécurité) et les nouveautés de la version 21.

- **Economat**
  - Inscription, gestion des pensions et tranches
  - Budget: recettes, dépenses, rubriques; états de caisse
  - Bus scolaire, cartes d’accès et contrôles
  - Salaires du personnel, bulletin de paie, barrières par tranche
- **Scolarité**
  - Dossier élève (matricule unique), suivi académique, listes et fiches
  - Heures d’absence, conseil de discipline et sanctions
  - Éditions: listes provisoires/définitives, trombinoscope
- **Études**
  - Classes, cycles, séries; matières et répartition par classe
  - Cahier de texte, disponibilité des enseignants
  - Emploi du temps automatique, programme des enseignants
- **Examens**
  - Saisie des notes (web et téléphone), bulletins, relevés
  - Conseil de classe automatique; stages et soutenances
  - Génération des anonymats; PV des notes; barèmes par compétence
- **Statistiques**
  - PV d’examens; fiches de notes et moyennes; taux de réussite
  - Classement par classe/établissement; meilleurs élèves; effectifs
- **Discipline**
  - Feuilles d’appel et saisie d’absences (journalière/période)
  - Procès-verbaux, conseils et sanctions; statistiques
- **Bibliothèque**
  - Catalogage, exemplaires, abonnés
  - Emprunt/retour, historique, liste noire
- **Messagerie (Publipostage)**
  - SMS/Email/WhatsApp; modèles de messages
  - Envoi de bulletins et éléments disciplinaires
  - Gestion des souscripteurs et fournisseurs (SMTP/SMS/WhatsApp)
- **Personnel**
  - Dossiers, affectations, profils et comptes utilisateurs
  - Droits d’accès granulaires par classe/matière
- **Sécurité & Accès**
  - Comptes et profils; RBAC (rôles/permissions) lecture/écriture
  - Sessions, journaux d’audit, politiques de mot de passe
- **Nouveautés (bonus)**
  - Nouvelle interface (Bulma 1.0.4), parents connectés (portail),
    emploi du temps automatique intégré, saisie des notes sur mobile,
    nouvelles éditions et rapports

### 3. Exigences non fonctionnelles
- **Performance**: pages < 2s sur réseau local; API < 500ms en moyenne
- **Sécurité**: RBAC, chiffrement `bcrypt` des mots de passe, HTTPS/TLS, journalisation d’audit, RGPD-like (minimisation, droit d’accès)
- **Disponibilité**: sauvegardes journalières; restauration testée mensuellement
- **Évolutivité**: architecture modulaire; ajout de modules sans migration lourde
- **Interopérabilité**: API REST JSON, documentation OpenAPI 3.0 (Swagger)
- **Accessibilité**: UI réactive, lisible, compatible mobile/tablette
- **Langue**: FR par défaut; extensible i18n

### 4. Architecture technique
- **Back-end**: PHP 8.4, Framework CodeIgniter 4  
  Documentation: [Guide CodeIgniter 4](https://codeigniter.com/user_guide/intro/index.html)
- **Base de données**: MariaDB (UTF8MB4), moteur InnoDB  
  Hôte: `100.69.65.33`, Port: `13306`, Utilisateur: `root`, Mot de passe: `Bateau123`
- **Front-end**: Bulma 1.0.4 (dernier), JS vanilla/ES6
- **API**: RESTful, versionnées (`/api/v1`), sécurisées par clés API et JWT
- **Docs API**: Swagger/OpenAPI générée automatiquement (annotations + endpoint `/docs`)
- **Configuration**: fichier `.env` complet; console d’administration pour gérer tous les paramètres (SMTP, WhatsApp, fournisseurs SMS, modules, licences, etc.)
- **Fichiers & Import**: export CSV des données (bulletins, absences, discipline); import CSV pour site web externe si fonctionnement autonome

### 5. Console d’administration
- Paramétrage global (nom, année scolaire en cours, logo, fuseau horaire)  
- Gestion des modules (ajout, activation, désactivation, suppression)  
- SMTP/Email, WhatsApp Business API, fournisseurs SMS (clés, webhooks)  
- Gestion des accès (rôles, permissions, affectations par classe/matière)  
- Gestion des licences: saisie, validation, renouvellement; alertes d’expiration  
- Visualisation des journaux d’audit et des queues d’envoi (SMS/Email)  
- Gestion du `.env` (édition sécurisée des variables autorisées)

### 6. Licences
- Licence annuelle, saisissable pour 2 ans, renouvelable à la demande  
- En absence de renouvellement: déconnexion automatique après 20 min d’utilisation  
- Stockage: table `licenses` (clé, type, dates, statut, dernière validation)  
- Algorithme fourni (voir classe PHP) avec segments aléatoires + année d’expiration  
- Clé secrète (`LICENSE_SECRET_SEED`) stockée dans `.env`

### 7. API publiques — cas d’usage clés
- **Récupération du bulletin**: `GET /api/v1/students/{matricule}/reports?dob=YYYY-MM-DD&year=YYYY`  
  Retourne notes, moyennes, rang, absences, discipline
- **Absences**: `GET /api/v1/students/{matricule}/attendance?dob=YYYY-MM-DD&period=...`
- **Discipline**: `GET /api/v1/students/{matricule}/discipline?dob=YYYY-MM-DD&year=...`
- **Export CSV**: `GET /api/v1/exports/bulletins.csv?year=YYYY`  
- Accès protégé par clé API et filtrage IP (optionnel)

### 8. Modèle de données (vue d’ensemble)
- Noyau: `schools`, `academic_years`, `users`, `roles`, `permissions`, `modules`, `settings`, `licenses`
- Élèves: `students`, `guardians`, `enrollments`
- Pédagogie: `classes`, `subjects`, `teachers`, `timetables`
- Évaluations: `exam_sessions`, `evaluations`, `grades`, `report_cards`, `anonymats`
- Discipline & présence: `attendance`, `incidents`, `sanctions`
- Bibliothèque: `books`, `book_copies`, `subscribers`, `borrows`, `blacklist`
- Economat: `fees`, `fee_tranches`, `payments`, `budgets`, `transactions`, `payroll_*`, `bus_passes`
- Messagerie: `message_templates`, `outbox`, `providers`
- Sécurité: `api_clients`, `api_keys`, `audit_logs`

Le script SQL fourni crée ces tables et les index essentiels (matricule, année, clés étrangères).

### 9. UX/UI
- Thème professionnel Bulma, palette douce, composants accessibles  
- Menu latéral modulable; tableaux filtrables; exports CSV; pagination  
- Dashboard par rôle (indicateurs clés: effectifs, encaissements, taux de réussite)

### 10. Déploiement & maintenance
- `.env` par environnement; migrations CI4; seeders modulaires  
- Sauvegardes automatiques MySQL/MariaDB; rotation des logs  
- CI/CD (optionnel) et tests automatisés (unitaires + API)

### 11. Livrables
- Code source CI4, schéma SQL MariaDB, classe licence PHP  
- Documentation utilisateur et d’administration  
- Documentation API (Swagger/OpenAPI), fichier `.env.example`

### 12. Critères d’acceptation
- Installation complète sur la base distante fournie  
- Connexion et configuration des providers (SMTP/SMS/WhatsApp)  
- Génération et validation d’une licence fonctionnelle  
- Publication de la documentation API sur `/docs`  
- Exports CSV opérationnels et endpoints matricule+date de naissance

### 13. Distribution
LYCOL est distribué gratuitement en version d’essai pour un (01) trimestre.