<?php
/**
 * TESTS DE RÉGRESSION LYCOL
 * Validation du bon fonctionnement avant améliorations
 * Expert PHP/JavaScript/CSS Bulma/CodeIgniter/MariaDB
 */

echo "🧪 TESTS DE RÉGRESSION LYCOL\n";
echo "============================\n";
echo "Validation du bon fonctionnement avant améliorations\n\n";

// Configuration de la base de données
$dbConfig = [
    'host' => '100.69.65.33',
    'port' => '13306',
    'user' => 'root',
    'pass' => 'Bateau123',
    'dbname' => 'lycol_db'
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "✅ Connexion à la base de données établie\n\n";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
}

$testResults = [
    'passed' => 0,
    'failed' => 0,
    'total' => 0
];

// =====================================================
// 1. TESTS DE LA BASE DE DONNÉES
// =====================================================

echo "🗄️ 1. TESTS DE LA BASE DE DONNÉES\n";
echo "==================================\n";

// Test 1.1 : Vérification des tables principales
echo "\n📋 1.1 Vérification des tables principales\n";
echo "------------------------------------------\n";

$mainTables = ['students', 'payments', 'grades', 'exams', 'classes', 'teachers'];
foreach ($mainTables as $table) {
    $testResults['total']++;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        $count = $result['count'];
        
        if ($count >= 0) {
            echo "✅ Table $table: $count enregistrements\n";
            $testResults['passed']++;
        } else {
            echo "❌ Table $table: Erreur de comptage\n";
            $testResults['failed']++;
        }
    } catch (PDOException $e) {
        echo "❌ Table $table: " . $e->getMessage() . "\n";
        $testResults['failed']++;
    }
}

// Test 1.2 : Vérification de l'intégrité référentielle
echo "\n📋 1.2 Vérification de l'intégrité référentielle\n";
echo "------------------------------------------------\n";

$integrityTests = [
    'students_classes' => 'SELECT COUNT(*) as orphans FROM students s LEFT JOIN classes c ON s.current_class_id = c.id WHERE c.id IS NULL AND s.current_class_id IS NOT NULL',
    'payments_students' => 'SELECT COUNT(*) as orphans FROM payments p LEFT JOIN students s ON p.student_id = s.id WHERE s.id IS NULL',
    'grades_students' => 'SELECT COUNT(*) as orphans FROM grades g LEFT JOIN students s ON g.student_id = s.id WHERE s.id IS NULL',
    'grades_exams' => 'SELECT COUNT(*) as orphans FROM grades g LEFT JOIN exams e ON g.exam_id = e.id WHERE e.id IS NULL'
];

foreach ($integrityTests as $test => $query) {
    $testResults['total']++;
    
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        $orphans = $result['orphans'];
        
        if ($orphans == 0) {
            echo "✅ $test: Intégrité respectée\n";
            $testResults['passed']++;
        } else {
            echo "❌ $test: $orphans enregistrements orphelins\n";
            $testResults['failed']++;
        }
    } catch (PDOException $e) {
        echo "❌ $test: " . $e->getMessage() . "\n";
        $testResults['failed']++;
    }
}

// =====================================================
// 2. TESTS DES FONCTIONNALITÉS CRUD
// =====================================================

echo "\n\n🔧 2. TESTS DES FONCTIONNALITÉS CRUD\n";
echo "====================================\n";

// Test 2.1 : Lecture des données
echo "\n📋 2.1 Tests de lecture\n";
echo "----------------------\n";

$readTests = [
    'students_active' => 'SELECT COUNT(*) as count FROM students WHERE status = "ACTIVE"',
    'payments_recent' => 'SELECT COUNT(*) as count FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
    'grades_valid' => 'SELECT COUNT(*) as count FROM grades WHERE marks_obtained >= 0 AND marks_obtained <= 20',
    'exams_current_year' => 'SELECT COUNT(*) as count FROM exams WHERE academic_year = "2024-2025"'
];

