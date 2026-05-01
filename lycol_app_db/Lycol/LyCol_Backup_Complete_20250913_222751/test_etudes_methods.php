<?php

echo "🔍 TEST DES MÉTHODES MODULE ÉTUDES\n";
echo "===================================\n\n";

// Charger CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use App\Models\ClassModel;
use App\Models\SubjectModel;
use App\Models\CycleModel;
use App\Models\TeacherModel;
use App\Models\TeacherAssignmentModel;

try {
    echo "📊 TEST DES MODÈLES:\n";
    echo "--------------------\n";
    
    // Test CycleModel
    echo "\n🔄 Test CycleModel:\n";
    $cycleModel = new CycleModel();
    
    try {
        $cycles = $cycleModel->getActiveCycles();
        echo "✓ getActiveCycles(): " . count($cycles) . " cycles trouvés\n";
    } catch (Exception $e) {
        echo "✗ getActiveCycles(): " . $e->getMessage() . "\n";
    }
    
    try {
        $cycleStats = $cycleModel->getCycleStats();
        echo "✓ getCycleStats(): " . count($cycleStats) . " statistiques trouvées\n";
    } catch (Exception $e) {
        echo "✗ getCycleStats(): " . $e->getMessage() . "\n";
    }
    
    // Test ClassModel
    echo "\n🔄 Test ClassModel:\n";
    $classModel = new ClassModel();
    
    try {
        $classes = $classModel->getActiveClasses();
        echo "✓ getActiveClasses(): " . count($classes) . " classes trouvées\n";
    } catch (Exception $e) {
        echo "✗ getActiveClasses(): " . $e->getMessage() . "\n";
    }
    
    try {
        $classesWithCycles = $classModel->getAllClassesWithCycles();
        echo "✓ getAllClassesWithCycles(): " . count($classesWithCycles) . " classes avec cycles trouvées\n";
    } catch (Exception $e) {
        echo "✗ getAllClassesWithCycles(): " . $e->getMessage() . "\n";
    }
    
    // Test SubjectModel
    echo "\n🔄 Test SubjectModel:\n";
    $subjectModel = new SubjectModel();
    
    try {
        $subjects = $subjectModel->getActiveSubjects();
        echo "✓ getActiveSubjects(): " . count($subjects) . " matières trouvées\n";
    } catch (Exception $e) {
        echo "✗ getActiveSubjects(): " . $e->getMessage() . "\n";
    }
    
    try {
        $subjectStats = $subjectModel->getSubjectStatistics();
        echo "✓ getSubjectStatistics(): " . count($subjectStats) . " statistiques trouvées\n";
    } catch (Exception $e) {
        echo "✗ getSubjectStatistics(): " . $e->getMessage() . "\n";
    }
    
    // Test TeacherModel
    echo "\n🔄 Test TeacherModel:\n";
    $teacherModel = new TeacherModel();
    
    try {
        $teachers = $teacherModel->getActiveTeachers();
        echo "✓ getActiveTeachers(): " . count($teachers) . " enseignants trouvés\n";
    } catch (Exception $e) {
        echo "✗ getActiveTeachers(): " . $e->getMessage() . "\n";
    }
    
    // Test TeacherAssignmentModel
    echo "\n🔄 Test TeacherAssignmentModel:\n";
    $assignmentModel = new TeacherAssignmentModel();
    
    try {
        $recentAssignments = $assignmentModel->getRecentAssignments(5);
        echo "✓ getRecentAssignments(): " . count($recentAssignments) . " assignations récentes trouvées\n";
    } catch (Exception $e) {
        echo "✗ getRecentAssignments(): " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ TESTS TERMINÉS\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR GÉNÉRALE: " . $e->getMessage() . "\n";
}
?>


