<?php
/**
 * Test simple de la vérification de licence
 */

echo "=== TEST VÉRIFICATION LICENCE SIMPLE ===\n\n";

// Test direct de la base de données
$host = '100.69.65.33';
$port = 13306;
$username = 'root';
$password = 'Bateau123';
$database = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérifier les licences
    $stmt = $pdo->query("SELECT * FROM licenses WHERE status = 'ACTIVE'");
    $licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Licences actives trouvées: " . count($licenses) . "\n";
    
    foreach ($licenses as $license) {
        echo "\nLicence:\n";
        echo "- ID: {$license['id']}\n";
        echo "- Clé: {$license['license_key']}\n";
        echo "- Client: {$license['client_id']}\n";
        echo "- Type: {$license['license_type']}\n";
        echo "- Date d'émission: {$license['issued_date']}\n";
        echo "- Date d'expiration: {$license['expiry_date']}\n";
        echo "- Statut: {$license['status']}\n";
        
        // Vérifier si la licence n'est pas expirée
        $today = date('Y-m-d');
        if ($license['expiry_date'] >= $today) {
            echo "✅ Licence valide (non expirée)\n";
        } else {
            echo "❌ Licence expirée\n";
        }
    }
    
    // Test de la validation avec LicenseGenerator
    if (count($licenses) > 0) {
        $license = $licenses[0];
        
        echo "\n🔍 Test de validation avec LicenseGenerator...\n";
        
        // Inclure la classe LicenseGenerator
        require_once 'app/Libraries/LicenseGenerator.php';
        
        $validation = \App\Libraries\LicenseGenerator::validateLicenseKey(
            $license['license_key'],
            $license['client_id'],
            $license['license_type'],
            $license['expiry_date'],
            'KISSAI_SECRET_KEY_2025'
        );
        
        echo "Résultat de validation: " . ($validation['valid'] ? 'true' : 'false') . "\n";
        
        if ($validation['valid']) {
            echo "✅ Licence validée avec succès\n";
        } else {
            echo "❌ Échec de validation: {$validation['reason']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 TEST TERMINÉ\n";




