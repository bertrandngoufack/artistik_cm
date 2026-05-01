<?php

/**
 * TEST COMPLET - GESTION DES PÉRIODES ACADÉMIQUES
 * Audit complet du module périodes académiques avec vérification CRUD et cohérence
 */

echo "🔍 TEST COMPLET - GESTION DES PÉRIODES ACADÉMIQUES\n";
echo "================================================\n\n";

$baseUrl = 'http://localhost:8080';
$results = [];
$errors = [];
$successCount = 0;
$totalTests = 0;

// Fonction de test de route
function testRoute($description, $url, $expectedCode = 200) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🔍 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        echo "✅ SUCCÈS (HTTP $httpCode)\n";
        $successCount++;
        $results[] = "✅ $description: OK";
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode (attendu: $expectedCode)";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// Fonction de test POST
function testPost($description, $url, $data, $expectedCode = 303) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🔄 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ SUCCÈS (HTTP $httpCode)\n";
        $successCount++;
        $results[] = "✅ $description: OK";
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// 1. Test des pages principales
echo "📊 TEST DES PAGES PRINCIPALES\n";
echo "------------------------------\n";

testRoute('Page périodes académiques', '/admin/examens/academic-periods');
testRoute('Page périodes avec année spécifique', '/admin/examens/academic-periods?academic_year=2024-2025');

echo "\n";

// 2. Test des opérations POST
echo "🔄 TEST DES OPÉRATIONS POST\n";
echo "----------------------------\n";

// Test mise à jour des périodes existantes
testPost('Mise à jour 1er trimestre 2024-2025', '/admin/examens/academic-periods/update', [
    'period_id' => 1,
    'start_date' => '2024-09-01',
    'end_date' => '2024-12-20'
]);

testPost('Mise à jour 2ème trimestre 2024-2025', '/admin/examens/academic-periods/update', [
    'period_id' => 2,
    'start_date' => '2025-01-06',
    'end_date' => '2025-03-28'
]);

testPost('Mise à jour 3ème trimestre 2024-2025', '/admin/examens/academic-periods/update', [
    'period_id' => 3,
    'start_date' => '2025-04-07',
    'end_date' => '2025-06-30'
]);

echo "\n";

// 3. Test création nouvelle année académique
echo "🆕 TEST CRÉATION NOUVELLE ANNÉE ACADÉMIQUE\n";
echo "==========================================\n";

testPost('Création année 2026-2027', '/admin/examens/academic-periods/create-year', [
    'academic_year' => '2026-2027'
]);

echo "\n";

// 4. Test de cohérence avec autres modules
echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
echo "-----------------------------------------\n";

testRoute('Module examens principal', '/admin/examens');
testRoute('Module examens avec périodes', '/admin/examens/exams');
testRoute('Création examen avec périodes', '/admin/examens/exams/create');

echo "\n";

// 5. Test des données en base de données
echo "🗄️ TEST DES DONNÉES EN BASE\n";
echo "-----------------------------\n";

// Vérifier les données en base
$dbHost = '100.69.65.33';
$dbPort = '13306';
$dbUser = 'root';
$dbPass = 'Bateau123';
$dbName = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Compter les périodes par année
    $stmt = $pdo->query("SELECT academic_year, COUNT(*) as count FROM academic_periods GROUP BY academic_year ORDER BY academic_year");
    $periodsByYear = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Périodes par année académique :\n";
    foreach ($periodsByYear as $year) {
        echo "     • {$year['academic_year']}: {$year['count']} périodes\n";
        if ($year['count'] == 3) {
            echo "       ✅ Nombre correct de périodes\n";
            $successCount++;
            $results[] = "✅ Année {$year['academic_year']}: 3 périodes OK";
        } else {
            echo "       ⚠️ Nombre incorrect de périodes (attendu: 3)\n";
            $errors[] = "Année {$year['academic_year']}: {$year['count']} périodes (attendu: 3)";
            $results[] = "❌ Année {$year['academic_year']}: {$year['count']} périodes";
        }
        $totalTests++;
    }
    
    // Vérifier les périodes actives
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM academic_periods WHERE is_active = 1");
    $activePeriods = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Périodes actives : {$activePeriods['count']}\n";
    if ($activePeriods['count'] > 0) {
        echo "     ✅ Des périodes sont actives\n";
        $successCount++;
        $results[] = "✅ Périodes actives: {$activePeriods['count']} OK";
    } else {
        echo "     ❌ Aucune période active\n";
        $errors[] = "Aucune période active";
        $results[] = "❌ Aucune période active";
    }
    $totalTests++;
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// 6. Test de validation des données
echo "✅ TEST DE VALIDATION DES DONNÉES\n";
echo "----------------------------------\n";

// Test avec données invalides
testPost('Validation - Dates invalides', '/admin/examens/academic-periods/update', [
    'period_id' => 1,
    'start_date' => '2024-12-20', // Date de fin comme date de début
    'end_date' => '2024-09-01'    // Date de début comme date de fin
]);

testPost('Validation - Année invalide', '/admin/examens/academic-periods/create-year', [
    'academic_year' => '2025' // Format invalide
]);

echo "\n";

// 7. Test de navigation et interface
echo "🌐 TEST DE NAVIGATION ET INTERFACE\n";
echo "-----------------------------------\n";

testRoute('Navigation depuis examens', '/admin/examens');
testRoute('Retour vers examens', '/admin/examens');

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - PÉRIODES ACADÉMIQUES\n";
echo "==========================================\n\n";

$successRate = ($totalTests > 0) ? round(($successCount / $totalTests) * 100, 1) : 0;

echo "📈 STATISTIQUES:\n";
echo "   • Tests réussis: {$successCount}/{$totalTests}\n";
echo "   • Taux de succès: {$successRate}%\n";
echo "   • Erreurs: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERREURS DÉTECTÉES:\n";
    echo "---------------------\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

echo "✅ TESTS RÉUSSIS:\n";
echo "-----------------\n";
foreach ($results as $result) {
    if (strpos($result, '✅') === 0) {
        echo "   $result\n";
    }
}
echo "\n";

// Analyse spécifique par catégorie
echo "🔍 ANALYSE PAR CATÉGORIE:\n";
echo "--------------------------\n";

$categories = [
    'Pages principales' => 0,
    'Opérations POST' => 0,
    'Création année' => 0,
    'Cohérence modules' => 0,
    'Base de données' => 0,
    'Validation' => 0,
    'Navigation' => 0
];

foreach ($results as $result) {
    if (strpos($result, 'Page') !== false) {
        $categories['Pages principales']++;
    } elseif (strpos($result, 'Mise à jour') !== false) {
        $categories['Opérations POST']++;
    } elseif (strpos($result, 'Création année') !== false) {
        $categories['Création année']++;
    } elseif (strpos($result, 'Module') !== false) {
        $categories['Cohérence modules']++;
    } elseif (strpos($result, 'Année') !== false || strpos($result, 'Périodes actives') !== false) {
        $categories['Base de données']++;
    } elseif (strpos($result, 'Validation') !== false) {
        $categories['Validation']++;
    } elseif (strpos($result, 'Navigation') !== false) {
        $categories['Navigation']++;
    }
}

foreach ($categories as $category => $count) {
    echo "   • $category: $count tests\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 GESTION DES PÉRIODES ACADÉMIQUES: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ GESTION DES PÉRIODES ACADÉMIQUES: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ GESTION DES PÉRIODES ACADÉMIQUES: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ GESTION DES PÉRIODES ACADÉMIQUES: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens/academic-periods\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


