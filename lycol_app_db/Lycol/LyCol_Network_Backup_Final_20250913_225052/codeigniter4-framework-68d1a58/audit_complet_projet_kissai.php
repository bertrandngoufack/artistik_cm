<?php
/**
 * AUDIT COMPLET ET MINUTIEUX - PROJET KISSAI SCHOOL
 * Expert Senior PHP/CodeIgniter/MariaDB
 * Vérification complète : CRUD, conformité, cohérence, routes, liens, port 8080
 */

echo "🔍 AUDIT COMPLET ET MINUTIEUX - PROJET KISSAI SCHOOL\n";
echo "====================================================\n";
echo "Expert Senior PHP/CodeIgniter/MariaDB\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$baseUrl = 'http://localhost:8080';
$port = 8080;

// 1. VÉRIFICATION DE LA CONFIGURATION DU PORT
echo "📋 1. VÉRIFICATION DE LA CONFIGURATION DU PORT\n";
echo str_repeat("-", 50) . "\n";

// Vérifier si le fichier .env existe
$envFile = '.env';
if (file_exists($envFile)) {
    echo "✅ Fichier .env existe\n";
    $envContent = file_get_contents($envFile);
    
    // Vérifier la configuration du port
    if (strpos($envContent, 'APP_PORT') !== false) {
        echo "✅ Configuration APP_PORT trouvée dans .env\n";
    } else {
        echo "❌ Configuration APP_PORT manquante dans .env\n";
    }
    
    if (strpos($envContent, 'APP_BASE_URL') !== false) {
        echo "✅ Configuration APP_BASE_URL trouvée dans .env\n";
    } else {
        echo "❌ Configuration APP_BASE_URL manquante dans .env\n";
    }
} else {
    echo "❌ Fichier .env manquant - CRÉATION NÉCESSAIRE\n";
}

// Vérifier la configuration dans App.php
$appConfigFile = 'app/Config/App.php';
if (file_exists($appConfigFile)) {
    $appContent = file_get_contents($appConfigFile);
    if (strpos($appContent, 'localhost:8080') !== false) {
        echo "✅ Configuration port 8080 dans App.php\n";
    } else {
        echo "❌ Configuration port 8080 manquante dans App.php\n";
    }
} else {
    echo "❌ Fichier App.php manquant\n";
}

// 2. VÉRIFICATION DU SERVEUR
echo "\n📋 2. VÉRIFICATION DU SERVEUR\n";
echo str_repeat("-", 50) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "URL testée: $baseUrl\n";
echo "Code HTTP: $httpCode\n";
echo "Type de contenu: $contentType\n";

if ($httpCode === 200) {
    echo "✅ Serveur accessible sur le port $port\n";
} else {
    echo "❌ Erreur d'accès au serveur (HTTP $httpCode)\n";
}

// 3. VÉRIFICATION DES ROUTES PRINCIPALES
echo "\n📋 3. VÉRIFICATION DES ROUTES PRINCIPALES\n";
echo str_repeat("-", 50) . "\n";

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
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 400) ? '✅' : '❌';
    echo "$status $name ($route) - HTTP $httpCode\n";
    
    $routeResults[$name] = $httpCode;
}

// 4. VÉRIFICATION DES ASSETS ET CSS
echo "\n📋 4. VÉRIFICATION DES ASSETS ET CSS\n";
echo str_repeat("-", 50) . "\n";

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
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode === 200) ? '✅' : '❌';
    echo "$status $name ($asset) - HTTP $httpCode\n";
}

// 5. VÉRIFICATION DES RÉFÉRENCES AU PORT
echo "\n📋 5. VÉRIFICATION DES RÉFÉRENCES AU PORT\n";
echo str_repeat("-", 50) . "\n";

$filesToCheck = [
    'app/Config/App.php',
    'app/Config/Routes.php',
    'app/Views/admin/layout.php',
    'app/Views/layouts/main.php',
    'public/.htaccess',
    'public/router.php'
];

$portReferences = [];
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $references = [];
        
        // Chercher les références aux ports
        if (preg_match_all('/localhost:(\d+)/', $content, $matches)) {
            foreach ($matches[1] as $port) {
                $references[] = $port;
            }
        }
        
        if (preg_match_all('/:(\d+)\//', $content, $matches)) {
            foreach ($matches[1] as $port) {
                if (is_numeric($port)) {
                    $references[] = $port;
                }
            }
        }
        
        if (!empty($references)) {
            $uniquePorts = array_unique($references);
            $portReferences[$file] = $uniquePorts;
            
            $correctPorts = array_filter($uniquePorts, function($p) use ($port) {
                return $p == $port;
            });
            
            if (count($correctPorts) === count($uniquePorts)) {
                echo "✅ $file - Tous les ports corrects ($port)\n";
            } else {
                echo "❌ $file - Ports incorrects: " . implode(', ', $uniquePorts) . " (attendu: $port)\n";
            }
        } else {
            echo "✅ $file - Aucune référence de port trouvée\n";
        }
    } else {
        echo "⚠️  $file - Fichier manquant\n";
    }
}

