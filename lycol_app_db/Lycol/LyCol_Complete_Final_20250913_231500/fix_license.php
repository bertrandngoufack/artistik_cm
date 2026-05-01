<?php
/**
 * Script pour corriger la licence
 */

echo "=== CORRECTION DE LA LICENCE ===\n\n";

// Inclure la classe LicenseGenerator
require_once 'app/Libraries/LicenseGenerator.php';

// Configuration de la base de données
$host = '100.69.65.33';
$port = 13306;
$username = 'root';
$password = 'Bateau123';
$database = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Générer une nouvelle licence valide
    $clientId = 'KISSAI_SCHOOL';
    $licenseType = 'TRIAL';
    $expiryDate = date('Y-m-d', strtotime('+3 months'));
    $secretSeed = 'KISSAI_SECRET_KEY_2025';
    
    echo "Génération d'une nouvelle licence...\n";
    echo "- Client ID: $clientId\n";
    echo "- Type: $licenseType\n";
    echo "- Date d'expiration: $expiryDate\n";
    echo "- Secret Seed: $secretSeed\n\n";
    
    $newLicenseKey = \App\Libraries\LicenseGenerator::generateLicenseKey(
        $clientId,
        $licenseType,
        $expiryDate,
        $secretSeed
    );
    
    echo "Nouvelle clé de licence générée: $newLicenseKey\n\n";
    
    // Valider la nouvelle licence
    $validation = \App\Libraries\LicenseGenerator::validateLicenseKey(
        $newLicenseKey,
        $clientId,
        $licenseType,
        $expiryDate,
        $secretSeed
    );
    
    if ($validation['valid']) {
        echo "✅ Nouvelle licence validée avec succès\n";
        
        // Mettre à jour la base de données
        $stmt = $pdo->prepare("UPDATE licenses SET license_key = ?, expiry_date = ? WHERE id = 1");
        $result = $stmt->execute([$newLicenseKey, $expiryDate]);
        
        if ($result) {
            echo "✅ Licence mise à jour dans la base de données\n";
        } else {
            echo "❌ Erreur lors de la mise à jour de la base de données\n";
        }
    } else {
        echo "❌ Échec de validation de la nouvelle licence: {$validation['reason']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 CORRECTION TERMINÉE\n";




