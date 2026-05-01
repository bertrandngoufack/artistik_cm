<?php
/**
 * Script pour vérifier les données existantes dans la base de données
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
    
    // Vérifier quelques paiements avec leurs détails
    echo "\n💳 Derniers paiements avec détails :\n";
    
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.student_id,
            p.fee_type_id,
            p.amount_paid,
            p.payment_date,
            p.payment_method,
            p.status,
            p.reference,
            s.first_name,
            s.last_name,
            s.matricule,
            ft.name as fee_type_name
        FROM payments p
        LEFT JOIN students s ON p.student_id = s.id
        LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
        ORDER BY p.payment_date DESC
        LIMIT 5
    ");
    
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($payments)) {
        echo "   ❌ Aucun paiement trouvé\n";
    } else {
        foreach ($payments as $payment) {
            echo "   - ID: {$payment['id']} | ";
            echo "Élève: {$payment['first_name']} {$payment['last_name']} ({$payment['matricule']}) | ";
            echo "Type: {$payment['fee_type_name']} | ";
            echo "Montant: {$payment['amount_paid']} FCFA | ";
            echo "Date: {$payment['payment_date']} | ";
            echo "Statut: {$payment['status']}\n";
        }
    }
    
    // Vérifier les problèmes de jointures
    echo "\n🔍 Diagnostic des jointures :\n";
    
    // Paiements sans élève
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM payments p 
        LEFT JOIN students s ON p.student_id = s.id 
        WHERE s.id IS NULL
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Paiements sans élève correspondant : {$result['count']}\n";
    
    // Paiements sans type de frais
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM payments p 
        LEFT JOIN fee_types ft ON p.fee_type_id = ft.id 
        WHERE ft.id IS NULL
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Paiements sans type de frais correspondant : {$result['count']}\n";
    
    // Vérifier les IDs des élèves et types de frais
    echo "\n📋 IDs disponibles :\n";
    
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM students LIMIT 5");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   - Élèves : ";
    foreach ($students as $student) {
        echo "ID {$student['id']} ({$student['first_name']} {$student['last_name']}) ";
    }
    echo "\n";
    
    $stmt = $pdo->query("SELECT id, name FROM fee_types LIMIT 5");
    $feeTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   - Types de frais : ";
    foreach ($feeTypes as $feeType) {
        echo "ID {$feeType['id']} ({$feeType['name']}) ";
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>
