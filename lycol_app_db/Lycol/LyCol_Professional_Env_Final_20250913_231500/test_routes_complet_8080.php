<?php
/**
 * TEST COMPLET DES ROUTES - PROJET KISSAI SCHOOL
 * Expert Senior PHP/CodeIgniter/MariaDB
 * Vérification complète avec cURL et POST
 */

echo "🧪 TEST COMPLET DES ROUTES - KISSAI SCHOOL PORT 8080\n";
echo "===================================================\n";
echo "Expert Senior PHP/CodeIgniter/MariaDB\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$baseUrl = 'http://localhost:8080';
$port = 8080;

// 1. TEST DE CONNECTIVITÉ DE BASE
echo "📋 1. TEST DE CONNECTIVITÉ DE BASE\n";
echo str_repeat("-", 40) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
curl_close($ch);

echo "URL testée: $baseUrl\n";
echo "Code HTTP: $httpCode\n";
echo "Taille du contenu: " . number_format($contentLength) . " octets\n";

if ($httpCode === 200) {
    echo "✅ Serveur accessible et contenu disponible\n";
} else {
    echo "❌ Problème d'accès au serveur\n";
    exit(1);
}

// 2. TEST DES ROUTES PRINCIPALES
echo "\n📋 2. TEST DES ROUTES PRINCIPALES\n";
echo str_repeat("-", 40) . "\n";

$routes = [
    'Page d\'accueil' => '/',
    'Connexion' => '/auth/login',
    'Espace Parents' => '/auth/parents',
    'Interface Mobile' => '/auth/mobile',
    'Dashboard Admin' => '/admin/dashboard',
    'Configuration' => '/admin/configuration',
    'Économat' => '/admin/economat',
    'Scolarité' => '/admin/scolarite',
    'Études' => '/admin/etudes',
    'Examens' => '/admin/examens',
    'Enseignants' => '/admin/enseignants',
    'Statistiques' => '/admin/statistiques',
    'Bibliothèque' => '/admin/bibliotheque',
    'Messagerie' => '/admin/messagerie',
    'Sécurité' => '/admin/securite'
];

$routeResults = [];
foreach ($routes as $name => $route) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    
    $status = ($httpCode === 200) ? '✅' : '❌';
    echo "$status $name ($route) - HTTP $httpCode - " . number_format($contentLength) . " octets\n";
    
    $routeResults[$name] = [
        'code' => $httpCode,
        'length' => $contentLength,
        'success' => ($httpCode === 200)
    ];
}

// 3. TEST DES ASSETS ET CSS
echo "\n📋 3. TEST DES ASSETS ET CSS\n";
echo str_repeat("-", 40) . "\n";

$assets = [
    'CSS Bulma' => '/assets/bulma/css/bulma.min.css',
    'JS Bulma' => '/assets/bulma/js/bulma.js',
    'Favicon' => '/favicon.ico',
    'Logo' => '/assets/images/logo.png'
];

foreach ($assets as $name => $asset) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $asset);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    
    $status = ($httpCode === 200) ? '✅' : '❌';
    echo "$status $name ($asset) - HTTP $httpCode - " . number_format($contentLength) . " octets\n";
}

// 4. TEST DES FONCTIONNALITÉS CRUD
echo "\n📋 4. TEST DES FONCTIONNALITÉS CRUD\n";
echo str_repeat("-", 40) . "\n";

