<?php
/**
 * Script de création de données d'exemple complètes pour KISSAI SCHOOL
 * Génère des données cohérentes pour tous les modules
 */

echo "🎓 CRÉATION DE DONNÉES D'EXEMPLE COMPLÈTES - KISSAI SCHOOL\n";
echo "========================================================\n\n";

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

// 1. CRÉATION DES CYCLES ÉDUCATIFS
echo "📚 1. CRÉATION DES CYCLES ÉDUCATIFS\n";
echo "----------------------------------\n";

$cycles = [
    ['name' => 'Maternelle', 'code' => 'MAT', 'description' => 'Cycle de la maternelle'],
    ['name' => 'Primaire', 'code' => 'PRI', 'description' => 'Cycle du primaire'],
    ['name' => 'Secondaire', 'code' => 'SEC', 'description' => 'Cycle du secondaire']
];

foreach ($cycles as $cycle) {
    $sql = "INSERT INTO cycles (name, code, description) VALUES ('{$cycle['name']}', '{$cycle['code']}', '{$cycle['description']}')";
    executeQuery($pdo, $sql, "Ajout du cycle {$cycle['name']}");
}

// 2. CRÉATION DES NIVEAUX
echo "\n📖 2. CRÉATION DES NIVEAUX\n";
echo "--------------------------\n";

$levels = [
    ['name' => 'Petite Section', 'code' => 'PS', 'cycle_id' => 1, 'description' => 'Petite Section Maternelle'],
    ['name' => 'Moyenne Section', 'code' => 'MS', 'cycle_id' => 1, 'description' => 'Moyenne Section Maternelle'],
    ['name' => 'Grande Section', 'code' => 'GS', 'cycle_id' => 1, 'description' => 'Grande Section Maternelle'],
    ['name' => 'CP', 'code' => 'CP', 'cycle_id' => 2, 'description' => 'Cours Préparatoire'],
    ['name' => 'CE1', 'code' => 'CE1', 'cycle_id' => 2, 'description' => 'Cours Élémentaire 1'],
    ['name' => 'CE2', 'code' => 'CE2', 'cycle_id' => 2, 'description' => 'Cours Élémentaire 2'],
    ['name' => 'CM1', 'code' => 'CM1', 'cycle_id' => 2, 'description' => 'Cours Moyen 1'],
    ['name' => 'CM2', 'code' => 'CM2', 'cycle_id' => 2, 'description' => 'Cours Moyen 2'],
    ['name' => '6ème', 'code' => '6E', 'cycle_id' => 3, 'description' => 'Sixième'],
    ['name' => '5ème', 'code' => '5E', 'cycle_id' => 3, 'description' => 'Cinquième'],
    ['name' => '4ème', 'code' => '4E', 'cycle_id' => 3, 'description' => 'Quatrième'],
    ['name' => '3ème', 'code' => '3E', 'cycle_id' => 3, 'description' => 'Troisième']
];

foreach ($levels as $level) {
    $sql = "INSERT INTO levels (name, code, cycle_id, description) VALUES ('{$level['name']}', '{$level['code']}', {$level['cycle_id']}, '{$level['description']}')";
    executeQuery($pdo, $sql, "Ajout du niveau {$level['name']}");
}

// 3. CRÉATION DES MATIÈRES
echo "\n📝 3. CRÉATION DES MATIÈRES\n";
echo "---------------------------\n";

$subjects = [
    ['name' => 'Mathématiques', 'code' => 'MATH', 'description' => 'Mathématiques', 'coefficient' => 4],
    ['name' => 'Français', 'code' => 'FR', 'description' => 'Langue française', 'coefficient' => 4],
    ['name' => 'Anglais', 'code' => 'EN', 'description' => 'Langue anglaise', 'coefficient' => 2],
    ['name' => 'Histoire-Géographie', 'code' => 'HG', 'description' => 'Histoire et Géographie', 'coefficient' => 2],
    ['name' => 'Sciences', 'code' => 'SC', 'description' => 'Sciences naturelles', 'coefficient' => 2],
    ['name' => 'Éducation physique', 'code' => 'EPS', 'description' => 'Éducation physique et sportive', 'coefficient' => 1],
    ['name' => 'Arts plastiques', 'code' => 'ART', 'description' => 'Arts plastiques', 'coefficient' => 1],
    ['name' => 'Informatique', 'code' => 'INFO', 'description' => 'Informatique', 'coefficient' => 1]
];

