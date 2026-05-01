<?php
/**
 * Script pour ajouter des données de test pour les examens
 */

// Initialiser CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

// Configuration de la base de données
$db = \Config\Database::connect();

echo "📚 AJOUT DE DONNÉES DE TEST POUR LES EXAMENS\n";
echo "============================================\n\n";

try {
    // 1. Ajouter des examens de test
    echo "1️⃣ Ajout d'examens de test...\n";
    
    $exams = [
        [
            'name' => '1er Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'subject_id' => 1,
            'exam_date' => '2024-10-15',
            'total_marks' => 20,
            'status' => 'COMPLETED',
            'description' => 'Examen du premier trimestre pour la classe CP A'
        ],
        [
            'name' => '2ème Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'subject_id' => 1,
            'exam_date' => '2025-01-15',
            'total_marks' => 20,
            'status' => 'SCHEDULED',
            'description' => 'Examen du deuxième trimestre pour la classe CP A'
        ],
        [
            'name' => '3ème Trimestre - CP A',
            'type' => 'MIDTERM',
            'class_id' => 1,
            'subject_id' => 1,
            'exam_date' => '2025-04-15',
            'total_marks' => 20,
            'status' => 'SCHEDULED',
            'description' => 'Examen du troisième trimestre pour la classe CP A'
        ],
        [
            'name' => 'Examen Final - CP A',
            'type' => 'FINAL',
            'class_id' => 1,
            'subject_id' => 1,
            'exam_date' => '2025-06-01',
            'total_marks' => 20,
            'status' => 'SCHEDULED',
            'description' => 'Examen final pour la classe CP A'
        ],
        [
            'name' => '1er Trimestre - CE1 A',
            'type' => 'MIDTERM',
            'class_id' => 2,
            'subject_id' => 1,
            'exam_date' => '2024-10-15',
            'total_marks' => 20,
            'status' => 'COMPLETED',
            'description' => 'Examen du premier trimestre pour la classe CE1 A'
        ],
        [
            'name' => 'Contrôle de Mathématiques - CP A',
            'type' => 'CONTROL',
            'class_id' => 1,
            'subject_id' => 2,
            'exam_date' => '2024-11-20',
            'total_marks' => 20,
            'status' => 'COMPLETED',
            'description' => 'Contrôle de mathématiques pour la classe CP A'
        ],
        [
            'name' => 'Composition de Français - CE1 A',
            'type' => 'COMPOSITION',
            'class_id' => 2,
            'subject_id' => 1,
            'exam_date' => '2024-12-10',
            'total_marks' => 20,
            'status' => 'COMPLETED',
            'description' => 'Composition de français pour la classe CE1 A'
        ]
    ];

    foreach ($exams as $exam) {
        $db->table('exams')->insert($exam);
    }
    
    echo "   ✅ " . count($exams) . " examens ajoutés\n";

    // 2. Ajouter des notes de test pour les examens complétés
    echo "\n2️⃣ Ajout de notes de test...\n";
    
    $completedExams = $db->table('exams')->where('status', 'COMPLETED')->get()->getResultArray();
    $students = $db->table('students')->limit(10)->get()->getResultArray();
    
    $gradesAdded = 0;
    
    foreach ($completedExams as $exam) {
        foreach ($students as $student) {
            // Générer une note aléatoire entre 5 et 18
            $marks = rand(5, 18);
            $percentage = ($marks / $exam['total_marks']) * 100;
            
            $grade = [
                'exam_id' => $exam['id'],
                'student_id' => $student['id'],
                'marks_obtained' => $marks,
                'percentage' => $percentage,
                'recorded_by' => 1,
                'comments' => 'Note de test générée automatiquement'
            ];
            
            $db->table('grades')->insert($grade);
            $gradesAdded++;
        }
    }
    
    echo "   ✅ {$gradesAdded} notes ajoutées\n";

    // 3. Vérifier les statistiques
    echo "\n3️⃣ Vérification des statistiques...\n";
    
    $totalExams = $db->table('exams')->countAllResults();
    $totalGrades = $db->table('grades')->countAllResults();
    $averageScore = $db->table('grades')->selectAvg('marks_obtained')->get()->getRow()->marks_obtained ?? 0;
    $passRate = $db->table('grades')->where('marks_obtained >=', 10)->countAllResults();
    $totalStudents = count($students);
    
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

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "Stack trace : " . $e->getTraceAsString() . "\n";
}
?>
