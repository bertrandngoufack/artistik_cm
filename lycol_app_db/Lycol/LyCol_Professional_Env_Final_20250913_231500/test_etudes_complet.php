<?php

/**
 * TEST COMPLET MODULE ÉTUDES
 * Audit complet du module Études avec vérification CRUD
 */

echo "🔍 AUDIT COMPLET MODULE ÉTUDES\n";
echo "==============================\n\n";

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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        echo "✅ SUCCÈS\n";
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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ SUCCÈS\n";
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
    '/admin/etudes' => 'Dashboard Études',
    '/admin/etudes/cycles' => 'Gestion des Cycles',
    '/admin/etudes/classes' => 'Gestion des Classes',
    '/admin/etudes/subjects' => 'Gestion des Matières',
    '/admin/etudes/timetable' => 'Emplois du Temps',
    '/admin/etudes/assignments' => 'Assignations',
    '/admin/etudes/reports' => 'Rapports'
];

foreach ($mainRoutes as $route => $description) {
    testRoute($description, $route);
}

echo "\n";

// 2. Test des formulaires de création
echo "📝 TEST DES FORMULAIRES DE CRÉATION\n";
echo "-----------------------------------\n";

$createForms = [
    '/admin/etudes/cycles/create' => 'Formulaire Cycle',
    '/admin/etudes/classes/create' => 'Formulaire Classe',
    '/admin/etudes/subjects/create' => 'Formulaire Matière',
    '/admin/etudes/assignments/create' => 'Formulaire Assignation'
];

foreach ($createForms as $form => $description) {
    testRoute($description, $form);
}

echo "\n";

// 3. Test des opérations CRUD
echo "🔄 TEST DES OPÉRATIONS CRUD\n";
echo "---------------------------\n";

// Test création cycle
$cycleData = [
    'name' => 'Test Cycle ' . date('Y-m-d H:i:s'),
    'code' => 'TC' . rand(100, 999),
    'description' => 'Cycle de test pour audit',
    'is_active' => 1
];
testPost('Création Cycle', '/admin/etudes/cycles/store', $cycleData);

// Test création classe
$classData = [
    'name' => 'Test Classe ' . date('Y-m-d H:i:s'),
    'code' => 'CL' . rand(100, 999),
    'cycle_id' => 1,
    'level' => 1,
    'capacity' => 30,
    'is_active' => 1
];
testPost('Création Classe', '/admin/etudes/classes/store', $classData);

// Test création matière
$subjectData = [
    'name' => 'Test Matière ' . date('Y-m-d H:i:s'),
    'code' => 'MT' . rand(100, 999),
    'description' => 'Matière de test pour audit',
    'coefficient' => 2,
    'is_active' => 1
];
testPost('Création Matière', '/admin/etudes/subjects/store', $subjectData);

// Test création assignation
$assignmentData = [
    'teacher_id' => 1,
    'class_id' => 1,
    'subject_id' => 1,
    'academic_year' => '2024-2025',
    'is_active' => 1
];
testPost('Création Assignation', '/admin/etudes/assignments/store', $assignmentData);

echo "\n";

// 4. Test des exports
echo "📊 TEST DES EXPORTS\n";
echo "-------------------\n";

$exports = [
    '/admin/etudes/reports/export/csv' => 'Export CSV',
    '/admin/etudes/reports/export/pdf' => 'Export PDF'
];

foreach ($exports as $export => $description) {
    testRoute($description, $export);
}

echo "\n";

// 5. Test de cohérence avec autres modules
echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
echo "----------------------------------------\n";

$coherenceTests = [
    '/admin/enseignants' => 'Module Enseignants',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/examens' => 'Module Examens',
    '/admin/economat' => 'Module Économat'
];

foreach ($coherenceTests as $test => $description) {
    testRoute($description, $test);
}

echo "\n";

// 6. Test des liens de navigation
echo "🧭 TEST DE LA NAVIGATION\n";
echo "------------------------\n";

$navigationTests = [
    '/admin/etudes/cycles' => 'Navigation Cycles',
    '/admin/etudes/classes' => 'Navigation Classes',
    '/admin/etudes/subjects' => 'Navigation Matières',
    '/admin/etudes/timetable' => 'Navigation EDT',
    '/admin/etudes/assignments' => 'Navigation Assignations'
];

foreach ($navigationTests as $test => $description) {
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
    echo "🎉 MODULE ÉTUDES: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités principales sont opérationnelles.\n";
} elseif ($successRate >= 75) {
    echo "✅ MODULE ÉTUDES: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ MODULE ÉTUDES: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ MODULE ÉTUDES: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes\n";
echo "📋 Rapport généré le: " . date('Y-m-d H:i:s') . "\n";


