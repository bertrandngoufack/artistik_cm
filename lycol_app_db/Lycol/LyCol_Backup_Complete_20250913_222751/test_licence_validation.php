<?php
/**
 * Script de test pour diagnostiquer le problème de validation de licence
 */

require_once 'app/Libraries/LicenseGenerator.php';

echo "🔍 DIAGNOSTIC DE VALIDATION DE LICENCE\n";
echo "=====================================\n\n";

// Informations de la licence actuelle
$licenseKey = 'Q7U3-Q5SN-7A31-2025';
$clientId = 'KISSAI_SCHOOL';
$licenseType = 'PERMANENT';
$expiryDate = '2099-12-31';

echo "📋 INFORMATIONS DE LA LICENCE:\n";
echo "   Clé: $licenseKey\n";
echo "   Client: $clientId\n";
echo "   Type: $licenseType\n";
echo "   Expiration: $expiryDate\n\n";

// Test 1: Validation avec la méthode actuelle
echo "🧪 TEST 1: Validation avec LicenseGenerator::validateLicenseKey\n";
echo "------------------------------------------------------------\n";

try {
    $result = \App\Libraries\LicenseGenerator::validateLicenseKey(
        $licenseKey,
        $clientId,
        $licenseType,
        $expiryDate
    );
    
    echo "Résultat: " . ($result['valid'] ? '✅ VALIDE' : '❌ INVALIDE') . "\n";
    if (!$result['valid']) {
        echo "Raison: " . $result['reason'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 2: Génération d'une nouvelle clé pour comparaison
echo "🧪 TEST 2: Génération d'une nouvelle clé\n";
echo "----------------------------------------\n";

try {
    $newKey = \App\Libraries\LicenseGenerator::generateLicenseKey(
        $clientId,
        $licenseType,
        $expiryDate
    );
    
    echo "Nouvelle clé générée: $newKey\n";
    echo "Clé actuelle: $licenseKey\n";
    echo "Correspondance: " . ($newKey === $licenseKey ? '✅ OUI' : '❌ NON') . "\n\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 3: Validation de la nouvelle clé
echo "🧪 TEST 3: Validation de la nouvelle clé\n";
echo "----------------------------------------\n";

try {
    $result = \App\Libraries\LicenseGenerator::validateLicenseKey(
        $newKey,
        $clientId,
        $licenseType,
        $expiryDate
    );
    
    echo "Résultat: " . ($result['valid'] ? '✅ VALIDE' : '❌ INVALIDE') . "\n";
    if (!$result['valid']) {
        echo "Raison: " . $result['reason'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 4: Analyse des segments
echo "🧪 TEST 4: Analyse des segments\n";
echo "-------------------------------\n";

$segments = explode('-', $licenseKey);
echo "Segments de la clé actuelle:\n";
foreach ($segments as $i => $segment) {
    echo "   Segment " . ($i + 1) . ": $segment\n";
}

$newSegments = explode('-', $newKey);
echo "\nSegments de la nouvelle clé:\n";
foreach ($newSegments as $i => $segment) {
    echo "   Segment " . ($i + 1) . ": $segment\n";
}

echo "\n";

// Test 5: Vérification de la signature
echo "🧪 TEST 5: Vérification de la signature\n";
echo "---------------------------------------\n";

try {
    $signatureData = $clientId . $licenseType . $expiryDate . 'LYCOL_SECRET_KEY_2025';
    $expectedSignature = \App\Libraries\LicenseGenerator::hashString($signatureData);
    $providedSignature = $segments[2];
    
    echo "Données de signature: $signatureData\n";
    echo "Signature attendue: " . substr($expectedSignature, 0, 4) . "\n";
    echo "Signature fournie: $providedSignature\n";
    echo "Correspondance: " . (substr($expectedSignature, 0, 4) === $providedSignature ? '✅ OUI' : '❌ NON') . "\n\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
}

echo "📄 FIN DU DIAGNOSTIC\n";
echo "===================\n";
?>





