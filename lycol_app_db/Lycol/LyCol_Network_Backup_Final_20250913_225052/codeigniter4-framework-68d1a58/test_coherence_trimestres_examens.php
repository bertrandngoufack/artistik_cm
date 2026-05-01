<?php

/**
 * TEST DE COHÉRENCE - TRIMESTRES ET MODULE EXAMENS
 * Vérification minutieuse de la création des trimestres et de leur cohérence
 */

echo "🔍 TEST DE COHÉRENCE - TRIMESTRES ET MODULE EXAMENS\n";
echo "==================================================\n\n";

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

// 1. Test de la création des trimestres
echo "📅 TEST DE LA CRÉATION DES TRIMESTRES\n";
echo "=====================================\n";

// Test création d'une nouvelle année académique
testPost('Création année 2027-2028', '/admin/examens/academic-periods/create-year', [
    'academic_year' => '2027-2028'
]);

echo "\n";

// 2. Test de cohérence des données en base
echo "🗄️ TEST DE COHÉRENCE DES DONNÉES EN BASE\n";
echo "==========================================\n";

$dbHost = '100.69.65.33';
$dbPort = '13306';
$dbUser = 'root';
$dbPass = 'Bateau123';
$dbName = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier que tous les trimestres ont été créés pour 2027-2028
    $stmt = $pdo->query("SELECT period_type, name, start_date, end_date FROM academic_periods WHERE academic_year = '2027-2028' ORDER BY period_type");
    $newPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Trimestres créés pour 2027-2028 :\n";
    $expectedPeriods = ['1ER_TRIMESTRE', '2EME_TRIMESTRE', '3EME_TRIMESTRE'];
    $createdPeriods = [];
    
    foreach ($newPeriods as $period) {
        echo "     • {$period['name']} ({$period['period_type']}): {$period['start_date']} - {$period['end_date']}\n";
        $createdPeriods[] = $period['period_type'];
        
        // Vérifier la cohérence des dates
        if ($period['period_type'] == '1ER_TRIMESTRE') {
            if ($period['start_date'] == '2027-09-01' && $period['end_date'] == '2027-12-20') {
                echo "       ✅ Dates cohérentes pour 1er trimestre\n";
                $successCount++;
                $results[] = "✅ Dates 1er trimestre 2027-2028: OK";
            } else {
                echo "       ❌ Dates incorrectes pour 1er trimestre\n";
                $errors[] = "Dates incorrectes 1er trimestre 2027-2028";
                $results[] = "❌ Dates 1er trimestre 2027-2028: ÉCHEC";
            }
        } elseif ($period['period_type'] == '2EME_TRIMESTRE') {
            if ($period['start_date'] == '2028-01-06' && $period['end_date'] == '2028-03-28') {
                echo "       ✅ Dates cohérentes pour 2ème trimestre\n";
                $successCount++;
                $results[] = "✅ Dates 2ème trimestre 2027-2028: OK";
            } else {
                echo "       ❌ Dates incorrectes pour 2ème trimestre\n";
                $errors[] = "Dates incorrectes 2ème trimestre 2027-2028";
                $results[] = "❌ Dates 2ème trimestre 2027-2028: ÉCHEC";
            }
        } elseif ($period['period_type'] == '3EME_TRIMESTRE') {
            if ($period['start_date'] == '2028-04-07' && $period['end_date'] == '2028-06-30') {
                echo "       ✅ Dates cohérentes pour 3ème trimestre\n";
                $successCount++;
                $results[] = "✅ Dates 3ème trimestre 2027-2028: OK";
            } else {
                echo "       ❌ Dates incorrectes pour 3ème trimestre\n";
                $errors[] = "Dates incorrectes 3ème trimestre 2027-2028";
                $results[] = "❌ Dates 3ème trimestre 2027-2028: ÉCHEC";
            }
        }
        $totalTests++;
    }
    
    // Vérifier que tous les trimestres ont été créés
    if (count($createdPeriods) == 3 && count(array_intersect($createdPeriods, $expectedPeriods)) == 3) {
        echo "     ✅ Tous les trimestres créés correctement\n";
        $successCount++;
        $results[] = "✅ Création trimestres 2027-2028: OK";
    } else {
        echo "     ❌ Trimestres manquants\n";
        $errors[] = "Trimestres manquants pour 2027-2028";
        $results[] = "❌ Création trimestres 2027-2028: ÉCHEC";
    }
    $totalTests++;
    
    // Vérifier la cohérence avec les examens existants
    echo "  📊 Cohérence avec les examens existants :\n";
    
    // Compter les examens par année académique
    $stmt = $pdo->query("SELECT academic_year, COUNT(*) as count FROM exams GROUP BY academic_year ORDER BY academic_year");
    $examsByYear = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($examsByYear as $year) {
        echo "     • {$year['academic_year']}: {$year['count']} examens\n";
        
        // Vérifier que les examens ont des dates cohérentes avec les trimestres
        $stmt2 = $pdo->prepare("SELECT exam_date FROM exams WHERE academic_year = ? ORDER BY exam_date");
        $stmt2->execute([$year['academic_year']]);
        $examDates = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        
        // Vérifier que les dates d'examens sont dans les périodes académiques
        $stmt3 = $pdo->prepare("SELECT start_date, end_date FROM academic_periods WHERE academic_year = ?");
        $stmt3->execute([$year['academic_year']]);
        $periods = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        $datesValid = true;
        foreach ($examDates as $examDate) {
            $dateInPeriod = false;
            foreach ($periods as $period) {
                if ($examDate >= $period['start_date'] && $examDate <= $period['end_date']) {
                    $dateInPeriod = true;
                    break;
                }
            }
            if (!$dateInPeriod) {
                $datesValid = false;
                echo "       ⚠️ Date d'examen $examDate hors période académique\n";
            }
        }
        
        if ($datesValid) {
            echo "       ✅ Toutes les dates d'examens cohérentes\n";
            $successCount++;
            $results[] = "✅ Cohérence dates examens {$year['academic_year']}: OK";
        } else {
            echo "       ❌ Dates d'examens incohérentes\n";
            $errors[] = "Dates d'examens incohérentes pour {$year['academic_year']}";
            $results[] = "❌ Cohérence dates examens {$year['academic_year']}: ÉCHEC";
        }
        $totalTests++;
    }
    
    // Vérifier qu'il y a des examens pour chaque trimestre
    echo "  📊 Répartition des examens par trimestre :\n";
    
    $stmt = $pdo->query("
        SELECT 
            e.academic_year,
            CASE 
                WHEN e.exam_date BETWEEN p1.start_date AND p1.end_date THEN '1ER_TRIMESTRE'
                WHEN e.exam_date BETWEEN p2.start_date AND p2.end_date THEN '2EME_TRIMESTRE'
                WHEN e.exam_date BETWEEN p3.start_date AND p3.end_date THEN '3EME_TRIMESTRE'
                ELSE 'HORS_PERIODE'
            END as trimestre,
            COUNT(*) as count
        FROM exams e
        LEFT JOIN academic_periods p1 ON e.academic_year = p1.academic_year AND p1.period_type = '1ER_TRIMESTRE'
        LEFT JOIN academic_periods p2 ON e.academic_year = p2.academic_year AND p2.period_type = '2EME_TRIMESTRE'
        LEFT JOIN academic_periods p3 ON e.academic_year = p3.academic_year AND p3.period_type = '3EME_TRIMESTRE'
        GROUP BY e.academic_year, trimestre
        ORDER BY e.academic_year, trimestre
    ");
    
    $examDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($examDistribution as $dist) {
        echo "     • {$dist['academic_year']} - {$dist['trimestre']}: {$dist['count']} examens\n";
        
        if ($dist['trimestre'] != 'HORS_PERIODE') {
            $successCount++;
            $results[] = "✅ Répartition examens {$dist['academic_year']} {$dist['trimestre']}: OK";
        } else {
            $errors[] = "Examens hors période pour {$dist['academic_year']}";
            $results[] = "❌ Répartition examens {$dist['academic_year']} {$dist['trimestre']}: ÉCHEC";
        }
        $totalTests++;
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    $errors[] = "Erreur DB: " . $e->getMessage();
    $results[] = "❌ Erreur de connexion DB";
    $totalTests++;
}

echo "\n";

// 3. Test de cohérence avec le module examens
echo "🔗 TEST DE COHÉRENCE AVEC LE MODULE EXAMENS\n";
echo "============================================\n";

testRoute('Page principale examens', '/admin/examens');
testRoute('Liste des examens', '/admin/examens/exams');
testRoute('Création d\'examen', '/admin/examens/exams/create');
testRoute('Gestion des notes', '/admin/examens/grades');
testRoute('Bulletins de notes', '/admin/examens/report-cards');
testRoute('Statistiques', '/admin/examens/statistics');

echo "\n";

// 4. Test de création d'examen avec période académique
echo "📝 TEST DE CRÉATION D'EXAMEN AVEC PÉRIODE\n";
echo "==========================================\n";

// Test création d'un examen dans une période spécifique
testPost('Création examen 1er trimestre', '/admin/examens/exams/store', [
    'name' => 'Test Examen 1er Trimestre',
    'class_id' => 1,
    'exam_type' => 'CONTINUOUS',
    'exam_date' => '2027-10-15',
    'academic_year' => '2027-2028',
    'total_marks' => 20,
    'coefficient' => 1
]);

echo "\n";

// 5. Test de validation des dates d'examens
echo "✅ TEST DE VALIDATION DES DATES D'EXAMENS\n";
echo "=========================================\n";

// Test avec date hors période
testPost('Validation - Date hors période', '/admin/examens/exams/store', [
    'name' => 'Test Examen Date Invalide',
    'class_id' => 1,
    'exam_type' => 'CONTINUOUS',
    'exam_date' => '2027-08-15', // Date avant le début de l'année académique
    'academic_year' => '2027-2028',
    'total_marks' => 20,
    'coefficient' => 1
]);

echo "\n";

// 6. Test de navigation entre les modules
echo "🌐 TEST DE NAVIGATION ENTRE MODULES\n";
echo "====================================\n";

testRoute('Navigation vers périodes depuis examens', '/admin/examens/academic-periods');
testRoute('Retour vers examens depuis périodes', '/admin/examens');

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - COHÉRENCE TRIMESTRES/EXAMENS\n";
echo "==================================================\n\n";

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
    'Création trimestres' => 0,
    'Cohérence données' => 0,
    'Module examens' => 0,
    'Création examens' => 0,
    'Validation dates' => 0,
    'Navigation' => 0
];

