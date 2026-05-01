<?php
/**
 * Script pour corriger la licence avec une nouvelle clé valide
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 CORRECTION DE LA LICENCE\n";
    echo "==========================\n\n";
    
    // Inclure la classe LicenseGenerator
    require_once 'app/Libraries/LicenseGenerator.php';
    
    // Générer une nouvelle clé de licence valide
    $clientId = 'KISSAI_SCHOOL';
    $licenseType = 'PERMANENT';
    $expiryDate = '2099-12-31';
    
    echo "📋 GÉNÉRATION D'UNE NOUVELLE LICENCE:\n";
    echo "   Client: $clientId\n";
    echo "   Type: $licenseType\n";
    echo "   Expiration: $expiryDate\n\n";
    
    $newLicenseKey = \App\Libraries\LicenseGenerator::generateLicenseKey(
        $clientId,
        $licenseType,
        $expiryDate
    );
    
    echo "🔑 NOUVELLE CLÉ GÉNÉRÉE: $newLicenseKey\n\n";
    
    // Vérifier que la nouvelle clé est valide
    $validation = \App\Libraries\LicenseGenerator::validateLicenseKey(
        $newLicenseKey,
        $clientId,
        $licenseType,
        $expiryDate
    );
    
    echo "✅ VALIDATION DE LA NOUVELLE CLÉ:\n";
    echo "   Valide: " . ($validation['valid'] ? 'OUI' : 'NON') . "\n";
    if (!$validation['valid']) {
        echo "   Raison: " . $validation['reason'] . "\n";
        exit(1);
    }
    echo "\n";
    
    // Mettre à jour la licence en base de données
    echo "💾 MISE À JOUR DE LA BASE DE DONNÉES:\n";
    
    $sql = "UPDATE licenses SET 
            license_key = :license_key,
            license_type = :license_type,
            expiry_date = :expiry_date,
            status = 'ACTIVE',
            updated_at = NOW()
            WHERE id = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':license_key' => $newLicenseKey,
        ':license_type' => $licenseType,
        ':expiry_date' => $expiryDate
    ]);
    
    echo "   ✅ Licence mise à jour avec succès\n\n";
    
    // Vérifier la licence mise à jour
    echo "🔍 VÉRIFICATION FINALE:\n";
    
    $stmt = $pdo->query("SELECT * FROM licenses WHERE id = 1");
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($license) {
        echo "   Clé: " . $license['license_key'] . "\n";
        echo "   Type: " . $license['license_type'] . "\n";
        echo "   Statut: " . $license['status'] . "\n";
        echo "   Expiration: " . $license['expiry_date'] . "\n";
        echo "   Mise à jour: " . $license['updated_at'] . "\n\n";
        
        // Validation finale
        $finalValidation = \App\Libraries\LicenseGenerator::validateLicenseKey(
            $license['license_key'],
            $license['client_id'],
            $license['license_type'],
            $license['expiry_date']
        );
        
        echo "🎯 RÉSULTAT FINAL:\n";
        echo "   Licence valide: " . ($finalValidation['valid'] ? '✅ OUI' : '❌ NON') . "\n";
        
        if ($finalValidation['valid']) {
            echo "   🎉 SUCCÈS ! La licence est maintenant valide et opérationnelle.\n";
        } else {
            echo "   ❌ ÉCHEC ! La licence n'est toujours pas valide.\n";
        }
    } else {
        echo "   ❌ Aucune licence trouvée\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE BASE DE DONNÉES: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n📄 FIN DE LA CORRECTION\n";
echo "======================\n";
?>





