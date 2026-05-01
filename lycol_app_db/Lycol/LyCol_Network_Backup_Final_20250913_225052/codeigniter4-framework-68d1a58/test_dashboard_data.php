<?php
/**
 * Test de diagnostic pour les données récentes du dashboard Scolarité
 */

echo "🔍 DIAGNOSTIC DES DONNÉES RÉCENTES - DASHBOARD SCOLARITÉ\n";
echo "========================================================\n\n";

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
    $limit = 5;
    
    // Test 1: Élèves récents
    echo "📊 Test 1: Élèves récents\n";
    echo "-------------------------\n";
    
    $recentStudents = $pdo->prepare("
        SELECT s.*, c.name as class_name
        FROM students s
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY s.created_at DESC
        LIMIT ?
    ");
    $recentStudents->execute([$academicYear, (int)$limit]);
    $students = $recentStudents->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'élèves récupérés : " . count($students) . "\n";
    if (count($students) > 0) {
        echo "   Derniers élèves :\n";
        foreach ($students as $student) {
            echo "   - " . $student['first_name'] . " " . $student['last_name'] . " (" . ($student['class_name'] ?? 'N/A') . ") - " . $student['created_at'] . "\n";
        }
    } else {
        echo "   ❌ Aucun élève trouvé\n";
    }
    echo "\n";
    
    // Test 2: Absences récentes
    echo "📊 Test 2: Absences récentes\n";
    echo "----------------------------\n";
    
    $recentAbsences = $pdo->prepare("
        SELECT a.*, s.first_name, s.last_name, s.matricule, c.name as class_name
        FROM absences a
        JOIN students s ON a.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY a.date DESC, a.created_at DESC
        LIMIT ?
    ");
    $recentAbsences->execute([$academicYear, (int)$limit]);
    $absences = $recentAbsences->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'absences récupérées : " . count($absences) . "\n";
    if (count($absences) > 0) {
        echo "   Dernières absences :\n";
        foreach ($absences as $absence) {
            echo "   - " . $absence['first_name'] . " " . $absence['last_name'] . " (" . $absence['date'] . ") - " . ($absence['class_name'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Aucune absence trouvée\n";
    }
    echo "\n";
    
    // Test 3: Incidents récents
    echo "📊 Test 3: Incidents récents\n";
    echo "----------------------------\n";
    
    $recentIncidents = $pdo->prepare("
        SELECT d.*, s.first_name, s.last_name, s.matricule, c.name as class_name
        FROM discipline_incidents d
        JOIN students s ON d.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY d.incident_date DESC, d.created_at DESC
        LIMIT ?
    ");
    $recentIncidents->execute([$academicYear, (int)$limit]);
    $incidents = $recentIncidents->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Nombre d'incidents récupérés : " . count($incidents) . "\n";
    if (count($incidents) > 0) {
        echo "   Derniers incidents :\n";
        foreach ($incidents as $incident) {
            echo "   - " . $incident['first_name'] . " " . $incident['last_name'] . " (" . $incident['incident_date'] . ") - " . $incident['incident_type'] . "\n";
        }
    } else {
        echo "   ℹ️  Aucun incident trouvé (normal)\n";
    }
    echo "\n";
    
    // Test 4: Vérification des données de base
    echo "📊 Test 4: Vérification des données de base\n";
    echo "-------------------------------------------\n";
    
    $totalStudents = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE academic_year = ?");
    $totalStudents->execute([$academicYear]);
    $studentCount = $totalStudents->fetch(PDO::FETCH_ASSOC)['count'];
    
    $totalAbsences = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM absences a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.academic_year = ?
    ");
    $totalAbsences->execute([$academicYear]);
    $absenceCount = $totalAbsences->fetch(PDO::FETCH_ASSOC)['count'];
    
    $totalIncidents = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM discipline_incidents d 
        JOIN students s ON d.student_id = s.id 
        WHERE s.academic_year = ?
    ");
    $totalIncidents->execute([$academicYear]);
    $incidentCount = $totalIncidents->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "   Total élèves : {$studentCount}\n";
    echo "   Total absences : {$absenceCount}\n";
    echo "   Total incidents : {$incidentCount}\n";
    echo "\n";
    
    // Test 5: Test de la méthode index du contrôleur
    echo "📊 Test 5: Simulation de la méthode index\n";
    echo "-----------------------------------------\n";
    
    // Simuler les appels de méthodes comme dans le contrôleur
    $recentStudents2 = $pdo->prepare("
        SELECT s.*, c.name as class_name
        FROM students s
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY s.created_at DESC
        LIMIT ?
    ");
    $recentStudents2->execute([$academicYear, (int)10]);
    $students2 = $recentStudents2->fetchAll(PDO::FETCH_OBJ);
    
    $recentAbsences2 = $pdo->prepare("
        SELECT a.*, s.first_name, s.last_name, s.matricule, c.name as class_name
        FROM absences a
        JOIN students s ON a.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY a.date DESC, a.created_at DESC
        LIMIT ?
    ");
    $recentAbsences2->execute([$academicYear, (int)10]);
    $absences2 = $recentAbsences2->fetchAll(PDO::FETCH_OBJ);
    
    $recentIncidents2 = $pdo->prepare("
        SELECT d.*, s.first_name, s.last_name, s.matricule, c.name as class_name
        FROM discipline_incidents d
        JOIN students s ON d.student_id = s.id
        LEFT JOIN classes c ON s.current_class_id = c.id
        WHERE s.academic_year = ?
        ORDER BY d.incident_date DESC, d.created_at DESC
        LIMIT ?
    ");
    $recentIncidents2->execute([$academicYear, (int)10]);
    $incidents2 = $recentIncidents2->fetchAll(PDO::FETCH_OBJ);
    
    echo "   Élèves récents (OBJ) : " . count($students2) . "\n";
    echo "   Absences récentes (OBJ) : " . count($absences2) . "\n";
    echo "   Incidents récents (OBJ) : " . count($incidents2) . "\n";
    
    if (count($students2) > 0) {
        echo "   ✅ Données élèves disponibles pour l'affichage\n";
    } else {
        echo "   ❌ Aucune donnée élève pour l'affichage\n";
    }
    
    if (count($absences2) > 0) {
        echo "   ✅ Données absences disponibles pour l'affichage\n";
    } else {
        echo "   ❌ Aucune donnée absence pour l'affichage\n";
    }
    
    if (count($incidents2) > 0) {
        echo "   ✅ Données incidents disponibles pour l'affichage\n";
    } else {
        echo "   ℹ️  Aucune donnée incident pour l'affichage (normal)\n";
    }
    
    echo "\n🎯 CONCLUSION :\n";
    echo "==============\n";
    
    if (count($students2) > 0 && count($absences2) > 0) {
        echo "✅ Les données sont disponibles dans la base\n";
        echo "❌ Le problème vient de l'affichage dans la vue\n";
        echo "🔧 Solution : Vérifier la logique d'affichage dans la vue\n";
    } else {
        echo "❌ Problème de récupération des données\n";
        echo "🔧 Solution : Vérifier les requêtes SQL\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
}
?>


