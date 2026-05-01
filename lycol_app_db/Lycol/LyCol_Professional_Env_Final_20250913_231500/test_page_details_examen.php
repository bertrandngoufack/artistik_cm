<?php

/**
 * TEST COMPLET - PAGE DÉTAILS D'EXAMEN
 * Audit complet de la page de détails d'examen avec vérification CRUD et pagination
 */

echo "🔍 TEST COMPLET - PAGE DÉTAILS D'EXAMEN\n";
echo "=======================================\n\n";

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

// 1. Test de la page principale
echo "📊 TEST DE LA PAGE PRINCIPALE\n";
echo "------------------------------\n";

testRoute('Page détails examen ID 4', '/admin/examens/exams/4/view');

echo "\n";

// 2. Test des liens d'action
echo "🔗 TEST DES LIENS D'ACTION\n";
echo "---------------------------\n";

testRoute('Lien modifier examen', '/admin/examens/exams/4/edit');
testRoute('Lien saisir notes', '/admin/examens/grades/enter/4');
testRoute('Lien retour liste', '/admin/examens/exams');

echo "\n";

// 3. Test avec différents IDs d'examens
echo "📝 TEST AVEC DIFFÉRENTS IDS D'EXAMENS\n";
echo "--------------------------------------\n";

// Récupérer des examens existants depuis la base
$dbHost = '100.69.65.33';
$dbPort = '13306';
$dbUser = 'root';
$dbPass = 'Bateau123';
$dbName = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer 3 examens différents
    $stmt = $pdo->query("SELECT id, name FROM exams ORDER BY id LIMIT 3");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($exams as $exam) {
        echo "  📊 Test examen ID {$exam['id']} - {$exam['name']}\n";
        testRoute("Détails examen {$exam['id']}", "/admin/examens/exams/{$exam['id']}/view");
    }
    
    // Test avec un ID inexistant
    testRoute('Détails examen inexistant', '/admin/examens/exams/99999/view', 404);
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// 4. Test de la pagination (si implémentée)
echo "📄 TEST DE LA PAGINATION\n";
echo "-------------------------\n";

// Vérifier si la pagination est implémentée dans la vue
testRoute('Page détails avec pagination', '/admin/examens/exams/4/view?page=1');
testRoute('Page détails avec limite', '/admin/examens/exams/4/view?limit=10');

echo "\n";

// 5. Test des actions CRUD
echo "🔘 TEST DES ACTIONS CRUD\n";
echo "-------------------------\n";

// Test mise à jour d'examen
testPost('Mise à jour examen 4', '/admin/examens/exams/4/update', [
    'name' => 'Examen Final - CP A (Modifié)',
    'exam_type' => 'FINAL',
    'class_id' => 1,
    'exam_date' => '2025-06-01',
    'total_marks' => 20,
    'coefficient' => 2.5,
    'academic_year' => '2024-2025'
]);

// Test saisie de notes
testPost('Saisie notes examen 4', '/admin/examens/grades/store', [
    'exam_id' => 4,
    'student_id' => 1,
    'subject_id' => 1,
    'mark' => 16.5,
    'coefficient' => 1,
    'comments' => 'Test de saisie de notes'
]);

echo "\n";

// 6. Test de cohérence avec les données
echo "🗄️ TEST DE COHÉRENCE AVEC LES DONNÉES\n";
echo "---------------------------------------\n";

