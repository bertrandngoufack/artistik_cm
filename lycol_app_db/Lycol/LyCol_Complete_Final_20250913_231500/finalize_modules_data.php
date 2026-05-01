<?php
/**
 * Script de finalisation des données des modules KISSAI SCHOOL
 * Crée des données cohérentes pour tous les modules
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

echo "\n🚀 Finalisation des données des modules\n";
echo "=====================================\n\n";

// 1. Types de frais (ÉCONOMAT)
echo "1. Types de frais (ÉCONOMAT)...\n";
$pdo->exec("INSERT IGNORE INTO fee_types (name, description, amount, frequency, is_active) VALUES
('Frais d\'inscription', 'Frais d\'inscription annuels', 50000, 'YEARLY', 1),
('Frais de scolarité', 'Frais de scolarité mensuels', 25000, 'MONTHLY', 1),
('Frais de cantine', 'Frais de cantine mensuels', 15000, 'MONTHLY', 1),
('Frais de transport', 'Frais de transport scolaire', 20000, 'MONTHLY', 1),
('Frais de laboratoire', 'Frais pour utilisation des laboratoires', 10000, 'YEARLY', 1),
('Frais de bibliothèque', 'Frais d\'accès à la bibliothèque', 5000, 'YEARLY', 1),
('Frais d\'uniforme', 'Uniforme scolaire', 15000, 'YEARLY', 1),
('Frais d\'examen', 'Frais d\'examen de fin d\'année', 8000, 'YEARLY', 1)
");
echo "✅ Types de frais créés\n\n";

// 2. Classes (ÉTUDES)
echo "2. Classes (ÉTUDES)...\n";
$pdo->exec("INSERT IGNORE INTO classes (name, code, level_id, teacher_id, academic_year, capacity) VALUES
('CP A', 'CPA', 4, 1, '2024-2025', 35),
('CP B', 'CPB', 4, 2, '2024-2025', 32),
('CE1 A', 'CE1A', 5, 3, '2024-2025', 30),
('CE1 B', 'CE1B', 5, 4, '2024-2025', 28),
('CE2 A', 'CE2A', 6, 5, '2024-2025', 33),
('CE2 B', 'CE2B', 6, 6, '2024-2025', 31),
('CM1 A', 'CM1A', 7, 7, '2024-2025', 29),
('CM1 B', 'CM1B', 7, 8, '2024-2025', 27),
('CM2 A', 'CM2A', 8, 9, '2024-2025', 34),
('CM2 B', 'CM2B', 8, 10, '2024-2025', 30),
('6ème A', '6A', 9, 11, '2024-2025', 40),
('6ème B', '6B', 9, 12, '2024-2025', 38),
('5ème A', '5A', 10, 13, '2024-2025', 36),
('5ème B', '5B', 10, 14, '2024-2025', 35),
('4ème A', '4A', 11, 15, '2024-2025', 37),
('4ème B', '4B', 11, 16, '2024-2025', 34),
('3ème A', '3A', 12, 17, '2024-2025', 39),
('3ème B', '3B', 12, 18, '2024-2025', 36)
");
echo "✅ Classes créées\n\n";

// 3. Élèves (SCOLARITÉ)
echo "3. Élèves (SCOLARITÉ)...\n";
$pdo->exec("INSERT IGNORE INTO students (matricule, first_name, last_name, date_of_birth, gender, current_class_id, admission_date, parent_phone, parent_email, address, parent_name, status) VALUES
('2024001', 'Amina', 'Diallo', '2018-03-15', 'F', 1, '2024-09-01', '+237 690 123 456', 'parent1@email.com', 'Douala, Akwa', 'M. Diallo', 'ACTIVE'),
('2024002', 'Kévin', 'Tchokouani', '2018-05-22', 'M', 1, '2024-09-01', '+237 691 234 567', 'parent2@email.com', 'Douala, Deido', 'M. Tchokouani', 'ACTIVE'),
('2024003', 'Fatou', 'Ndiaye', '2018-01-10', 'F', 1, '2024-09-01', '+237 692 345 678', 'parent3@email.com', 'Douala, Bali', 'Mme Ndiaye', 'ACTIVE'),
('2024004', 'Mohamed', 'Bello', '2018-07-08', 'M', 2, '2024-09-01', '+237 693 456 789', 'parent4@email.com', 'Douala, Bonamoussadi', 'M. Bello', 'ACTIVE'),
('2024005', 'Sarah', 'Johnson', '2018-11-30', 'F', 2, '2024-09-01', '+237 694 567 890', 'parent5@email.com', 'Douala, Akwa', 'M. Johnson', 'ACTIVE'),
('2024006', 'David', 'Mvondo', '2017-09-14', 'M', 3, '2024-09-01', '+237 695 678 901', 'parent6@email.com', 'Douala, Deido', 'M. Mvondo', 'ACTIVE'),
('2024007', 'Marie', 'Ngono', '2017-12-03', 'F', 3, '2024-09-01', '+237 696 789 012', 'parent7@email.com', 'Douala, Bali', 'Mme Ngono', 'ACTIVE'),
('2024008', 'Pierre', 'Essomba', '2017-04-18', 'M', 4, '2024-09-01', '+237 697 890 123', 'parent8@email.com', 'Douala, Bonamoussadi', 'M. Essomba', 'ACTIVE'),
('2024009', 'Claire', 'Mvogo', '2017-06-25', 'F', 4, '2024-09-01', '+237 698 901 234', 'parent9@email.com', 'Douala, Akwa', 'Mme Mvogo', 'ACTIVE'),
('2024010', 'Thomas', 'Etoa', '2016-08-12', 'M', 5, '2024-09-01', '+237 699 012 345', 'parent10@email.com', 'Douala, Deido', 'M. Etoa', 'ACTIVE'),
('2024011', 'Sophie', 'Nguemo', '2016-10-07', 'F', 5, '2024-09-01', '+237 700 123 456', 'parent11@email.com', 'Douala, Bali', 'Mme Nguemo', 'ACTIVE'),
('2024012', 'Lucas', 'Mbarga', '2016-02-28', 'M', 6, '2024-09-01', '+237 701 234 567', 'parent12@email.com', 'Douala, Bonamoussadi', 'M. Mbarga', 'ACTIVE'),
('2024013', 'Emma', 'Tchakounte', '2015-12-15', 'F', 7, '2024-09-01', '+237 702 345 678', 'parent13@email.com', 'Douala, Akwa', 'Mme Tchakounte', 'ACTIVE'),
('2024014', 'Hugo', 'Ndong', '2015-03-20', 'M', 7, '2024-09-01', '+237 703 456 789', 'parent14@email.com', 'Douala, Deido', 'M. Ndong', 'ACTIVE'),
('2024015', 'Léa', 'Mvondo', '2015-07-11', 'F', 8, '2024-09-01', '+237 704 567 890', 'parent15@email.com', 'Douala, Bali', 'Mme Mvondo', 'ACTIVE'),
('2024016', 'Nathan', 'Essomba', '2014-11-05', 'M', 9, '2024-09-01', '+237 705 678 901', 'parent16@email.com', 'Douala, Bonamoussadi', 'M. Essomba', 'ACTIVE'),
('2024017', 'Jade', 'Ngono', '2014-01-18', 'F', 9, '2024-09-01', '+237 706 789 012', 'parent17@email.com', 'Douala, Akwa', 'Mme Ngono', 'ACTIVE'),
('2024018', 'Adam', 'Mvogo', '2014-05-30', 'M', 10, '2024-09-01', '+237 707 890 123', 'parent18@email.com', 'Douala, Deido', 'M. Mvogo', 'ACTIVE'),
('2024019', 'Zoé', 'Etoa', '2013-09-22', 'F', 10, '2024-09-01', '+237 708 901 234', 'parent19@email.com', 'Douala, Bali', 'Mme Etoa', 'ACTIVE'),
('2024020', 'Raphaël', 'Nguemo', '2013-12-08', 'M', 11, '2024-09-01', '+237 709 012 345', 'parent20@email.com', 'Douala, Bonamoussadi', 'M. Nguemo', 'ACTIVE')
");
echo "✅ Élèves créés\n\n";

// 4. Sessions d'examen (EXAMENS)
echo "4. Sessions d'examen (EXAMENS)...\n";
$pdo->exec("INSERT IGNORE INTO exams (name, class_id, exam_type, exam_date, total_marks, coefficient, status) VALUES
('1er Trimestre - CP A', 1, 'MIDTERM', '2024-10-15', 20.00, 1.00, 'COMPLETED'),
('2ème Trimestre - CP A', 1, 'MIDTERM', '2025-01-15', 20.00, 1.00, 'SCHEDULED'),
('3ème Trimestre - CP A', 1, 'MIDTERM', '2025-04-15', 20.00, 1.00, 'SCHEDULED'),
('Examen Final - CP A', 1, 'FINAL', '2025-06-01', 20.00, 2.00, 'SCHEDULED'),
('1er Trimestre - CE1 A', 3, 'MIDTERM', '2024-10-15', 20.00, 1.00, 'COMPLETED'),
('2ème Trimestre - CE1 A', 3, 'MIDTERM', '2025-01-15', 20.00, 1.00, 'SCHEDULED'),
('3ème Trimestre - CE1 A', 3, 'MIDTERM', '2025-04-15', 20.00, 1.00, 'SCHEDULED'),
('Examen Final - CE1 A', 3, 'FINAL', '2025-06-01', 20.00, 2.00, 'SCHEDULED')
");
echo "✅ Sessions d'examen créées\n\n";

// 5. Livres (BIBLIOTHÈQUE)
echo "5. Livres (BIBLIOTHÈQUE)...\n";
$pdo->exec("INSERT IGNORE INTO books (title, author, isbn, category, total_copies, available_copies, location) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupéry', '9782070612758', 'Littérature', 5, 5, 'Étagère A1'),
('Les Fables de La Fontaine', 'Jean de La Fontaine', '9782070403080', 'Littérature', 3, 3, 'Étagère A2'),
('Mathématiques CP', 'Collectif', '9782011172345', 'Scolaire', 15, 15, 'Étagère B1'),
('Français CE1', 'Collectif', '9782011172352', 'Scolaire', 12, 12, 'Étagère B2'),
('Histoire-Géographie 6ème', 'Collectif', '9782011172369', 'Scolaire', 8, 8, 'Étagère C1'),
('Sciences et Vie de la Terre', 'Collectif', '9782011172376', 'Scolaire', 6, 6, 'Étagère C2'),
('Physique-Chimie 4ème', 'Collectif', '9782011172383', 'Scolaire', 7, 7, 'Étagère C3'),
('Anglais 5ème', 'Collectif', '9782011172390', 'Scolaire', 10, 10, 'Étagère D1'),
('Dictionnaire Larousse', 'Collectif', '9782035928456', 'Référence', 2, 2, 'Étagère E1'),
('Atlas du Monde', 'Collectif', '9782035928463', 'Référence', 2, 2, 'Étagère E2')
");
echo "✅ Livres créés\n\n";

// 6. Messages (MESSAGERIE)
echo "6. Messages (MESSAGERIE)...\n";
$pdo->exec("INSERT IGNORE INTO message_templates (name, subject, content, variables, is_active) VALUES
('Bulletin trimestriel', 'Bulletin de notes - {student_name}', 'Bonjour {parent_name},\n\nVeuillez trouver ci-joint le bulletin de notes de {student_name} pour le {period}.\n\nCordialement,\nL\'équipe pédagogique', '[\"student_name\", \"parent_name\", \"period\"]', 1),
('Absence non justifiée', 'Absence de {student_name}', 'Bonjour {parent_name},\n\nNous vous informons que {student_name} a été absent(e) le {date} sans justification.\n\nMerci de nous fournir une justification.\n\nCordialement,\nL\'équipe pédagogique', '[\"student_name\", \"parent_name\", \"date\"]', 1),
('Rappel paiement', 'Rappel - Paiement des frais de scolarité', 'Bonjour {parent_name},\n\nNous vous rappelons que le paiement des frais de scolarité pour {student_name} est attendu.\n\nMontant restant : {amount} FCFA\n\nMerci de régulariser votre situation.\n\nCordialement,\nLe service comptabilité', '[\"parent_name\", \"student_name\", \"amount\"]', 1),
('Conseil de discipline', 'Conseil de discipline - {student_name}', 'Bonjour {parent_name},\n\nNous vous convoquons à un conseil de discipline concernant {student_name} le {date} à {time}.\n\nMotif : {reason}\n\nVotre présence est obligatoire.\n\nCordialement,\nLa direction', '[\"parent_name\", \"student_name\", \"date\", \"time\", \"reason\"]', 1),
('Félicitations', 'Félicitations - {student_name}', 'Bonjour {parent_name},\n\nNous avons le plaisir de vous féliciter pour les excellents résultats de {student_name}.\n\nContinuez ainsi !\n\nCordialement,\nL\'équipe pédagogique', '[\"parent_name\", \"student_name\"]', 1)
");
echo "✅ Modèles de messages créés\n\n";

echo "🎉 Données des modules créées avec succès !\n";
echo "Continuez avec le script suivant pour les données de paiement et les relations.\n";
?>
