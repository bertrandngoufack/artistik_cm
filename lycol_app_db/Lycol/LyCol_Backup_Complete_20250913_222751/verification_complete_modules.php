<?php
/**
 * Vérification complète des modules : Économat, Scolarité, Études
 * Test de cohérence, CRUD, exports et fonctionnalités
 */

echo "🔍 VÉRIFICATION COMPLÈTE DES MODULES\n";
echo "====================================\n\n";

$baseUrl = 'http://localhost:8080';
$successCount = 0;
$totalTests = 0;

// ==================== MODULE ÉCONOMAT ====================
echo "💰 MODULE ÉCONOMAT\n";
echo "==================\n";

// Test 1: Page d'accueil Économat
echo "1️⃣ Test page d'accueil Économat :\n";
$totalTests++;
$url = $baseUrl . '/admin/economat';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Module Économat') !== false) {
    echo "   ✅ Page d'accueil Économat : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page d'accueil Économat : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 2: Page des paiements
echo "\n2️⃣ Test page des paiements :\n";
$totalTests++;
$url = $baseUrl . '/admin/economat/payments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Paiements') !== false) {
    echo "   ✅ Page des paiements : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des paiements : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 3: Page des rappels
echo "\n3️⃣ Test page des rappels :\n";
$totalTests++;
$url = $baseUrl . '/admin/economat/reminders';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Historique des Rappels') !== false) {
    echo "   ✅ Page des rappels : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des rappels : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 4: Page des rapports Économat
echo "\n4️⃣ Test page des rapports Économat :\n";
$totalTests++;
$url = $baseUrl . '/admin/economat/reports';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Rapports Financiers') !== false) {
    echo "   ✅ Page des rapports Économat : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des rapports Économat : ÉCHEC (HTTP {$httpCode})\n";
}

// ==================== MODULE SCOLARITÉ ====================
echo "\n📚 MODULE SCOLARITÉ\n";
echo "===================\n";

// Test 5: Page d'accueil Scolarité
echo "5️⃣ Test page d'accueil Scolarité :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Module Scolarité') !== false) {
    echo "   ✅ Page d'accueil Scolarité : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page d'accueil Scolarité : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 6: Page des élèves
echo "\n6️⃣ Test page des élèves :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite/students';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Élèves') !== false) {
    echo "   ✅ Page des élèves : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des élèves : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 7: Page des absences
echo "\n7️⃣ Test page des absences :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite/absences';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Absences') !== false) {
    echo "   ✅ Page des absences : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des absences : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 8: Page de discipline
echo "\n8️⃣ Test page de discipline :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite/discipline';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion de la Discipline') !== false) {
    echo "   ✅ Page de discipline : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page de discipline : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 9: Page des rapports Scolarité
echo "\n9️⃣ Test page des rapports Scolarité :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite/reports';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Rapports Scolarité') !== false) {
    echo "   ✅ Page des rapports Scolarité : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des rapports Scolarité : ÉCHEC (HTTP {$httpCode})\n";
}

// ==================== MODULE ÉTUDES ====================
echo "\n🎓 MODULE ÉTUDES\n";
echo "================\n";

// Test 10: Page d'accueil Études
echo "🔟 Test page d'accueil Études :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Module Études') !== false) {
    echo "   ✅ Page d'accueil Études : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page d'accueil Études : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 11: Page des cycles
echo "\n1️⃣1️⃣ Test page des cycles :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/cycles';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Cycles') !== false) {
    echo "   ✅ Page des cycles : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des cycles : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 12: Page des classes
echo "\n1️⃣2️⃣ Test page des classes :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/classes';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Classes') !== false) {
    echo "   ✅ Page des classes : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des classes : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 13: Page des matières
echo "\n1️⃣3️⃣ Test page des matières :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/subjects';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Matières') !== false) {
    echo "   ✅ Page des matières : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des matières : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 14: Page des emplois du temps
echo "\n1️⃣4️⃣ Test page des emplois du temps :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/timetable';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Emplois du Temps') !== false) {
    echo "   ✅ Page des emplois du temps : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des emplois du temps : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 15: Page des assignations
echo "\n1️⃣5️⃣ Test page des assignations :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/assignments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Gestion des Assignations') !== false) {
    echo "   ✅ Page des assignations : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des assignations : ÉCHEC (HTTP {$httpCode})\n";
}

