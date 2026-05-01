<?php
/**
 * Ajout de données de test pour les incidents disciplinaires
 */

echo "🎯 AJOUT DE DONNÉES DE TEST POUR LES INCIDENTS DISCIPLINAIRES\n";
echo "=========================================================\n\n";

// Configuration de la base de données
$dbHost = '100.69.65.33';
$dbPort = '13306';
$dbUser = 'root';
$dbPass = 'Bateau123';
$dbName = 'lycol_db';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;dbname=$dbName",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connexion à la base de données réussie.\n";

    // Vérifier si la table discipline_incidents existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'discipline_incidents'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'discipline_incidents' n'existe pas.\n";
        exit(1);
    } else {
        echo "✅ Table 'discipline_incidents' existe.\n";
    }

    // Vérifier s'il y a déjà des incidents
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM discipline_incidents");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "⚠️  Il y a déjà {$count} incidents dans la base. Suppression des anciens incidents...\n";
        $pdo->exec("DELETE FROM discipline_incidents");
        echo "✅ Anciens incidents supprimés.\n";
    }

    // Récupérer quelques IDs d'élèves existants
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM students LIMIT 5");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($students)) {
        echo "❌ Aucun élève trouvé dans la base de données. Impossible d'ajouter des incidents.\n";
        exit(1);
    }

    echo "📋 Utilisation de " . count($students) . " élèves pour les tests :\n";
    foreach ($students as $student) {
        echo "   - {$student['first_name']} {$student['last_name']} (ID: {$student['id']})\n";
    }

    $incidents = [
        [
            'student_id' => $students[0]['id'],
            'incident_type' => 'MINOR',
            'description' => 'Arrivé en retard 3 fois cette semaine sans justification valable.',
            'sanction' => 'Avertissement oral et convocation des parents',
            'incident_date' => '2025-08-20',
            'incident_time' => '08:15:00',
            'location' => 'Salle de classe CP A',
            'witnesses' => 'Mme Martin, enseignante',
            'sanction_duration' => '1 semaine',
            'parent_notified' => 1,
            'notification_sent' => 1,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[1]['id'],
            'incident_type' => 'MAJOR',
            'description' => 'A perturbé le cours de mathématiques en parlant fort et en refusant de se taire malgré les avertissements répétés.',
            'sanction' => 'Retenue de 2 heures et exclusion temporaire de cours',
            'incident_date' => '2025-08-21',
            'incident_time' => '10:30:00',
            'location' => 'Salle de classe CP B',
            'witnesses' => 'M. Dubois, enseignant et 25 élèves',
            'sanction_duration' => '2 heures',
            'parent_notified' => 0,
            'notification_sent' => 0,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[0]['id'],
            'incident_type' => 'MINOR',
            'description' => 'Utilisation du téléphone portable en classe après avertissement préalable.',
            'sanction' => 'Confiscation du téléphone et exclusion temporaire de cours',
            'incident_date' => '2025-08-22',
            'incident_time' => '14:20:00',
            'location' => 'Salle de classe CP A',
            'witnesses' => 'Mme Martin, enseignante',
            'sanction_duration' => '1 jour',
            'parent_notified' => 1,
            'notification_sent' => 1,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[2]['id'],
            'incident_type' => 'MAJOR',
            'description' => 'Absence injustifiée de 2 jours consécutifs sans notification préalable.',
            'sanction' => 'Avertissement écrit et convocation des parents',
            'incident_date' => '2025-08-23',
            'incident_time' => '08:00:00',
            'location' => 'Établissement',
            'witnesses' => 'Surveillant général',
            'sanction_duration' => '1 mois',
            'parent_notified' => 0,
            'notification_sent' => 0,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[3]['id'],
            'incident_type' => 'CRITICAL',
            'description' => 'Bagarre avec un autre élève dans la cour de récréation causant des blessures légères.',
            'sanction' => 'Exclusion temporaire de 3 jours et convocation immédiate des parents',
            'incident_date' => '2025-08-24',
            'incident_time' => '12:15:00',
            'location' => 'Cour de récréation',
            'witnesses' => 'Surveillant et plusieurs élèves',
            'sanction_duration' => '3 jours',
            'parent_notified' => 1,
            'notification_sent' => 1,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[4]['id'],
            'incident_type' => 'MINOR',
            'description' => 'Non-respect du code vestimentaire de l\'établissement.',
            'sanction' => 'Avertissement et obligation de respecter le règlement',
            'incident_date' => '2025-08-25',
            'incident_time' => '08:05:00',
            'location' => 'Entrée de l\'établissement',
            'witnesses' => 'Surveillant d\'entrée',
            'sanction_duration' => '1 jour',
            'parent_notified' => 0,
            'notification_sent' => 0,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[1]['id'],
            'incident_type' => 'MAJOR',
            'description' => 'Tricherie lors d\'un contrôle de français avec utilisation de documents non autorisés.',
            'sanction' => 'Zéro au contrôle, retenue de 4 heures et avertissement écrit',
            'incident_date' => '2025-08-26',
            'incident_time' => '09:45:00',
            'location' => 'Salle de classe CP B',
            'witnesses' => 'M. Dubois, enseignant',
            'sanction_duration' => '4 heures',
            'parent_notified' => 1,
            'notification_sent' => 1,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ],
        [
            'student_id' => $students[0]['id'],
            'incident_type' => 'MINOR',
            'description' => 'Bavardage excessif en classe malgré les rappels à l\'ordre.',
            'sanction' => 'Changement de place et avertissement',
            'incident_date' => '2025-08-27',
            'incident_time' => '11:30:00',
            'location' => 'Salle de classe CP A',
            'witnesses' => 'Mme Martin, enseignante',
            'sanction_duration' => '1 cours',
            'parent_notified' => 0,
            'notification_sent' => 0,
            'recorded_by' => 1,
            'academic_year' => '2024-2025'
        ]
    ];

    $insertedCount = 0;
    foreach ($incidents as $incident) {
        $stmt = $pdo->prepare("
            INSERT INTO discipline_incidents (
                student_id, incident_type, description, sanction, incident_date, incident_time,
                location, witnesses, sanction_duration, parent_notified, notification_sent,
                recorded_by, academic_year
            ) VALUES (
                :student_id, :incident_type, :description, :sanction, :incident_date, :incident_time,
                :location, :witnesses, :sanction_duration, :parent_notified, :notification_sent,
                :recorded_by, :academic_year
            )
        ");
        $stmt->execute($incident);
        $insertedCount++;
        
        // Trouver le nom de l'élève
        $studentName = '';
        foreach ($students as $student) {
            if ($student['id'] == $incident['student_id']) {
                $studentName = $student['first_name'] . ' ' . $student['last_name'];
                break;
            }
        }
        
        echo "   ✅ Incident ajouté pour {$studentName} : {$incident['incident_type']} ({$incident['incident_date']})\n";
    }

    echo "\n🎉 {$insertedCount} incidents disciplinaires de test ajoutés avec succès.\n";
    
    // Vérifier le résultat
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM discipline_incidents");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 Total d'incidents dans la base : {$total}\n";
    
    // Afficher un aperçu
    echo "\n📋 Aperçu des incidents ajoutés :\n";
    $stmt = $pdo->query("
        SELECT d.*, s.first_name, s.last_name 
        FROM discipline_incidents d 
        JOIN students s ON d.student_id = s.id 
        ORDER BY d.incident_date DESC 
        LIMIT 5
    ");
    $recentIncidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recentIncidents as $incident) {
        $parentStatus = $incident['parent_notified'] ? 'Parents notifiés' : 'Parents non notifiés';
        echo "   - {$incident['first_name']} {$incident['last_name']} : {$incident['incident_type']} ({$incident['incident_date']}) - {$parentStatus}\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Script terminé avec succès !\n";
?>
