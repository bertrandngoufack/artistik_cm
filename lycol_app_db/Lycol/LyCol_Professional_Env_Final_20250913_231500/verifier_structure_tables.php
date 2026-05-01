<?php
/**
 * Script pour vérifier la structure réelle des tables
 */

$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 VÉRIFICATION DE LA STRUCTURE DES TABLES\n";
    echo "==========================================\n\n";
    
    // Vérifier la structure de la table students
    echo "📚 STRUCTURE DE LA TABLE students :\n";
    echo "-----------------------------------\n";
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    echo "\n📊 STRUCTURE DE LA TABLE classes :\n";
    echo "-----------------------------------\n";
    $stmt = $pdo->query("DESCRIBE classes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    echo "\n📝 STRUCTURE DE LA TABLE exams :\n";
    echo "--------------------------------\n";
    $stmt = $pdo->query("DESCRIBE exams");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    echo "\n📊 STRUCTURE DE LA TABLE grades :\n";
    echo "---------------------------------\n";
    $stmt = $pdo->query("DESCRIBE grades");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
    }
    
    // Vérifier les données réelles
    echo "\n📚 DONNÉES RÉELLES - ÉLÈVES :\n";
    echo "-----------------------------\n";
    $stmt = $pdo->query("SELECT * FROM students LIMIT 3");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($students)) {
        foreach ($students as $student) {
            echo "✅ Élève : " . json_encode($student) . "\n";
        }
    } else {
        echo "❌ Aucun élève trouvé\n";
    }
    
    echo "\n📊 DONNÉES RÉELLES - CLASSES :\n";
    echo "-----------------------------\n";
    $stmt = $pdo->query("SELECT * FROM classes LIMIT 3");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($classes)) {
        foreach ($classes as $class) {
            echo "✅ Classe : " . json_encode($class) . "\n";
        }
    } else {
        echo "❌ Aucune classe trouvée\n";
    }
    
    echo "\n📝 DONNÉES RÉELLES - EXAMENS :\n";
    echo "-----------------------------\n";
    $stmt = $pdo->query("SELECT * FROM exams LIMIT 3");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($exams)) {
        foreach ($exams as $exam) {
            echo "✅ Examen : " . json_encode($exam) . "\n";
        }
    } else {
        echo "❌ Aucun examen trouvé\n";
    }
    
    echo "\n📊 DONNÉES RÉELLES - NOTES :\n";
    echo "---------------------------\n";
    $stmt = $pdo->query("SELECT * FROM grades LIMIT 3");
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($grades)) {
        foreach ($grades as $grade) {
            echo "✅ Note : " . json_encode($grade) . "\n";
        }
    } else {
        echo "❌ Aucune note trouvée\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
}
?>