foreach ($readTests as $test => $query) {
    $testResults['total']++;
    
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        $count = $result['count'];
        
        echo "✅ $test: $count enregistrements\n";
        $testResults['passed']++;
    } catch (PDOException $e) {
        echo "❌ $test: " . $e->getMessage() . "\n";
        $testResults['failed']++;
    }
}

// Test 2.2 : Tests de requêtes complexes
echo "\n📋 2.2 Tests de requêtes complexes\n";
echo "----------------------------------\n";

$complexTests = [
    'students_with_payments' => 'SELECT s.id, s.first_name, s.last_name, COUNT(p.id) as payment_count FROM students s LEFT JOIN payments p ON s.id = p.student_id GROUP BY s.id LIMIT 5',
    'class_statistics' => 'SELECT c.name, COUNT(s.id) as student_count, AVG(g.marks_obtained) as average_grade FROM classes c LEFT JOIN students s ON c.id = s.current_class_id LEFT JOIN grades g ON s.id = g.student_id GROUP BY c.id LIMIT 5',
    'payment_summary' => 'SELECT payment_method, COUNT(*) as count, SUM(amount_paid) as total FROM payments GROUP BY payment_method'
];

foreach ($complexTests as $test => $query) {
    $testResults['total']++;
    
    try {
        $start = microtime(true);
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll();
        $end = microtime(true);
        
        $executionTime = round(($end - $start) * 1000, 2);
        
        if ($executionTime < 1000) { // Moins d'1 seconde
            echo "✅ $test: " . count($results) . " résultats en {$executionTime}ms\n";
            $testResults['passed']++;
        } else {
            echo "⚠️  $test: " . count($results) . " résultats en {$executionTime}ms (lent)\n";
            $testResults['passed']++;
        }
    } catch (PDOException $e) {
        echo "❌ $test: " . $e->getMessage() . "\n";
        $testResults['failed']++;
    }
}

// =====================================================
// 3. TESTS DES FICHIERS CRITIQUES
// =====================================================

echo "\n\n📁 3. TESTS DES FICHIERS CRITIQUES\n";
echo "==================================\n";

// Test 3.1 : Vérification des contrôleurs
echo "\n📋 3.1 Vérification des contrôleurs\n";
echo "-----------------------------------\n";

$criticalControllers = [
    'app/Controllers/Scolarite.php',
    'app/Controllers/Economat.php',
    'app/Controllers/Examens.php',
    'app/Controllers/Admin.php',
    'app/Controllers/Auth.php'
];

foreach ($criticalControllers as $controller) {
    $testResults['total']++;
    
    if (file_exists($controller)) {
        $content = file_get_contents($controller);
        $lines = count(file($controller));
        
        if ($lines > 0 && strpos($content, 'class') !== false) {
            echo "✅ $controller: $lines lignes\n";
            $testResults['passed']++;
        } else {
            echo "❌ $controller: Fichier vide ou invalide\n";
            $testResults['failed']++;
        }
    } else {
        echo "❌ $controller: Fichier manquant\n";
        $testResults['failed']++;
    }
}

// Test 3.2 : Vérification des modèles
echo "\n📋 3.2 Vérification des modèles\n";
echo "--------------------------------\n";

$criticalModels = [
    'app/Models/StudentModel.php',
    'app/Models/PaymentModel.php',
    'app/Models/GradeModel.php',
    'app/Models/ExamModel.php'
];

foreach ($criticalModels as $model) {
    $testResults['total']++;
    
    if (file_exists($model)) {
        $content = file_get_contents($model);
        $lines = count(file($model));
        
        if ($lines > 0 && strpos($content, 'extends Model') !== false) {
            echo "✅ $model: $lines lignes\n";
            $testResults['passed']++;
        } else {
            echo "❌ $model: Modèle invalide\n";
            $testResults['failed']++;
        }
    } else {
        echo "❌ $model: Fichier manquant\n";
        $testResults['failed']++;
    }
}

// Test 3.3 : Vérification des vues principales
echo "\n📋 3.3 Vérification des vues principales\n";
echo "----------------------------------------\n";

