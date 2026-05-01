<?php
/**
 * Script pour vérifier l'existence des tables et leurs structures
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 VÉRIFICATION DES TABLES - KISSAI SCHOOL\n";
    echo "=========================================\n\n";
    
    // Liste des tables à vérifier
    $tables = ['students', 'teachers', 'classes', 'users', 'licenses'];
    
    foreach ($tables as $table) {
        echo "📋 TABLE: $table\n";
        echo "----------------\n";
        
        try {
            // Vérifier si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table existe\n";
                
                // Compter les enregistrements
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "   Enregistrements: $count\n";
                
                // Afficher la structure
                $stmt = $pdo->query("DESCRIBE $table");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "   Colonnes:\n";
                foreach ($columns as $column) {
                    echo "     - " . $column['Field'] . " (" . $column['Type'] . ")\n";
                }
            } else {
                echo "❌ Table n'existe pas\n";
            }
        } catch (PDOException $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Test des requêtes spécifiques
    echo "🧪 TEST DES REQUÊTES SPÉCIFIQUES\n";
    echo "================================\n\n";
    
    $queries = [
        'students' => "SELECT COUNT(*) as count FROM students",
        'teachers' => "SELECT COUNT(*) as count FROM teachers", 
        'classes' => "SELECT COUNT(*) as count FROM classes",
        'users' => "SELECT COUNT(*) as count FROM users WHERE is_active = 1"
    ];
    
    foreach ($queries as $table => $query) {
        echo "📊 $table:\n";
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ Requête réussie: " . $result['count'] . " enregistrements\n";
        } catch (PDOException $e) {
            echo "   ❌ Erreur: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE CONNEXION: " . $e->getMessage() . "\n";
}

echo "📄 FIN DE LA VÉRIFICATION\n";
echo "=========================\n";
?>





