<?php
/**
 * Test final des exports rapides - Module Études
 * Vérification simplifiée des exports CSV
 */

echo "🧪 TEST FINAL - EXPORTS RAPIDES\n";
echo "===============================\n\n";

$baseUrl = 'http://localhost:8080';
$successCount = 0;
$totalTests = 0;

// Test 1: Export CSV - Assignations
echo "1️⃣ Test Export CSV - Assignations :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=assignments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Enseignant,Classe,Matière,Principal,Année') !== false) {
    echo "   ✅ Export CSV - Assignations : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV - Assignations : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 2: Export CSV - Classes
echo "\n2️⃣ Test Export CSV - Classes :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=classes';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Classe,Cycle,Élèves,Enseignants,Matières,"Heures EDT"') !== false) {
    echo "   ✅ Export CSV - Classes : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV - Classes : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 3: Export CSV - Général
echo "\n3️⃣ Test Export CSV - Général :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=summary';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Statistique,Valeur') !== false) {
    echo "   ✅ Export CSV - Général : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV - Général : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 4: Export CSV - Cycles
echo "\n4️⃣ Test Export CSV - Cycles :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=cycles';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Cycle,Classes,Élèves,Enseignants') !== false) {
    echo "   ✅ Export CSV - Cycles : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV - Cycles : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 5: Page des rapports
echo "\n5️⃣ Test Page des rapports :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Export CSV - Général') !== false && strpos($response, 'Export CSV - Assignations') !== false) {
    echo "   ✅ Page des rapports : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des rapports : ÉCHEC (HTTP {$httpCode})\n";
}

echo "\n🎉 RÉSUMÉ FINAL :\n";
echo "=================\n";
echo "✅ Tests réussis : {$successCount}/{$totalTests}\n";
echo "📊 Taux de réussite : " . round(($successCount / $totalTests) * 100, 1) . "%\n";

if ($successCount === $totalTests) {
    echo "\n🎉 TOUS LES EXPORTS RAPIDES FONCTIONNENT PARFAITEMENT !\n";
    echo "📋 La zone d'export rapide est entièrement opérationnelle.\n";
    echo "\n🌐 URLs fonctionnelles :\n";
    echo "   - Export CSV - Assignations : {$baseUrl}/admin/etudes/reports/export/csv?report_type=assignments\n";
    echo "   - Export CSV - Classes : {$baseUrl}/admin/etudes/reports/export/csv?report_type=classes\n";
    echo "   - Export CSV - Général : {$baseUrl}/admin/etudes/reports/export/csv?report_type=summary\n";
    echo "   - Export CSV - Cycles : {$baseUrl}/admin/etudes/reports/export/csv?report_type=cycles\n";
    echo "   - Page des rapports : {$baseUrl}/admin/etudes/reports\n";
} else {
    echo "\n⚠️  Certains exports ont échoué. Vérifiez les erreurs ci-dessus.\n";
}
?>









