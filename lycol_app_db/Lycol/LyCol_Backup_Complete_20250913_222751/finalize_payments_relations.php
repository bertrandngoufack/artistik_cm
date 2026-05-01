<?php
/**
 * Script de finalisation des paiements et relations entre modules
 * Gère les paiements en plusieurs tranches et les relations cohérentes
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage() . "\n");
}

echo "\n🚀 Finalisation des paiements et relations\n";
echo "========================================\n\n";

// 1. Paiements simples (ÉCONOMAT)
echo "1. Paiements simples (ÉCONOMAT)...\n";

// Créer les paiements pour les élèves
$students = $pdo->query("SELECT id, matricule, first_name, last_name FROM students LIMIT 10")->fetchAll();
$feeTypes = $pdo->query("SELECT id, name, amount FROM fee_types")->fetchAll();

foreach ($students as $student) {
    foreach ($feeTypes as $feeType) {
        // Créer le paiement
        $amountPaid = rand(0, $feeType['amount']); // Montant payé aléatoire
        $paymentDate = date('Y-m-d', strtotime("-" . rand(1, 30) . " days"));
        $paymentMethod = ['CASH', 'BANK_TRANSFER', 'MOBILE_MONEY'][array_rand(['CASH', 'BANK_TRANSFER', 'MOBILE_MONEY'])];
        
        $pdo->exec("INSERT IGNORE INTO payments (student_id, fee_type_id, amount_paid, payment_date, payment_method, reference_number, academic_year, notes) VALUES
        ({$student['id']}, {$feeType['id']}, $amountPaid, '$paymentDate', '$paymentMethod', 'PAY-{$student['matricule']}-{$feeType['id']}', '2024-2025', 'Paiement automatique')");
    }
}
echo "✅ Paiements créés\n\n";

// 2. Notes et évaluations (EXAMENS)
echo "2. Notes et évaluations (EXAMENS)...\n";

// Créer des notes pour les examens existants
$exams = $pdo->query("SELECT id, name FROM exams LIMIT 5")->fetchAll();
$students = $pdo->query("SELECT id, first_name, last_name FROM students LIMIT 10")->fetchAll();

foreach ($exams as $exam) {
    foreach ($students as $student) {
        $score = rand(8, 20); // Note entre 8 et 20
        $subjectId = rand(1, 5); // Subject ID aléatoire
        $pdo->exec("INSERT IGNORE INTO grades (exam_id, student_id, subject_id, marks_obtained, remarks) VALUES
        ({$exam['id']}, {$student['id']}, $subjectId, $score, 'Note enregistrée automatiquement')");
    }
}
echo "✅ Notes et évaluations créées\n\n";

// 3. Absences (SCOLARITÉ)
echo "3. Absences (SCOLARITÉ)...\n";

// Créer des absences pour quelques élèves
$students = $pdo->query("SELECT id, first_name, last_name FROM students LIMIT 8")->fetchAll();

foreach ($students as $student) {
    // Créer 1-3 absences par élève
    $absenceCount = rand(1, 3);
    for ($i = 0; $i < $absenceCount; $i++) {
        $absenceDate = date('Y-m-d', strtotime("-" . rand(1, 30) . " days"));
        $isJustified = rand(0, 1);
        $justification = $isJustified ? 'Certificat médical' : null;
        
        $pdo->exec("INSERT IGNORE INTO absences (student_id, date, justified, reason, created_by) VALUES
({$student['id']}, '$absenceDate', $isJustified, " . ($justification ? "'$justification'" : "NULL") . ", 1)");
    }
}
echo "✅ Absences créées\n\n";

// 4. Incidents disciplinaires (SCOLARITÉ)
echo "4. Incidents disciplinaires (SCOLARITÉ)...\n";

$incidentTypes = ['Retard', 'Bavardage', 'Absence non justifiée', 'Comportement inapproprié', 'Non-respect du règlement'];
$sanctions = ['WARNING', 'SUSPENSION', 'EXPULSION', 'OTHER'];

foreach ($students as $student) {
    // Créer 0-2 incidents par élève
    $incidentCount = rand(0, 2);
    for ($i = 0; $i < $incidentCount; $i++) {
        $incidentDate = date('Y-m-d', strtotime("-" . rand(1, 60) . " days"));
        $incidentType = $incidentTypes[array_rand($incidentTypes)];
        $sanction = $sanctions[array_rand($sanctions)];
        $description = "Incident: $incidentType - Sanction: $sanction";
        
        $pdo->exec("INSERT IGNORE INTO discipline (student_id, incident_date, incident_description, sanction_type, sanction_details, created_by) VALUES
({$student['id']}, '$incidentDate', '$description', '$sanction', 'Sanction appliquée', 1)");
    }
}
echo "✅ Incidents disciplinaires créés\n\n";

// 5. Emprunts de livres (BIBLIOTHÈQUE)
echo "5. Emprunts de livres (BIBLIOTHÈQUE)...\n";

$books = $pdo->query("SELECT id, title FROM books LIMIT 5")->fetchAll();

foreach ($students as $student) {
    // Créer 0-2 emprunts par élève
    $loanCount = rand(0, 2);
    for ($i = 0; $i < $loanCount; $i++) {
        $book = $books[array_rand($books)];
        $loanDate = date('Y-m-d', strtotime("-" . rand(1, 15) . " days"));
        $dueDate = date('Y-m-d', strtotime("+15 days", strtotime($loanDate)));
        $isReturned = rand(0, 1);
        $returnDate = $isReturned ? date('Y-m-d', strtotime("+" . rand(1, 14) . " days", strtotime($loanDate))) : null;
        
        $pdo->exec("INSERT IGNORE INTO book_loans (book_id, member_id, member_type, loan_date, due_date, return_date, status) VALUES
({$book['id']}, {$student['id']}, 'STUDENT', '$loanDate', '$dueDate', " . ($returnDate ? "'$returnDate'" : "NULL") . ", " . ($isReturned ? "'RETURNED'" : "'BORROWED'") . ")");
    }
}
echo "✅ Emprunts de livres créés\n\n";

// 6. Messages envoyés (MESSAGERIE)
echo "6. Messages envoyés (MESSAGERIE)...\n";

$templates = $pdo->query("SELECT id, name FROM message_templates")->fetchAll();

foreach ($students as $student) {
    // Créer 1-2 messages par élève
    $messageCount = rand(1, 2);
    for ($i = 0; $i < $messageCount; $i++) {
        $sendDate = date('Y-m-d H:i:s', strtotime("-" . rand(1, 7) . " days"));
        $status = rand(0, 1) ? 'SENT' : 'DRAFT';
        
        $pdo->exec("INSERT IGNORE INTO messages (title, content, recipient_type, recipient_ids, sender_id, status, sent_at) VALUES
        ('Message automatique', 'Contenu du message pour {$student['first_name']}', 'SPECIFIC', '{$student['id']}', 1, '$status', '$sendDate')");
    }
}
echo "✅ Messages créés\n\n";

// 7. Mise à jour des spécialisations des enseignants (ENSEIGNANTS)
echo "7. Mise à jour des spécialisations des enseignants (ENSEIGNANTS)...\n";

$teachers = $pdo->query("SELECT id, first_name, last_name FROM teachers LIMIT 10")->fetchAll();
$subjects = $pdo->query("SELECT id, name FROM subjects LIMIT 5")->fetchAll();

foreach ($teachers as $teacher) {
    // Assigner 1-2 matières par enseignant
    $subjectCount = rand(1, 2);
    $teacherSubjects = array_slice($subjects, 0, $subjectCount);
    $specializations = [];
    
    foreach ($teacherSubjects as $subject) {
        $specializations[] = $subject['name'];
    }
    
    $specialization = implode(', ', $specializations);
    $pdo->exec("UPDATE teachers SET specialization = '$specialization' WHERE id = {$teacher['id']}");
}
echo "✅ Spécialisations des enseignants mises à jour\n\n";

echo "🎉 Finalisation des paiements et relations terminée !\n";
echo "Tous les modules sont maintenant interconnectés avec des données cohérentes.\n";
?>
