<?php
/**
 * Script pour tester les méthodes de statistiques du module Examens
 */

$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🎯 TEST DES MÉTHODES DE STATISTIQUES - MODULE EXAMENS\n";
    echo "====================================================\n\n";
    
    // Test 1: Vérifier la structure de la table grades
    echo "📊 TEST 1: STRUCTURE DE LA TABLE grades\n";
    echo "----------------------------------------\n";
    $stmt = $pdo->query("DESCRIBE grades");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    // Test 2: Vérifier la structure de la table subjects
    echo "\n📚 TEST 2: STRUCTURE DE LA TABLE subjects\n";
    echo "------------------------------------------\n";
    $stmt = $pdo->query("DESCRIBE subjects");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    // Test 3: Vérifier les données dans grades
    echo "\n📊 TEST 3: DONNÉES DANS LA TABLE grades\n";
    echo "----------------------------------------\n";
    $stmt = $pdo->query("SELECT id, exam_id, student_id, subject_id, marks_obtained FROM grades LIMIT 5");
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($grades as $grade) {
        echo "✅ ID: {$grade['id']}, Examen: {$grade['exam_id']}, Élève: {$grade['student_id']}, Matière: {$grade['subject_id']}, Note: {$grade['marks_obtained']}\n";
    }
    
    // Test 4: Vérifier les données dans subjects
    echo "\n📚 TEST 4: DONNÉES DANS LA TABLE subjects\n";
    echo "------------------------------------------\n";
    $stmt = $pdo->query("SELECT id, name, coefficient FROM subjects LIMIT 5");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subjects as $subject) {
        echo "✅ ID: {$subject['id']}, Nom: {$subject['name']}, Coefficient: {$subject['coefficient']}\n";
    }
    
    // Test 5: Tester la requête getAverageScores
    echo "\n📈 TEST 5: REQUÊTE getAverageScores\n";
    echo "-----------------------------------\n";
    try {
        $stmt = $pdo->query("
            SELECT subjects.name, subjects.coefficient, 
                   AVG(grades.marks_obtained) as average_score,
                   MAX(grades.marks_obtained) as max_score, 
                   MIN(grades.marks_obtained) as min_score,
                   COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                   COUNT(*) as total
            FROM grades 
            JOIN subjects ON subjects.id = grades.subject_id
            GROUP BY subjects.id
            ORDER BY average_score DESC
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $result) {
            echo "✅ {$result['name']}: Moyenne {$result['average_score']}, Max {$result['max_score']}, Min {$result['min_score']}, Réussis {$result['passed']}/{$result['total']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur dans getAverageScores: " . $e->getMessage() . "\n";
    }
    
    // Test 6: Tester la requête getPassRates
    echo "\n📊 TEST 6: REQUÊTE getPassRates\n";
    echo "--------------------------------\n";
    try {
        $stmt = $pdo->query("
            SELECT subjects.name, 
                   COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed, 
                   COUNT(*) as total
            FROM grades 
            JOIN subjects ON subjects.id = grades.subject_id
            GROUP BY subjects.id
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $result) {
            $passRate = $result['total'] > 0 ? round(($result['passed'] / $result['total']) * 100, 1) : 0;
            echo "✅ {$result['name']}: {$result['passed']}/{$result['total']} ({$passRate}%)\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur dans getPassRates: " . $e->getMessage() . "\n";
    }
    
    // Test 7: Tester la requête getPerformanceByClass
    echo "\n🏫 TEST 7: REQUÊTE getPerformanceByClass\n";
    echo "----------------------------------------\n";
    try {
        $stmt = $pdo->query("
            SELECT classes.name as class_name, 
                   AVG(grades.marks_obtained) as average_score,
                   COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                   COUNT(*) as total
            FROM grades 
            JOIN students ON students.id = grades.student_id
            JOIN classes ON classes.id = students.current_class_id
            GROUP BY classes.id
            ORDER BY average_score DESC
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $result) {
            $passRate = $result['total'] > 0 ? round(($result['passed'] / $result['total']) * 100, 1) : 0;
            echo "✅ {$result['class_name']}: Moyenne {$result['average_score']}, Réussis {$result['passed']}/{$result['total']} ({$passRate}%)\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur dans getPerformanceByClass: " . $e->getMessage() . "\n";
    }
    
    // Test 8: Tester la requête getTopStudents
    echo "\n👥 TEST 8: REQUÊTE getTopStudents\n";
    echo "---------------------------------\n";
    try {
        $stmt = $pdo->query("
            SELECT students.first_name, students.last_name, students.matricule, 
                   classes.name as class_name,
                   AVG(grades.marks_obtained) as average_score,
                   COUNT(grades.id) as exam_count
            FROM grades 
            JOIN students ON students.id = grades.student_id
            LEFT JOIN classes ON classes.id = students.current_class_id
            GROUP BY students.id
            ORDER BY average_score DESC
            LIMIT 5
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $result) {
            echo "✅ {$result['first_name']} {$result['last_name']} ({$result['matricule']}) - Classe: {$result['class_name']}, Moyenne: {$result['average_score']}, Examens: {$result['exam_count']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur dans getTopStudents: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 TEST TERMINÉ !\n";
    echo "================\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n";
}
?>