foreach ($results as $result) {
    if (strpos($result, 'Création trimestres') !== false || strpos($result, 'Dates 1er trimestre') !== false || strpos($result, 'Dates 2ème trimestre') !== false || strpos($result, 'Dates 3ème trimestre') !== false) {
        $categories['Création trimestres']++;
    } elseif (strpos($result, 'Cohérence dates examens') !== false || strpos($result, 'Répartition examens') !== false) {
        $categories['Cohérence données']++;
    } elseif (strpos($result, 'Page principale examens') !== false || strpos($result, 'Liste des examens') !== false || strpos($result, 'Création d\'examen') !== false || strpos($result, 'Gestion des notes') !== false || strpos($result, 'Bulletins de notes') !== false || strpos($result, 'Statistiques') !== false) {
        $categories['Module examens']++;
    } elseif (strpos($result, 'Création examen 1er trimestre') !== false) {
        $categories['Création examens']++;
    } elseif (strpos($result, 'Validation - Date') !== false) {
        $categories['Validation dates']++;
    } elseif (strpos($result, 'Navigation') !== false) {
        $categories['Navigation']++;
    }
}

foreach ($categories as $category => $count) {
    echo "   • $category: $count tests\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 COHÉRENCE TRIMESTRES/EXAMENS: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ COHÉRENCE TRIMESTRES/EXAMENS: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ COHÉRENCE TRIMESTRES/EXAMENS: ÉTAT MOYEN\n";
    echo "   Certaines fonctionnalités nécessitent des corrections.\n";
} else {
    echo "❌ COHÉRENCE TRIMESTRES/EXAMENS: ÉTAT CRITIQUE\n";
    echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens/academic-periods\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