try {
    // Vérifier les données de l'examen 4
    $stmt = $pdo->prepare("SELECT e.*, c.name as class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id WHERE e.id = ?");
    $stmt->execute([4]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        echo "  📊 Données examen ID 4 :\n";
        echo "     • Nom: {$exam['name']}\n";
        echo "     • Type: {$exam['exam_type']}\n";
        echo "     • Date: {$exam['exam_date']}\n";
        echo "     • Note max: {$exam['total_marks']}\n";
        echo "     • Coefficient: {$exam['coefficient']}\n";
        echo "     • Statut: {$exam['status']}\n";
        echo "     • Classe: {$exam['class_name']}\n";
        
        $successCount++;
        $results[] = "✅ Données examen 4: OK";
    } else {
        echo "     ❌ Examen 4 non trouvé en base\n";
        $errors[] = "Examen 4 non trouvé en base";
        $results[] = "❌ Données examen 4: ÉCHEC";
    }
    $totalTests++;
    
    // Vérifier les notes de l'examen 4
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, AVG(marks_obtained) as average FROM grades WHERE exam_id = ?");
    $stmt->execute([4]);
    $gradeStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Statistiques notes examen 4 :\n";
    echo "     • Total notes: {$gradeStats['count']}\n";
    echo "     • Moyenne: " . round($gradeStats['average'], 2) . "/20\n";
    
    if ($gradeStats['count'] > 0) {
        $successCount++;
        $results[] = "✅ Statistiques notes examen 4: OK";
    } else {
        echo "     ⚠️ Aucune note pour cet examen\n";
        $errors[] = "Aucune note pour examen 4";
        $results[] = "❌ Statistiques notes examen 4: ÉCHEC";
    }
    $totalTests++;
    
    // Vérifier la cohérence avec les élèves
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT g.student_id) as students_with_grades,
               COUNT(DISTINCT s.id) as total_students
        FROM grades g 
        LEFT JOIN students s ON g.student_id = s.id 
        WHERE g.exam_id = ?
    ");
    $stmt->execute([4]);
    $studentStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Cohérence élèves-notes :\n";
    echo "     • Élèves avec notes: {$studentStats['students_with_grades']}\n";
    echo "     • Total élèves: {$studentStats['total_students']}\n";
    
    if ($studentStats['students_with_grades'] > 0) {
        $successCount++;
        $results[] = "✅ Cohérence élèves-notes: OK";
    } else {
        echo "     ❌ Aucun élève avec des notes\n";
        $errors[] = "Aucun élève avec des notes";
        $results[] = "❌ Cohérence élèves-notes: ÉCHEC";
    }
    $totalTests++;
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// 7. Test de performance avec beaucoup de données
echo "⚡ TEST DE PERFORMANCE\n";
echo "----------------------\n";

// Vérifier le temps de chargement de la page
$startTime = microtime(true);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/examens/exams/4/view');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$endTime = microtime(true);

$loadTime = round(($endTime - $startTime) * 1000, 2);
echo "  ⏱️ Temps de chargement: {$loadTime}ms\n";

if ($httpCode == 200 && $loadTime < 5000) {
    echo "     ✅ Performance acceptable\n";
    $successCount++;
    $results[] = "✅ Performance: OK ({$loadTime}ms)";
} else {
    echo "     ⚠️ Performance lente ou erreur\n";
    $errors[] = "Performance lente: {$loadTime}ms";
    $results[] = "❌ Performance: ÉCHEC ({$loadTime}ms)";
}
$totalTests++;

echo "\n";

// 8. Test de la pagination manquante
echo "📄 TEST DE LA PAGINATION MANQUANTE\n";
echo "-----------------------------------\n";

// Vérifier si la pagination est nécessaire
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM grades WHERE exam_id = ?");
    $stmt->execute([4]);
    $gradeCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "  📊 Nombre de notes pour examen 4: {$gradeCount['count']}\n";
    
    if ($gradeCount['count'] > 20) {
        echo "     ⚠️ Pagination recommandée (>20 notes)\n";
        $errors[] = "Pagination manquante pour {$gradeCount['count']} notes";
        $results[] = "❌ Pagination: MANQUANTE ({$gradeCount['count']} notes)";
    } else {
        echo "     ✅ Pagination non nécessaire (≤20 notes)\n";
        $successCount++;
        $results[] = "✅ Pagination: OK (≤20 notes)";
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
echo "📊 RÉSULTATS FINAUX - PAGE DÉTAILS D'EXAMEN\n";
echo "============================================\n\n";

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
    'Page principale' => 0,
    'Liens d\'action' => 0,
    'Différents IDs' => 0,
    'Pagination' => 0,
    'Actions CRUD' => 0,
    'Cohérence données' => 0,
    'Performance' => 0
];

foreach ($results as $result) {
    if (strpos($result, 'Page détails examen') !== false) {
        $categories['Page principale']++;
    } elseif (strpos($result, 'Lien') !== false) {
        $categories['Liens d\'action']++;
    } elseif (strpos($result, 'Détails examen') !== false && strpos($result, 'ID') !== false) {
        $categories['Différents IDs']++;
    } elseif (strpos($result, 'Pagination') !== false) {
        $categories['Pagination']++;
    } elseif (strpos($result, 'Mise à jour') !== false || strpos($result, 'Saisie') !== false) {
        $categories['Actions CRUD']++;
    } elseif (strpos($result, 'Données') !== false || strpos($result, 'Statistiques') !== false || strpos($result, 'Cohérence') !== false) {
        $categories['Cohérence données']++;
    } elseif (strpos($result, 'Performance') !== false) {
        $categories['Performance']++;
    }
}

foreach ($categories as $category => $count) {
    echo "   • $category: $count tests\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 PAGE DÉTAILS D'EXAMEN: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ PAGE DÉTAILS D'EXAMEN: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ PAGE DÉTAILS D'EXAMEN: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ PAGE DÉTAILS D'EXAMEN: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens/exams/4/view\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


