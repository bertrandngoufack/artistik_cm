<?php
/**
 * Script pour vérifier la structure de la base de données
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier la structure de la table students
    echo "\n📚 Structure de la table 'students' :\n";
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']} : {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // Vérifier la structure de la table fee_types
    echo "\n💰 Structure de la table 'fee_types' :\n";
    $stmt = $pdo->query("DESCRIBE fee_types");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']} : {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // Vérifier la structure de la table payments
    echo "\n💳 Structure de la table 'payments' :\n";
    $stmt = $pdo->query("DESCRIBE payments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']} : {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // Vérifier les données existantes
    echo "\n📊 Données existantes :\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Élèves : {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fee_types");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Types de frais : {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Paiements : {$result['count']}\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>


