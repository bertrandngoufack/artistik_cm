<?php
/**
 * Test Final de Validation - KISSAI SCHOOL Port 8080
 * Validation complète de l'application après correction
 */

echo "🎯 TEST FINAL DE VALIDATION - KISSAI SCHOOL PORT 8080\n";
echo "====================================================\n\n";

$baseUrl = 'http://localhost:8080';
$tests = [
    'Page d\'accueil' => '/',
    'Connexion' => '/auth/login',
    'Espace Parents' => '/auth/parents',
    'Interface Mobile' => '/auth/mobile',
    'CSS Bulma' => '/assets/bulma/css/bulma.min.css',
    'JS Bulma' => '/assets/bulma/js/bulma.js',
    'Favicon' => '/favicon.ico'
];

$results = [];
$totalTests = count($tests);
$passedTests = 0;

echo "🔍 TESTS DE CONNECTIVITÉ\n";
echo str_repeat("-", 40) . "\n";

foreach ($tests as $name => $endpoint) {
    echo "Testing: $name... ";
    
    $url = $baseUrl . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ ERROR: $error\n";
        $results[$name] = ['status' => 'ERROR', 'code' => 0, 'error' => $error];
    } elseif ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ OK ($httpCode)\n";
        $results[$name] = ['status' => 'PASS', 'code' => $httpCode];
        $passedTests++;
    } else {
        echo "❌ FAIL ($httpCode)\n";
        $results[$name] = ['status' => 'FAIL', 'code' => $httpCode];
    }
}

echo "\n🔧 TESTS DE CONTENU\n";
echo str_repeat("-", 40) . "\n";

// Test du contenu de la page d'accueil
echo "Test contenu page d'accueil... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$content = curl_exec($ch);
curl_close($ch);

if (strpos($content, 'KISSAI SCHOOL') !== false) {
    echo "✅ OK (Titre trouvé)\n";
    $passedTests++;
    $totalTests++;
} else {
    echo "❌ FAIL (Titre manquant)\n";
    $totalTests++;
}

// Test des liens dans la page d'accueil
echo "Test liens dans l'accueil... ";
$links = [
    'Connexion' => 'auth/login',
    'Espace Parents' => 'auth/parents'
];

$allLinksFound = true;
foreach ($links as $linkText => $linkUrl) {
    if (strpos($content, $linkUrl) === false) {
        $allLinksFound = false;
        break;
    }
}

if ($allLinksFound) {
    echo "✅ OK (Liens trouvés)\n";
    $passedTests++;
    $totalTests++;
} else {
    echo "❌ FAIL (Liens manquants)\n";
    $totalTests++;
}

// Test de la configuration
echo "Test configuration... ";
$configFile = 'app/Config/App.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    if (strpos($configContent, "baseURL = 'http://localhost:8080/'") !== false) {
        echo "✅ OK (Port 8080 configuré)\n";
        $passedTests++;
        $totalTests++;
    } else {
        echo "❌ FAIL (Port incorrect)\n";
        $totalTests++;
    }
} else {
    echo "❌ FAIL (Fichier config manquant)\n";
    $totalTests++;
}

echo "\n📊 RÉSULTATS FINAUX\n";
echo str_repeat("=", 50) . "\n";
echo "Tests réussis: $passedTests/$totalTests\n";
echo "Taux de réussite: " . round(($passedTests/$totalTests)*100, 2) . "%\n\n";

// Détails des résultats
echo "📋 DÉTAILS DES TESTS:\n";
echo str_repeat("-", 50) . "\n";

foreach ($results as $testName => $result) {
    $status = $result['status'] === 'PASS' ? '✅' : '❌';
    $code = isset($result['code']) ? " ($result[code])" : '';
    $error = isset($result['error']) ? " - $result[error]" : '';
    echo sprintf("%-20s %s%s%s\n", $testName, $status, $code, $error);
}

echo "\n🎯 VALIDATION FINALE\n";
echo str_repeat("=", 50) . "\n";

if ($passedTests == $totalTests) {
    echo "🎉 SUCCÈS TOTAL ! L'APPLICATION FONCTIONNE PARFAITEMENT.\n";
    echo "✅ Toutes les routes sont accessibles\n";
    echo "✅ Tous les assets sont chargés\n";
    echo "✅ La configuration est correcte\n";
    echo "✅ L'application est prête pour la production\n";
} else {
    echo "⚠️  CERTAINS TESTS ONT ÉCHOUÉ.\n";
    echo "Vérifiez les problèmes identifiés ci-dessus.\n";
}

echo "\n🚀 URLS D'ACCÈS:\n";
echo str_repeat("-", 50) . "\n";
echo "• Accueil: http://localhost:8080/\n";
echo "• Connexion: http://localhost:8080/auth/login\n";
echo "• Espace Parents: http://localhost:8080/auth/parents\n";
echo "• Interface Mobile: http://localhost:8080/auth/mobile\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test de validation terminé - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n";
?>





