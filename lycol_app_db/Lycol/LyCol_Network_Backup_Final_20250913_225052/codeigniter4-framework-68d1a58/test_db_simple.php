<?php
// Test simple de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TEST SIMPLE BASE DE DONNÉES ===\n\n";
    
    // Test 1: Compter les livres
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 1");
    $totalBooks = $stmt->fetchColumn();
    echo "Total livres: $totalBooks\n";
    
    // Test 2: Compter les emprunts
    $stmt = $pdo->query("SELECT COUNT(*) FROM book_loans");
    $totalLoans = $stmt->fetchColumn();
    echo "Total emprunts: $totalLoans\n";
    
    // Test 3: Livres disponibles
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 1 AND available_copies > 0");
    $availableBooks = $stmt->fetchColumn();
    echo "Livres disponibles: $availableBooks\n";
    
    // Test 4: Emprunts actifs
    $stmt = $pdo->query("SELECT COUNT(*) FROM book_loans WHERE status = 'BORROWED'");
    $activeLoans = $stmt->fetchColumn();
    echo "Emprunts actifs: $activeLoans\n";
    
    echo "\n✅ Base de données fonctionnelle\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>






