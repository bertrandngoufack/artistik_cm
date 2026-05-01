<?php
/**
 * Débogage du dashboard économat
 */

require_once 'vendor/autoload.php';

// Initialiser CodeIgniter
$app = require_once 'app/Config/Paths.php';
$paths = new \Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require_once $bootstrap;

// Connexion à la base de données
$db = \Config\Database::connect();

echo "🔍 DÉBOGAGE DU DASHBOARD ÉCONOMAT\n";
echo "==================================\n\n";

// Test 1: Vérifier la requête des derniers paiements
echo "📊 Test 1: Requête des derniers paiements\n";
echo "-----------------------------------------\n";

$builder = $db->table('payments');
$builder->select('payments.*, CONCAT(students.first_name, " ", students.last_name) as student_name, fee_types.name as fee_type_name');
$builder->join('students', 'students.id = payments.student_id', 'left');
$builder->join('fee_types', 'fee_types.id = payments.fee_type_id', 'left');
$builder->orderBy('payments.payment_date', 'DESC');
$builder->limit(5);

$query = $builder->getCompiledSelect();
echo "Requête SQL : $query\n\n";

$result = $builder->get()->getResultArray();

if (empty($result)) {
    echo "❌ Aucun résultat trouvé\n";
} else {
    echo "✅ " . count($result) . " résultats trouvés\n\n";
    
    foreach ($result as $payment) {
        echo "ID: {$payment['id']} | ";
        echo "Élève: {$payment['student_name']} | ";
        echo "Type: {$payment['fee_type_name']} | ";
        echo "Montant: " . number_format($payment['amount_paid'], 0, ',', ' ') . " FCFA | ";
        echo "Date: {$payment['payment_date']}\n";
    }
}

echo "\n📊 Test 2: Statistiques\n";
echo "----------------------\n";

// Total des recettes
$total_revenue = $db->table('payments')->selectSum('amount_paid')->get()->getRow()->amount_paid ?? 0;
echo "Total recettes : " . number_format($total_revenue, 0, ',', ' ') . " FCFA\n";

// Nombre de paiements
$paid_payments = $db->table('payments')->countAllResults();
echo "Total paiements : " . number_format($paid_payments, 0, ',', ' ') . "\n";

// Paiements en retard
$overdue_payments = $db->table('payments')->where('payment_date <', date('Y-m-d'))->countAllResults();
echo "Paiements en retard : " . number_format($overdue_payments, 0, ',', ' ') . "\n";

echo "\n🔍 Test 3: Vérification des jointures\n";
echo "====================================\n";

// Vérifier quelques paiements avec leurs IDs
$payments = $db->table('payments')->select('id, student_id, fee_type_id, amount_paid, payment_date')->limit(3)->get()->getResultArray();

foreach ($payments as $payment) {
    echo "Paiement ID {$payment['id']} : student_id = {$payment['student_id']}, fee_type_id = {$payment['fee_type_id']}\n";
    
    // Vérifier l'élève
    $student = $db->table('students')->where('id', $payment['student_id'])->get()->getRow();
    if ($student) {
        echo "  ✅ Élève trouvé : {$student->first_name} {$student->last_name}\n";
    } else {
        echo "  ❌ Élève non trouvé pour ID {$payment['student_id']}\n";
    }
    
    // Vérifier le type de frais
    $feeType = $db->table('fee_types')->where('id', $payment['fee_type_id'])->get()->getRow();
    if ($feeType) {
        echo "  ✅ Type de frais trouvé : {$feeType->name}\n";
    } else {
        echo "  ❌ Type de frais non trouvé pour ID {$payment['fee_type_id']}\n";
    }
    
    echo "\n";
}

echo "🎯 CONCLUSION\n";
echo "=============\n";
if (!empty($result) && $result[0]['student_name'] !== null) {
    echo "✅ Les jointures fonctionnent correctement\n";
    echo "✅ Les données sont récupérées avec succès\n";
    echo "✅ Le problème vient probablement de la vue\n";
} else {
    echo "❌ Les jointures ne fonctionnent pas\n";
    echo "❌ Les données ne sont pas récupérées correctement\n";
    echo "❌ Vérifiez la structure de la base de données\n";
}
?>


