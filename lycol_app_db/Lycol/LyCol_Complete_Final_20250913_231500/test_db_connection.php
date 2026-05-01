<?php
/**
 * Test de connexion à la base de données
 */

// Charger CodeIgniter
require_once 'vendor/autoload.php';

// Configuration de base
$hostname = '100.69.65.33';
$username = 'root';
$password = 'Bateau123';
$database = 'lycol_db';
$port = 13306;

echo "=== TEST DE CONNEXION À LA BASE DE DONNÉES ===\n\n";

try {
    // Test de connexion directe
    echo "1. Test de connexion directe...\n";
    $mysqli = new mysqli($hostname, $username, $password, $database, $port);
    
    if ($mysqli->connect_error) {
        echo "   ✗ Erreur de connexion: " . $mysqli->connect_error . "\n";
    } else {
        echo "   ✓ Connexion réussie\n";
        
        // Test de requête simple
        echo "\n2. Test de requête simple...\n";
        $result = $mysqli->query("SELECT COUNT(*) as count FROM subjects");
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   ✓ Nombre de matières: " . $row['count'] . "\n";
        } else {
            echo "   ✗ Erreur de requête: " . $mysqli->error . "\n";
        }
        
        // Test de la table subjects
        echo "\n3. Test de la table subjects...\n";
        $result = $mysqli->query("DESCRIBE subjects");
        
        if ($result) {
            echo "   ✓ Structure de la table subjects:\n";
            while ($row = $result->fetch_assoc()) {
                echo "      - " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        } else {
            echo "   ✗ Erreur de description: " . $mysqli->error . "\n";
        }
        
        $mysqli->close();
    }
    
} catch (Exception $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";
?>