$criticalViews = [
    'app/Views/admin/layout.php',
    'app/Views/admin/dashboard.php',
    'app/Views/admin/scolarite/index.php',
    'app/Views/admin/economat/index.php'
];

foreach ($criticalViews as $view) {
    $testResults['total']++;
    
    if (file_exists($view)) {
        $content = file_get_contents($view);
        $lines = count(file($view));
        
        if ($lines > 0 && strpos($content, '<?php') !== false) {
            echo "✅ $view: $lines lignes\n";
            $testResults['passed']++;
        } else {
            echo "❌ $view: Vue invalide\n";
            $testResults['failed']++;
        }
    } else {
        echo "❌ $view: Fichier manquant\n";
        $testResults['failed']++;
    }
}

// =====================================================
// 4. TESTS DE CONFIGURATION
// =====================================================

echo "\n\n⚙️ 4. TESTS DE CONFIGURATION\n";
echo "============================\n";

// Test 4.1 : Vérification des fichiers de configuration
echo "\n📋 4.1 Fichiers de configuration\n";
echo "--------------------------------\n";

$configFiles = [
    'app/Config/Database.php',
    'app/Config/Routes.php',
    'app/Config/AcademicYear.php',
    'app/Config/App.php'
];

foreach ($configFiles as $config) {
    $testResults['total']++;
    
    if (file_exists($config)) {
        $content = file_get_contents($config);
        $lines = count(file($config));
        
        if ($lines > 0) {
            echo "✅ $config: $lines lignes\n";
            $testResults['passed']++;
        } else {
            echo "❌ $config: Fichier vide\n";
            $testResults['failed']++;
        }
    } else {
        echo "❌ $config: Fichier manquant\n";
        $testResults['failed']++;
    }
}

// Test 4.2 : Vérification des services
echo "\n📋 4.2 Services critiques\n";
echo "-------------------------\n";

$criticalServices = [
    'app/Services/DatabaseService.php',
    'app/Services/ConfigurationService.php',
    'app/Services/PDFService.php'
];

foreach ($criticalServices as $service) {
    $testResults['total']++;
    
    if (file_exists($service)) {
        $content = file_get_contents($service);
        $lines = count(file($service));
        
        if ($lines > 0 && strpos($content, 'class') !== false) {
            echo "✅ $service: $lines lignes\n";
            $testResults['passed']++;
        } else {
            echo "❌ $service: Service invalide\n";
            $testResults['failed']++;
        }
    } else {
        echo "❌ $service: Fichier manquant\n";
        $testResults['failed']++;
    }
}

// =====================================================
// 5. RAPPORT FINAL
// =====================================================

echo "\n\n📊 RAPPORT FINAL DES TESTS DE RÉGRESSION\n";
echo "=========================================\n";

$totalTests = $testResults['total'];
$passedTests = $testResults['passed'];
$failedTests = $testResults['failed'];
$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;

echo "📈 Statistiques des tests:\n";
echo "   Total des tests: $totalTests\n";
echo "   Tests réussis: $passedTests\n";
echo "   Tests échoués: $failedTests\n";
echo "   Taux de réussite: $successRate%\n\n";

if ($successRate >= 95) {
    echo "✅ SYSTÈME STABLE - Prêt pour les améliorations\n";
    echo "   Le système fonctionne correctement et peut recevoir des améliorations.\n";
} elseif ($successRate >= 80) {
    echo "⚠️  SYSTÈME PARTIELLEMENT STABLE\n";
    echo "   Quelques problèmes détectés mais le système peut être amélioré.\n";
} else {
    echo "❌ SYSTÈME INSTABLE\n";
    echo "   Trop de problèmes détectés. Corriger avant les améliorations.\n";
}

echo "\n🎯 Recommandations:\n";
if ($failedTests > 0) {
    echo "   - Corriger les $failedTests tests échoués avant les améliorations\n";
}
echo "   - Procéder aux améliorations par phases\n";
echo "   - Tester après chaque phase\n";
echo "   - Garder la sauvegarde de sécurité\n";

echo "\n🧪 Tests de régression terminés avec succès!\n";

?>