// Test POST - Configuration Appearance
$testData = [
    'app_name' => 'TEST KISSAI SCHOOL ' . date('Y-m-d H:i:s'),
    'primary_color' => '#ff0000',
    'secondary_color' => '#00ff00',
    'app_description' => 'Test description pour KISSAI SCHOOL',
    'app_keywords' => 'test, école, gestion'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/configuration/save-appearance');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test POST save-appearance: HTTP $httpCode\n";
if ($httpCode === 200 || $httpCode === 302) {
    echo "✅ Test CRUD configuration réussi\n";
} else {
    echo "❌ Test CRUD configuration échoué\n";
}

// Test POST - Authentification
$authData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test POST authentification: HTTP $httpCode\n";
if ($httpCode === 200 || $httpCode === 302) {
    echo "✅ Test authentification réussi\n";
} else {
    echo "❌ Test authentification échoué\n";
}

// 5. TEST DES RÉFÉRENCES AU PORT
echo "\n📋 5. TEST DES RÉFÉRENCES AU PORT\n";
echo str_repeat("-", 40) . "\n";

$filesToCheck = [
    'app/Config/App.php',
    'app/Config/Routes.php',
    'app/Views/admin/layout.php',
    'app/Views/layouts/main.php',
    'public/.htaccess',
    'public/router.php'
];

$portIssues = [];
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $references = [];
        
        // Chercher les références aux ports
        if (preg_match_all('/localhost:(\d+)/', $content, $matches)) {
            foreach ($matches[1] as $foundPort) {
                $references[] = $foundPort;
            }
        }
        
        if (preg_match_all('/:(\d+)\//', $content, $matches)) {
            foreach ($matches[1] as $foundPort) {
                if (is_numeric($foundPort)) {
                    $references[] = $foundPort;
                }
            }
        }
        
        if (!empty($references)) {
            $uniquePorts = array_unique($references);
            $incorrectPorts = array_filter($uniquePorts, function($p) use ($port) {
                return $p != $port;
            });
            
            if (empty($incorrectPorts)) {
                echo "✅ $file - Tous les ports corrects ($port)\n";
            } else {
                echo "❌ $file - Ports incorrects: " . implode(', ', $incorrectPorts) . " (attendu: $port)\n";
                $portIssues[$file] = $incorrectPorts;
            }
        } else {
            echo "✅ $file - Aucune référence de port trouvée\n";
        }
    } else {
        echo "⚠️  $file - Fichier manquant\n";
    }
}

// 6. TEST DE LA BASE DE DONNÉES
echo "\n📋 6. TEST DE LA BASE DE DONNÉES\n";
echo str_repeat("-", 40) . "\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=kissai_school', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier les tables principales
    $tables = ['students', 'teachers', 'classes', 'subjects', 'grades', 'payments', 'users', 'settings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✅ Table $table: $count enregistrements\n";
        } catch (Exception $e) {
            echo "❌ Table $table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

// 7. TEST DE PERFORMANCE
echo "\n📋 7. TEST DE PERFORMANCE\n";
echo str_repeat("-", 40) . "\n";

$startTime = microtime(true);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');

$response = curl_exec($ch);
$endTime = microtime(true);
$loadTime = ($endTime - $startTime) * 1000;
curl_close($ch);

echo "Temps de chargement: " . number_format($loadTime, 2) . " ms\n";

if ($loadTime < 1000) {
    echo "✅ Performance excellente (< 1s)\n";
} elseif ($loadTime < 3000) {
    echo "⚠️  Performance acceptable (< 3s)\n";
} else {
    echo "❌ Performance lente (> 3s)\n";
}

// 8. RAPPORT FINAL
echo "\n📋 8. RAPPORT FINAL\n";
echo str_repeat("=", 60) . "\n";

$totalRoutes = count($routeResults);
$successfulRoutes = count(array_filter($routeResults, function($result) {
    return $result['success'];
}));

$successRate = ($totalRoutes > 0) ? ($successfulRoutes / $totalRoutes) * 100 : 0;

echo "Résumé des tests:\n";
echo "- Total des routes testées: $totalRoutes\n";
echo "- Routes accessibles: $successfulRoutes\n";
echo "- Taux de réussite: " . number_format($successRate, 1) . "%\n";

if ($successRate >= 90) {
    echo "🎉 EXCELLENT - Toutes les routes fonctionnelles\n";
} elseif ($successRate >= 75) {
    echo "✅ BON - La plupart des routes fonctionnelles\n";
} elseif ($successRate >= 60) {
    echo "⚠️  MOYEN - Quelques routes problématiques\n";
} else {
    echo "❌ CRITIQUE - Beaucoup de routes inaccessibles\n";
}

// Problèmes identifiés
if (!empty($portIssues)) {
    echo "\n🔧 PROBLÈMES IDENTIFIÉS:\n";
    foreach ($portIssues as $file => $ports) {
        echo "- $file: ports incorrects " . implode(', ', $ports) . "\n";
    }
}

echo "\n🔧 RECOMMANDATIONS:\n";
echo "1. Corriger les références de port incorrectes\n";
echo "2. Vérifier la configuration de la base de données\n";
echo "3. Optimiser les routes lentes\n";
echo "4. Tester toutes les fonctionnalités CRUD\n";
echo "5. Vérifier la sécurité des endpoints\n";

echo "\n✅ TEST TERMINÉ - " . date('Y-m-d H:i:s') . "\n";
?>
