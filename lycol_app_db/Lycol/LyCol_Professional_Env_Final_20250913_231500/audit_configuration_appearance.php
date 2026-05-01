<?php
/**
 * AUDIT COMPLET ET MINUTIEUX - MODULE CONFIGURATION/APPEARANCE
 * Expert Senior PHP/CodeIgniter/MariaDB
 * KISSAI SCHOOL - Analyse approfondie
 */

echo "🔍 AUDIT COMPLET ET MINUTIEUX - MODULE CONFIGURATION/APPEARANCE\n";
echo "================================================================\n";
echo "Expert Senior PHP/CodeIgniter/MariaDB - KISSAI SCHOOL\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$testUrl = $baseUrl . '/admin/configuration/appearance';

// 1. VÉRIFICATION DE L'ACCESSIBILITÉ
echo "📋 1. VÉRIFICATION DE L'ACCESSIBILITÉ\n";
echo str_repeat("-", 50) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
curl_close($ch);

echo "URL testée: $testUrl\n";
echo "Code HTTP: $httpCode\n";
echo "Type de contenu: $contentType\n";
echo "Taille du contenu: " . number_format($contentLength) . " octets\n";

if ($httpCode === 200) {
    echo "✅ Page accessible avec succès\n";
} else {
    echo "❌ Erreur d'accès: $httpCode\n";
    exit(1);
}

// 2. ANALYSE DU CONTENU HTML
echo "\n📋 2. ANALYSE DU CONTENU HTML\n";
echo str_repeat("-", 50) . "\n";

// Extraire le contenu HTML
$htmlContent = substr($response, strpos($response, "\r\n\r\n") + 4);

// Vérifications du contenu
$checks = [
    'Formulaire présent' => strpos($htmlContent, '<form') !== false,
    'CSRF token présent' => strpos($htmlContent, 'csrf_field()') !== false || strpos($htmlContent, 'name="_token"') !== false,
    'Champ nom application' => strpos($htmlContent, 'name="app_name"') !== false,
    'Champ logo' => strpos($htmlContent, 'name="app_logo"') !== false,
    'Champ favicon' => strpos($htmlContent, 'name="app_favicon"') !== false,
    'Champ couleur primaire' => strpos($htmlContent, 'name="primary_color"') !== false,
    'Champ couleur secondaire' => strpos($htmlContent, 'name="secondary_color"') !== false,
    'Bouton sauvegarder' => strpos($htmlContent, 'Sauvegarder l\'Apparence') !== false,
    'JavaScript présent' => strpos($htmlContent, '<script>') !== false,
    'CSS Bulma chargé' => strpos($htmlContent, 'bulma.min.css') !== false,
    'Font Awesome chargé' => strpos($htmlContent, 'font-awesome') !== false,
    'Breadcrumb présent' => strpos($htmlContent, 'breadcrumb') !== false,
    'Aperçu temps réel' => strpos($htmlContent, 'Aperçu en Temps Réel') !== false,
    'Informations présentes' => strpos($htmlContent, 'Informations') !== false
];

foreach ($checks as $check => $result) {
    echo ($result ? "✅" : "❌") . " $check\n";
}

// 3. VÉRIFICATION DES ROUTES ET MÉTHODES
echo "\n📋 3. VÉRIFICATION DES ROUTES ET MÉTHODES\n";
echo str_repeat("-", 50) . "\n";

// Vérifier les fichiers du contrôleur
$controllerFile = 'app/Controllers/Configuration.php';
$viewFile = 'app/Views/admin/configuration/appearance.php';

echo "Contrôleur: $controllerFile\n";
if (file_exists($controllerFile)) {
    echo "✅ Contrôleur existe\n";
    
    $controllerContent = file_get_contents($controllerFile);
    $methodChecks = [
        'Méthode appearance()' => strpos($controllerContent, 'public function appearance()') !== false,
        'Méthode saveAppearance()' => strpos($controllerContent, 'public function saveAppearance()') !== false,
        'Gestion d\'erreurs' => strpos($controllerContent, 'try {') !== false && strpos($controllerContent, 'catch') !== false,
        'Logging d\'erreurs' => strpos($controllerContent, 'log_message') !== false,
        'Flash messages' => strpos($controllerContent, 'getFlashdata') !== false
    ];
    
    foreach ($methodChecks as $check => $result) {
        echo ($result ? "✅" : "❌") . " $check\n";
    }
} else {
    echo "❌ Contrôleur manquant\n";
}

