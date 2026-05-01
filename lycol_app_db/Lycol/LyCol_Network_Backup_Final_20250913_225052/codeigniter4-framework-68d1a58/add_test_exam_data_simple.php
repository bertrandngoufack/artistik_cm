<?php
/**
 * Script simple pour ajouter des données de test pour les examens
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "📚 AJOUT DE DONNÉES DE TEST POUR LES EXAMENS\n";
    echo "============================================\n\n";

    // 1. Ajouter des examens de test
    echo "1️⃣ Ajout d'examens de test...\n";
    
    $exams = [
        [
            'name' => '1er Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'exam_date' => '2024-10-15',
            'total_marks' => 20,
            'status' => 'COMPLETED'
        ],
        [
            'name' => '2ème Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'exam_date' => '2025-01-15',
            'total_marks' => 20,
            'status' => 'SCHEDULED'
        ],
        [
            'name' => '3ème Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'exam_date' => '2025-04-15',
            'total_marks' => 20,
            'status' => 'SCHEDULED'
        ],
        [
            'name' => 'Examen Final - CP A',
            'type' => 'FINAL',
            'class_id' => 1,
            'exam_date' => '2025-06-01',
            'total_marks' => 20,
            'status' => 'SCHEDULED'
        ],
        [
            'name' => '1er Trimestre - CE1 A',
            'type' => 'MIDTERM',
            'class_id' => 2,
            'exam_date' => '2024-10-15',
            'total_marks' => 20,
            'status' => 'COMPLETED'
        ],
        [
            'name' => 'Contrôle Continu - CP A',
            'type' => 'CONTINUOUS',
            'class_id' => 1,
            'exam_date' => '2024-11-20',
            'total_marks' => 20,
            'status' => 'COMPLETED'
        ],
        [
            'name' => 'Examen Compétitif - CE1 A',
            'type' => 'COMPETITIVE',
            'class_id' => 2,
            'exam_date' => '2024-12-10',
            'total_marks' => 20,
            'status' => 'COMPLETED'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO exams (name, exam_type, class_id, exam_date, total_marks, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($exams as $exam) {
        $stmt->execute([
            $exam['name'],
            $exam['type'],
            $exam['class_id'],
            $exam['exam_date'],
            $exam['total_marks'],
            $exam['status']
        ]);
    }
    
    echo "   ✅ " . count($exams) . " examens ajoutés\n";

    // 2. Récupérer les examens complétés, les étudiants et les matières
    $completedExams = $pdo->query("SELECT id, total_marks FROM exams WHERE status = 'COMPLETED'")->fetchAll(PDO::FETCH_ASSOC);
    $students = $pdo->query("SELECT id FROM students LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $subjects = $pdo->query("SELECT id FROM subjects LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n2️⃣ Ajout de notes de test...\n";
    
    $gradesAdded = 0;
    $stmt = $pdo->prepare("INSERT INTO grades (exam_id, student_id, subject_id, marks_obtained, remarks, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($completedExams as $exam) {
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                // Générer une note aléatoire entre 5 et 18
                $marks = rand(5, 18);
                
                $stmt->execute([
                    $exam['id'],
                    $student['id'],
                    $subject['id'],
                    $marks,
                    'Note de test générée automatiquement'
                ]);
                
                $gradesAdded++;
            }
        }
    }
    
    echo "   ✅ {$gradesAdded} notes ajoutées\n";

    // 3. Vérifier les statistiques
    echo "\n3️⃣ Vérification des statistiques...\n";
    
    $totalExams = $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
    $totalGrades = $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    $averageScore = $pdo->query("SELECT AVG(marks_obtained) FROM grades")->fetchColumn();
    $passRate = $pdo->query("SELECT COUNT(*) FROM grades WHERE marks_obtained >= 10")->fetchColumn();
    
    echo "   📊 Total examens : {$totalExams}\n";
    echo "   📊 Total notes : {$totalGrades}\n";
    echo "   📊 Moyenne générale : " . round($averageScore, 2) . "/20\n";
    echo "   📊 Taux de réussite : " . round(($passRate / $totalGrades) * 100, 1) . "%\n";

    echo "\n🎉 DONNÉES DE TEST AJOUTÉES AVEC SUCCÈS !\n";
    echo "==========================================\n";
    echo "Vous pouvez maintenant tester le module Examens avec des données réalistes.\n";
    echo "URLs à tester :\n";
    echo "• Dashboard : http://localhost:8080/admin/examens\n";
    echo "• Liste des examens : http://localhost:8080/admin/examens/exams\n";
    echo "• Gestion des notes : http://localhost:8080/admin/examens/grades\n";

} catch (PDOException $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}
?>
