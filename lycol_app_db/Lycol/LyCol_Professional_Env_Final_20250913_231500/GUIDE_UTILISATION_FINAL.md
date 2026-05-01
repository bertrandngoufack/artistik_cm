# GUIDE D'UTILISATION - KISSAI SCHOOL - LyCol

## 🚀 Démarrage Rapide

### Prérequis
- PHP 8.4.5 ou supérieur
- MariaDB/MySQL
- Composer (pour les dépendances)

### Configuration de la Base de Données
```bash
# Informations de connexion
Host: 100.69.65.33
Port: 13306
Utilisateur: root
Mot de passe: Bateau123
Base de données: lycol_db
```

### Démarrage du Serveur

#### Option 1: Script Automatique (Recommandé)
```bash
# Rendre le script exécutable
chmod +x demarrer_serveur_8080.sh

# Démarrer le serveur
./demarrer_serveur_8080.sh
```

#### Option 2: Commande Manuelle
```bash
# Arrêter les processus existants
pkill -f "spark serve"
pkill -f "php -S"

# Libérer le port 8080
sudo fuser -k 8080/tcp

# Démarrer le serveur
php spark serve --port=8080 --host=0.0.0.0
```

### Accès à l'Application
- **URL principale**: http://localhost:8080
- **Connexion**: http://localhost:8080/auth/login
- **Administration**: http://localhost:8080/admin/configuration

## 📋 Modules Principaux

### 1. Configuration (`/admin/configuration`)
Gestion des paramètres système et de l'apparence.

**Fonctionnalités:**
- ✅ Configuration générale de l'école
- ✅ Gestion des licences logicielles
- ✅ Personnalisation de l'apparence
- ✅ Diagnostics système
- ✅ Statistiques en temps réel

### 2. Licences (`/admin/configuration/license`)
Gestion des licences du système.

**Fonctionnalités:**
- ✅ Vérification du statut de licence
- ✅ Activation de licence définitive
- ✅ Historique des licences
- ✅ Validation automatique

### 3. Économat (`/admin/economat`)
Gestion financière et des paiements.

**Fonctionnalités:**
- ✅ Gestion des paiements
- ✅ Rappels automatiques
- ✅ Notifications
- ✅ Rapports financiers

### 4. Scolarité (`/admin/scolarite`)
Gestion des étudiants et de la vie scolaire.

**Fonctionnalités:**
- ✅ Gestion des étudiants
- ✅ Suivi des absences
- ✅ Gestion de la discipline
- ✅ Rapports scolaires

### 5. Études (`/admin/etudes`)
Gestion des classes et des matières.

**Fonctionnalités:**
- ✅ Gestion des cycles
- ✅ Gestion des classes
- ✅ Gestion des matières
- ✅ Emplois du temps

### 6. Examens (`/admin/examens`)
Gestion des évaluations et des notes.

**Fonctionnalités:**
- ✅ Création d'examens
- ✅ Saisie des notes
- ✅ Bulletins scolaires
- ✅ Statistiques académiques

### 7. Bibliothèque (`/admin/bibliotheque`)
Gestion de la bibliothèque scolaire.

**Fonctionnalités:**
- ✅ Gestion des livres
- ✅ Gestion des emprunts
- ✅ Gestion des membres
- ✅ Rapports bibliothèque

## 🔧 Scripts Utiles

### Tests et Audit
```bash
# Test complet du projet
php test_complet_final_8080.php

# Audit complet
php audit_complet_projet_8080.php

# Correction des références au port
php corriger_references_port_8080.php
```

### Maintenance
```bash
# Vider le cache
php spark cache:clear

# Vérifier les routes
php spark routes

# Vérifier l'environnement
php spark env
```

## 📊 API Endpoints

### Statistiques Système
```http
GET /admin/configuration/system-stats-api
```

### Vérification de Licence
```http
GET /admin/configuration/check-license
```

### Vidage du Cache
```http
POST /admin/configuration/clear-cache
```

## 🎨 Personnalisation

### CSS Personnalisé
Le fichier `public/assets/css/style.css` contient les styles personnalisés pour:
- Couleurs de l'école
- Animations
- Responsive design
- Composants personnalisés

### JavaScript
Le fichier `public/assets/js/app.js` gère:
- Interactions utilisateur
- Appels API
- Notifications
- Gestion des formulaires

## 🔒 Sécurité

### Authentification
- Protection CSRF activée
- Sessions sécurisées
- Validation des formulaires
- Filtres d'authentification

### Recommandations
- Changer les mots de passe par défaut
- Surveiller les logs d'accès
- Sauvegarder régulièrement la base de données
- Maintenir les mises à jour de sécurité

## 📱 Interfaces

### Interface Web
- **Administration**: Interface complète pour les administrateurs
- **Parents**: Interface simplifiée pour les parents
- **Mobile**: Interface adaptée aux appareils mobiles

### Points d'Accès
- **Admin**: http://localhost:8080/admin
- **Parents**: http://localhost:8080/parents
- **Mobile**: http://localhost:8080/mobile

## 🚨 Dépannage

### Problèmes Courants

#### Serveur ne démarre pas sur le port 8080
```bash
# Solution 1: Utiliser le script automatique
./demarrer_serveur_8080.sh

# Solution 2: Libérer le port manuellement
sudo fuser -k 8080/tcp
php spark serve --port=8080 --host=0.0.0.0
```

#### Erreur de connexion à la base de données
```bash
# Vérifier la configuration
cat app/Config/Database.php

# Tester la connexion
mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 -e "SHOW DATABASES;"
```

#### Assets non chargés
```bash
# Vérifier les permissions
chmod -R 755 public/assets/

# Vérifier les liens
ls -la public/assets/css/
ls -la public/assets/js/
```

### Logs et Debug
```bash
# Voir les logs d'erreur
tail -f writable/logs/log-*.php

# Mode debug
# Modifier app/Config/Boot/production.php
```

## 📈 Performance

### Optimisations Recommandées
1. **Cache**: Activer le cache Redis/Memcached
2. **Assets**: Minifier CSS/JS en production
3. **Base de données**: Optimiser les requêtes
4. **Images**: Compresser les images

### Monitoring
- Surveiller l'utilisation CPU/Mémoire
- Vérifier les temps de réponse
- Analyser les logs d'erreur
- Surveiller l'espace disque

## 🔄 Sauvegarde

### Base de Données
```bash
# Sauvegarde complète
mysqldump -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Restauration
mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db < backup_file.sql
```

### Fichiers
```bash
# Sauvegarde des fichiers uploadés
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz writable/uploads/

# Sauvegarde de la configuration
tar -czf config_backup_$(date +%Y%m%d).tar.gz app/Config/
```

## 📞 Support

### Informations de Contact
- **Projet**: KISSAI SCHOOL - LyCol System
- **Version**: 1.0
- **Framework**: CodeIgniter 4.6.3
- **Port**: 8080

### Ressources
- **Documentation CodeIgniter**: https://codeigniter4.github.io/
- **Documentation Bulma**: https://bulma.io/documentation/
- **Logs**: `writable/logs/`

---

**Guide créé le:** 26 Août 2025  
**Version:** 1.0  
**Statut:** ✅ Prêt pour la production