foreach ($subjects as $subject) {
    $sql = "INSERT INTO subjects (name, code, description, coefficient) VALUES ('{$subject['name']}', '{$subject['code']}', '{$subject['description']}', {$subject['coefficient']})";
    executeQuery($pdo, $sql, "Ajout de la matière {$subject['name']}");
}

// 4. CRÉATION DES TYPES DE FRAIS
echo "\n💰 4. CRÉATION DES TYPES DE FRAIS\n";
echo "--------------------------------\n";

$feeTypes = [
    ['name' => 'Frais de scolarité', 'amount' => 150000, 'frequency' => 'YEARLY', 'description' => 'Frais de scolarité annuels'],
    ['name' => 'Frais d\'inscription', 'amount' => 50000, 'frequency' => 'YEARLY', 'description' => 'Frais d\'inscription'],
    ['name' => 'Frais de cantine', 'amount' => 25000, 'frequency' => 'MONTHLY', 'description' => 'Frais de restauration'],
    ['name' => 'Frais de transport', 'amount' => 30000, 'frequency' => 'MONTHLY', 'description' => 'Transport scolaire'],
    ['name' => 'Frais de laboratoire', 'amount' => 15000, 'frequency' => 'YEARLY', 'description' => 'Frais de laboratoire']
];

foreach ($feeTypes as $fee) {
    $sql = "INSERT INTO fee_types (name, amount, frequency, description) VALUES ('{$fee['name']}', {$fee['amount']}, '{$fee['frequency']}', '{$fee['description']}')";
    executeQuery($pdo, $sql, "Ajout du type de frais {$fee['name']}");
}

// 5. CRÉATION DES CLASSES
echo "\n🏫 5. CRÉATION DES CLASSES\n";
echo "-------------------------\n";

$classes = [
    ['name' => 'CP A', 'code' => 'CP-A', 'level_id' => 4, 'teacher_id' => 1, 'academic_year' => '2024-2025', 'capacity' => 30],
    ['name' => 'CP B', 'code' => 'CP-B', 'level_id' => 4, 'teacher_id' => 2, 'academic_year' => '2024-2025', 'capacity' => 28],
    ['name' => 'CE1 A', 'code' => 'CE1-A', 'level_id' => 5, 'teacher_id' => 3, 'academic_year' => '2024-2025', 'capacity' => 32],
    ['name' => 'CE1 B', 'code' => 'CE1-B', 'level_id' => 5, 'teacher_id' => 4, 'academic_year' => '2024-2025', 'capacity' => 30],
    ['name' => 'CE2 A', 'code' => 'CE2-A', 'level_id' => 6, 'teacher_id' => 5, 'academic_year' => '2024-2025', 'capacity' => 29],
    ['name' => 'CM1 A', 'code' => 'CM1-A', 'level_id' => 7, 'teacher_id' => 6, 'academic_year' => '2024-2025', 'capacity' => 31],
    ['name' => 'CM2 A', 'code' => 'CM2-A', 'level_id' => 8, 'teacher_id' => 7, 'academic_year' => '2024-2025', 'capacity' => 30],
    ['name' => '6ème A', 'code' => '6E-A', 'level_id' => 9, 'teacher_id' => 8, 'academic_year' => '2024-2025', 'capacity' => 35],
    ['name' => '5ème A', 'code' => '5E-A', 'level_id' => 10, 'teacher_id' => 1, 'academic_year' => '2024-2025', 'capacity' => 33],
    ['name' => '4ème A', 'code' => '4E-A', 'level_id' => 11, 'teacher_id' => 2, 'academic_year' => '2024-2025', 'capacity' => 34],
    ['name' => '3ème A', 'code' => '3E-A', 'level_id' => 12, 'teacher_id' => 3, 'academic_year' => '2024-2025', 'capacity' => 32]
];

foreach ($classes as $class) {
    $sql = "INSERT INTO classes (name, code, level_id, teacher_id, academic_year, capacity) VALUES ('{$class['name']}', '{$class['code']}', {$class['level_id']}, {$class['teacher_id']}, '{$class['academic_year']}', {$class['capacity']})";
    executeQuery($pdo, $sql, "Ajout de la classe {$class['name']}");
}

echo "\n🎉 CRÉATION DES DONNÉES DE BASE TERMINÉE !\n";
echo "==========================================\n";
echo "✅ Cycles éducatifs créés\n";
echo "✅ Niveaux créés\n";
echo "✅ Matières créées\n";
echo "✅ Types de frais créés\n";
echo "✅ Classes créées\n\n";

echo "🚀 L'application KISSAI SCHOOL est prête avec des données cohérentes !\n";
?>