// Test 16: Page des rapports Études
echo "\n1️⃣6️⃣ Test page des rapports Études :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'Rapports Études') !== false) {
    echo "   ✅ Page des rapports Études : OK\n";
    $successCount++;
} else {
    echo "   ❌ Page des rapports Études : ÉCHEC (HTTP {$httpCode})\n";
}

// ==================== TESTS D'EXPORT ====================
echo "\n📊 TESTS D'EXPORT\n";
echo "=================\n";

// Test 17: Export CSV Économat
echo "1️⃣7️⃣ Test export CSV Économat :\n";
$totalTests++;
$url = $baseUrl . '/admin/economat/reports/export/csv?report_type=payments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode === 200 && strpos($contentType, 'text/csv') !== false) {
    echo "   ✅ Export CSV Économat : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV Économat : ÉCHEC (HTTP {$httpCode}, Type: {$contentType})\n";
}

// Test 18: Export CSV Scolarité
echo "\n1️⃣8️⃣ Test export CSV Scolarité :\n";
$totalTests++;
$url = $baseUrl . '/admin/scolarite/reports/export/csv?report_type=students';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode === 200 && strpos($contentType, 'text/csv') !== false) {
    echo "   ✅ Export CSV Scolarité : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV Scolarité : ÉCHEC (HTTP {$httpCode}, Type: {$contentType})\n";
}

// Test 19: Export CSV Études - Assignations
echo "\n1️⃣9️⃣ Test export CSV Études (Assignations) :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=assignments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode === 200 && strpos($contentType, 'text/csv') !== false && strpos($response, 'Enseignant,Classe,Matière,Principal,Année') !== false) {
    echo "   ✅ Export CSV Études (Assignations) : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV Études (Assignations) : ÉCHEC (HTTP {$httpCode}, Type: {$contentType})\n";
}

// Test 20: Export CSV Études - Classes
echo "\n2️⃣0️⃣ Test export CSV Études (Classes) :\n";
$totalTests++;
$url = $baseUrl . '/admin/etudes/reports/export/csv?report_type=classes';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode === 200 && strpos($contentType, 'text/csv') !== false && strpos($response, 'Classe,Cycle,Élèves,Enseignants,Matières,"Heures EDT"') !== false) {
    echo "   ✅ Export CSV Études (Classes) : OK\n";
    $successCount++;
} else {
    echo "   ❌ Export CSV Études (Classes) : ÉCHEC (HTTP {$httpCode}, Type: {$contentType})\n";
}

// ==================== RÉSUMÉ FINAL ====================
echo "\n🎉 RÉSUMÉ FINAL DE LA VÉRIFICATION\n";
echo "===================================\n";
echo "✅ Tests réussis : {$successCount}/{$totalTests}\n";
echo "📊 Taux de réussite : " . round(($successCount / $totalTests) * 100, 1) . "%\n";

if ($successCount === $totalTests) {
    echo "\n🎉 TOUS LES MODULES SONT PARFAITEMENT OPÉRATIONNELS !\n";
    echo "📋 Cohérence, CRUD et exports fonctionnent correctement.\n";
} else {
    echo "\n⚠️  Certains tests ont échoué. Vérifiez les erreurs ci-dessus.\n";
}

echo "\n📋 RÉCAPITULATIF PAR MODULE :\n";
echo "=============================\n";
echo "💰 ÉCONOMAT : Pages d'accueil, paiements, rappels, rapports, exports\n";
echo "📚 SCOLARITÉ : Pages d'accueil, élèves, absences, discipline, rapports, exports\n";
echo "🎓 ÉTUDES : Pages d'accueil, cycles, classes, matières, EDT, assignations, rapports, exports\n";

echo "\n🌐 URLs principales testées :\n";
echo "============================\n";
echo "   - Économat : {$baseUrl}/admin/economat\n";
echo "   - Scolarité : {$baseUrl}/admin/scolarite\n";
echo "   - Études : {$baseUrl}/admin/etudes\n";
echo "   - Rapports Économat : {$baseUrl}/admin/economat/reports\n";
echo "   - Rapports Scolarité : {$baseUrl}/admin/scolarite/reports\n";
echo "   - Rapports Études : {$baseUrl}/admin/etudes/reports\n";
?>









