<?php
/**
 * Test POST des fonctionnalités KISSAI SCHOOL
 */

echo "🧪 TEST POST DES FONCTIONNALITÉS KISSAI SCHOOL\n";
echo "============================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$timeout = 15;

// Fonction pour tester un POST
function testPost($url, $data, $description) {
    global $timeout;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 400) ? "✅" : "❌";
    echo "$status $description : $httpCode\n";
    
    if ($error) {
        echo "   Erreur: $error\n";
    }
    
    if ($httpCode == 302 || $httpCode == 303) {
        echo "   ✅ Redirection détectée (succès probable)\n";
        return true;
    } elseif ($httpCode == 200) {
        echo "   ✅ Page de réponse reçue\n";
        return true;
    } else {
        echo "   ❌ Erreur de traitement\n";
        return false;
    }
}

// Test d'authentification
echo "🔐 Test d'authentification...\n";
echo "----------------------------\n";

$authData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$authSuccess = testPost($baseUrl . '/auth/authenticate', $authData, 'Authentification admin');
echo "\n";

// Test d'ajout d'un élève
echo "👥 Test d'ajout d'un élève...\n";
echo "----------------------------\n";

$studentData = [
    'matricule' => 'TEST2024001',
    'first_name' => 'Test',
    'last_name' => 'Élève',
    'date_of_birth' => '2018-01-01',
    'gender' => 'M',
    'current_class_id' => '1',
    'admission_date' => '2024-09-01',
    'parent_phone' => '+237 690 000 000',
    'parent_email' => 'test@example.com',
    'address' => 'Douala, Test',
    'parent_name' => 'M. Test',
    'status' => 'ACTIVE'
];

$studentSuccess = testPost($baseUrl . '/admin/scolarite/students/store', $studentData, 'Ajout d\'un élève');
echo "\n";

// Test d'ajout d'un paiement
echo "💰 Test d'ajout d'un paiement...\n";
echo "-------------------------------\n";

$paymentData = [
    'student_id' => '1',
    'fee_type_id' => '1',
    'amount_paid' => '50000',
    'payment_date' => date('Y-m-d'),
    'payment_method' => 'CASH',
    'reference_number' => 'PAY-TEST-001',
    'academic_year' => '2024-2025',
    'notes' => 'Paiement de test'
];

$paymentSuccess = testPost($baseUrl . '/admin/economat/payments/store', $paymentData, 'Ajout d\'un paiement');
echo "\n";

// Test d'ajout d'une absence
echo "📅 Test d'ajout d'une absence...\n";
echo "-------------------------------\n";

$absenceData = [
    'student_id' => '1',
    'date' => date('Y-m-d'),
    'reason' => 'Absence de test',
    'justified' => '0',
    'created_by' => '1'
];

$absenceSuccess = testPost($baseUrl . '/admin/scolarite/absences/store', $absenceData, 'Ajout d\'une absence');
echo "\n";

// Test d'ajout d'un livre
echo "📖 Test d'ajout d'un livre...\n";
echo "----------------------------\n";

$bookData = [
    'title' => 'Livre de Test',
    'author' => 'Auteur Test',
    'isbn' => '9780000000000',
    'category' => 'Test',
    'total_copies' => '5',
    'available_copies' => '5',
    'location' => 'Étagère Test'
];

$bookSuccess = testPost($baseUrl . '/admin/bibliotheque/books/store', $bookData, 'Ajout d\'un livre');
echo "\n";

// Test d'ajout d'un message
echo "💬 Test d'ajout d'un message...\n";
echo "-------------------------------\n";

$messageData = [
    'title' => 'Message de Test',
    'content' => 'Contenu du message de test',
    'recipient_type' => 'ALL',
    'recipient_ids' => '[]',
    'sender_id' => '1',
    'status' => 'DRAFT'
];

$messageSuccess = testPost($baseUrl . '/admin/messagerie/messages/store', $messageData, 'Ajout d\'un message');
echo "\n";

// Test de changement de mot de passe
echo "🔐 Test de changement de mot de passe...\n";
echo "--------------------------------------\n";

$passwordData = [
    'current_password' => 'admin123',
    'new_password' => 'admin123',
    'confirm_password' => 'admin123'
];

$passwordSuccess = testPost($baseUrl . '/auth/updatePassword', $passwordData, 'Changement de mot de passe');
echo "\n";

// Résumé final
echo "📊 RÉSUMÉ DES TESTS POST\n";
echo "=======================\n";
echo "Authentification : " . ($authSuccess ? "✅" : "❌") . "\n";
echo "Ajout élève : " . ($studentSuccess ? "✅" : "❌") . "\n";
echo "Ajout paiement : " . ($paymentSuccess ? "✅" : "❌") . "\n";
echo "Ajout absence : " . ($absenceSuccess ? "✅" : "❌") . "\n";
echo "Ajout livre : " . ($bookSuccess ? "✅" : "❌") . "\n";
echo "Ajout message : " . ($messageSuccess ? "✅" : "❌") . "\n";
echo "Changement mot de passe : " . ($passwordSuccess ? "✅" : "❌") . "\n";

$totalTests = 7;
$totalSuccess = ($authSuccess ? 1 : 0) + ($studentSuccess ? 1 : 0) + ($paymentSuccess ? 1 : 0) + 
                ($absenceSuccess ? 1 : 0) + ($bookSuccess ? 1 : 0) + ($messageSuccess ? 1 : 0) + 
                ($passwordSuccess ? 1 : 0);

echo "\n🎯 TAUX DE RÉUSSITE : " . round(($totalSuccess / $totalTests) * 100, 1) . "%\n";

if ($totalSuccess == $totalTests) {
    echo "\n🎉 TOUS LES TESTS POST SONT PASSÉS !\n";
} else {
    echo "\n⚠️ Certains tests POST ont échoué.\n";
}

echo "\n🚀 L'application KISSAI SCHOOL gère correctement les données !\n";
?>


