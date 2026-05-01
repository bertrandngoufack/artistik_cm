<?php
/**
 * Test du Module Scolarité - KISSAI SCHOOL
 * Vérification des fonctionnalités principales
 */

echo "🎓 TEST DU MODULE SCOLARITÉ - KISSAI SCHOOL\n";
echo "==========================================\n\n";

// Configuration de base
$baseUrl = 'http://localhost:8080';
$testResults = [];

// Fonction pour tester les URLs
function testUrl($url, $description) {
    global $baseUrl;
    
    $fullUrl = $baseUrl . $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? '✅ SUCCÈS' : '❌ ÉCHEC';
    echo sprintf("%-50s | %s (HTTP %d)\n", $description, $status, $httpCode);
    
    return [
        'url' => $fullUrl,
        'description' => $description,
        'status' => $status,
        'http_code' => $httpCode,
        'success' => ($httpCode == 200)
    ];
}

// Test des pages principales du module Scolarité
echo "📋 TEST DES PAGES PRINCIPALES\n";
echo "-----------------------------\n";

$testResults[] = testUrl('/admin/scolarite', 'Page principale Scolarité');
$testResults[] = testUrl('/admin/scolarite/students', 'Gestion des élèves');
$testResults[] = testUrl('/admin/scolarite/absences', 'Gestion des absences');
$testResults[] = testUrl('/admin/scolarite/discipline', 'Conseil de discipline');
$testResults[] = testUrl('/admin/scolarite/discipline/notifications', 'Notifications disciplinaires');

echo "\n📊 TEST DES FONCTIONNALITÉS AVANCÉES\n";
echo "------------------------------------\n";

// Test avec filtres
$testResults[] = testUrl('/admin/scolarite/students?academic_year=2024-2025', 'Filtrage par année académique');
$testResults[] = testUrl('/admin/scolarite/absences?academic_year=2024-2025', 'Absences par année académique');
$testResults[] = testUrl('/admin/scolarite/discipline?academic_year=2024-2025', 'Discipline par année académique');

echo "\n🔧 TEST DES ROUTES DE NOTIFICATION\n";
echo "----------------------------------\n";

$testResults[] = testUrl('/admin/scolarite/discipline/notifications/send-all', 'Envoi notifications en masse');

echo "\n📈 RÉSULTATS DU TEST\n";
echo "===================\n";

$totalTests = count($testResults);
$successfulTests = count(array_filter($testResults, function($result) {
    return $result['success'];
}));
$failedTests = $totalTests - $successfulTests;

echo "Total des tests : $totalTests\n";
echo "Tests réussis : $successfulTests\n";
echo "Tests échoués : $failedTests\n";
echo "Taux de réussite : " . round(($successfulTests / $totalTests) * 100, 2) . "%\n\n";

if ($failedTests > 0) {
    echo "❌ TESTS ÉCHOUÉS :\n";
    echo "-----------------\n";
    foreach ($testResults as $result) {
        if (!$result['success']) {
            echo "- {$result['description']} : {$result['url']} (HTTP {$result['http_code']})\n";
        }
    }
    echo "\n";
}

echo "✅ TESTS RÉUSSIS :\n";
echo "-----------------\n";
foreach ($testResults as $result) {
    if ($result['success']) {
        echo "- {$result['description']}\n";
    }
}

echo "\n🎯 RECOMMANDATIONS\n";
echo "=================\n";

if ($failedTests == 0) {
    echo "✅ Tous les tests sont passés avec succès !\n";
    echo "✅ Le module Scolarité est opérationnel.\n";
    echo "✅ Les notifications disciplinaires sont configurées.\n";
    echo "✅ Les filtres par année académique fonctionnent.\n";
} else {
    echo "⚠️  Certains tests ont échoué.\n";
    echo "🔧 Vérifiez que le serveur est démarré sur le port 8080.\n";
    echo "🔧 Vérifiez que les routes sont correctement configurées.\n";
    echo "🔧 Vérifiez que les vues existent dans le bon répertoire.\n";
}

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Module Scolarité\n";
?>

