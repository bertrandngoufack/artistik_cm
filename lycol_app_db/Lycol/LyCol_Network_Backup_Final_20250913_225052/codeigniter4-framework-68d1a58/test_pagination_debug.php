<?php

require_once 'vendor/autoload.php';

use App\Models\GradeModel;

$gradeModel = new GradeModel();

echo "🔍 TEST PAGINATION DEBUG\n";
echo "========================\n\n";

try {
    // Test méthode getTotalGradesByExam
    echo "📊 Test getTotalGradesByExam(4):\n";
    $total = $gradeModel->getTotalGradesByExam(4);
    echo "   Total: $total\n";
    
    // Test méthode getGradesByExamPaginated
    echo "📊 Test getGradesByExamPaginated(4, 10, 0):\n";
    $grades = $gradeModel->getGradesByExamPaginated(4, 10, 0);
    echo "   Nombre de notes récupérées: " . count($grades) . "\n";
    
    if (!empty($grades)) {
        echo "   Première note: " . json_encode($grades[0]) . "\n";
    }
    
    echo "✅ Tests réussis\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n";
}


