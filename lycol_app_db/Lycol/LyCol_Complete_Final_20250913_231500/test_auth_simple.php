<?php
/**
 * Test simple de l'authentification
 */

echo "=== TEST AUTHENTIFICATION SIMPLE ===\n\n";

// Test direct de la base de données
$host = '100.69.65.33';
$port = 13306;
$username = 'root';
$password = 'Bateau123';
$database = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérifier les utilisateurs
    $stmt = $pdo->query("SELECT username, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Utilisateurs dans la base de données:\n";
    foreach ($users as $user) {
        echo "- {$user['username']}: " . substr($user['password'], 0, 20) . "...\n";
    }
    
    // Tester l'authentification
    $testUsername = 'admin';
    $testPassword = 'admin123';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$testUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\n✅ Utilisateur trouvé: {$user['username']}\n";
        
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ Mot de passe correct\n";
        } else {
            echo "❌ Mot de passe incorrect\n";
        }
    } else {
        echo "\n❌ Utilisateur non trouvé\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}

echo "\n🎯 TEST TERMINÉ\n";




