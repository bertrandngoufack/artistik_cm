# Documentation du Système de Licences - KISSAI SCHOOL

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture du Système](#architecture-du-système)
3. [Génération des Licences](#génération-des-licences)
4. [Validation des Licences](#validation-des-licences)
5. [Gestion des Licences](#gestion-des-licences)
6. [Processus d'Installation](#processus-dinstallation)
7. [Dépannage](#dépannage)
8. [API de Licences](#api-de-licences)

## Vue d'ensemble

Le système de licences de KISSAI SCHOOL est conçu pour gérer l'accès à l'application selon différents types de licences :
- **Licence d'essai** : 90 jours gratuits
- **Licence annuelle** : 1 an renouvelable
- **Licence biennale** : 2 ans renouvelable

### Caractéristiques principales
- Génération automatique de clés de licence sécurisées
- Validation en temps réel des licences
- Système de déconnexion automatique après expiration
- Gestion des fonctionnalités par licence
- Interface d'administration complète

## Architecture du Système

### Composants principaux

#### 1. LicenseGenerator (app/Libraries/LicenseGenerator.php)
```php
class LicenseGenerator
{
    static ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    static SEGMENTS = 4;
    static SEGMENT_LENGTH = 4;
    
    // Méthodes principales
    public static function generateLicenseKey($clientId, $licenseType, $expiryDate, $secretSeed)
    public static function validateLicenseKey($licenseKey, $clientId, $licenseType, $expiryDate, $secretSeed)
    public static function decodeLicenseInfo($licenseKey)
}
```

#### 2. LicenseModel (app/Models/LicenseModel.php)
```php
class LicenseModel extends Model
{
    protected $table = 'licenses';
    protected $allowedFields = ['client_id', 'license_key', 'license_type', 'issued_date', 'expiry_date', 'status'];
    
    // Méthodes principales
    public function getActiveLicense()
    public function validateLicense($licenseKey)
    public function revokeLicense($licenseId)
}
```

#### 3. Base de données
```sql
CREATE TABLE licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id VARCHAR(100) NOT NULL,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    license_type ENUM('TRIAL', 'ANNUAL', 'BIENNIAL') NOT NULL,
    issued_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('ACTIVE', 'EXPIRED', 'REVOKED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Génération des Licences

### Algorithme de génération

1. **Validation des paramètres** : Vérification de la présence de clientId, expiryDate et secretSeed
2. **Création de la signature** : Combinaison des paramètres + secretSeed
3. **Génération des segments** : 3 segments aléatoires + 1 segment avec l'année d'expiration
4. **Format final** : XXXX-XXXX-XXXX-YYYY (où YYYY est l'année d'expiration)

### Exemple de génération

```php
$clientId = 'KISSAI_SCHOOL';
$licenseType = 'TRIAL';
$expiryDate = '2025-03-22';
$secretSeed = 'KISSAI_SECRET_KEY_2025';

$licenseKey = LicenseGenerator::generateLicenseKey(
    $clientId, 
    $licenseType, 
    $expiryDate, 
    $secretSeed
);
// Résultat : ABCD-EFGH-IJKL-2025
```

### Script de génération (fix_license.php)

```php
<?php
require_once 'app/Libraries/LicenseGenerator.php';

$clientId = 'KISSAI_SCHOOL';
$licenseType = 'TRIAL';
$expiryDate = date('Y-m-d', strtotime('+3 months'));
$secretSeed = 'KISSAI_SECRET_KEY_2025';

$newLicenseKey = LicenseGenerator::generateLicenseKey(
    $clientId, $licenseType, $expiryDate, $secretSeed
);

$validation = LicenseGenerator::validateLicenseKey(
    $newLicenseKey, $clientId, $licenseType, $expiryDate, $secretSeed
);

if ($validation['valid']) {
    // Mise à jour en base de données
    $stmt = $pdo->prepare("UPDATE licenses SET license_key = ?, expiry_date = ? WHERE id = 1");
    $result = $stmt->execute([$newLicenseKey, $expiryDate]);
}
?>
```

## Validation des Licences

### Processus de validation

1. **Vérification du format** : Validation de la structure XXXX-XXXX-XXXX-YYYY
2. **Vérification de l'année** : Comparaison avec l'année d'expiration
3. **Vérification de la signature** : Validation cryptographique
4. **Vérification en base** : Contrôle du statut et de la date d'expiration

### Intégration dans l'authentification

```php
// Dans Auth.php - méthode checkLicense()
public function checkLicense()
{
    $licenseModel = new LicenseModel();
    $activeLicense = $licenseModel->getActiveLicense();
    
    if (!$activeLicense) {
        return false;
    }
    
    $validation = LicenseGenerator::validateLicenseKey(
        $activeLicense['license_key'],
        $activeLicense['client_id'],
        $activeLicense['license_type'],
        $activeLicense['expiry_date'],
        'KISSAI_SECRET_KEY_2025'
    );
    
    return $validation['valid'];
}
```

## Gestion des Licences

### Interface d'administration

L'interface de gestion des licences se trouve dans :
- **URL** : `/admin/configuration` (onglet Licence)
- **Vue** : `app/Views/admin/configuration/index.php`

### Fonctionnalités disponibles

1. **Visualisation des licences actives**
2. **Génération de nouvelles licences**
3. **Configuration des paramètres de licence**
4. **Gestion des fonctionnalités par licence**
5. **Historique des licences**

### Paramètres configurables

```php
// Paramètres de licence dans SettingModel
$licenseSettings = [
    'license.secret_seed' => 'KISSAI_SECRET_KEY_2025',
    'license.trial_duration' => 90, // jours
    'license.max_users' => 1000,
    'license.features' => ['economat', 'scolarite', 'etudes', 'examens', 'statistiques', 'bibliotheque', 'messagerie', 'securite']
];
```

## Processus d'Installation

### 1. Installation initiale

```bash
# 1. Cloner le projet
git clone [repository_url]
cd kissai-school

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
cp env .env
# Modifier .env avec les paramètres de base de données

# 4. Créer la base de données
mysql -h 100.69.65.33 -P 13306 -u root -p < database/lycol_schema.sql

# 5. Initialiser les utilisateurs par défaut
php init_users.php

# 6. Générer une licence d'essai
php fix_license.php
```

### 2. Configuration de la licence

```bash
# 1. Accéder à l'interface d'administration
http://localhost:8080/admin/configuration

# 2. Aller dans l'onglet "Licence"

# 3. Configurer les paramètres :
# - Clé secrète : KISSAI_SECRET_KEY_2025
# - Durée d'essai : 90 jours
# - Nombre max d'utilisateurs : 1000
# - Fonctionnalités activées : Toutes

# 4. Cliquer sur "Générer une Nouvelle Licence"
```

### 3. Vérification de l'installation

```bash
# Tester l'authentification
php test_auth_simple.php

# Tester la validation de licence
php test_license_simple.php

# Tester l'application complète
php test_final.php
```

## Dépannage

### Problèmes courants

#### 1. Erreur "Class LicenseGenerator not found"
**Solution** : Vérifier que le fichier `app/Libraries/LicenseGenerator.php` existe

#### 2. Erreur "Invalid license key"
**Solution** : 
```bash
# Régénérer une nouvelle licence
php fix_license.php
```

#### 3. Erreur "License expired"
**Solution** :
```bash
# Prolonger la licence
php -r "
require_once 'app/Libraries/LicenseGenerator.php';
\$expiryDate = date('Y-m-d', strtotime('+1 year'));
// Mettre à jour la base de données
"
```

#### 4. Erreur "Database connection failed"
**Solution** : Vérifier les paramètres de connexion dans `app/Config/Database.php`

### Logs de débogage

```php
// Activer les logs de licence
log_message('info', 'License validation: ' . json_encode($validation));
```

## API de Licences

### Endpoints disponibles

#### 1. Vérifier le statut de la licence
```http
GET /api/license/status
```

#### 2. Générer une nouvelle licence
```http
POST /api/license/generate
Content-Type: application/json

{
    "client_id": "KISSAI_SCHOOL",
    "license_type": "TRIAL",
    "expiry_date": "2025-12-31"
}
```

#### 3. Valider une licence
```http
POST /api/license/validate
Content-Type: application/json

{
    "license_key": "ABCD-EFGH-IJKL-2025"
}
```

### Exemple d'utilisation cURL

```bash
# Vérifier le statut
curl -X GET http://localhost:8080/api/license/status

# Générer une licence
curl -X POST http://localhost:8080/api/license/generate \
  -H "Content-Type: application/json" \
  -d '{"client_id":"KISSAI_SCHOOL","license_type":"TRIAL","expiry_date":"2025-12-31"}'

# Valider une licence
curl -X POST http://localhost:8080/api/license/validate \
  -H "Content-Type: application/json" \
  -d '{"license_key":"ABCD-EFGH-IJKL-2025"}'
```

## Sécurité

### Mesures de sécurité implémentées

1. **Chiffrement des clés** : Utilisation d'une clé secrète pour la génération
2. **Validation cryptographique** : Vérification de l'intégrité des licences
3. **Expiration automatique** : Déconnexion après expiration
4. **Audit des licences** : Journalisation des validations
5. **Protection contre la réutilisation** : Licences uniques par client

### Bonnes pratiques

1. **Ne jamais exposer la clé secrète** dans le code client
2. **Utiliser HTTPS** pour toutes les communications API
3. **Valider les licences** à chaque connexion
4. **Maintenir un journal** des validations de licence
5. **Sauvegarder régulièrement** la base de données des licences

## Maintenance

### Tâches de maintenance régulières

1. **Vérification quotidienne** des licences expirées
2. **Sauvegarde hebdomadaire** de la base de données
3. **Mise à jour mensuelle** des paramètres de sécurité
4. **Audit trimestriel** des licences actives

### Scripts de maintenance

```bash
# Vérifier les licences expirées
php maintenance/check_expired_licenses.php

# Sauvegarder la base de données
php maintenance/backup_database.php

# Nettoyer les logs anciens
php maintenance/cleanup_logs.php
```

---

## Conclusion

Ce système de licences offre une solution complète et sécurisée pour la gestion des accès à KISSAI SCHOOL. Il est conçu pour être robuste, évolutif et facile à maintenir.

Pour toute question ou problème, consulter les logs d'erreur et utiliser les scripts de diagnostic fournis.




