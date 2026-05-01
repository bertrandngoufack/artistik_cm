<?php
/**
 * Script simple pour tester les méthodes de statistiques
 */

// Simuler l'environnement CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

try {
    echo "🎯 TEST SIMPLE DES STATISTIQUES\n";
    echo "==============================\n\n";
    
    // Initialiser les modèles
    $gradeModel = new \App\Models\GradeModel();
    $examModel = new \App\Models\ExamModel();
    
    echo "✅ Modèles initialisés\n";
    
    // Test 1: getAverageScores
    echo "\n📈 TEST 1: getAverageScores\n";
    echo "----------------------------\n";
    try {
        $averageScores = $gradeModel->getAverageScores();
        echo "✅ getAverageScores: " . count($averageScores) . " résultats\n";
        
        if (!empty($averageScores)) {
            foreach (array_slice($averageScores, 0, 3) as $score) {
                echo "   • {$score['name']}: {$score['average_score']}/20\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur getAverageScores: " . $e->getMessage() . "\n";
    }
    
    // Test 2: getPassRates
    echo "\n📊 TEST 2: getPassRates\n";
    echo "-----------------------\n";
    try {
        $passRates = $gradeModel->getPassRates();
        echo "✅ getPassRates: " . count($passRates) . " résultats\n";
        
        if (!empty($passRates)) {
            foreach (array_slice($passRates, 0, 3) as $rate) {
                $passRate = $rate['total'] > 0 ? round(($rate['passed'] / $rate['total']) * 100, 1) : 0;
                echo "   • {$rate['name']}: {$rate['passed']}/{$rate['total']} ({$passRate}%)\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur getPassRates: " . $e->getMessage() . "\n";
    }
    
    // Test 3: getTopStudents
    echo "\n👥 TEST 3: getTopStudents\n";
    echo "-------------------------\n";
    try {
        $topStudents = $gradeModel->getTopStudents(5);
        echo "✅ getTopStudents: " . count($topStudents) . " résultats\n";
        
        if (!empty($topStudents)) {
            foreach (array_slice($topStudents, 0, 3) as $student) {
                echo "   • {$student['first_name']} {$student['last_name']}: {$student['average_score']}/20\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur getTopStudents: " . $e->getMessage() . "\n";
    }
    
    // Test 4: getPerformanceByClass
    echo "\n🏫 TEST 4: getPerformanceByClass\n";
    echo "--------------------------------\n";
    try {
        $performanceByClass = $gradeModel->getPerformanceByClass();
        echo "✅ getPerformanceByClass: " . count($performanceByClass) . " résultats\n";
        
        if (!empty($performanceByClass)) {
            foreach (array_slice($performanceByClass, 0, 3) as $class) {
                $passRate = $class['total'] > 0 ? round(($class['passed'] / $class['total']) * 100, 1) : 0;
                echo "   • {$class['class_name']}: {$class['average_score']}/20 ({$passRate}%)\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur getPerformanceByClass: " . $e->getMessage() . "\n";
    }
    
    // Test 5: getPerformanceBySubject
    echo "\n📚 TEST 5: getPerformanceBySubject\n";
    echo "-----------------------------------\n";
    try {
        $performanceBySubject = $gradeModel->getPerformanceBySubject();
        echo "✅ getPerformanceBySubject: " . count($performanceBySubject) . " résultats\n";
        
        if (!empty($performanceBySubject)) {
            foreach (array_slice($performanceBySubject, 0, 3) as $subject) {
                $passRate = $subject['total'] > 0 ? round(($subject['passed'] / $subject['total']) * 100, 1) : 0;
                echo "   • {$subject['subject_name']}: {$subject['average_score']}/20 ({$passRate}%)\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur getPerformanceBySubject: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 TEST TERMINÉ !\n";
    echo "================\n";
    
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>









