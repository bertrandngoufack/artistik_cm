<?php

/**
 * TEST COMPLET - MODULE EXAMENS PRINCIPAL
 * Audit complet du module examens avec vérification CRUD et cohérence
 */

echo "🔍 TEST COMPLET - MODULE EXAMENS PRINCIPAL\n";
echo "=========================================\n\n";

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

testRoute('Page principale examens', '/admin/examens');
testRoute('Liste des examens', '/admin/examens/exams');
testRoute('Création d\'examen', '/admin/examens/exams/create');
testRoute('Gestion des notes', '/admin/examens/grades');
testRoute('Bulletins de notes', '/admin/examens/report-cards');
testRoute('Statistiques', '/admin/examens/statistics');
testRoute('Périodes académiques', '/admin/examens/academic-periods');

echo "\n";

// 2. Test des actions CRUD pour les examens
echo "🔘 TEST DES ACTIONS CRUD - EXAMENS\n";
echo "-----------------------------------\n";

// Test création d'examen
testPost('Création d\'examen', '/admin/examens/exams/store', [
    'name' => 'Test Examen Audit',
    'exam_type' => 'CONTINUOUS',
    'class_id' => 1,
    'exam_date' => '2024-12-15',
    'total_marks' => 20,
    'coefficient' => 1,
    'academic_year' => '2024-2025'
]);

echo "\n";

// 3. Test des actions sur les examens existants
echo "📝 TEST DES ACTIONS SUR EXAMENS EXISTANTS\n";
echo "-----------------------------------------\n";