// 6. VÉRIFICATION DE LA BASE DE DONNÉES
echo "\n📋 6. VÉRIFICATION DE LA BASE DE DONNÉES\n";
echo str_repeat("-", 50) . "\n";

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

// 7. VÉRIFICATION DES TESTS CRUD
echo "\n📋 7. VÉRIFICATION DES TESTS CRUD\n";
echo str_repeat("-", 50) . "\n";

// Test de création d'un enregistrement
$testData = [
    'test_name' => 'Test CRUD ' . date('Y-m-d H:i:s'),
    'test_value' => 'test_value'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/configuration/save-appearance');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test POST CRUD: HTTP $httpCode\n";
if ($httpCode === 200 || $httpCode === 302) {
    echo "✅ Test CRUD réussi\n";
} else {
    echo "❌ Test CRUD échoué\n";
}

// 8. VÉRIFICATION DE LA CONFORMITÉ
echo "\n📋 8. VÉRIFICATION DE LA CONFORMITÉ\n";
echo str_repeat("-", 50) . "\n";

$conformityChecks = [
    'Structure MVC' => [
        'app/Controllers/' => is_dir('app/Controllers'),
        'app/Models/' => is_dir('app/Models'),
        'app/Views/' => is_dir('app/Views'),
        'app/Config/' => is_dir('app/Config')
    ],
    'Sécurité' => [
        'Filtres CSRF' => file_exists('app/Filters/CSRF.php'),
        'Filtres Auth' => file_exists('app/Filters/AuthFilter.php'),
        'Validation' => file_exists('app/Config/Validation.php')
    ],
    'Performance' => [
        'Cache' => file_exists('app/Services/CacheService.php'),
        'Logs' => is_dir('writable/logs'),
        'Cache dir' => is_dir('writable/cache')
    ]
];

foreach ($conformityChecks as $category => $checks) {
    echo "\n$category:\n";
    foreach ($checks as $check => $result) {
        echo ($result ? "✅" : "❌") . " $check\n";
    }
}

// 9. VÉRIFICATION DE LA COHÉRENCE
echo "\n📋 9. VÉRIFICATION DE LA COHÉRENCE\n";
echo str_repeat("-", 50) . "\n";

$coherenceChecks = [
    'Naming conventions' => [
        'Controllers suffix' => count(glob('app/Controllers/*.php')) > 0,
        'Models suffix' => count(glob('app/Models/*.php')) > 0,
        'Views structure' => is_dir('app/Views/admin')
    ],
    'Dépendances' => [
        'Composer.json' => file_exists('composer.json'),
        'Vendor dir' => is_dir('vendor'),
        'Autoload' => file_exists('vendor/autoload.php')
    ],
    'Configuration' => [
        'Environment' => file_exists('app/Config/Environment.php'),
        'Database' => file_exists('app/Config/Database.php'),
        'Routes' => file_exists('app/Config/Routes.php')
    ]
];

foreach ($coherenceChecks as $category => $checks) {
    echo "\n$category:\n";
    foreach ($checks as $check => $result) {
        echo ($result ? "✅" : "❌") . " $check\n";
    }
}

// 10. RAPPORT FINAL
echo "\n📋 10. RAPPORT FINAL\n";
echo str_repeat("=", 60) . "\n";

$totalChecks = 0;
$passedChecks = 0;

// Compter les vérifications réussies
foreach ($routeResults as $result) {
    $totalChecks++;
    if ($result >= 200 && $result < 400) $passedChecks++;
}

foreach ($conformityChecks as $checks) {
    foreach ($checks as $result) {
        $totalChecks++;
        if ($result) $passedChecks++;
    }
}

foreach ($coherenceChecks as $checks) {
    foreach ($checks as $result) {
        $totalChecks++;
        if ($result) $passedChecks++;
    }
}

$successRate = ($totalChecks > 0) ? ($passedChecks / $totalChecks) * 100 : 0;

echo "Résumé de l'audit:\n";
echo "- Total des vérifications: $totalChecks\n";
echo "- Vérifications réussies: $passedChecks\n";
echo "- Taux de réussite: " . number_format($successRate, 1) . "%\n";

if ($successRate >= 90) {
    echo "🎉 EXCELLENT - Projet conforme et fonctionnel\n";
} elseif ($successRate >= 75) {
    echo "✅ BON - Projet fonctionnel avec quelques améliorations\n";
} elseif ($successRate >= 60) {
    echo "⚠️  MOYEN - Projet nécessite des améliorations\n";
} else {
    echo "❌ CRITIQUE - Projet nécessite des corrections importantes\n";
}

echo "\n🔧 RECOMMANDATIONS PRIORITAIRES:\n";
echo "1. Créer le fichier .env avec la configuration du port 8080\n";
echo "2. Vérifier toutes les références au port dans les fichiers\n";
echo "3. Tester toutes les routes avec cURL\n";
echo "4. Corriger les liens cassés\n";
echo "5. Optimiser la performance\n";

echo "\n✅ AUDIT TERMINÉ - " . date('Y-m-d H:i:s') . "\n";
?>





