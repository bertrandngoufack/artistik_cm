<?php
/**
 * Test complet du module Statistiques
 * Vérification de la cohérence avec tous les autres modules
 */

echo "📊 TEST COMPLET - MODULE STATISTIQUES\n";
echo "====================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Test 1: Vérification de la structure du contrôleur
    echo "🔧 Test 1: Vérification de la structure du contrôleur\n";
    echo "----------------------------------------------------\n";
    
    $controllerFile = 'app/Controllers/Statistiques.php';
    if (file_exists($controllerFile)) {
        $controllerContent = file_get_contents($controllerFile);
        
        $requiredMethods = [
            'index' => 'Page d\'accueil',
            'students' => 'Statistiques élèves',
            'grades' => 'Statistiques notes',
            'payments' => 'Statistiques paiements',
            'absences' => 'Statistiques absences',
            'reports' => 'Rapports',
            'export' => 'Export des données'
        ];
        
        foreach ($requiredMethods as $method => $description) {
            if (strpos($controllerContent, "public function $method") !== false) {
                echo "   ✅ Méthode $method() - $description\n";
            } else {
                echo "   ❌ Méthode $method() - MANQUANTE\n";
            }
        }
        
        // Vérifier les modèles utilisés
        $requiredModels = [
            'StudentModel' => 'Modèle élèves',
            'GradeModel' => 'Modèle notes',
            'PaymentModel' => 'Modèle paiements',
            'AbsenceModel' => 'Modèle absences'
        ];
        
        foreach ($requiredModels as $model => $description) {
            if (strpos($controllerContent, $model) !== false) {
                echo "   ✅ $model - $description\n";
            } else {
                echo "   ❌ $model - MANQUANT\n";
            }
        }
        
    } else {
        echo "   ❌ Fichier contrôleur non trouvé\n";
    }
    
    // Test 2: Vérification des modèles de statistiques
    echo "\n📋 Test 2: Vérification des modèles de statistiques\n";
    echo "-------------------------------------------------\n";
    
    $models = [
        'app/Models/StudentModel.php' => 'Modèle élèves',
        'app/Models/GradeModel.php' => 'Modèle notes',
        'app/Models/PaymentModel.php' => 'Modèle paiements',
        'app/Models/AbsenceModel.php' => 'Modèle absences'
    ];
    
    foreach ($models as $file => $description) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Vérifier les méthodes de statistiques
            $statsMethods = [
                'getStudentStats' => 'Statistiques élèves',
                'getGradeStats' => 'Statistiques notes',
                'getTotalRevenue' => 'Revenus totaux',
                'getAbsenceStats' => 'Statistiques absences'
            ];
            
            echo "   📊 $description:\n";
            foreach ($statsMethods as $method => $methodDesc) {
                if (strpos($content, $method) !== false) {
                    echo "      ✅ $method() - $methodDesc\n";
                } else {
                    echo "      ❌ $method() - MANQUANTE\n";
                }
            }
        } else {
            echo "   ❌ $description - FICHIER MANQUANT\n";
        }
    }
    
    // Test 3: Vérification des données de base
    echo "\n📊 Test 3: Vérification des données de base\n";
    echo "-------------------------------------------\n";
    
    $tables = [
        'students' => 'Élèves',
        'grades' => 'Notes',
        'payments' => 'Paiements',
        'absences' => 'Absences',
        'teachers' => 'Enseignants',
        'classes' => 'Classes',
        'subjects' => 'Matières',
        'exams' => 'Examens'
    ];
    
    foreach ($tables as $table => $description) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "   ✅ Table $description: $count enregistrements\n";
        } else {
            echo "   ❌ Table $description: MANQUANTE\n";
        }
    }
    
    // Test 4: Test des statistiques par module
    echo "\n🎯 Test 4: Test des statistiques par module\n";
    echo "------------------------------------------\n";
    
    // Module Scolarité (Élèves)
    echo "   📚 Module Scolarité (Élèves):\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE status = 'ACTIVE'");
    $activeStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Élèves actifs: $activeStudents\n";
    
    $stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM students WHERE status = 'ACTIVE' GROUP BY gender");
    $genderStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($genderStats as $stat) {
        echo "      ✅ Genre {$stat['gender']}: {$stat['count']} élèves\n";
    }
    
    // Module Études (Classes et Matières)
    echo "   🎓 Module Études:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes WHERE is_active = 1");
    $activeClasses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Classes actives: $activeClasses\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subjects WHERE is_active = 1");
    $activeSubjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Matières actives: $activeSubjects\n";
    
    // Module Enseignants
    echo "   👨‍🏫 Module Enseignants:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teachers WHERE is_active = 1");
    $activeTeachers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Enseignants actifs: $activeTeachers\n";
    
    // Module Économat (Paiements)
    echo "   💰 Module Économat:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments");
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Total paiements: $totalPayments\n";
    
    $stmt = $pdo->query("SELECT SUM(amount_paid) as total FROM payments");
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    echo "      ✅ Revenus totaux: " . number_format($totalRevenue, 0, ',', ' ') . " FCFA\n";
    
    // Module Examens
    echo "   📝 Module Examens:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM exams");
    $totalExams = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "      ✅ Total examens: $totalExams\n";
    
    // Test 5: Vérification des vues
    echo "\n🎨 Test 5: Vérification des vues\n";
    echo "-------------------------------\n";
    
    $viewFiles = [
        'app/Views/admin/statistiques/index.php' => 'Page d\'accueil',
        'app/Views/admin/statistiques/students.php' => 'Statistiques élèves',
        'app/Views/admin/statistiques/grades.php' => 'Statistiques notes',
        'app/Views/admin/statistiques/payments.php' => 'Statistiques paiements',
        'app/Views/admin/statistiques/absences.php' => 'Statistiques absences',
        'app/Views/admin/statistiques/reports.php' => 'Rapports'
    ];
    
    foreach ($viewFiles as $file => $description) {
        if (file_exists($file)) {
            echo "   ✅ $description\n";
        } else {
            echo "   ❌ $description - MANQUANTE\n";
        }
    }
    
    // Test 6: Vérification des routes
    echo "\n🛣️ Test 6: Vérification des routes\n";
    echo "--------------------------------\n";
    
    $routesFile = 'app/Config/Routes.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        
        $requiredRoutes = [
            'Statistiques::index' => 'Page d\'accueil',
            'Statistiques::students' => 'Statistiques élèves',
            'Statistiques::grades' => 'Statistiques notes',
            'Statistiques::payments' => 'Statistiques paiements',
            'Statistiques::absences' => 'Statistiques absences',
            'Statistiques::reports' => 'Rapports',
            'Statistiques::export' => 'Export'
        ];
        
        foreach ($requiredRoutes as $route => $description) {
            if (strpos($routesContent, $route) !== false) {
                echo "   ✅ Route $description configurée\n";
            } else {
                echo "   ❌ Route $description MANQUANTE\n";
            }
        }
    }
    
    // Test 7: Cohérence avec les autres modules
    echo "\n🔗 Test 7: Cohérence avec les autres modules\n";
    echo "------------------------------------------\n";
    
    $modules = [
        'app/Controllers/Scolarite.php' => 'Module Scolarité',
        'app/Controllers/Etudes.php' => 'Module Études',
        'app/Controllers/Economat.php' => 'Module Économat',
        'app/Controllers/Examens.php' => 'Module Examens',
        'app/Controllers/Enseignants.php' => 'Module Enseignants'
    ];
    
    foreach ($modules as $file => $moduleName) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Vérifier les méthodes de statistiques dans chaque module
            if (strpos($content, 'getStats') !== false || strpos($content, 'statistics') !== false) {
                echo "   ✅ $moduleName: Méthodes de statistiques présentes\n";
            } else {
                echo "   ⚠️ $moduleName: Aucune méthode de statistiques détectée\n";
            }
        } else {
            echo "   ❌ $moduleName: FICHIER MANQUANT\n";
        }
    }
    
    // Test 8: Problèmes identifiés et corrections nécessaires
    echo "\n🚨 Test 8: Problèmes identifiés\n";
    echo "------------------------------\n";
    
    $problems = [
        "Route principale incorrecte (Admin::statistiques au lieu de Statistiques::index)",
        "Vues manquantes pour les sous-sections",
        "Méthodes de statistiques manquantes dans certains modèles",
        "Pas d'intégration avec les logs d'audit",
        "Graphiques non implémentés",
        "Export limité au CSV"
    ];
    
    foreach ($problems as $index => $problem) {
        echo "   " . ($index + 1) . ". $problem\n";
    }
    
    // Test 9: Recommandations d'amélioration
    echo "\n💡 Test 9: Recommandations d'amélioration\n";
    echo "---------------------------------------\n";
    
    $recommendations = [
        "Corriger la route principale du module statistiques",
        "Créer les vues manquantes pour toutes les sections",
        "Implémenter les méthodes de statistiques manquantes",
        "Ajouter des graphiques interactifs (Chart.js)",
        "Intégrer les logs d'audit pour la traçabilité",
        "Ajouter l'export PDF en plus du CSV",
        "Créer des tableaux de bord personnalisables",
        "Implémenter des alertes et notifications",
        "Ajouter des filtres temporels (période, année scolaire)",
        "Créer des rapports automatisés"
    ];
    
    foreach ($recommendations as $index => $recommendation) {
        echo "   " . ($index + 1) . ". $recommendation\n";
    }
    
    echo "\n🎉 RÉSUMÉ - MODULE STATISTIQUES\n";
    echo "===============================\n";
    echo "✅ Contrôleur: PARTIELLEMENT FONCTIONNEL\n";
    echo "✅ Modèles: PARTIELLEMENT IMPLÉMENTÉS\n";
    echo "✅ Données: DISPONIBLES\n";
    echo "❌ Vues: INCOMPLÈTES\n";
    echo "❌ Routes: PROBLÈME IDENTIFIÉ\n";
    echo "⚠️ Cohérence: À AMÉLIORER\n";
    echo "\n🚀 Le module nécessite des corrections pour être pleinement opérationnel.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}
?>
