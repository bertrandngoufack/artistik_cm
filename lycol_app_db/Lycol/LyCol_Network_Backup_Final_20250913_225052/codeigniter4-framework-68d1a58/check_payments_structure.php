<?php
/**
 * Script pour vérifier la structure exacte de la table payments
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
    
    // Vérifier la structure de la table payments
    echo "\n💳 Structure de la table 'payments' :\n";
    $stmt = $pdo->query("DESCRIBE payments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']} : {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    // Vérifier quelques enregistrements
    echo "\n📋 Quelques enregistrements de la table payments :\n";
    $stmt = $pdo->query("SELECT * FROM payments LIMIT 3");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($payments as $payment) {
        echo "   - ";
        foreach ($payment as $key => $value) {
            echo "{$key}: {$value} | ";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>


