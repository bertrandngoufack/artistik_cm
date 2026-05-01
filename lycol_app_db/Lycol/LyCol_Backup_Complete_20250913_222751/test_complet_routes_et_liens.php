<?php
/**
 * Test Complet des Routes et Liens - KISSAI SCHOOL
 * Vérification et correction de tous les liens et routes
 */

echo "🔍 TEST COMPLET DES ROUTES ET LIENS - KISSAI SCHOOL\n";
echo "================================================\n\n";

$baseUrl = 'http://localhost:8080';
$results = [];
$totalTests = 0;
$passedTests = 0;

// Configuration pour les tests
$testConfig = [
    'timeout' => 10,
    'follow_redirects' => true,
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
];

/**
 * Fonction pour effectuer une requête cURL
 */
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

/**
 * Fonction pour tester une route
 */
function testRoute($name, $endpoint, $expectedCode = 200, $method = 'GET', $data = null) {
    global $baseUrl, $results, $totalTests, $passedTests;
    
    $totalTests++;
    echo "Testing: $name ($endpoint)... ";
    
    $url = $baseUrl . $endpoint;
    $result = makeRequest($url, $method, $data);
    
    if ($result['error']) {
        echo "❌ ERROR: " . $result['error'] . "\n";
        $results[$name] = ['status' => 'ERROR', 'code' => 0, 'error' => $result['error']];
        return;
    }
    
    $success = ($result['code'] >= 200 && $result['code'] < 400);
    if ($expectedCode && $result['code'] !== $expectedCode) {
        $success = false;
    }
    
    if ($success) {
        echo "✅ OK ($result[code])\n";
        $results[$name] = ['status' => 'PASS', 'code' => $result['code']];
        $passedTests++;
    } else {
        echo "❌ FAIL ($result[code])\n";
        $results[$name] = ['status' => 'FAIL', 'code' => $result['code']];
    }
}

/**
 * Fonction pour tester un lien dans le contenu
 */
function testLinkInContent($name, $endpoint, $linkText) {
    global $baseUrl, $results, $totalTests, $passedTests;
    
    $totalTests++;
    echo "Testing: $name (lien: $linkText)... ";
    
    $url = $baseUrl . $endpoint;
    $result = makeRequest($url);
    
    if ($result['error']) {
        echo "❌ ERROR: " . $result['error'] . "\n";
        $results[$name] = ['status' => 'ERROR', 'code' => 0, 'error' => $result['error']];
        return;
    }
    
    if (strpos($result['response'], $linkText) !== false) {
        echo "✅ OK (lien trouvé)\n";
        $results[$name] = ['status' => 'PASS', 'code' => $result['code']];
        $passedTests++;
    } else {
        echo "❌ FAIL (lien manquant)\n";
        $results[$name] = ['status' => 'FAIL', 'code' => $result['code']];
    }
}

// ============================================================================
// TESTS DES PAGES PUBLIQUES
// ============================================================================
echo "📄 TESTS DES PAGES PUBLIQUES\n";
echo str_repeat("-", 50) . "\n";

testRoute('Page d\'accueil', '/');
testRoute('Page de connexion', '/auth/login');
testRoute('Espace parents', '/auth/parents');
testRoute('Interface mobile', '/auth/mobile');

// ============================================================================
// TESTS DES PAGES ADMIN (sans authentification)
// ============================================================================
echo "\n🔐 TESTS DES PAGES ADMIN (redirection attendue)\n";
echo str_repeat("-", 50) . "\n";

testRoute('Dashboard admin', '/admin/dashboard', 302); // Redirection vers login
testRoute('Configuration', '/admin/configuration', 302);
testRoute('Économat', '/admin/economat', 302);
testRoute('Scolarité', '/admin/scolarite', 302);
testRoute('Études', '/admin/etudes', 302);
testRoute('Examens', '/admin/examens', 302);
testRoute('Enseignants', '/admin/enseignants', 302);
testRoute('Statistiques', '/admin/statistiques', 302);
testRoute('Bibliothèque', '/admin/bibliotheque', 302);
testRoute('Messagerie', '/admin/messagerie', 302);
testRoute('Sécurité', '/admin/securite', 302);

// ============================================================================
// TESTS DES APIs
// ============================================================================
echo "\n🔌 TESTS DES APIs\n";
echo str_repeat("-", 50) . "\n";

testRoute('API vérification licence', '/admin/configuration/check-license', 302);
testRoute('API statistiques système', '/admin/configuration/system-stats-api', 302);

// ============================================================================
// TESTS DES ASSETS
// ============================================================================
echo "\n🎨 TESTS DES ASSETS\n";
echo str_repeat("-", 50) . "\n";

