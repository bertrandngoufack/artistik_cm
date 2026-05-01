<?php
/**
 * Script d'analyse de la cohérence entre les modules
 * Économat, Scolarité et Études
 */

echo "==========================================\n";
echo "ANALYSE DE COHÉRENCE ENTRE LES MODULES\n";
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
    
    // 1. ANALYSE DES TABLES ET RELATIONS
    echo "1. ANALYSE DES TABLES ET RELATIONS\n";
    echo "==================================\n";
    
    $tables = [
        'students' => 'Module Scolarité',
        'classes' => 'Module Études',
        'cycles' => 'Module Études',
        'payments' => 'Module Économat',
        'fee_types' => 'Module Économat',
        'subjects' => 'Module Études',
        'timetables' => 'Module Études',
        'teacher_assignments' => 'Module Études',
        'teachers' => 'Module Études',
        'users' => 'Système'
    ];
    
    foreach ($tables as $table => $module) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✓ Table $table ($module): $count enregistrements\n";
        } catch (PDOException $e) {
            echo "✗ Table $table ($module): Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n2. ANALYSE DES RELATIONS ENTRE MODULES\n";
    echo "======================================\n";
    
    // Relations critiques à vérifier
    $relations = [
        'Students -> Classes' => "SELECT COUNT(*) FROM students s JOIN classes c ON s.current_class_id = c.id",
        'Students -> Cycles (via Classes)' => "SELECT COUNT(*) FROM students s JOIN classes c ON s.current_class_id = c.id JOIN cycles cy ON c.cycle_id = cy.id",
        'Payments -> Students' => "SELECT COUNT(*) FROM payments p JOIN students s ON p.student_id = s.id",
        'Payments -> Fee Types' => "SELECT COUNT(*) FROM payments p JOIN fee_types ft ON p.fee_type_id = ft.id",
        'Classes -> Cycles' => "SELECT COUNT(*) FROM classes c JOIN cycles cy ON c.cycle_id = cy.id",
        'Timetables -> Classes' => "SELECT COUNT(*) FROM timetables t JOIN classes c ON t.class_id = c.id",
        'Timetables -> Subjects' => "SELECT COUNT(*) FROM timetables t JOIN subjects s ON t.subject_id = s.id",
        'Timetables -> Teachers' => "SELECT COUNT(*) FROM timetables t JOIN teachers th ON t.teacher_id = th.id",
        'Assignments -> Teachers' => "SELECT COUNT(*) FROM teacher_assignments ta JOIN teachers th ON ta.teacher_id = th.id",
        'Assignments -> Classes' => "SELECT COUNT(*) FROM teacher_assignments ta JOIN classes c ON ta.class_id = c.id",
        'Assignments -> Subjects' => "SELECT COUNT(*) FROM teacher_assignments ta JOIN subjects s ON ta.subject_id = s.id"
    ];
    
    foreach ($relations as $relation => $query) {
        try {
            $stmt = $pdo->query($query);
            $count = $stmt->fetchColumn();
            echo "✓ $relation: $count relations\n";
        } catch (PDOException $e) {
            echo "✗ $relation: Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n3. ANALYSE DES INCOHÉRENCES DÉTECTÉES\n";
    echo "=====================================\n";
    
    // Vérification des incohérences
    $incoherences = [];
    
    // 3.1 Élèves sans classe
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE current_class_id IS NULL OR current_class_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count élèves n'ont pas de classe assignée";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des élèves sans classe";
    }
    
    // 3.2 Classes sans cycle
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM classes WHERE cycle_id IS NULL OR cycle_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count classes n'ont pas de cycle assigné";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des classes sans cycle";
    }
    
    // 3.3 Paiements sans élève
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE student_id IS NULL OR student_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count paiements n'ont pas d'élève assigné";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des paiements sans élève";
    }
    
    // 3.4 Paiements sans type de frais
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE fee_type_id IS NULL OR fee_type_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count paiements n'ont pas de type de frais assigné";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des paiements sans type de frais";
    }
    
    // 3.5 Emplois du temps sans classe
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM timetables WHERE class_id IS NULL OR class_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count emplois du temps n'ont pas de classe assignée";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des emplois du temps sans classe";
    }
    
    // 3.6 Assignations sans enseignant
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM teacher_assignments WHERE teacher_id IS NULL OR teacher_id = 0");
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $incoherences[] = "⚠️  $count assignations n'ont pas d'enseignant assigné";
        }
    } catch (PDOException $e) {
        $incoherences[] = "❌ Erreur lors de la vérification des assignations sans enseignant";
    }
    
    if (empty($incoherences)) {
        echo "✅ Aucune incohérence détectée\n";
    } else {
        foreach ($incoherences as $incoherence) {
            echo "$incoherence\n";
        }
    }
    
    echo "\n4. ANALYSE DE LA LOGIQUE MÉTIER\n";
    echo "===============================\n";
    
    // 4.1 Vérification de l'année académique
    try {
        $stmt = $pdo->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC LIMIT 5");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✓ Années académiques des élèves: " . implode(', ', $years) . "\n";
        
        $stmt = $pdo->query("SELECT DISTINCT academic_year FROM payments ORDER BY academic_year DESC LIMIT 5");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✓ Années académiques des paiements: " . implode(', ', $years) . "\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la vérification des années académiques\n";
    }
    
    // 4.2 Vérification des statuts
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM students GROUP BY status");
        $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Statuts des élèves:\n";
        foreach ($statuses as $status) {
            echo "  - {$status['status']}: {$status['count']} élèves\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la vérification des statuts\n";
    }
    
    // 4.3 Vérification des méthodes de paiement
    try {
        $stmt = $pdo->query("SELECT payment_method, COUNT(*) as count FROM payments GROUP BY payment_method");
        $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Méthodes de paiement:\n";
        foreach ($methods as $method) {
            echo "  - {$method['payment_method']}: {$method['count']} paiements\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la vérification des méthodes de paiement\n";
    }
    
    echo "\n5. RECOMMANDATIONS POUR LA COHÉRENCE\n";
    echo "====================================\n";
    
    $recommendations = [
        "🔧 Implémenter des contraintes de clés étrangères pour toutes les relations",
        "🔧 Ajouter des validations côté application pour les relations critiques",
        "🔧 Standardiser les années académiques entre tous les modules",
        "🔧 Implémenter des triggers pour maintenir la cohérence des données",
        "🔧 Ajouter des index sur les colonnes de jointure pour optimiser les performances",
        "🔧 Créer des vues pour simplifier les requêtes complexes entre modules",
        "🔧 Implémenter des procédures stockées pour les opérations critiques",
        "🔧 Ajouter des logs pour tracer les modifications de données importantes"
    ];
    
    foreach ($recommendations as $recommendation) {
        echo "$recommendation\n";
    }
    
    echo "\n6. SCRIPT DE CORRECTION DES INCOHÉRENCES\n";
    echo "=========================================\n";
    
    echo "-- Script SQL pour corriger les incohérences détectées\n";
    echo "-- À exécuter avec précaution après sauvegarde\n\n";
    
    echo "-- 1. Supprimer les paiements orphelins\n";
    echo "DELETE FROM payments WHERE student_id NOT IN (SELECT id FROM students);\n";
    echo "DELETE FROM payments WHERE fee_type_id NOT IN (SELECT id FROM fee_types);\n\n";
    
    echo "-- 2. Supprimer les emplois du temps orphelins\n";
    echo "DELETE FROM timetables WHERE class_id NOT IN (SELECT id FROM classes);\n";
    echo "DELETE FROM timetables WHERE subject_id NOT IN (SELECT id FROM subjects);\n";
    echo "DELETE FROM timetables WHERE teacher_id NOT IN (SELECT id FROM teachers);\n\n";
    
    echo "-- 3. Supprimer les assignations orphelines\n";
    echo "DELETE FROM teacher_assignments WHERE teacher_id NOT IN (SELECT id FROM teachers);\n";
    echo "DELETE FROM teacher_assignments WHERE class_id NOT IN (SELECT id FROM classes);\n";
    echo "DELETE FROM teacher_assignments WHERE subject_id NOT IN (SELECT id FROM subjects);\n\n";
    
    echo "-- 4. Mettre à jour les élèves sans classe\n";
    echo "UPDATE students SET current_class_id = (SELECT id FROM classes WHERE is_active = 1 LIMIT 1) WHERE current_class_id IS NULL OR current_class_id = 0;\n\n";
    
    echo "-- 5. Mettre à jour les classes sans cycle\n";
    echo "UPDATE classes SET cycle_id = (SELECT id FROM cycles WHERE is_active = 1 LIMIT 1) WHERE cycle_id IS NULL OR cycle_id = 0;\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n==========================================\n";
echo "FIN DE L'ANALYSE\n";
echo "==========================================\n";
?>