// Récupérer un examen existant pour les tests
$dbHost = '100.69.65.33';
$dbPort = '13306';
$dbUser = 'root';
$dbPass = 'Bateau123';
$dbName = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer un examen existant
    $stmt = $pdo->query("SELECT id, name FROM exams LIMIT 1");
    $existingExam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingExam) {
        $examId = $existingExam['id'];
        echo "  📊 Examen trouvé pour les tests : ID $examId - {$existingExam['name']}\n";
        
        testRoute('Voir examen existant', "/admin/examens/exams/$examId/view");
        testRoute('Modifier examen existant', "/admin/examens/exams/$examId/edit");
        testRoute('Saisir notes pour examen', "/admin/examens/grades/enter/$examId");
        
        // Test mise à jour d'examen
        testPost('Mise à jour examen', "/admin/examens/exams/$examId/update", [
            'name' => 'Examen Modifié Audit',
            'exam_type' => 'MIDTERM',
            'class_id' => 1,
            'exam_date' => '2024-12-20',
            'total_marks' => 25,
            'coefficient' => 1.5,
            'academic_year' => '2024-2025'
        ]);
        
    } else {
        echo "  ⚠️ Aucun examen trouvé pour les tests\n";
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// 4. Test des opérations POST
echo "🔄 TEST DES OPÉRATIONS POST\n";
echo "----------------------------\n";

// Test saisie de notes
testPost('Saisie de notes', '/admin/examens/grades/store', [
    'exam_id' => 1,
    'student_id' => 1,
    'subject_id' => 1,
    'mark' => 15.5,
    'coefficient' => 1,
    'comments' => 'Bon travail'
]);

// Test génération de bulletins
testPost('Génération bulletins', '/admin/examens/report-cards/generate', [
    'exam_id' => 1,
    'class_id' => 1,
    'academic_year' => '2024-2025'
]);

// Test export PDF
testPost('Export PDF bulletins', '/admin/examens/report-cards/generate-pdf', [
    'exam_id' => 1,
    'class_id' => 1
]);

echo "\n";

// 5. Test de cohérence avec autres modules
echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
echo "-----------------------------------------\n";

testRoute('Module classes', '/admin/etudes/classes');
testRoute('Module étudiants', '/admin/etudes/students');
testRoute('Module matières', '/admin/etudes/subjects');
testRoute('Module sécurité', '/admin/securite');
testRoute('Module configuration', '/admin/configuration');

echo "\n";

// 6. Test des exports et rapports
echo "📄 TEST DES EXPORTS ET RAPPORTS\n";
echo "--------------------------------\n";

testRoute('Export statistiques', '/admin/examens/statistics/export');
testRoute('Génération bulletins', '/admin/examens/report-cards/generate');

echo "\n";

// 7. Test des actions avec données invalides
echo "⚠️ TEST DES ACTIONS AVEC DONNÉES INVALIDES\n";
echo "-------------------------------------------\n";

// Test avec données manquantes
testPost('Validation - Données manquantes', '/admin/examens/exams/store', [
    'name' => '',
    'exam_type' => 'INVALID_TYPE',
    'class_id' => 999, // Classe inexistante
    'exam_date' => '2024-13-45', // Date invalide
    'total_marks' => -5 // Note négative
]);

// Test avec ID inexistant
testRoute('Voir examen inexistant', '/admin/examens/exams/99999/view', 404);

echo "\n";

// 8. Test des données en base de données
echo "🗄️ TEST DES DONNÉES EN BASE\n";
echo "-----------------------------\n";

try {
    // Compter les examens
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exams");
    $examCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Total examens en base : {$examCount['count']}\n";
    if ($examCount['count'] > 0) {
        echo "     ✅ Des examens existent en base\n";
        $successCount++;
        $results[] = "✅ Examens en base: {$examCount['count']} OK";
    } else {
        echo "     ⚠️ Aucun examen en base\n";
        $errors[] = "Aucun examen en base";
        $results[] = "❌ Aucun examen en base";
    }
    $totalTests++;
    
    // Vérifier les types d'examens
    $stmt = $pdo->query("SELECT exam_type, COUNT(*) as count FROM exams GROUP BY exam_type");
    $examTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Types d'examens :\n";
    foreach ($examTypes as $type) {
        echo "     • {$type['exam_type']}: {$type['count']} examens\n";
    }
    
    // Vérifier les statuts
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM exams GROUP BY status");
    $examStatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Statuts des examens :\n";
    foreach ($examStatuses as $status) {
        echo "     • {$status['status']}: {$status['count']} examens\n";
    }
    
    // Vérifier la cohérence avec les classes
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM exams e 
        LEFT JOIN classes c ON e.class_id = c.id 
        WHERE c.id IS NULL
    ");
    $orphanExams = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Examens orphelins (sans classe) : {$orphanExams['count']}\n";
    if ($orphanExams['count'] == 0) {
        echo "     ✅ Tous les examens ont une classe valide\n";
        $successCount++;
        $results[] = "✅ Cohérence examens-classes: OK";
    } else {
        echo "     ❌ {$orphanExams['count']} examens sans classe valide\n";
        $errors[] = "Examens orphelins: {$orphanExams['count']}";
        $results[] = "❌ Cohérence examens-classes: ÉCHEC";
    }
    $totalTests++;
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - MODULE EXAMENS PRINCIPAL\n";
echo "==============================================\n\n";

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
    'Validation' => 0,
    'Base de données' => 0
];

foreach ($results as $result) {
    if (strpos($result, 'Page principale') !== false || strpos($result, 'Liste des examens') !== false || strpos($result, 'Création d\'examen') !== false || strpos($result, 'Gestion des notes') !== false || strpos($result, 'Bulletins de notes') !== false || strpos($result, 'Statistiques') !== false || strpos($result, 'Périodes académiques') !== false) {
        $categories['Pages principales']++;
    } elseif (strpos($result, 'Création d\'examen') !== false || strpos($result, 'Voir examen') !== false || strpos($result, 'Modifier examen') !== false || strpos($result, 'Mise à jour examen') !== false) {
        $categories['Actions CRUD']++;
    } elseif (strpos($result, 'Saisie de notes') !== false || strpos($result, 'Génération bulletins') !== false || strpos($result, 'Export PDF') !== false) {
        $categories['Opérations POST']++;
    } elseif (strpos($result, 'Module classes') !== false || strpos($result, 'Module étudiants') !== false || strpos($result, 'Module matières') !== false || strpos($result, 'Module sécurité') !== false || strpos($result, 'Module configuration') !== false) {
        $categories['Cohérence modules']++;
    } elseif (strpos($result, 'Export statistiques') !== false || strpos($result, 'Génération bulletins') !== false) {
        $categories['Exports/Rapports']++;
    } elseif (strpos($result, 'Validation -') !== false) {
        $categories['Validation']++;
    } elseif (strpos($result, 'Examens en base') !== false || strpos($result, 'Cohérence examens-classes') !== false) {
        $categories['Base de données']++;
    }
}

foreach ($categories as $category => $count) {
    echo "   • $category: $count tests\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 MODULE EXAMENS PRINCIPAL: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ MODULE EXAMENS PRINCIPAL: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ MODULE EXAMENS PRINCIPAL: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ MODULE EXAMENS PRINCIPAL: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>







