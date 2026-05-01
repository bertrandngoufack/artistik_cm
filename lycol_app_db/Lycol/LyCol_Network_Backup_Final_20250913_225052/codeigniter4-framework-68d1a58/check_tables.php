<?php
// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VÉRIFICATION DES TABLES BIBLIOTHÈQUE ===\n\n";
    
    // Vérifier si les tables existent
    $tables = ['books', 'book_loans'];
    
    foreach ($tables as $table) {
        echo "Table: $table\n";
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ Table existe\n";
            
            // Afficher la structure
            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            echo "  Colonnes:\n";
            foreach ($columns as $column) {
                echo "    - " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }
        } else {
            echo "  ❌ Table n'existe pas\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>