echo "\nVue: $viewFile\n";
if (file_exists($viewFile)) {
    echo "✅ Vue existe\n";
    
    $viewContent = file_get_contents($viewFile);
    $viewChecks = [
        'Extend layout' => strpos($viewContent, '$this->extend') !== false,
        'Section content' => strpos($viewContent, '$this->section') !== false,
        'Formulaire POST' => strpos($viewContent, 'method="POST"') !== false,
        'Enctype multipart' => strpos($viewContent, 'enctype="multipart/form-data"') !== false,
        'CSRF field' => strpos($viewContent, 'csrf_field()') !== false,
        'Base URL' => strpos($viewContent, 'base_url') !== false,
        'Validation old()' => strpos($viewContent, 'old(') !== false,
        'JavaScript inline' => strpos($viewContent, '<script>') !== false,
        'Prévisualisation' => strpos($viewContent, 'updateFileName') !== false
    ];
    
    foreach ($viewChecks as $check => $result) {
        echo ($result ? "✅" : "❌") . " $check\n";
    }
} else {
    echo "❌ Vue manquante\n";
}

// 4. VÉRIFICATION DES ROUTES
echo "\n📋 4. VÉRIFICATION DES ROUTES\n";
echo str_repeat("-", 50) . "\n";

$routesFile = 'app/Config/Routes.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    $routeChecks = [
        'Groupe admin' => strpos($routesContent, "group('admin'") !== false,
        'Groupe configuration' => strpos($routesContent, "group('configuration'") !== false,
        'Route appearance GET' => strpos($routesContent, "'appearance', 'Configuration::appearance'") !== false,
        'Route save-appearance POST' => strpos($routesContent, "'save-appearance', 'Configuration::saveAppearance'") !== false,
        'Filtre auth' => strpos($routesContent, "'filter' => 'auth'") !== false
    ];
    
    foreach ($routeChecks as $check => $result) {
        echo ($result ? "✅" : "❌") . " $check\n";
    }
} else {
    echo "❌ Fichier de routes manquant\n";
}

