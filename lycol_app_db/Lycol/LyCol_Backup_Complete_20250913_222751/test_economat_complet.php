<?php
/**
 * Test complet du module Économat
 * Vérification de toutes les routes, vues et fonctionnalités CRUD
 */

echo "🔍 TEST COMPLET DU MODULE ÉCONOMAT\n";
echo "=====================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$adminUrl = $baseUrl . '/admin/economat';

// Fonction pour tester une URL
function testUrl($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'url' => $url,
        'method' => $method,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Fonction pour afficher le résultat d'un test
function displayTestResult($test, $expectedCode = 200) {
    $status = ($test['http_code'] === $expectedCode) ? '✅' : '❌';
    echo "{$status} {$test['method']} {$test['url']} - HTTP {$test['http_code']}\n";
    
    if ($test['error']) {
        echo "   Erreur: {$test['error']}\n";
    }
    
    if ($test['http_code'] !== $expectedCode) {
        echo "   Attendu: HTTP {$expectedCode}\n";
    }
    
    return $test['http_code'] === $expectedCode;
}

echo "1. TEST DES ROUTES PRINCIPALES\n";
echo "-----------------------------\n";

// Test de la page d'accueil
$tests = [
    testUrl($baseUrl . '/'),
    testUrl($baseUrl . '/auth/login'),
    testUrl($adminUrl),
    testUrl($adminUrl . '/payments'),
    testUrl($adminUrl . '/fees'),
    testUrl($adminUrl . '/reports'),
    testUrl($adminUrl . '/reminders'),
    testUrl($adminUrl . '/notifications'),
];

$successCount = 0;
foreach ($tests as $test) {
    if (displayTestResult($test)) {
        $successCount++;
    }
}

echo "\nRésultat: {$successCount}/" . count($tests) . " routes fonctionnent\n\n";

echo "2. TEST DES ROUTES CRUD PAIEMENTS\n";
echo "--------------------------------\n";

$paymentTests = [
    testUrl($adminUrl . '/payments/create'),
    testUrl($adminUrl . '/payments/1', 'GET'), // View payment
    testUrl($adminUrl . '/payments/1/edit', 'GET'), // Edit payment
    testUrl($adminUrl . '/payments/1/print', 'GET'), // Print receipt
    testUrl($adminUrl . '/payments/1/pdf', 'GET'), // Export PDF
];

$paymentSuccessCount = 0;
foreach ($paymentTests as $test) {
    if (displayTestResult($test, 200)) {
        $paymentSuccessCount++;
    }
}

echo "\nRésultat: {$paymentSuccessCount}/" . count($paymentTests) . " routes CRUD fonctionnent\n\n";

echo "3. TEST DES ROUTES DE RAPPORTS\n";
echo "-----------------------------\n";

$reportTests = [
    testUrl($adminUrl . '/reports/export/csv'),
    testUrl($adminUrl . '/reports/export/pdf'),
];

$reportSuccessCount = 0;
foreach ($reportTests as $test) {
    if (displayTestResult($test, 200)) {
        $reportSuccessCount++;
    }
}

echo "\nRésultat: {$reportSuccessCount}/" . count($reportTests) . " routes de rapports fonctionnent\n\n";

echo "4. TEST DES ROUTES DE RAPPELS\n";
echo "----------------------------\n";

$reminderTests = [
    testUrl($adminUrl . '/reminders/create'),
    testUrl($adminUrl . '/reminders/1/edit', 'GET'),
    testUrl($adminUrl . '/reminders/1/send', 'GET'),
];

$reminderSuccessCount = 0;
foreach ($reminderTests as $test) {
    if (displayTestResult($test, 200)) {
        $reminderSuccessCount++;
    }
}

echo "\nRésultat: {$reminderSuccessCount}/" . count($reminderTests) . " routes de rappels fonctionnent\n\n";

echo "5. TEST DES ROUTES DE NOTIFICATIONS\n";
echo "----------------------------------\n";

$notificationTests = [
    testUrl($adminUrl . '/notifications/send', 'GET'),
    testUrl($adminUrl . '/notifications/history', 'GET'),
];

$notificationSuccessCount = 0;
foreach ($notificationTests as $test) {
    if (displayTestResult($test, 200)) {
        $notificationSuccessCount++;
    }
}

echo "\nRésultat: {$notificationSuccessCount}/" . count($notificationTests) . " routes de notifications fonctionnent\n\n";

echo "6. TEST DES ROUTES DE SUPPRESSION\n";
echo "--------------------------------\n";

$deleteTests = [
    testUrl($adminUrl . '/payments/1/delete', 'GET'),
    testUrl($adminUrl . '/reminders/1/delete', 'GET'),
];

$deleteSuccessCount = 0;
foreach ($deleteTests as $test) {
    if (displayTestResult($test, 200)) {
        $deleteSuccessCount++;
    }
}

echo "\nRésultat: {$deleteSuccessCount}/" . count($deleteTests) . " routes de suppression fonctionnent\n\n";

echo "7. TEST DES FORMULAIRES POST\n";
echo "---------------------------\n";

// Test des formulaires POST
$postTests = [
    testUrl($adminUrl . '/payments/store', 'POST', [
        'student_id' => '1',
        'fee_type_id' => '1',
        'amount_paid' => '50000',
        'payment_date' => date('Y-m-d'),
        'payment_method' => 'cash',
        'reference_number' => 'REF001'
    ]),
    testUrl($adminUrl . '/payments/1/update', 'POST', [
        'amount_paid' => '55000',
        'payment_method' => 'bank_transfer'
    ]),
    testUrl($adminUrl . '/reminders/store', 'POST', [
        'student_id' => '1',
        'message' => 'Rappel de paiement',
        'due_date' => date('Y-m-d', strtotime('+7 days'))
    ]),
    testUrl($adminUrl . '/notifications/send', 'POST', [
        'recipients' => 'all',
        'message' => 'Test notification',
        'type' => 'payment_reminder'
    ])
];

$postSuccessCount = 0;
foreach ($postTests as $test) {
    if (displayTestResult($test, 200)) {
        $postSuccessCount++;
    }
}

echo "\nRésultat: {$postSuccessCount}/" . count($postTests) . " formulaires POST fonctionnent\n\n";

echo "8. VÉRIFICATION DES VUES\n";
echo "----------------------\n";

$viewFiles = [
    'app/Views/admin/economat/index.php',
    'app/Views/admin/economat/payments.php',
    'app/Views/admin/economat/create_payment.php',
    'app/Views/admin/economat/edit_payment.php',
    'app/Views/admin/economat/view_payment.php',
    'app/Views/admin/economat/fees.php',
    'app/Views/admin/economat/reports.php',
    'app/Views/admin/economat/reminders.php',
    'app/Views/admin/economat/receipt.php',
    'app/Views/admin/economat/receipt_pdf.php'
];

$viewSuccessCount = 0;
foreach ($viewFiles as $viewFile) {
    if (file_exists($viewFile)) {
        echo "✅ Vue trouvée: {$viewFile}\n";
        $viewSuccessCount++;
    } else {
        echo "❌ Vue manquante: {$viewFile}\n";
    }
}

echo "\nRésultat: {$viewSuccessCount}/" . count($viewFiles) . " vues existent\n\n";

echo "9. VÉRIFICATION DU CONTRÔLEUR\n";
echo "----------------------------\n";

$controllerFile = 'app/Controllers/Economat.php';
if (file_exists($controllerFile)) {
    echo "✅ Contrôleur trouvé: {$controllerFile}\n";
    
    // Vérifier les méthodes principales
    $controllerContent = file_get_contents($controllerFile);
    $methods = [
        'index',
        'payments',
        'createPayment',
        'storePayment',
        'editPayment',
        'updatePayment',
        'deletePayment',
        'viewPayment',
        'fees',
        'reports',
        'reminders',
        'notifications',
        'printReceipt',
        'exportReceiptPDF',
        'exportToCSV',
        'exportToPDF'
    ];
    
    $methodSuccessCount = 0;
    foreach ($methods as $method) {
        if (strpos($controllerContent, "public function {$method}") !== false) {
            echo "✅ Méthode trouvée: {$method}\n";
            $methodSuccessCount++;
        } else {
            echo "❌ Méthode manquante: {$method}\n";
        }
    }
    
    echo "\nRésultat: {$methodSuccessCount}/" . count($methods) . " méthodes existent\n";
} else {
    echo "❌ Contrôleur manquant: {$controllerFile}\n";
}

echo "\n10. VÉRIFICATION DES MODÈLES\n";
echo "---------------------------\n";

$modelFiles = [
    'app/Models/StudentModel.php',
    'app/Models/PaymentModel.php',
    'app/Models/FeeModel.php'
];

$modelSuccessCount = 0;
foreach ($modelFiles as $modelFile) {
    if (file_exists($modelFile)) {
        echo "✅ Modèle trouvé: {$modelFile}\n";
        $modelSuccessCount++;
    } else {
        echo "❌ Modèle manquant: {$modelFile}\n";
    }
}

echo "\nRésultat: {$modelSuccessCount}/" . count($modelFiles) . " modèles existent\n\n";

echo "11. VÉRIFICATION DES SERVICES\n";
echo "----------------------------\n";

$serviceFiles = [
    'app/Services/ConfigurationService.php',
    'app/Services/DatabaseService.php'
];

$serviceSuccessCount = 0;
foreach ($serviceFiles as $serviceFile) {
    if (file_exists($serviceFile)) {
        echo "✅ Service trouvé: {$serviceFile}\n";
        $serviceSuccessCount++;
    } else {
        echo "❌ Service manquant: {$serviceFile}\n";
    }
}

echo "\nRésultat: {$serviceSuccessCount}/" . count($serviceFiles) . " services existent\n\n";

echo "12. VÉRIFICATION DES TRAITS\n";
echo "--------------------------\n";

$traitFiles = [
    'app/Traits/AcademicYearTrait.php'
];

$traitSuccessCount = 0;
foreach ($traitFiles as $traitFile) {
    if (file_exists($traitFile)) {
        echo "✅ Trait trouvé: {$traitFile}\n";
        $traitSuccessCount++;
    } else {
        echo "❌ Trait manquant: {$traitFile}\n";
    }
}

echo "\nRésultat: {$traitSuccessCount}/" . count($traitFiles) . " traits existent\n\n";

echo "13. VÉRIFICATION DES ASSETS\n";
echo "-------------------------\n";

$assetFiles = [
    'public/assets/bulma/css/bulma.min.css',
    'public/assets/fontawesome/css/all.min.css',
    'public/assets/bulma/js/bulma.js'
];

$assetSuccessCount = 0;
foreach ($assetFiles as $assetFile) {
    if (file_exists($assetFile)) {
        echo "✅ Asset trouvé: {$assetFile}\n";
        $assetSuccessCount++;
    } else {
        echo "❌ Asset manquant: {$assetFile}\n";
    }
}

echo "\nRésultat: {$assetSuccessCount}/" . count($assetFiles) . " assets existent\n\n";

echo "14. TEST DE CONNEXION À LA BASE DE DONNÉES\n";
echo "----------------------------------------\n";

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db;charset=utf8mb4',
        'root',
        'Bateau123',
        [PDO::ATTR_TIMEOUT => 5]
    );
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier les tables principales
    $tables = ['students', 'payments', 'fee_types', 'academic_years'];
    $tableSuccessCount = 0;
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table trouvée: {$table}\n";
                $tableSuccessCount++;
            } else {
                echo "❌ Table manquante: {$table}\n";
            }
        } catch (PDOException $e) {
            echo "❌ Erreur table {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nRésultat: {$tableSuccessCount}/" . count($tables) . " tables existent\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n15. RÉSUMÉ FINAL\n";
echo "================\n";

$totalTests = count($tests) + count($paymentTests) + count($reportTests) + 
              count($reminderTests) + count($notificationTests) + count($deleteTests) + 
              count($postTests);

$totalSuccess = $successCount + $paymentSuccessCount + $reportSuccessCount + 
                $reminderSuccessCount + $notificationSuccessCount + $deleteSuccessCount + 
                $postSuccessCount;

echo "Tests de routes: {$totalSuccess}/{$totalTests} réussis\n";
echo "Vues: {$viewSuccessCount}/" . count($viewFiles) . " existent\n";
echo "Modèles: {$modelSuccessCount}/" . count($modelFiles) . " existent\n";
echo "Services: {$serviceSuccessCount}/" . count($serviceFiles) . " existent\n";
echo "Traits: {$traitSuccessCount}/" . count($traitFiles) . " existent\n";
echo "Assets: {$assetSuccessCount}/" . count($assetFiles) . " existent\n";

$overallSuccess = ($totalSuccess / $totalTests) * 100;
echo "\nTaux de réussite global: " . number_format($overallSuccess, 1) . "%\n";

if ($overallSuccess >= 80) {
    echo "🎉 Le module Économat est fonctionnel !\n";
} elseif ($overallSuccess >= 60) {
    echo "⚠️  Le module Économat nécessite des corrections mineures.\n";
} else {
    echo "🚨 Le module Économat nécessite des corrections majeures.\n";
}

echo "\n✅ Test terminé !\n";
?>


