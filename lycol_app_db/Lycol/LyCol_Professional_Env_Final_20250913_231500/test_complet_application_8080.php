<?php
/**
 * Test Complet de l'Application KISSAI SCHOOL - Port 8080
 * Vérification de toutes les fonctionnalités, routes, CSS, JS
 */

echo "🔍 TEST COMPLET DE L'APPLICATION KISSAI SCHOOL - PORT 8080\n";
echo "========================================================\n\n";

$baseUrl = 'http://localhost:8080';
$tests = [
    // Tests des pages publiques
    'Page d\'accueil' => '/',
    'Page de connexion' => '/auth/login',
    'Espace parents' => '/auth/parents',
    'Interface mobile' => '/auth/mobile',
    
    // Tests des pages admin (sans authentification pour vérifier la redirection)
    'Dashboard admin' => '/admin/dashboard',
    'Configuration' => '/admin/configuration',
    'Économat' => '/admin/economat',
    'Scolarité' => '/admin/scolarite',
    'Études' => '/admin/etudes',
    'Examens' => '/admin/examens',
    'Enseignants' => '/admin/enseignants',
    'Statistiques' => '/admin/statistiques',
    'Bibliothèque' => '/admin/bibliotheque',
    'Messagerie' => '/admin/messagerie',
    'Sécurité' => '/admin/securite',
    
    // Tests des APIs
    'API vérification licence' => '/admin/configuration/check-license',
    'API statistiques système' => '/admin/configuration/system-stats-api',
    
    // Tests des assets
    'CSS Bulma' => '/assets/bulma/css/bulma.min.css',
    'JS Bulma' => '/assets/bulma/js/bulma.js',
    'Favicon' => '/favicon.ico',
];

$results = [];
$totalTests = count($tests);
$passedTests = 0;

foreach ($tests as $testName => $endpoint) {
    echo "Testing: $testName ($endpoint)... ";
    
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
    curl_close($ch);
    
    // Vérifier le code de statut HTTP
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ OK ($httpCode)\n";
        $results[$testName] = ['status' => 'PASS', 'code' => $httpCode];
        $passedTests++;
    } else {
        echo "❌ FAIL ($httpCode)\n";
        $results[$testName] = ['status' => 'FAIL', 'code' => $httpCode];
    }
}

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
    echo sprintf("%-30s %s (%d)\n", $testName, $status, $result['code']);
}

// Test spécial des fonctionnalités
echo "\n🔧 TESTS SPÉCIAUX:\n";
echo str_repeat("-", 60) . "\n";

// Test de la base de données
echo "Test de la base de données... ";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kissai_school', 'root', '');
    $stmt = $pdo->query('SELECT COUNT(*) FROM students');
    $count = $stmt->fetchColumn();
    echo "✅ OK ($count étudiants)\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
}

// Test des fichiers CSS/JS
echo "Test des fichiers CSS/JS... ";
$cssFile = 'public/assets/bulma/css/bulma.min.css';
$jsFile = 'public/assets/bulma/js/bulma.js';

if (file_exists($cssFile) && file_exists($jsFile)) {
    $cssSize = filesize($cssFile);
    $jsSize = filesize($jsFile);
    echo "✅ OK (CSS: " . round($cssSize/1024, 1) . "KB, JS: " . round($jsSize/1024, 1) . "KB)\n";
} else {
    echo "❌ FAIL: Fichiers manquants\n";
}

// Test de la configuration
echo "Test de la configuration... ";
$configFile = 'app/Config/App.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    if (strpos($content, "baseURL = 'http://localhost:8080/'") !== false) {
        echo "✅ OK (Port 8080 configuré)\n";
    } else {
        echo "❌ FAIL: Port incorrect\n";
    }
} else {
    echo "❌ FAIL: Fichier de configuration manquant\n";
}

// Test des routes
echo "Test des routes... ";
$routesFile = 'app/Config/Routes.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    if (strpos($content, "admin/dashboard") !== false && 
        strpos($content, "admin/configuration") !== false) {
        echo "✅ OK (Routes principales définies)\n";
    } else {
        echo "❌ FAIL: Routes manquantes\n";
    }
} else {
    echo "❌ FAIL: Fichier de routes manquant\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($passedTests == $totalTests) {
    echo "🎉 TOUS LES TESTS SONT RÉUSSIS ! L'APPLICATION FONCTIONNE PARFAITEMENT.\n";
} else {
    echo "⚠️  CERTAINS TESTS ONT ÉCHOUÉ. VÉRIFIEZ LES PROBLÈMES IDENTIFIÉS.\n";
}
echo str_repeat("=", 60) . "\n";
?>





