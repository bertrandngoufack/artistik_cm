<?php
/**
 * Script pour vérifier les vraies données des élèves et examens
 */

$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 VÉRIFICATION DES DONNÉES RÉELLES DANS LA BASE\n";
    echo "===============================================\n\n";
    
    // Vérifier les élèves
    echo "📚 ÉLÈVES DANS LA BASE :\n";
    echo "------------------------\n";
    $stmt = $pdo->query("SELECT id, matricule, first_name, last_name, class_id, status FROM students LIMIT 10");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        echo "❌ Aucun élève trouvé dans la base\n";
    } else {
        foreach ($students as $student) {
            echo "✅ ID: {$student['id']}, Matricule: {$student['matricule']}, Nom: {$student['last_name']} {$student['first_name']}, Classe: {$student['class_id']}, Statut: {$student['status']}\n";
        }
    }
    
    echo "\n📊 CLASSES DANS LA BASE :\n";
    echo "-------------------------\n";
    $stmt = $pdo->query("SELECT id, name, level_id FROM classes LIMIT 10");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($classes)) {
        echo "❌ Aucune classe trouvée dans la base\n";
    } else {
        foreach ($classes as $class) {
            echo "✅ ID: {$class['id']}, Nom: {$class['name']}, Niveau: {$class['level_id']}\n";
        }
    }
    
    echo "\n📝 EXAMENS DANS LA BASE :\n";
    echo "-------------------------\n";
    $stmt = $pdo->query("SELECT id, name, exam_type, class_id, exam_date, total_marks, status FROM exams LIMIT 10");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($exams)) {
        echo "❌ Aucun examen trouvé dans la base\n";
    } else {
        foreach ($exams as $exam) {
            echo "✅ ID: {$exam['id']}, Nom: {$exam['name']}, Type: {$exam['exam_type']}, Classe: {$exam['class_id']}, Date: {$exam['exam_date']}, Total: {$exam['total_marks']}, Statut: {$exam['status']}\n";
        }
    }
    
    echo "\n📊 NOTES DANS LA BASE :\n";
    echo "----------------------\n";
    $stmt = $pdo->query("SELECT id, exam_id, student_id, marks_obtained, remarks FROM grades LIMIT 10");
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($grades)) {
        echo "❌ Aucune note trouvée dans la base\n";
    } else {
        foreach ($grades as $grade) {
            echo "✅ ID: {$grade['id']}, Examen: {$grade['exam_id']}, Élève: {$grade['student_id']}, Note: {$grade['marks_obtained']}, Remarques: {$grade['remarks']}\n";
        }
    }
    
    // Vérifier les relations
    echo "\n🔗 VÉRIFICATION DES RELATIONS :\n";
    echo "-------------------------------\n";
    
    // Élèves avec leurs classes
    $stmt = $pdo->query("
        SELECT s.id, s.matricule, s.first_name, s.last_name, c.name as class_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE s.status = 'ACTIVE' 
        LIMIT 5
    ");
    $studentsWithClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($studentsWithClasses)) {
        echo "✅ Élèves avec leurs classes :\n";
        foreach ($studentsWithClasses as $student) {
            echo "   • {$student['first_name']} {$student['last_name']} (Matricule: {$student['matricule']}) - Classe: {$student['class_name']}\n";
        }
    }
    
    // Examens avec leurs classes
    $stmt = $pdo->query("
        SELECT e.id, e.name, e.exam_type, c.name as class_name, e.exam_date 
        FROM exams e 
        LEFT JOIN classes c ON e.class_id = c.id 
        LIMIT 5
    ");
    $examsWithClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($examsWithClasses)) {
        echo "\n✅ Examens avec leurs classes :\n";
        foreach ($examsWithClasses as $exam) {
            echo "   • {$exam['name']} (Type: {$exam['exam_type']}) - Classe: {$exam['class_name']} - Date: {$exam['exam_date']}\n";
        }
    }
    
    // Notes avec détails
    $stmt = $pdo->query("
        SELECT g.id, g.marks_obtained, s.first_name, s.last_name, e.name as exam_name, c.name as class_name
        FROM grades g 
        LEFT JOIN students s ON g.student_id = s.id 
        LEFT JOIN exams e ON g.exam_id = e.id 
        LEFT JOIN classes c ON e.class_id = c.id 
        LIMIT 5
    ");
    $gradesWithDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($gradesWithDetails)) {
        echo "\n✅ Notes avec détails :\n";
        foreach ($gradesWithDetails as $grade) {
            echo "   • {$grade['first_name']} {$grade['last_name']} - {$grade['exam_name']} - Note: {$grade['marks_obtained']}/20 - Classe: {$grade['class_name']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
}
?>



















