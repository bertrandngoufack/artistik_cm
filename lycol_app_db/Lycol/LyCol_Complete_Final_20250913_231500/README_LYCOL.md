# 🎓 LYCOL - Solution de Gestion Scolaire

**LyCol** est une solution complète de gestion scolaire adaptée au système éducatif camerounais, développée avec CodeIgniter 4 et Bulma CSS.

## 📋 Caractéristiques

### 🏗️ Architecture
- **Framework :** CodeIgniter 4 avec PHP 8.4
- **Base de données :** MariaDB 12
- **Interface :** Bulma CSS 1.0.4 (responsive et moderne)
- **API :** RESTful avec documentation Swagger
- **Sécurité :** RBAC (Role-Based Access Control)

### 📦 Modules intégrés
1. **Économat** - Gestion financière complète
2. **Scolarité** - Suivi des élèves et discipline
3. **Études** - Classes, matières, emplois du temps
4. **Examens** - Notes, bulletins, conseils de classe
5. **Statistiques** - Analyses et rapports
6. **Bibliothèque** - Gestion des livres et emprunts
7. **Messagerie** - SMS, email, WhatsApp
8. **Sécurité** - Gestion des accès et licences

### 🔐 Système de licences
- **Période d'essai :** 1 trimestre gratuit
- **Licence annuelle :** Renouvelable sur 2 ans
- **Contrôle automatique :** Déconnexion après 20 min si licence expirée

## 🚀 Installation

### Prérequis
- PHP 8.4 ou supérieur
- MariaDB 12 ou MySQL 8.0
- Extensions PHP : mbstring, intl, xml, curl, zip, sqlite3, mysql

### 1. Configuration de la base de données

```bash
# Connexion à MariaDB
mysql -h 100.69.65.33 -P 13306 -u root -p

# Exécution du script SQL
source database/lycol_schema.sql
```

### 2. Configuration de l'application

```bash
# Copier le fichier d'environnement
cp env .env

# Éditer la configuration
nano .env
```

Configuration minimale dans `.env` :
```env
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8081/'

# Base de données
database.default.hostname = 100.69.65.33
database.default.database = lycol_db
database.default.username = root
database.default.password = Bateau123
database.default.port = 13306
```

### 3. Installation des dépendances

```bash
# Installation via Composer
composer install

# Ou si Composer n'est pas installé
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install
```

### 4. Permissions des dossiers

```bash
# Définir les permissions
chmod -R 755 writable/
chmod -R 755 public/assets/
```

### 5. Démarrage du serveur

```bash
# Serveur de développement
php spark serve --port 8081 --host 0.0.0.0

# Ou serveur PHP simple
php -S 0.0.0.0:8081 -t public
```

## 🔧 Configuration

### Console d'administration
Accédez à `http://localhost:8081/admin` pour configurer :
- Paramètres généraux de l'école
- Fournisseurs SMS/Email/WhatsApp
- Gestion des licences
- Configuration des modules

### Première connexion
- **URL :** `http://localhost:8081/admin`
- **Utilisateur :** `admin`
- **Mot de passe :** `admin123`
- **⚠️ Important :** Changez le mot de passe après la première connexion

## 📊 Utilisation

### Gestion des élèves
1. **Inscription :** Module Scolarité → Inscriptions
2. **Suivi :** Module Scolarité → Élèves
3. **Absences :** Module Scolarité → Absences
4. **Discipline :** Module Scolarité → Sanctions

### Gestion financière
1. **Frais :** Module Économat → Types de frais
2. **Paiements :** Module Économat → Paiements
3. **Budget :** Module Économat → Budget
4. **Dépenses :** Module Économat → Dépenses

### Gestion académique
1. **Classes :** Module Études → Classes
2. **Matières :** Module Études → Matières
3. **Examens :** Module Examens → Examens
4. **Notes :** Module Examens → Saisie des notes
5. **Bulletins :** Module Examens → Bulletins

### Communication
1. **Messages :** Module Messagerie → Nouveau message
2. **Modèles :** Module Messagerie → Modèles
3. **Envoi groupé :** Module Messagerie → Envoi groupé

## 🔌 API RESTful

### Documentation Swagger
- **URL :** `http://localhost:8081/api/docs`
- **Authentification :** Bearer Token

### Endpoints principaux
```
GET    /api/students              # Liste des élèves
GET    /api/students/{id}         # Détails d'un élève
GET    /api/students/{id}/grades  # Notes d'un élève
GET    /api/students/{id}/absences # Absences d'un élève
POST   /api/grades                # Saisie de notes
GET    /api/report-cards/{id}     # Bulletin d'un élève
```

