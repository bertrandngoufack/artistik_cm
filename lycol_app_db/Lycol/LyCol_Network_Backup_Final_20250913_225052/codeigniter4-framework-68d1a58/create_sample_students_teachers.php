<?php
/**
 * Script de création d'élèves et enseignants d'exemple
 */

echo "👥 CRÉATION D'ÉLÈVES ET ENSEIGNANTS D'EXEMPLE\n";
echo "============================================\n\n";

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

// 1. CRÉATION DES ENSEIGNANTS
echo "👨‍🏫 1. CRÉATION DES ENSEIGNANTS\n";
echo "-----------------------------\n";

$teachers = [
    ['first_name' => 'Marie', 'last_name' => 'Dupont', 'email' => 'marie.dupont@kissai.edu', 'phone' => '+237 690 111 111', 'specialization' => 'Mathématiques', 'qualification' => 'Master en Mathématiques', 'hire_date' => '2020-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Jean', 'last_name' => 'Martin', 'email' => 'jean.martin@kissai.edu', 'phone' => '+237 690 222 222', 'specialization' => 'Français', 'qualification' => 'Master en Lettres', 'hire_date' => '2019-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Sophie', 'last_name' => 'Bernard', 'email' => 'sophie.bernard@kissai.edu', 'phone' => '+237 690 333 333', 'specialization' => 'Anglais', 'qualification' => 'Master en Anglais', 'hire_date' => '2021-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Pierre', 'last_name' => 'Petit', 'email' => 'pierre.petit@kissai.edu', 'phone' => '+237 690 444 444', 'specialization' => 'Histoire-Géographie', 'qualification' => 'Master en Histoire', 'hire_date' => '2018-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Claire', 'last_name' => 'Moreau', 'email' => 'claire.moreau@kissai.edu', 'phone' => '+237 690 555 555', 'specialization' => 'Sciences', 'qualification' => 'Master en Biologie', 'hire_date' => '2022-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Michel', 'last_name' => 'Leroy', 'email' => 'michel.leroy@kissai.edu', 'phone' => '+237 690 666 666', 'specialization' => 'Éducation physique', 'qualification' => 'Master en STAPS', 'hire_date' => '2020-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'Isabelle', 'last_name' => 'Roux', 'email' => 'isabelle.roux@kissai.edu', 'phone' => '+237 690 777 777', 'specialization' => 'Arts plastiques', 'qualification' => 'Master en Arts', 'hire_date' => '2021-09-01', 'status' => 'ACTIVE'],
    ['first_name' => 'François', 'last_name' => 'Simon', 'email' => 'francois.simon@kissai.edu', 'phone' => '+237 690 888 888', 'specialization' => 'Informatique', 'qualification' => 'Master en Informatique', 'hire_date' => '2023-09-01', 'status' => 'ACTIVE']
];

foreach ($teachers as $teacher) {
    $sql = "INSERT INTO teachers (first_name, last_name, email, phone, specialization, qualification, hire_date, status) VALUES ('{$teacher['first_name']}', '{$teacher['last_name']}', '{$teacher['email']}', '{$teacher['phone']}', '{$teacher['specialization']}', '{$teacher['qualification']}', '{$teacher['hire_date']}', '{$teacher['status']}')";
    executeQuery($pdo, $sql, "Ajout de l'enseignant {$teacher['first_name']} {$teacher['last_name']}");
}

// 2. CRÉATION DES ÉLÈVES
echo "\n👦 2. CRÉATION DES ÉLÈVES\n";
echo "------------------------\n";

$students = [
    // CP A
    ['matricule' => '2024CP001', 'first_name' => 'Lucas', 'last_name' => 'Dubois', 'date_of_birth' => '2018-03-15', 'gender' => 'M', 'current_class_id' => 1, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 001 001', 'parent_email' => 'parent1@email.com', 'address' => 'Douala, Akwa', 'parent_name' => 'M. Dubois', 'status' => 'ACTIVE'],
    ['matricule' => '2024CP002', 'first_name' => 'Emma', 'last_name' => 'Leroy', 'date_of_birth' => '2018-05-22', 'gender' => 'F', 'current_class_id' => 1, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 002 002', 'parent_email' => 'parent2@email.com', 'address' => 'Douala, Deido', 'parent_name' => 'Mme Leroy', 'status' => 'ACTIVE'],
    ['matricule' => '2024CP003', 'first_name' => 'Hugo', 'last_name' => 'Moreau', 'date_of_birth' => '2018-07-10', 'gender' => 'M', 'current_class_id' => 1, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 003 003', 'parent_email' => 'parent3@email.com', 'address' => 'Douala, Bali', 'parent_name' => 'M. Moreau', 'status' => 'ACTIVE'],
    
    // CE1 A
    ['matricule' => '2024CE1001', 'first_name' => 'Chloé', 'last_name' => 'Simon', 'date_of_birth' => '2017-02-18', 'gender' => 'F', 'current_class_id' => 3, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 004 004', 'parent_email' => 'parent4@email.com', 'address' => 'Douala, Bonanjo', 'parent_name' => 'Mme Simon', 'status' => 'ACTIVE'],
    ['matricule' => '2024CE1002', 'first_name' => 'Thomas', 'last_name' => 'Michel', 'date_of_birth' => '2017-04-25', 'gender' => 'M', 'current_class_id' => 3, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 005 005', 'parent_email' => 'parent5@email.com', 'address' => 'Douala, Akwa', 'parent_name' => 'M. Michel', 'status' => 'ACTIVE'],
    ['matricule' => '2024CE1003', 'first_name' => 'Léa', 'last_name' => 'Garcia', 'date_of_birth' => '2017-08-12', 'gender' => 'F', 'current_class_id' => 3, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 006 006', 'parent_email' => 'parent6@email.com', 'address' => 'Douala, Deido', 'parent_name' => 'Mme Garcia', 'status' => 'ACTIVE'],
    
    // CM1 A
    ['matricule' => '2024CM1001', 'first_name' => 'Jules', 'last_name' => 'David', 'date_of_birth' => '2015-01-30', 'gender' => 'M', 'current_class_id' => 6, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 007 007', 'parent_email' => 'parent7@email.com', 'address' => 'Douala, Bali', 'parent_name' => 'M. David', 'status' => 'ACTIVE'],
    ['matricule' => '2024CM1002', 'first_name' => 'Alice', 'last_name' => 'Bertrand', 'date_of_birth' => '2015-06-14', 'gender' => 'F', 'current_class_id' => 6, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 008 008', 'parent_email' => 'parent8@email.com', 'address' => 'Douala, Bonanjo', 'parent_name' => 'Mme Bertrand', 'status' => 'ACTIVE'],
    ['matricule' => '2024CM1003', 'first_name' => 'Louis', 'last_name' => 'Roux', 'date_of_birth' => '2015-09-08', 'gender' => 'M', 'current_class_id' => 6, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 009 009', 'parent_email' => 'parent9@email.com', 'address' => 'Douala, Akwa', 'parent_name' => 'M. Roux', 'status' => 'ACTIVE'],
    
    // 6ème A
    ['matricule' => '20246E001', 'first_name' => 'Eva', 'last_name' => 'Vincent', 'date_of_birth' => '2013-11-20', 'gender' => 'F', 'current_class_id' => 8, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 010 010', 'parent_email' => 'parent10@email.com', 'address' => 'Douala, Deido', 'parent_name' => 'M. Vincent', 'status' => 'ACTIVE'],
    ['matricule' => '20246E002', 'first_name' => 'Nathan', 'last_name' => 'Moulin', 'date_of_birth' => '2013-12-05', 'gender' => 'M', 'current_class_id' => 8, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 011 011', 'parent_email' => 'parent11@email.com', 'address' => 'Douala, Bali', 'parent_name' => 'Mme Moulin', 'status' => 'ACTIVE'],
    ['matricule' => '20246E003', 'first_name' => 'Jade', 'last_name' => 'Andre', 'date_of_birth' => '2013-03-18', 'gender' => 'F', 'current_class_id' => 8, 'admission_date' => '2024-09-01', 'parent_phone' => '+237 690 012 012', 'parent_email' => 'parent12@email.com', 'address' => 'Douala, Bonanjo', 'parent_name' => 'M. Andre', 'status' => 'ACTIVE']
];

foreach ($students as $student) {
    $sql = "INSERT INTO students (matricule, first_name, last_name, date_of_birth, gender, current_class_id, admission_date, parent_phone, parent_email, address, parent_name, status) VALUES ('{$student['matricule']}', '{$student['first_name']}', '{$student['last_name']}', '{$student['date_of_birth']}', '{$student['gender']}', {$student['current_class_id']}, '{$student['admission_date']}', '{$student['parent_phone']}', '{$student['parent_email']}', '{$student['address']}', '{$student['parent_name']}', '{$student['status']}')";
    executeQuery($pdo, $sql, "Ajout de l'élève {$student['first_name']} {$student['last_name']} ({$student['matricule']})");
}

echo "\n🎉 CRÉATION DES ÉLÈVES ET ENSEIGNANTS TERMINÉE !\n";
echo "===============================================\n";
echo "✅ 8 enseignants créés\n";
echo "✅ 12 élèves créés\n\n";

echo "🚀 Données d'exemple prêtes pour tous les modules !\n";
?>