testRoute('CSS Bulma', '/assets/bulma/css/bulma.min.css');
testRoute('JS Bulma', '/assets/bulma/js/bulma.js');
testRoute('Favicon', '/favicon.ico');

// ============================================================================
// TESTS DES LIENS DANS LE CONTENU
// ============================================================================
echo "\n🔗 TESTS DES LIENS DANS LE CONTENU\n";
echo str_repeat("-", 50) . "\n";

testLinkInContent('Lien connexion dans accueil', '/', 'Connexion');
testLinkInContent('Lien espace parents dans accueil', '/', 'Espace Parents');
testLinkInContent('Lien documentation dans accueil', '/', 'Documentation');

// ============================================================================
// TESTS POST (formulaires)
// ============================================================================
echo "\n📝 TESTS DES FORMULAIRES POST\n";
echo str_repeat("-", 50) . "\n";

// Test de connexion (sans authentification)
$loginData = [
    'username' => 'admin',
    'password' => 'admin123',
    'csrf_test_name' => 'test_token'
];

testRoute('Formulaire de connexion', '/auth/login', 200, 'POST', http_build_query($loginData));

// ============================================================================
// TESTS SPÉCIAUX
// ============================================================================
echo "\n🔧 TESTS SPÉCIAUX\n";
echo str_repeat("-", 50) . "\n";

// Test de la base de données
echo "Test de la base de données... ";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kissai_school', 'root', '');
    $stmt = $pdo->query('SELECT COUNT(*) FROM students');
    $count = $stmt->fetchColumn();
    echo "✅ OK ($count étudiants)\n";
    $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $totalTests++;
}

// Test des fichiers CSS/JS
echo "Test des fichiers CSS/JS... ";
$cssFile = 'public/assets/bulma/css/bulma.min.css';
$jsFile = 'public/assets/bulma/js/bulma.js';

if (file_exists($cssFile) && file_exists($jsFile)) {
    $cssSize = filesize($cssFile);
    $jsSize = filesize($jsFile);
    echo "✅ OK (CSS: " . round($cssSize/1024, 1) . "KB, JS: " . round($jsSize/1024, 1) . "KB)\n";
    $passedTests++;
    $totalTests++;
} else {
    echo "❌ FAIL: Fichiers manquants\n";
    $totalTests++;
}

// Test de la configuration
echo "Test de la configuration... ";
$configFile = 'app/Config/App.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    if (strpos($content, "baseURL = 'http://localhost:8080/'") !== false) {
        echo "✅ OK (Port 8080 configuré)\n";
        $passedTests++;
        $totalTests++;
    } else {
        echo "❌ FAIL: Port incorrect\n";
        $totalTests++;
    }
} else {
    echo "❌ FAIL: Fichier de configuration manquant\n";
    $totalTests++;
}

// ============================================================================
// RÉSULTATS FINAUX
// ============================================================================
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSULTATS DU TEST COMPLET\n";
echo str_repeat("=", 60) . "\n";
echo "Tests réussis: $passedTests/$totalTests\n";
echo "Taux de réussite: " . round(($passedTests/$totalTests)*100, 2) . "%\n\n";

// Détails des résultats
echo "📋 DÉTAILS DES TESTS:\n";
echo str_repeat("-", 60) . "\n";

foreach ($results as $testName => $result) {
    $status = $result['status'] === 'PASS' ? '✅' : '❌';
    $code = isset($result['code']) ? " ($result[code])" : '';
    $error = isset($result['error']) ? " - $result[error]" : '';
    echo sprintf("%-40s %s%s%s\n", $testName, $status, $code, $error);
}

// Recommandations
echo "\n💡 RECOMMANDATIONS:\n";
echo str_repeat("-", 60) . "\n";

$failedTests = array_filter($results, function($result) {
    return $result['status'] === 'FAIL' || $result['status'] === 'ERROR';
});

if (empty($failedTests)) {
    echo "🎉 TOUS LES TESTS SONT RÉUSSIS ! L'APPLICATION FONCTIONNE PARFAITEMENT.\n";
} else {
    echo "⚠️  TESTS ÉCHOUÉS À CORRIGER:\n";
    foreach ($failedTests as $testName => $result) {
        echo "  - $testName: Code $result[code]\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($passedTests == $totalTests) {
    echo "🎉 MISSION ACCOMPLIE ! TOUS LES LIENS ET ROUTES FONCTIONNENT.\n";
} else {
    echo "⚠️  CERTAINS PROBLÈMES ONT ÉTÉ IDENTIFIÉS. CORRECTION RECOMMANDÉE.\n";
}
echo str_repeat("=", 60) . "\n";
?>





