<?php

/**
 * TEST COMPLET DES ACTIONS CRUD - GESTION DES CLASSES
 * Audit complet des fonctionnalités CRUD du module Classes
 */

echo "🔍 TEST COMPLET DES ACTIONS CRUD - GESTION DES CLASSES\n";
echo "=====================================================\n\n";

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
    curl_setopt($ch, CURLOPT_NOBODY, true);
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

// 1. Test des routes principales
echo "🔗 TEST DES ROUTES PRINCIPALES\n";
echo "-----------------------------\n";

$mainRoutes = [
    '/admin/etudes/classes' => 'Liste des Classes',
    '/admin/etudes/classes/create' => 'Formulaire Création',
    '/admin/etudes/classes/1/view' => 'Vue Classe ID 1',
    '/admin/etudes/classes/1/edit' => 'Édition Classe ID 1'
];

foreach ($mainRoutes as $route => $description) {
    testRoute($description, $route);
}

echo "\n";

// 2. Test des opérations CRUD
echo "🔄 TEST DES OPÉRATIONS CRUD\n";
echo "---------------------------\n";

// Test création classe
$classData = [
    'name' => 'Test Classe Actions ' . date('Y-m-d H:i:s'),
    'code' => 'CLACT' . rand(100, 999),
    'cycle_id' => 1,
    'level' => 1,
    'capacity' => 30,
    'description' => 'Classe de test pour audit actions',
    'is_active' => 1
];
testPost('Création Classe', '/admin/etudes/classes/store', $classData);

// Test mise à jour classe
$updateData = [
    'name' => 'Test Classe Modifiée ' . date('Y-m-d H:i:s'),
    'code' => 'CLMOD' . rand(100, 999),
    'cycle_id' => 1,
    'level' => 2,
    'capacity' => 35,
    'description' => 'Classe modifiée pour audit'
];
testPost('Mise à jour Classe', '/admin/etudes/classes/1/update', $updateData);

echo "\n";

// 3. Test des boutons d'action
echo "🔘 TEST DES BOUTONS D'ACTION\n";
echo "----------------------------\n";

$actionRoutes = [
    '/admin/etudes/classes/1/view' => 'Bouton Voir (👁️)',
    '/admin/etudes/classes/1/edit' => 'Bouton Éditer (✏️)',
    '/admin/etudes/classes/1/delete' => 'Bouton Supprimer (🗑️)'
];

foreach ($actionRoutes as $route => $description) {
    testRoute($description, $route);
}

echo "\n";

// 4. Test de cohérence avec autres modules
echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
echo "----------------------------------------\n";

$coherenceTests = [
    '/admin/etudes/cycles' => 'Module Cycles (pour cycle_id)',
    '/admin/etudes/subjects' => 'Module Matières (pour assignations)',
    '/admin/etudes/assignments' => 'Module Assignations (pour classes)',
    '/admin/enseignants' => 'Module Enseignants (pour assignations)',
    '/admin/scolarite' => 'Module Scolarité (pour élèves)'
];

foreach ($coherenceTests as $test => $description) {
    testRoute($description, $test);
}

echo "\n";

// 5. Test des liens de navigation
echo "🧭 TEST DE LA NAVIGATION\n";
echo "------------------------\n";

$navigationTests = [
    '/admin/etudes' => 'Retour Dashboard Études',
    '/admin/etudes/classes' => 'Navigation Classes',
    '/admin/etudes/cycles' => 'Navigation Cycles',
    '/admin/etudes/subjects' => 'Navigation Matières'
];

foreach ($navigationTests as $test => $description) {
    testRoute($description, $test);
}

echo "\n";

// 6. Test des filtres et recherche
echo "🔍 TEST DES FILTRES ET RECHERCHE\n";
echo "--------------------------------\n";

$filterTests = [
    '/admin/etudes/classes?cycle=1' => 'Filtre par Cycle',
    '/admin/etudes/classes?level=1' => 'Filtre par Niveau',
    '/admin/etudes/classes?status=1' => 'Filtre par Statut',
    '/admin/etudes/classes?search=test' => 'Recherche par nom'
];

foreach ($filterTests as $test => $description) {
    testRoute($description, $test);
}

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX\n";
echo "===================\n\n";

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

if ($successRate >= 90) {
    echo "🎉 GESTION DES CLASSES: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités CRUD sont opérationnelles.\n";
} elseif ($successRate >= 75) {
    echo "✅ GESTION DES CLASSES: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ GESTION DES CLASSES: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ GESTION DES CLASSES: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes/classes\n";
echo "📋 Rapport généré le: " . date('Y-m-d H:i:s') . "\n";


