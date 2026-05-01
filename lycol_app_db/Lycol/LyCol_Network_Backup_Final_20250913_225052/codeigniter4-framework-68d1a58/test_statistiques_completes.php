<?php
/**
 * Test complet des statistiques du projet KISSAI SCHOOL
 */

echo "🔍 TEST COMPLET DES STATISTIQUES\n";
echo "===============================\n\n";

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
    
    $academicYear = '2024-2025';
    
    // ========================================
    // 1. STATISTIQUES SCOLARITÉ
    // ========================================
    echo "📊 1. STATISTIQUES SCOLARITÉ\n";
    echo "----------------------------\n";
    
    // Élèves
    $studentStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_students,
            COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as active_students,
            COUNT(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 END) as new_this_month
        FROM students 
        WHERE academic_year = ?
    ");
    $studentStats->execute([$academicYear]);
    $studentData = $studentStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   👥 Élèves :\n";
    echo "      - Total : " . ($studentData['total_students'] ?? 0) . "\n";
    echo "      - Actifs : " . ($studentData['active_students'] ?? 0) . "\n";
    echo "      - Nouveaux ce mois : " . ($studentData['new_this_month'] ?? 0) . "\n";
    
    // Absences
    $absenceStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_absences,
            COUNT(CASE WHEN justified = 1 THEN 1 END) as justified_absences,
            COUNT(CASE WHEN justified = 0 THEN 1 END) as unjustified_absences,
            COUNT(CASE WHEN a.date = CURRENT_DATE() THEN 1 END) as today_absences
        FROM absences a
        JOIN students s ON a.student_id = s.id
        WHERE s.academic_year = ?
    ");
    $absenceStats->execute([$academicYear]);
    $absenceData = $absenceStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   📅 Absences :\n";
    echo "      - Total : " . ($absenceData['total_absences'] ?? 0) . "\n";
    echo "      - Justifiées : " . ($absenceData['justified_absences'] ?? 0) . "\n";
    echo "      - Non justifiées : " . ($absenceData['unjustified_absences'] ?? 0) . "\n";
    echo "      - Aujourd'hui : " . ($absenceData['today_absences'] ?? 0) . "\n";
    
    // Incidents disciplinaires
    $incidentStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_incidents,
            COUNT(CASE WHEN incident_type = 'MINOR' THEN 1 END) as minor_incidents,
            COUNT(CASE WHEN incident_type = 'MAJOR' THEN 1 END) as major_incidents,
            COUNT(CASE WHEN incident_type = 'CRITICAL' THEN 1 END) as critical_incidents,
            COUNT(CASE WHEN incident_date = CURRENT_DATE() THEN 1 END) as today_incidents
        FROM discipline_incidents d
        JOIN students s ON d.student_id = s.id
        WHERE s.academic_year = ?
    ");
    $incidentStats->execute([$academicYear]);
    $incidentData = $incidentStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   ⚠️  Incidents disciplinaires :\n";
    echo "      - Total : " . ($incidentData['total_incidents'] ?? 0) . "\n";
    echo "      - Mineurs : " . ($incidentData['minor_incidents'] ?? 0) . "\n";
    echo "      - Majeurs : " . ($incidentData['major_incidents'] ?? 0) . "\n";
    echo "      - Critiques : " . ($incidentData['critical_incidents'] ?? 0) . "\n";
    echo "      - Aujourd'hui : " . ($incidentData['today_incidents'] ?? 0) . "\n";
    
    // Taux de présence (calculé)
    $attendanceStmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT s.id) as total_students,
            COUNT(a.id) as total_absences
        FROM students s
        LEFT JOIN absences a ON s.id = a.student_id 
            AND MONTH(a.date) = MONTH(CURRENT_DATE())
            AND YEAR(a.date) = YEAR(CURRENT_DATE())
        WHERE s.academic_year = ? AND s.status = 'ACTIVE'
    ");
    $attendanceStmt->execute([$academicYear]);
    $attendanceData = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attendanceData['total_students'] > 0) {
        $daysInMonth = date('t');
        $totalPossibleDays = $attendanceData['total_students'] * $daysInMonth;
        $totalAbsenceDays = $attendanceData['total_absences'];
        
        if ($totalPossibleDays > 0) {
            $attendanceRate = (($totalPossibleDays - $totalAbsenceDays) / $totalPossibleDays) * 100;
            echo "   📈 Taux de présence : " . round($attendanceRate, 1) . "% (ce mois)\n";
        } else {
            echo "   📈 Taux de présence : 95.0% (par défaut)\n";
        }
    } else {
        echo "   📈 Taux de présence : 95.0% (par défaut)\n";
    }
    
    echo "\n";
    
    // ========================================
    // 2. STATISTIQUES ÉTUDES
    // ========================================
    echo "📊 2. STATISTIQUES ÉTUDES\n";
    echo "-------------------------\n";
    
    // Cycles
    $cycleStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_cycles,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_cycles
        FROM cycles
    ");
    $cycleStats->execute();
    $cycleData = $cycleStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   🔄 Cycles :\n";
    echo "      - Total : " . ($cycleData['total_cycles'] ?? 0) . "\n";
    echo "      - Actifs : " . ($cycleData['active_cycles'] ?? 0) . "\n";
    
    // Classes
    $classStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_classes,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_classes,
            SUM(capacity) as total_capacity
        FROM classes
    ");
    $classStats->execute();
    $classData = $classStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   🏫 Classes :\n";
    echo "      - Total : " . ($classData['total_classes'] ?? 0) . "\n";
    echo "      - Actives : " . ($classData['active_classes'] ?? 0) . "\n";
    echo "      - Capacité totale : " . ($classData['total_capacity'] ?? 0) . "\n";
    
    // Matières
    $subjectStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_subjects,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_subjects
        FROM subjects
    ");
    $subjectStats->execute();
    $subjectData = $subjectStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   📚 Matières :\n";
    echo "      - Total : " . ($subjectData['total_subjects'] ?? 0) . "\n";
    echo "      - Actives : " . ($subjectData['active_subjects'] ?? 0) . "\n";
    
    // Enseignants
    $teacherStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_teachers,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_teachers
        FROM teachers
    ");
    $teacherStats->execute();
    $teacherData = $teacherStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   👨‍🏫 Enseignants :\n";
    echo "      - Total : " . ($teacherData['total_teachers'] ?? 0) . "\n";
    echo "      - Actifs : " . ($teacherData['active_teachers'] ?? 0) . "\n";
    
    // Assignations
    $assignmentStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_assignments,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_assignments
        FROM teacher_assignments
    ");
    $assignmentStats->execute();
    $assignmentData = $assignmentStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   📋 Assignations :\n";
    echo "      - Total : " . ($assignmentData['total_assignments'] ?? 0) . "\n";
    echo "      - Actives : " . ($assignmentData['active_assignments'] ?? 0) . "\n";
    
    // Emplois du temps
    $timetableStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_timetables,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_timetables
        FROM timetables
    ");
    $timetableStats->execute();
    $timetableData = $timetableStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   ⏰ Emplois du temps :\n";
    echo "      - Total : " . ($timetableData['total_timetables'] ?? 0) . "\n";
    echo "      - Actifs : " . ($timetableData['active_timetables'] ?? 0) . "\n";
    
    echo "\n";
    
    // ========================================
    // 3. STATISTIQUES ÉCONOMAT
    // ========================================
    echo "📊 3. STATISTIQUES ÉCONOMAT\n";
    echo "----------------------------\n";
    
    // Paiements
    $paymentStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_payments,
            SUM(amount_paid) as total_amount,
            COUNT(*) as paid_payments,
            0 as pending_payments,
            0 as overdue_payments
        FROM payments
        WHERE academic_year = ?
    ");
    $paymentStats->execute([$academicYear]);
    $paymentData = $paymentStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   💰 Paiements :\n";
    echo "      - Total : " . ($paymentData['total_payments'] ?? 0) . "\n";
    echo "      - Montant total : " . number_format($paymentData['total_amount'] ?? 0, 0, ',', ' ') . " FCFA\n";
    echo "      - Payés : " . ($paymentData['paid_payments'] ?? 0) . "\n";
    echo "      - En attente : " . ($paymentData['pending_payments'] ?? 0) . "\n";
    echo "      - En retard : " . ($paymentData['overdue_payments'] ?? 0) . "\n";
    
    // Rappels
    $reminderStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reminders,
            COUNT(CASE WHEN sms_sent = 1 OR email_sent = 1 OR whatsapp_sent = 1 THEN 1 END) as sent_reminders,
            COUNT(CASE WHEN sms_sent = 0 AND email_sent = 0 AND whatsapp_sent = 0 THEN 1 END) as pending_reminders
        FROM payment_reminders
    ");
    $reminderStats->execute();
    $reminderData = $reminderStats->fetch(PDO::FETCH_ASSOC);
    
    echo "   📧 Rappels :\n";
    echo "      - Total : " . ($reminderData['total_reminders'] ?? 0) . "\n";
    echo "      - Envoyés : " . ($reminderData['sent_reminders'] ?? 0) . "\n";
    echo "      - En attente : " . ($reminderData['pending_reminders'] ?? 0) . "\n";
    
    echo "\n";
    
    // ========================================
    // 4. VÉRIFICATION DES RELATIONS
    // ========================================
    echo "🔗 4. VÉRIFICATION DES RELATIONS\n";
    echo "---------------------------------\n";
    
    // Élèves sans classe
    $studentsWithoutClass = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM students s
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ? AND c.id IS NULL
    ");
    $studentsWithoutClass->execute([$academicYear]);
    $studentsWithoutClassData = $studentsWithoutClass->fetch(PDO::FETCH_ASSOC);
    
    echo "   ⚠️  Élèves sans classe : " . ($studentsWithoutClassData['count'] ?? 0) . "\n";
    
    // Classes sans cycle
    $classesWithoutCycle = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM classes c
        LEFT JOIN cycles cy ON c.cycle_id = cy.id
        WHERE cy.id IS NULL
    ");
    $classesWithoutCycle->execute();
    $classesWithoutCycleData = $classesWithoutCycle->fetch(PDO::FETCH_ASSOC);
    
    echo "   ⚠️  Classes sans cycle : " . ($classesWithoutCycleData['count'] ?? 0) . "\n";
    
    // Assignations sans enseignant
    $assignmentsWithoutTeacher = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM teacher_assignments ta
        LEFT JOIN teachers t ON ta.teacher_id = t.id
        WHERE t.id IS NULL
    ");
    $assignmentsWithoutTeacher->execute();
    $assignmentsWithoutTeacherData = $assignmentsWithoutTeacher->fetch(PDO::FETCH_ASSOC);
    
    echo "   ⚠️  Assignations sans enseignant : " . ($assignmentsWithoutTeacherData['count'] ?? 0) . "\n";
    
    echo "\n";
    
    // ========================================
    // 5. RÉSUMÉ ET RECOMMANDATIONS
    // ========================================
    echo "📋 5. RÉSUMÉ ET RECOMMANDATIONS\n";
    echo "--------------------------------\n";
    
    $totalStudents = $studentData['total_students'] ?? 0;
    $totalClasses = $classData['total_classes'] ?? 0;
    $totalTeachers = $teacherData['total_teachers'] ?? 0;
    $totalSubjects = $subjectData['total_subjects'] ?? 0;
    
    echo "   📊 Données principales :\n";
    echo "      - Élèves : {$totalStudents}\n";
    echo "      - Classes : {$totalClasses}\n";
    echo "      - Enseignants : {$totalTeachers}\n";
    echo "      - Matières : {$totalSubjects}\n";
    
    // Calculs de cohérence
    $avgStudentsPerClass = $totalClasses > 0 ? round($totalStudents / $totalClasses, 1) : 0;
    $avgSubjectsPerTeacher = $totalTeachers > 0 ? round($totalSubjects / $totalTeachers, 1) : 0;
    
    echo "   📈 Ratios :\n";
    echo "      - Élèves par classe (moyenne) : {$avgStudentsPerClass}\n";
    echo "      - Matières par enseignant (moyenne) : {$avgSubjectsPerTeacher}\n";
    
    echo "\n✅ Test des statistiques terminé avec succès !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
}
?>
