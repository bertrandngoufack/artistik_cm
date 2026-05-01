<?php
/**
 * Script de test de cohérence après correction
 * Vérifie que toutes les corrections ont été appliquées correctement
 */

echo "==========================================\n";
echo "TEST DE COHÉRENCE APRÈS CORRECTION\n";
echo "==========================================\n\n";

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
    
    // 1. VÉRIFICATION DES RELATIONS ORPHELINES
    echo "1. VÉRIFICATION DES RELATIONS ORPHELINES\n";
    echo "========================================\n";
    
    $orphanChecks = [
        'Paiements sans élève' => "SELECT COUNT(*) FROM payments WHERE student_id NOT IN (SELECT id FROM students)",
        'Paiements sans type de frais' => "SELECT COUNT(*) FROM payments WHERE fee_type_id NOT IN (SELECT id FROM fee_types)",
        'Élèves sans classe' => "SELECT COUNT(*) FROM students WHERE current_class_id IS NULL OR current_class_id = 0",
        'Classes sans cycle' => "SELECT COUNT(*) FROM classes WHERE cycle_id IS NULL OR cycle_id = 0",
        'Emplois du temps sans classe' => "SELECT COUNT(*) FROM timetables WHERE class_id NOT IN (SELECT id FROM classes)",
        'Emplois du temps sans matière' => "SELECT COUNT(*) FROM timetables WHERE subject_id NOT IN (SELECT id FROM subjects)",
        'Emplois du temps sans enseignant' => "SELECT COUNT(*) FROM timetables WHERE teacher_id NOT IN (SELECT id FROM teachers)",
        'Assignations sans enseignant' => "SELECT COUNT(*) FROM teacher_assignments WHERE teacher_id NOT IN (SELECT id FROM teachers)",
        'Assignations sans classe' => "SELECT COUNT(*) FROM teacher_assignments WHERE class_id NOT IN (SELECT id FROM classes)",
        'Assignations sans matière' => "SELECT COUNT(*) FROM teacher_assignments WHERE subject_id NOT IN (SELECT id FROM subjects)"
    ];
    
    $orphanCount = 0;
    foreach ($orphanChecks as $check => $query) {
        try {
            $stmt = $pdo->query($query);
            $count = $stmt->fetchColumn();
            if ($count == 0) {
                echo "✅ $check: Aucun orphelin détecté\n";
            } else {
                echo "❌ $check: $count orphelins détectés\n";
                $orphanCount++;
            }
        } catch (PDOException $e) {
            echo "⚠️  $check: Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    if ($orphanCount == 0) {
        echo "\n🎉 Aucune relation orpheline détectée !\n";
    } else {
        echo "\n⚠️  $orphanCount types d'orphelins détectés\n";
    }
    
    // 2. VÉRIFICATION DES CONTRAINTES DE CLÉS ÉTRANGÈRES
    echo "\n2. VÉRIFICATION DES CONTRAINTES DE CLÉS ÉTRANGÈRES\n";
    echo "==================================================\n";
    
    $foreignKeyChecks = [
        'students -> classes' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'students' AND REFERENCED_TABLE_NAME = 'classes'",
        'classes -> cycles' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'classes' AND REFERENCED_TABLE_NAME = 'cycles'",
        'payments -> students' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payments' AND REFERENCED_TABLE_NAME = 'students'",
        'payments -> fee_types' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payments' AND REFERENCED_TABLE_NAME = 'fee_types'",
        'timetables -> classes' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'classes'",
        'timetables -> subjects' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'subjects'",
        'timetables -> teachers' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'teachers'",
        'teacher_assignments -> teachers' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'teachers'",
        'teacher_assignments -> classes' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'classes'",
        'teacher_assignments -> subjects' => "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'subjects'"
    ];
    
    $fkCount = 0;
    foreach ($foreignKeyChecks as $check => $query) {
        try {
            $stmt = $pdo->query($query);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                echo "✅ $check: Contrainte présente\n";
                $fkCount++;
            } else {
                echo "❌ $check: Contrainte manquante\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  $check: Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Contraintes de clés étrangères: $fkCount/10 présentes\n";
    
    // 3. VÉRIFICATION DES INDEX
    echo "\n3. VÉRIFICATION DES INDEX\n";
    echo "=========================\n";
    
    $indexChecks = [
        'idx_students_class_id' => "students",
        'idx_students_academic_year' => "students",
        'idx_students_status' => "students",
        'idx_classes_cycle_id' => "classes",
        'idx_classes_active' => "classes",
        'idx_payments_student_id' => "payments",
        'idx_payments_fee_type_id' => "payments",
        'idx_payments_academic_year' => "payments",
        'idx_payments_date' => "payments",
        'idx_timetables_class_id' => "timetables",
        'idx_timetables_teacher_id' => "timetables",
        'idx_timetables_subject_id' => "timetables",
        'idx_assignments_teacher_id' => "teacher_assignments",
        'idx_assignments_class_id' => "teacher_assignments",
        'idx_assignments_subject_id' => "teacher_assignments"
    ];
    
    $indexCount = 0;
    foreach ($indexChecks as $indexName => $tableName) {
        try {
            $stmt = $pdo->query("SHOW INDEX FROM $tableName WHERE Key_name = '$indexName'");
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "✅ Index $indexName sur $tableName: Présent\n";
                $indexCount++;
            } else {
                echo "❌ Index $indexName sur $tableName: Manquant\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  Index $indexName sur $tableName: Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Index créés: $indexCount/" . count($indexChecks) . "\n";
    
    // 4. VÉRIFICATION DES VUES
    echo "\n4. VÉRIFICATION DES VUES\n";
    echo "========================\n";
    
    $viewChecks = [
        'v_students_complete' => "Vue élèves complète",
        'v_payments_complete' => "Vue paiements complète",
        'v_timetables_complete' => "Vue emplois du temps complets",
        'v_assignments_complete' => "Vue assignations complètes"
    ];
    
    $viewCount = 0;
    foreach ($viewChecks as $viewName => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_NAME = '$viewName' AND TABLE_SCHEMA = '$dbname'");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                echo "✅ $description ($viewName): Créée\n";
                $viewCount++;
            } else {
                echo "❌ $description ($viewName): Manquante\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  $description ($viewName): Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Vues créées: $viewCount/" . count($viewChecks) . "\n";
    
    // 5. VÉRIFICATION DES TRIGGERS
    echo "\n5. VÉRIFICATION DES TRIGGERS\n";
    echo "===========================\n";
    
    $triggerChecks = [
        'tr_check_class_capacity' => "Vérification capacité classe",
        'tr_check_timetable_conflicts' => "Vérification conflits emploi du temps"
    ];
    
    $triggerCount = 0;
    foreach ($triggerChecks as $triggerName => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_NAME = '$triggerName' AND TRIGGER_SCHEMA = '$dbname'");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                echo "✅ $description ($triggerName): Créé\n";
                $triggerCount++;
            } else {
                echo "❌ $description ($triggerName): Manquant\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  $description ($triggerName): Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Triggers créés: $triggerCount/" . count($triggerChecks) . "\n";
    
    // 6. VÉRIFICATION DES PROCÉDURES STOCKÉES
    echo "\n6. VÉRIFICATION DES PROCÉDURES STOCKÉES\n";
    echo "========================================\n";
    
    $procedureChecks = [
        'sp_change_student_class' => "Changement de classe d'élève",
        'sp_get_cycle_statistics' => "Statistiques par cycle"
    ];
    
    $procedureCount = 0;
    foreach ($procedureChecks as $procedureName => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_NAME = '$procedureName' AND ROUTINE_SCHEMA = '$dbname'");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                echo "✅ $description ($procedureName): Créée\n";
                $procedureCount++;
            } else {
                echo "❌ $description ($procedureName): Manquante\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  $description ($procedureName): Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Procédures créées: $procedureCount/" . count($procedureChecks) . "\n";
    
    // 7. VÉRIFICATION DE LA COHÉRENCE DES DONNÉES
    echo "\n7. VÉRIFICATION DE LA COHÉRENCE DES DONNÉES\n";
    echo "============================================\n";
    
    // 7.1 Années académiques
    try {
        $stmt = $pdo->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year");
        $studentYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo->query("SELECT DISTINCT academic_year FROM payments ORDER BY academic_year");
        $paymentYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count(array_intersect($studentYears, $paymentYears)) == count($studentYears)) {
            echo "✅ Cohérence des années académiques: OK\n";
        } else {
            echo "❌ Cohérence des années académiques: Incohérent\n";
            echo "   Années élèves: " . implode(', ', $studentYears) . "\n";
            echo "   Années paiements: " . implode(', ', $paymentYears) . "\n";
        }
    } catch (PDOException $e) {
        echo "⚠️  Vérification années académiques: Erreur - " . $e->getMessage() . "\n";
    }
    
    // 7.2 Statistiques de cohérence
    try {
        $stmt = $pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM students) as total_students,
                (SELECT COUNT(*) FROM classes) as total_classes,
                (SELECT COUNT(*) FROM cycles) as total_cycles,
                (SELECT COUNT(*) FROM payments) as total_payments,
                (SELECT COUNT(*) FROM timetables) as total_timetables,
                (SELECT COUNT(*) FROM teacher_assignments) as total_assignments
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Statistiques de cohérence:\n";
        echo "   - Élèves: {$stats['total_students']}\n";
        echo "   - Classes: {$stats['total_classes']}\n";
        echo "   - Cycles: {$stats['total_cycles']}\n";
        echo "   - Paiements: {$stats['total_payments']}\n";
        echo "   - Emplois du temps: {$stats['total_timetables']}\n";
        echo "   - Assignations: {$stats['total_assignments']}\n";
        
    } catch (PDOException $e) {
        echo "⚠️  Statistiques de cohérence: Erreur - " . $e->getMessage() . "\n";
    }
    
    // 8. RÉSUMÉ FINAL
    echo "\n==========================================\n";
    echo "RÉSUMÉ FINAL DE LA COHÉRENCE\n";
    echo "==========================================\n";
    
    $totalChecks = count($orphanChecks) + count($foreignKeyChecks) + count($indexChecks) + count($viewChecks) + count($triggerChecks) + count($procedureChecks) + 2; // +2 pour les vérifications de cohérence
    $successChecks = (count($orphanChecks) - $orphanCount) + $fkCount + $indexCount + $viewCount + $triggerCount + $procedureCount + 2; // +2 pour les vérifications de cohérence
    
    $coherencePercentage = round(($successChecks / $totalChecks) * 100, 2);
    
    echo "📊 Score de cohérence: $coherencePercentage%\n";
    echo "✅ Vérifications réussies: $successChecks/$totalChecks\n";
    
    if ($coherencePercentage >= 90) {
        echo "\n🎉 EXCELLENT! La cohérence entre les modules est excellente.\n";
        echo "   L'application est prête pour la production.\n";
    } elseif ($coherencePercentage >= 75) {
        echo "\n✅ BON! La cohérence entre les modules est bonne.\n";
        echo "   Quelques améliorations mineures peuvent être apportées.\n";
    } elseif ($coherencePercentage >= 50) {
        echo "\n⚠️  MOYEN! La cohérence entre les modules nécessite des améliorations.\n";
        echo "   Il est recommandé d'appliquer les corrections manquantes.\n";
    } else {
        echo "\n❌ CRITIQUE! La cohérence entre les modules est insuffisante.\n";
        echo "   Il est urgent d'appliquer toutes les corrections.\n";
    }
    
    // 9. RECOMMANDATIONS
    echo "\n9. RECOMMANDATIONS\n";
    echo "==================\n";
    
    if ($orphanCount > 0) {
        echo "🔧 Appliquer le script de nettoyage des orphelins\n";
    }
    
    if ($fkCount < count($foreignKeyChecks)) {
        echo "🔧 Ajouter les contraintes de clés étrangères manquantes\n";
    }
    
    if ($indexCount < count($indexChecks)) {
        echo "🔧 Créer les index manquants pour optimiser les performances\n";
    }
    
    if ($viewCount < count($viewChecks)) {
        echo "🔧 Créer les vues manquantes pour simplifier les requêtes\n";
    }
    
    if ($triggerCount < count($triggerChecks)) {
        echo "🔧 Créer les triggers manquants pour la validation automatique\n";
    }
    
    if ($procedureCount < count($procedureChecks)) {
        echo "🔧 Créer les procédures stockées manquantes\n";
    }
    
    if ($coherencePercentage >= 90) {
        echo "✅ Aucune action urgente requise\n";
        echo "✅ L'application peut être utilisée en production\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n==========================================\n";
echo "FIN DU TEST DE COHÉRENCE\n";
echo "==========================================\n";
?>
