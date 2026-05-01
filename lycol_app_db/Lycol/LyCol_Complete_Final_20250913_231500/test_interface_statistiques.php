<?php
/**
 * Test simple de l'interface et des statistiques
 */

echo "🎓 TEST DE L'INTERFACE ET DES STATISTIQUES - KISSAI SCHOOL\n";
echo "========================================================\n\n";

// Test 1: Vérification du serveur
echo "🔍 Test 1: Vérification du serveur\n";
echo "----------------------------------\n";

$url = 'http://localhost:8080/admin/economat/payments?academic_year=2024-2025&student_id=1';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur de connexion: $error\n";
} elseif ($httpCode == 200) {
    echo "✅ Serveur accessible (HTTP $httpCode)\n";
    echo "🌐 URL testée: $url\n";
} else {
    echo "❌ Serveur non accessible (HTTP $httpCode)\n";
}

echo "\n";

// Test 2: Vérification des données dans la base
echo "🔍 Test 2: Vérification des données dans la base\n";
echo "-----------------------------------------------\n";

$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $academicYear = '2024-2025';
    $studentId = 1;
    
    // Statistiques pour l'élève ID 1
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_payments,
            SUM(amount_paid) as total_amount
        FROM payments 
        WHERE academic_year = ? AND student_id = ?
    ");
    $stmt->execute([$academicYear, $studentId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Données pour l'élève ID 1:\n";
    echo "   - Total paiements: " . number_format($stats['total_payments']) . "\n";
    echo "   - Total recettes: " . number_format($stats['total_amount']) . " FCFA\n";
    
    // Informations de l'élève
    $stmt = $pdo->prepare("
        SELECT first_name, last_name
        FROM students 
        WHERE id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        echo "   - Nom de l'élève: {$student['first_name']} {$student['last_name']}\n";
    }
    
    echo "\n";
    
    // Test 3: Simulation des requêtes du contrôleur
    echo "🔍 Test 3: Simulation des requêtes du contrôleur\n";
    echo "-----------------------------------------------\n";
    
    // Simuler exactement les requêtes du contrôleur
    $whereConditions = ["p.academic_year = ?"];
    $params = [$academicYear];
    
    // Filtre par élève
    if (!empty($studentId)) {
        $whereConditions[] = "p.student_id = ?";
        $params[] = $studentId;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    
    // Total recettes
    $stmt = $pdo->prepare("
        SELECT SUM(amount_paid) as total 
        FROM payments p
        $whereClause
    ");
    $stmt->execute($params);
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total paiements
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM payments p
        $whereClause
    ");
    $stmt->execute($params);
    $paid_payments = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Paiements en attente
    $pendingWhereConditions = $whereConditions;
    $pendingWhereConditions[] = "p.payment_date > CURDATE()";
    $pendingWhereClause = "WHERE " . implode(" AND ", $pendingWhereConditions);
    $pendingParams = array_merge($params, []);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM payments p
        $pendingWhereClause
    ");
    $stmt->execute($pendingParams);
    $pending_payments = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Paiements en retard
    $overdueWhereConditions = $whereConditions;
    $overdueWhereConditions[] = "p.payment_date < CURDATE()";
    $overdueWhereClause = "WHERE " . implode(" AND ", $overdueWhereConditions);
    $overdueParams = array_merge($params, []);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM payments p
        $overdueWhereClause
    ");
    $stmt->execute($overdueParams);
    $overdue_payments = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    echo "📊 Statistiques simulées (contrôleur):\n";
    echo "   - Total recettes: " . number_format($total_revenue) . " FCFA\n";
    echo "   - Paiements payés: " . number_format($paid_payments) . "\n";
    echo "   - Paiements en attente: " . number_format($pending_payments) . "\n";
    echo "   - Paiements en retard: " . number_format($overdue_payments) . "\n";
    
    echo "\n";
    
    // Test 4: Résumé et recommandations
    echo "📊 Test 4: Résumé et Recommandations\n";
    echo "-----------------------------------\n";
    
    echo "✅ DONNÉES ATTENDUES DANS L'INTERFACE:\n";
    echo "   - Total Recettes: " . number_format($total_revenue) . " FCFA\n";
    echo "   - Paiements Payés: " . number_format($paid_payments) . "\n";
    echo "   - Paiements En Attente: " . number_format($pending_payments) . "\n";
    echo "   - Paiements En Retard: " . number_format($overdue_payments) . "\n\n";
    
    echo "🎯 URL À TESTER:\n";
    echo "   $url\n\n";
    
    echo "⚠️ SI LES STATISTIQUES SONT À 0:\n";
    echo "   1. Vérifier que le contrôleur utilise les bonnes requêtes\n";
    echo "   2. Vérifier que les filtres sont appliqués aux statistiques\n";
    echo "   3. Vérifier que les paramètres sont passés correctement\n\n";
    
    echo "🚀 RECOMMANDATIONS:\n";
    echo "   1. Tester l'URL dans le navigateur\n";
    echo "   2. Vérifier les valeurs affichées\n";
    echo "   3. Comparer avec les données attendues\n";
    echo "   4. Déboguer si nécessaire\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test de l'interface et des statistiques\n";
?>