### Exemple d'utilisation API
```bash
# Récupérer les informations d'un élève
curl -X GET "http://localhost:8081/api/students/123" \
     -H "Authorization: Bearer YOUR_TOKEN"

# Saisir une note
curl -X POST "http://localhost:8081/api/grades" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "student_id": 123,
       "exam_id": 456,
       "subject_id": 789,
       "marks_obtained": 15.5
     }'
```

## 🔐 Système de licences

### Génération de licences
```php
use App\Libraries\LicenseGenerator;

// Licence d'essai (3 mois)
$trialLicense = LicenseGenerator::generateTrialLicense('CLIENT123');

// Licence annuelle
$annualLicense = LicenseGenerator::generateAnnualLicense('CLIENT123', 'PRO');

// Licence pour 2 ans
$biennialLicense = LicenseGenerator::generateBiennialLicense('CLIENT123', 'ENTERPRISE');
```

### Validation de licences
```php
$validation = LicenseGenerator::validateLicenseKey(
    $licenseKey,
    $clientId,
    $licenseType,
    $expiryDate
);

if ($validation['valid']) {
    echo "Licence valide - " . $validation['details']['daysRemaining'] . " jours restants";
} else {
    echo "Licence invalide : " . $validation['reason'];
}
```

## 📱 Interface mobile

### Saisie des notes via mobile
- **URL :** `http://localhost:8081/mobile/grades`
- **Authentification :** Code enseignant
- **Fonctionnalités :**
  - Saisie rapide des notes
  - Validation automatique
  - Synchronisation en temps réel

### Espace parents
- **URL :** `http://localhost:8081/parents`
- **Accès :** Matricule + année de naissance
- **Fonctionnalités :**
  - Consultation des bulletins
  - Suivi des absences
  - Communication avec l'école

## 📈 Statistiques et rapports

### Rapports disponibles
1. **Statistiques par classe**
2. **Taux de réussite**
3. **Meilleurs élèves**
4. **Analyses des notes**
5. **Rapports financiers**
6. **Statistiques d'absence**

### Export de données
- **Format CSV** pour intégration sur sites web
- **API publique** pour consultation externe
- **Rapports PDF** pour impression

## 🔧 Maintenance

### Sauvegarde de la base de données
```bash
# Sauvegarde complète
mysqldump -h 100.69.65.33 -P 13306 -u root -p lycol_db > backup_$(date +%Y%m%d).sql

# Restauration
mysql -h 100.69.65.33 -P 13306 -u root -p lycol_db < backup_20250822.sql
```

### Mise à jour
```bash
# Sauvegarde avant mise à jour
cp -r . ../lycol_backup_$(date +%Y%m%d)

# Mise à jour du code
git pull origin main

# Mise à jour de la base de données
php spark migrate

# Vérification
php spark db:show_tables
```

### Logs et débogage
```bash
# Logs de l'application
tail -f writable/logs/log-*.php

# Logs d'erreur PHP
tail -f /var/log/php_errors.log

# Vérification de la configuration
php spark config:show
```

## 🆘 Support et dépannage

### Problèmes courants

#### Erreur de connexion à la base de données
```bash
# Vérifier la connectivité
telnet 100.69.65.33 13306

# Tester la connexion MySQL
mysql -h 100.69.65.33 -P 13306 -u root -p -e "SELECT 1;"
```

#### Problème de permissions
```bash
# Corriger les permissions
chmod -R 755 writable/
chown -R www-data:www-data writable/
```

#### Licence expirée
1. Accéder à la console d'administration
2. Module Sécurité → Licences
3. Générer une nouvelle licence
4. Activer la nouvelle licence

### Support technique
- **Documentation :** `http://localhost:8081/docs`
- **Forum :** https://forum.lycol.cm
- **Email :** support@lycol.cm
- **Téléphone :** +237 XXX XXX XXX

## 📄 Licence

LyCol est distribué gratuitement en version d'essai pour un (01) trimestre.

**Conditions d'utilisation :**
- Utilisation limitée à un établissement scolaire
- Période d'essai de 3 mois
- Licence annuelle renouvelable
- Support technique inclus

## 🤝 Contribution

Nous acceptons les contributions de la communauté :
- Signalement de bugs
- Suggestions d'amélioration
- Développement de modules
- Traductions

**Repository :** https://github.com/lycol/lycol-school

---

**© 2025 LyCol - Solution de Gestion Scolaire**
*Développé pour le système éducatif camerounais*








