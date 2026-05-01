<?php
/**
 * Test de diagnostic pour le dashboard Scolarité
 */

echo "🔍 DIAGNOSTIC DU DASHBOARD SCOLARITÉ\n";
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
    
    $academicYear = '2024-2025';
    
    // Test 1: Statistiques des élèves
    echo "📊 Test 1: Statistiques des élèves\n";
    echo "----------------------------------\n";
    
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
    
    echo "   Total élèves : " . ($studentData['total_students'] ?? 0) . "\n";
    echo "   Élèves actifs : " . ($studentData['active_students'] ?? 0) . "\n";
    echo "   Nouveaux ce mois : " . ($studentData['new_this_month'] ?? 0) . "\n\n";
    
    // Test 2: Statistiques des absences
    echo "📊 Test 2: Statistiques des absences\n";
    echo "------------------------------------\n";
    
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
    
    echo "   Total absences : " . ($absenceData['total_absences'] ?? 0) . "\n";
    echo "   Absences justifiées : " . ($absenceData['justified_absences'] ?? 0) . "\n";
    echo "   Absences non justifiées : " . ($absenceData['unjustified_absences'] ?? 0) . "\n";
    echo "   Absences aujourd'hui : " . ($absenceData['today_absences'] ?? 0) . "\n\n";
    
    // Test 3: Statistiques des incidents disciplinaires
    echo "📊 Test 3: Statistiques des incidents disciplinaires\n";
    echo "---------------------------------------------------\n";
    
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
    
    echo "   Total incidents : " . ($incidentData['total_incidents'] ?? 0) . "\n";
    echo "   Incidents mineurs : " . ($incidentData['minor_incidents'] ?? 0) . "\n";
    echo "   Incidents majeurs : " . ($incidentData['major_incidents'] ?? 0) . "\n";
    echo "   Incidents critiques : " . ($incidentData['critical_incidents'] ?? 0) . "\n";
    echo "   Incidents aujourd'hui : " . ($incidentData['today_incidents'] ?? 0) . "\n\n";
    
    // Test 4: Élèves récents
    echo "📊 Test 4: Élèves récents\n";
    echo "-------------------------\n";
    
    $recentStudents = $pdo->prepare("
        SELECT s.*, c.name as class_name
        FROM students s
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $recentStudents->execute([$academicYear]);
    $students = $recentStudents->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'élèves récents : " . count($students) . "\n";
    foreach ($students as $student) {
        echo "   - " . $student['first_name'] . " " . $student['last_name'] . " (" . ($student['class_name'] ?? 'N/A') . ")\n";
    }
    echo "\n";
    
    // Test 5: Absences récentes
    echo "📊 Test 5: Absences récentes\n";
    echo "----------------------------\n";
    
    $recentAbsences = $pdo->prepare("
        SELECT a.*, s.first_name, s.last_name, c.name as class_name
        FROM absences a
        JOIN students s ON a.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
                        ORDER BY a.date DESC
        LIMIT 5
    ");
    $recentAbsences->execute([$academicYear]);
    $absences = $recentAbsences->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'absences récentes : " . count($absences) . "\n";
    foreach ($absences as $absence) {
                    echo "   - " . $absence['first_name'] . " " . $absence['last_name'] . " (" . $absence['date'] . ")\n";
    }
    echo "\n";
    
    // Test 6: Incidents récents
    echo "📊 Test 6: Incidents récents\n";
    echo "----------------------------\n";
    
    $recentIncidents = $pdo->prepare("
        SELECT d.*, s.first_name, s.last_name, c.name as class_name
        FROM discipline_incidents d
        JOIN students s ON d.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY d.incident_date DESC
        LIMIT 5
    ");
    $recentIncidents->execute([$academicYear]);
    $incidents = $recentIncidents->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'incidents récents : " . count($incidents) . "\n";
    foreach ($incidents as $incident) {
        echo "   - " . $incident['first_name'] . " " . $incident['last_name'] . " (" . $incident['incident_type'] . " - " . $incident['incident_date'] . ")\n";
    }
    echo "\n";
    
    // Test 7: Calcul du taux de présence
    echo "📊 Test 7: Calcul du taux de présence\n";
    echo "-------------------------------------\n";
    
    $attendanceStmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT s.id) as total_students,
            COUNT(a.id) as total_absences
        FROM students s
        LEFT JOIN absences a ON s.id = a.student_id AND s.academic_year = a.academic_year
        WHERE s.academic_year = ? AND s.status = 'ACTIVE'
    ");
    $attendanceStmt->execute([$academicYear]);
    $attendanceData = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attendanceData['total_students'] > 0) {
        $attendanceRate = (($attendanceData['total_students'] - $attendanceData['total_absences']) / $attendanceData['total_students']) * 100;
        echo "   Total élèves actifs : " . $attendanceData['total_students'] . "\n";
        echo "   Total absences : " . $attendanceData['total_absences'] . "\n";
        echo "   Taux de présence : " . round($attendanceRate, 1) . "%\n";
    } else {
        echo "   Aucun élève actif trouvé\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
}
?>
