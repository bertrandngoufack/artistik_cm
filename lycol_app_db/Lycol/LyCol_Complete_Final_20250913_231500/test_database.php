<?php

// TEST DE LA BASE DE DONNÉES
echo "=== TEST DE LA BASE DE DONNÉES ===\n\n";

// Charger l'environnement CodeIgniter
require_once 'vendor/autoload.php';

// Configuration de base
$config = new \Config\Database();
$db = \Config\Database::connect();

try {
    // Test de connexion
    $query = $db->query("SELECT 1 as test");
    $result = $query->getRow();
    
    if ($result && $result->test == 1) {
        echo "✅ Connexion à la base de données réussie\n";
    } else {
        echo "❌ Problème de connexion à la base de données\n";
    }
    
    // Test des tables
    $tables = ['users', 'students', 'classes', 'subjects', 'exams', 'grades', 'payments'];
    
    echo "\nVérification des tables:\n";
    foreach ($tables as $table) {
        try {
            $query = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $query->getRow();
            echo "✅ Table $table: {$result->count} enregistrements\n";
        } catch (Exception $e) {
            echo "❌ Table $table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