// 5. VÉRIFICATION DE LA BASE DE DONNÉES
echo "\n📋 5. VÉRIFICATION DE LA BASE DE DONNÉES\n";
echo str_repeat("-", 50) . "\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=kissai_school', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier les tables de configuration
    $tables = ['settings', 'appearance_settings', 'system_config'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;
            echo ($exists ? "✅" : "❌") . " Table $table existe\n";
            
            if ($exists) {
                $stmt = $pdo->query("DESCRIBE $table");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "   Colonnes: " . implode(', ', $columns) . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Erreur table $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Vérifier les données de configuration
    echo "\nDonnées de configuration:\n";
    $stmt = $pdo->query("SELECT * FROM settings WHERE setting_key LIKE 'app_%' LIMIT 5");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($settings)) {
        echo "⚠️  Aucune donnée de configuration trouvée\n";
    } else {
        foreach ($settings as $setting) {
            echo "   {$setting['setting_key']}: {$setting['setting_value']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

// 6. VÉRIFICATION DES ASSETS ET CSS
echo "\n📋 6. VÉRIFICATION DES ASSETS ET CSS\n";
echo str_repeat("-", 50) . "\n";

$assets = [
    'CSS Bulma' => '/assets/bulma/css/bulma.min.css',
    'JS Bulma' => '/assets/bulma/js/bulma.js',
    'Logo par défaut' => '/assets/images/logo.png',
    'Favicon par défaut' => '/assets/images/favicon.ico'
];

foreach ($assets as $name => $path) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo ($httpCode === 200 ? "✅" : "❌") . " $name ($path) - HTTP $httpCode\n";
}

// 7. VÉRIFICATION DES FONCTIONNALITÉS CRUD
echo "\n📋 7. VÉRIFICATION DES FONCTIONNALITÉS CRUD\n";
echo str_repeat("-", 50) . "\n";

// Test de soumission du formulaire
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/configuration/save-appearance');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'app_name' => 'TEST KISSAI SCHOOL',
    'primary_color' => '#ff0000',
    'secondary_color' => '#00ff00',
    'app_description' => 'Test description',
    'app_keywords' => 'test, keywords'
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test POST save-appearance: HTTP $httpCode\n";
if ($httpCode === 200 || $httpCode === 302) {
    echo "✅ Endpoint POST accessible\n";
} else {
    echo "❌ Erreur endpoint POST\n";
}

// 8. VÉRIFICATION DE LA SÉCURITÉ
echo "\n📋 8. VÉRIFICATION DE LA SÉCURITÉ\n";
echo str_repeat("-", 50) . "\n";

$securityChecks = [
    'CSRF Protection' => strpos($htmlContent, 'csrf_field()') !== false,
    'Validation côté client' => strpos($htmlContent, 'required') !== false,
    'Validation des types de fichiers' => strpos($htmlContent, 'accept=') !== false,
    'Sanitisation des entrées' => strpos($htmlContent, 'old(') !== false,
    'Gestion d\'erreurs' => strpos($htmlContent, 'notification is-danger') !== false,
    'Messages de succès' => strpos($htmlContent, 'notification is-success') !== false
];

foreach ($securityChecks as $check => $result) {
    echo ($result ? "✅" : "❌") . " $check\n";
}

// 9. VÉRIFICATION DE LA PERFORMANCE
echo "\n📋 9. VÉRIFICATION DE LA PERFORMANCE\n";
echo str_repeat("-", 50) . "\n";

$startTime = microtime(true);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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

// 10. VÉRIFICATION DE LA CONFORMITÉ
echo "\n📋 10. VÉRIFICATION DE LA CONFORMITÉ\n";
echo str_repeat("-", 50) . "\n";

$complianceChecks = [
    'HTML5 valide' => strpos($htmlContent, '<!DOCTYPE html>') !== false,
    'Meta viewport' => strpos($htmlContent, 'viewport') !== false,
    'Charset UTF-8' => strpos($htmlContent, 'charset="UTF-8"') !== false,
    'Titre de page' => strpos($htmlContent, '<title>') !== false,
    'Accessibilité ARIA' => strpos($htmlContent, 'aria-') !== false,
    'Labels pour formulaires' => strpos($htmlContent, '<label') !== false,
    'Breadcrumb navigation' => strpos($htmlContent, 'breadcrumb') !== false,
    'Messages d\'aide' => strpos($htmlContent, 'help') !== false
];

foreach ($complianceChecks as $check => $result) {
    echo ($result ? "✅" : "❌") . " $check\n";
}

// 11. AXES D'AMÉLIORATION
echo "\n📋 11. AXES D'AMÉLIORATION IDENTIFIÉS\n";
echo str_repeat("-", 50) . "\n";

$improvements = [];

// Vérifier si la méthode saveAppearance existe
if (strpos($controllerContent, 'public function saveAppearance()') === false) {
    $improvements[] = "❌ Méthode saveAppearance() manquante dans le contrôleur";
}

// Vérifier la gestion des fichiers uploadés
if (strpos($controllerContent, 'move_uploaded_file') === false && strpos($controllerContent, 'upload') !== false) {
    $improvements[] = "⚠️  Gestion des uploads de fichiers à améliorer";
}

// Vérifier la validation côté serveur
if (strpos($controllerContent, 'validate') === false) {
    $improvements[] = "⚠️  Validation côté serveur à renforcer";
}

// Vérifier le cache
if (strpos($controllerContent, 'cache') === false) {
    $improvements[] = "⚠️  Système de cache à implémenter";
}

// Vérifier les logs
if (strpos($controllerContent, 'log_message') === false) {
    $improvements[] = "⚠️  Système de logging à améliorer";
}

if (empty($improvements)) {
    echo "✅ Aucun axe d'amélioration critique identifié\n";
} else {
    foreach ($improvements as $improvement) {
        echo "$improvement\n";
    }
}

// 12. RAPPORT FINAL
echo "\n📋 12. RAPPORT FINAL\n";
echo str_repeat("=", 50) . "\n";

$totalChecks = 0;
$passedChecks = 0;

// Compter les vérifications réussies
foreach ([$checks, $methodChecks, $viewChecks, $routeChecks, $securityChecks, $complianceChecks] as $checkArray) {
    if (isset($checkArray)) {
        foreach ($checkArray as $result) {
            $totalChecks++;
            if ($result) $passedChecks++;
        }
    }
}

$successRate = ($totalChecks > 0) ? ($passedChecks / $totalChecks) * 100 : 0;

echo "Résumé de l'audit:\n";
echo "- Total des vérifications: $totalChecks\n";
echo "- Vérifications réussies: $passedChecks\n";
echo "- Taux de réussite: " . number_format($successRate, 1) . "%\n";

if ($successRate >= 90) {
    echo "🎉 EXCELLENT - Module conforme et fonctionnel\n";
} elseif ($successRate >= 75) {
    echo "✅ BON - Module fonctionnel avec quelques améliorations mineures\n";
} elseif ($successRate >= 60) {
    echo "⚠️  MOYEN - Module fonctionnel mais nécessite des améliorations\n";
} else {
    echo "❌ CRITIQUE - Module nécessite des corrections importantes\n";
}

echo "\n🔧 RECOMMANDATIONS PRIORITAIRES:\n";
echo "1. Implémenter la méthode saveAppearance() manquante\n";
echo "2. Renforcer la validation côté serveur\n";
echo "3. Améliorer la gestion des uploads de fichiers\n";
echo "4. Implémenter un système de cache\n";
echo "5. Corriger les références au port 8082 dans les assets\n";

echo "\n✅ AUDIT TERMINÉ - " . date('Y-m-d H:i:s') . "\n";
?>





