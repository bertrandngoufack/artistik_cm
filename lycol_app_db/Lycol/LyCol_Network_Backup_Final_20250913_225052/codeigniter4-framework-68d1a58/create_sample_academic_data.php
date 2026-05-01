<?php
/**
 * Script de création de données académiques d'exemple
 */

echo "📚 CRÉATION DE DONNÉES ACADÉMIQUES D'EXEMPLE\n";
echo "===========================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données établie\n\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage() . "\n");
}

// Fonction pour exécuter une requête
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "✅ $description\n";
        return true;
    } catch (PDOException $e) {
        echo "❌ Erreur lors de $description : " . $e->getMessage() . "\n";
        return false;
    }
}

// 1. CRÉATION DES EXAMENS
echo "📝 1. CRÉATION DES EXAMENS\n";
echo "-------------------------\n";

$exams = [
    ['name' => 'Contrôle de Mathématiques - CP', 'class_id' => 1, 'exam_type' => 'CONTROLE', 'exam_date' => '2024-10-15', 'total_marks' => 20, 'coefficient' => 1, 'status' => 'COMPLETED'],
    ['name' => 'Contrôle de Français - CP', 'class_id' => 1, 'exam_type' => 'CONTROLE', 'exam_date' => '2024-10-20', 'total_marks' => 20, 'coefficient' => 1, 'status' => 'COMPLETED'],
    ['name' => 'Devoir de Mathématiques - CE1', 'class_id' => 3, 'exam_type' => 'DEVOIR', 'exam_date' => '2024-10-18', 'total_marks' => 20, 'coefficient' => 1, 'status' => 'COMPLETED'],
    ['name' => 'Examen de Sciences - CM1', 'class_id' => 6, 'exam_type' => 'EXAMEN', 'exam_date' => '2024-11-05', 'total_marks' => 20, 'coefficient' => 2, 'status' => 'COMPLETED'],
    ['name' => 'Contrôle d\'Anglais - 6ème', 'class_id' => 8, 'exam_type' => 'CONTROLE', 'exam_date' => '2024-10-25', 'total_marks' => 20, 'coefficient' => 1, 'status' => 'COMPLETED']
];

foreach ($exams as $exam) {
    $sql = "INSERT INTO exams (name, class_id, exam_type, exam_date, total_marks, coefficient, status) VALUES ('{$exam['name']}', {$exam['class_id']}, '{$exam['exam_type']}', '{$exam['exam_date']}', {$exam['total_marks']}, {$exam['coefficient']}, '{$exam['status']}')";
    executeQuery($pdo, $sql, "Ajout de l'examen {$exam['name']}");
}

// 2. CRÉATION DES NOTES
echo "\n📊 2. CRÉATION DES NOTES\n";
echo "-----------------------\n";

$grades = [
    // Notes pour l'examen de Mathématiques CP
    ['exam_id' => 1, 'student_id' => 1, 'subject_id' => 1, 'marks_obtained' => 18, 'remarks' => 'Excellent travail'],
    ['exam_id' => 1, 'student_id' => 2, 'subject_id' => 1, 'marks_obtained' => 15, 'remarks' => 'Bon travail'],
    ['exam_id' => 1, 'student_id' => 3, 'subject_id' => 1, 'marks_obtained' => 12, 'remarks' => 'Peut mieux faire'],
    
    // Notes pour l'examen de Français CP
    ['exam_id' => 2, 'student_id' => 1, 'subject_id' => 2, 'marks_obtained' => 16, 'remarks' => 'Très bien'],
    ['exam_id' => 2, 'student_id' => 2, 'subject_id' => 2, 'marks_obtained' => 19, 'remarks' => 'Excellent'],
    ['exam_id' => 2, 'student_id' => 3, 'subject_id' => 2, 'marks_obtained' => 14, 'remarks' => 'Bien'],
    
    // Notes pour l'examen de Mathématiques CE1
    ['exam_id' => 3, 'student_id' => 4, 'subject_id' => 1, 'marks_obtained' => 17, 'remarks' => 'Très bon travail'],
    ['exam_id' => 3, 'student_id' => 5, 'subject_id' => 1, 'marks_obtained' => 13, 'remarks' => 'Satisfaisant'],
    ['exam_id' => 3, 'student_id' => 6, 'subject_id' => 1, 'marks_obtained' => 20, 'remarks' => 'Parfait'],
    
    // Notes pour l'examen de Sciences CM1
    ['exam_id' => 4, 'student_id' => 7, 'subject_id' => 5, 'marks_obtained' => 16, 'remarks' => 'Bon travail'],
    ['exam_id' => 4, 'student_id' => 8, 'subject_id' => 5, 'marks_obtained' => 18, 'remarks' => 'Excellent'],
    ['exam_id' => 4, 'student_id' => 9, 'subject_id' => 5, 'marks_obtained' => 15, 'remarks' => 'Très bien'],
    
    // Notes pour l'examen d'Anglais 6ème
    ['exam_id' => 5, 'student_id' => 10, 'subject_id' => 3, 'marks_obtained' => 14, 'remarks' => 'Bien'],
    ['exam_id' => 5, 'student_id' => 11, 'subject_id' => 3, 'marks_obtained' => 17, 'remarks' => 'Très bien'],
    ['exam_id' => 5, 'student_id' => 12, 'subject_id' => 3, 'marks_obtained' => 19, 'remarks' => 'Excellent']
];

