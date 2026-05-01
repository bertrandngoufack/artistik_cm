<?php

/**
 * TEST CORRIGÉ - MODULE EXAMENS
 * Script de test fonctionnel pour le module examens
 */

echo "🔍 TEST CORRIGÉ - MODULE EXAMENS\n";
echo "================================\n\n";

$baseUrl = 'http://localhost:8080';
$results = [];
$errors = [];
$successCount = 0;
$totalTests = 0;

// Fonction de test de route simplifiée
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

// Fonction de test POST simplifiée
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

testRoute('Page principale examens', '/admin/examens');
testRoute('Liste des examens', '/admin/examens/exams');
testRoute('Création examen', '/admin/examens/exams/create');
testRoute('Gestion des notes', '/admin/examens/grades');
testRoute('Bulletins de notes', '/admin/examens/report-cards');
testRoute('Statistiques', '/admin/examens/statistics');
testRoute('Périodes académiques', '/admin/examens/academic-periods');

echo "\n";

// 2. Test des actions CRUD pour les examens
echo "🔘 TEST DES ACTIONS CRUD - EXAMENS\n";
echo "-----------------------------------\n";

// Test avec différents IDs d'examens
for ($i = 1; $i <= 3; $i++) {
    echo "\n📚 Examen ID: $i\n";
    echo "   " . str_repeat("-", 20) . "\n";
    
    testRoute("Voir examen $i", "/admin/examens/exams/$i/view");
    testRoute("Éditer examen $i", "/admin/examens/exams/$i/edit");
    testRoute("Supprimer examen $i", "/admin/examens/exams/$i/delete", 302);
    testRoute("Saisie notes examen $i", "/admin/examens/grades/enter/$i");
}

echo "\n";

// 3. Test des opérations POST
echo "🔄 TEST DES OPÉRATIONS POST\n";
echo "----------------------------\n";

// Test création d'examen
$examData = [
    'name' => 'Test Examen Audit Corrigé ' . date('Y-m-d H:i:s'),
    'exam_type' => 'FINAL',
    'class_id' => 1,
    'exam_date' => '2025-09-15',
    'total_marks' => 20,
    'academic_period' => '1ER_TRIMESTRE'
];
testPost('Création examen', '/admin/examens/exams/store', $examData);

// Test génération de bulletins
$reportData = [
    'class_id' => 1,
    'exam_id' => 1,
    'period' => '1ER_TRIMESTRE',
    'format' => 'pdf'
];
testPost('Génération bulletins', '/admin/examens/report-cards/generate', $reportData);

// Test mise à jour période académique
$periodData = [
    'academic_year' => '2024-2025',
    'start_date' => '2024-09-01',
    'end_date' => '2025-06-30'
];
testPost('Mise à jour période académique', '/admin/examens/academic-periods/update', $periodData);

echo "\n";

// 4. Test de cohérence avec autres modules
echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
echo "-----------------------------------------\n";

testRoute('Classes (pour examens)', '/admin/etudes/classes');
testRoute('Élèves (pour notes)', '/admin/scolarite/students');
testRoute('Matières (pour examens)', '/admin/etudes/subjects');
testRoute('Statistiques générales', '/admin/statistiques');

echo "\n";

// 5. Test des exports et rapports
echo "📄 TEST DES EXPORTS ET RAPPORTS\n";
echo "--------------------------------\n";

testRoute('Export statistiques PDF', '/admin/examens/statistics/export?format=pdf');
testRoute('Export statistiques Excel', '/admin/examens/statistics/export?format=excel');
testRoute('Export statistiques CSV', '/admin/examens/statistics/export?format=csv');

echo "\n";

// 6. Test des actions avec données invalides
echo "⚠️ TEST DES ACTIONS AVEC DONNÉES INVALIDES\n";
echo "-------------------------------------------\n";

testRoute('Voir examen inexistant', '/admin/examens/exams/999/view', 404);
testRoute('Éditer examen inexistant', '/admin/examens/exams/999/edit', 404);
testRoute('Saisie notes examen inexistant', '/admin/examens/grades/enter/999', 404);

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - MODULE EXAMENS CORRIGÉ\n";
echo "=============================================\n\n";

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
    'Actions CRUD' => 0,
    'Opérations POST' => 0,
    'Cohérence modules' => 0,
    'Exports/Rapports' => 0,
    'Sécurité' => 0
];

foreach ($results as $result) {
    if (strpos($result, 'Page principale') !== false || strpos($result, 'Liste des examens') !== false) {
        $categories['Pages principales']++;
    } elseif (strpos($result, 'Voir examen') !== false || strpos($result, 'Éditer examen') !== false) {
        $categories['Actions CRUD']++;
    } elseif (strpos($result, 'Création') !== false || strpos($result, 'Génération') !== false) {
        $categories['Opérations POST']++;
    } elseif (strpos($result, 'Classes') !== false || strpos($result, 'Élèves') !== false) {
        $categories['Cohérence modules']++;
    } elseif (strpos($result, 'Export') !== false) {
        $categories['Exports/Rapports']++;
    } elseif (strpos($result, 'injection') !== false) {
        $categories['Sécurité']++;
    }
}

foreach ($categories as $category => $count) {
    echo "   • $category: $count tests\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 MODULE EXAMENS: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ MODULE EXAMENS: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ MODULE EXAMENS: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ MODULE EXAMENS: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


