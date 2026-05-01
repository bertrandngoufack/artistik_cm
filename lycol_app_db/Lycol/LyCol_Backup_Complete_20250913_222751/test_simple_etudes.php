<?php
// Test simple des modèles Études

// Connexion à la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion à la base de données réussie\n\n";
} catch (PDOException $e) {
    echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
    exit;
}

echo "=== TEST DES DONNÉES ÉTUDES ===\n\n";

// Test des classes
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM classes WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Classes actives: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur classes: " . $e->getMessage() . "\n";
}

// Test des matières
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM subjects WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Matières actives: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur matières: " . $e->getMessage() . "\n";
}

// Test des cycles
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cycles WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Cycles actifs: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur cycles: " . $e->getMessage() . "\n";
}

// Test des emplois du temps
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM timetables WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Emplois du temps actifs: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur emplois du temps: " . $e->getMessage() . "\n";
}

// Test des assignations
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM teacher_assignments WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Assignations actives: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur assignations: " . $e->getMessage() . "\n";
}

// Test des enseignants
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Enseignants actifs: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "✗ Erreur enseignants: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
?>