foreach ($grades as $grade) {
    $sql = "INSERT INTO grades (exam_id, student_id, subject_id, marks_obtained, remarks) VALUES ({$grade['exam_id']}, {$grade['student_id']}, {$grade['subject_id']}, {$grade['marks_obtained']}, '{$grade['remarks']}')";
    executeQuery($pdo, $sql, "Ajout de la note {$grade['marks_obtained']}/20 pour l'élève {$grade['student_id']}");
}

// 3. CRÉATION DES PAIEMENTS
echo "\n💰 3. CRÉATION DES PAIEMENTS\n";
echo "---------------------------\n";

$payments = [
    ['student_id' => 1, 'fee_type_id' => 1, 'amount_paid' => 150000, 'payment_date' => '2024-09-15', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-001', 'academic_year' => '2024-2025', 'notes' => 'Paiement complet', 'status' => 'PAID'],
    ['student_id' => 2, 'fee_type_id' => 1, 'amount_paid' => 75000, 'payment_date' => '2024-09-20', 'payment_method' => 'TRANSFER', 'reference_number' => 'PAY-2024-002', 'academic_year' => '2024-2025', 'notes' => 'Première tranche', 'status' => 'PAID'],
    ['student_id' => 3, 'fee_type_id' => 1, 'amount_paid' => 150000, 'payment_date' => '2024-09-25', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-003', 'academic_year' => '2024-2025', 'notes' => 'Paiement complet', 'status' => 'PAID'],
    ['student_id' => 4, 'fee_type_id' => 1, 'amount_paid' => 150000, 'payment_date' => '2024-10-01', 'payment_method' => 'CARD', 'reference_number' => 'PAY-2024-004', 'academic_year' => '2024-2025', 'notes' => 'Paiement complet', 'status' => 'PAID'],
    ['student_id' => 5, 'fee_type_id' => 1, 'amount_paid' => 50000, 'payment_date' => '2024-10-05', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-005', 'academic_year' => '2024-2025', 'notes' => 'Frais d\'inscription', 'status' => 'PAID'],
    ['student_id' => 6, 'fee_type_id' => 3, 'amount_paid' => 25000, 'payment_date' => '2024-10-10', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-006', 'academic_year' => '2024-2025', 'notes' => 'Frais de cantine octobre', 'status' => 'PAID'],
    ['student_id' => 7, 'fee_type_id' => 1, 'amount_paid' => 150000, 'payment_date' => '2024-10-15', 'payment_method' => 'TRANSFER', 'reference_number' => 'PAY-2024-007', 'academic_year' => '2024-2025', 'notes' => 'Paiement complet', 'status' => 'PAID'],
    ['student_id' => 8, 'fee_type_id' => 4, 'amount_paid' => 30000, 'payment_date' => '2024-10-20', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-008', 'academic_year' => '2024-2025', 'notes' => 'Transport novembre', 'status' => 'PAID'],
    ['student_id' => 9, 'fee_type_id' => 1, 'amount_paid' => 75000, 'payment_date' => '2024-11-01', 'payment_method' => 'CASH', 'reference_number' => 'PAY-2024-009', 'academic_year' => '2024-2025', 'notes' => 'Deuxième tranche', 'status' => 'PAID'],
    ['student_id' => 10, 'fee_type_id' => 1, 'amount_paid' => 150000, 'payment_date' => '2024-11-05', 'payment_method' => 'CARD', 'reference_number' => 'PAY-2024-010', 'academic_year' => '2024-2025', 'notes' => 'Paiement complet', 'status' => 'PAID']
];

foreach ($payments as $payment) {
    $sql = "INSERT INTO payments (student_id, fee_type_id, amount_paid, payment_date, payment_method, reference_number, academic_year, notes, status) VALUES ({$payment['student_id']}, {$payment['fee_type_id']}, {$payment['amount_paid']}, '{$payment['payment_date']}', '{$payment['payment_method']}', '{$payment['reference_number']}', '{$payment['academic_year']}', '{$payment['notes']}', '{$payment['status']}')";
    executeQuery($pdo, $sql, "Ajout du paiement {$payment['reference_number']} - {$payment['amount_paid']} FCFA");
}

// 4. CRÉATION DES ABSENCES
echo "\n📅 4. CRÉATION DES ABSENCES\n";
echo "---------------------------\n";

$absences = [
    ['student_id' => 1, 'date' => '2024-10-16', 'reason' => 'Maladie', 'justified' => 1, 'created_by' => 1],
    ['student_id' => 2, 'date' => '2024-10-17', 'reason' => 'Rendez-vous médical', 'justified' => 1, 'created_by' => 1],
    ['student_id' => 3, 'date' => '2024-10-18', 'reason' => 'Absence non justifiée', 'justified' => 0, 'created_by' => 1],
    ['student_id' => 4, 'date' => '2024-10-19', 'reason' => 'Voyage familial', 'justified' => 1, 'created_by' => 1],
    ['student_id' => 5, 'date' => '2024-10-20', 'reason' => 'Absence non justifiée', 'justified' => 0, 'created_by' => 1],
    ['student_id' => 6, 'date' => '2024-10-21', 'reason' => 'Maladie', 'justified' => 1, 'created_by' => 1],
    ['student_id' => 7, 'date' => '2024-10-22', 'reason' => 'Absence non justifiée', 'justified' => 0, 'created_by' => 1],
    ['student_id' => 8, 'date' => '2024-10-23', 'reason' => 'Rendez-vous médical', 'justified' => 1, 'created_by' => 1]
];

foreach ($absences as $absence) {
    $sql = "INSERT INTO absences (student_id, date, reason, justified, created_by) VALUES ({$absence['student_id']}, '{$absence['date']}', '{$absence['reason']}', {$absence['justified']}, {$absence['created_by']})";
    executeQuery($pdo, $sql, "Ajout de l'absence pour l'élève {$absence['student_id']} le {$absence['date']}");
}

// 5. CRÉATION DES INCIDENTS DISCIPLINAIRES
echo "\n⚠️ 5. CRÉATION DES INCIDENTS DISCIPLINAIRES\n";
echo "-----------------------------------------\n";

$discipline = [
    ['student_id' => 3, 'incident_date' => '2024-10-18', 'description' => 'Bagarre dans la cour', 'sanction_type' => 'AVERTISSEMENT', 'sanction_details' => 'Avertissement oral', 'created_by' => 1],
    ['student_id' => 5, 'incident_date' => '2024-10-20', 'description' => 'Retard répété', 'sanction_type' => 'RETENUE', 'sanction_details' => 'Retenue de 2 heures', 'created_by' => 1],
    ['student_id' => 7, 'incident_date' => '2024-10-22', 'description' => 'Non-respect du règlement', 'sanction_type' => 'AVERTISSEMENT', 'sanction_details' => 'Avertissement écrit', 'created_by' => 1],
    ['student_id' => 9, 'incident_date' => '2024-10-25', 'description' => 'Tricherie lors d\'un contrôle', 'sanction_type' => 'EXCLUSION', 'sanction_details' => 'Exclusion temporaire de 3 jours', 'created_by' => 1]
];

foreach ($discipline as $incident) {
    $sql = "INSERT INTO discipline (student_id, incident_date, description, sanction_type, sanction_details, created_by) VALUES ({$incident['student_id']}, '{$incident['incident_date']}', '{$incident['description']}', '{$incident['sanction_type']}', '{$incident['sanction_details']}', {$incident['created_by']})";
    executeQuery($pdo, $sql, "Ajout de l'incident disciplinaire pour l'élève {$incident['student_id']}");
}

echo "\n🎉 CRÉATION DES DONNÉES ACADÉMIQUES TERMINÉE !\n";
echo "=============================================\n";
echo "✅ 5 examens créés\n";
echo "✅ 15 notes créées\n";
echo "✅ 10 paiements créés\n";
echo "✅ 8 absences créées\n";
echo "✅ 4 incidents disciplinaires créés\n\n";

echo "🚀 Données académiques complètes et cohérentes !\n";
?>


