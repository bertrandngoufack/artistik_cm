<?php
/**
 * Script pour corriger les mots de passe des utilisateurs
 */

echo "=== CORRECTION DES MOTS DE PASSE ===\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = 13306;
$username = 'root';
$password = 'Bateau123';
$database = 'lycol_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Mots de passe à mettre à jour
    $passwords = [
        'admin' => 'admin123',
        'directeur' => 'directeur123',
        'secretaire' => 'secretaire123',
        'enseignant' => 'enseignant123'
    ];
    
    foreach ($passwords as $username => $plainPassword) {
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $result = $stmt->execute([$hashedPassword, $username]);
        
        if ($result) {
            echo "✅ Mot de passe mis à jour pour $username: $plainPassword\n";
        } else {
            echo "❌ Erreur lors de la mise à jour du mot de passe pour $username\n";
        }
    }
    
    echo "\n🎯 CORRECTION TERMINÉE\n";
    echo "Vous pouvez maintenant vous connecter avec:\n";
    echo "- admin / admin123\n";
    echo "- directeur / directeur123\n";
    echo "- secretaire / secretaire123\n";
    echo "- enseignant / enseignant123\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}




