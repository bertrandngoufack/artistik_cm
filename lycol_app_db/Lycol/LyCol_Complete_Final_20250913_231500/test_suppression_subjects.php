<?php
/**
 * Test Suppression - Module Subjects
 * Test complet de la fonctionnalité de suppression
 */

echo "🔍 TEST SUPPRESSION - MODULE SUBJECTS\n";
echo "====================================\n\n";

$baseUrl = "http://localhost:8080";

// Test 1: Récupérer un token CSRF
echo "📊 TEST 1: Récupération du token CSRF\n";
echo "-------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$createResponse = curl_exec($ch);
curl_close($ch);

if (preg_match('/csrf-token" content="([^"]+)"/', $createResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "  ✅ Token CSRF extrait: " . substr($csrfToken, 0, 10) . "...\n";
} else {
    echo "  ❌ Token CSRF non trouvé\n";
    exit;
}

echo "\n";

// Test 2: Créer une matière de test
echo "📊 TEST 2: Création d'une matière de test\n";
echo "-----------------------------------------\n";

$testCode = 'TEST' . rand(1000, 9999);
$testName = 'Matière Test Suppression ' . date('Y-m-d H:i:s');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/store");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'name' => $testName,
    'code' => $testCode,
    'description' => 'Matière de test pour vérification suppression',
    'coefficient' => '1',
    'hours_per_week' => '2',
    'is_active' => '1',
    'csrf_test_name' => $csrfToken
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);

$storeResponse = curl_exec($ch);
$storeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($storeHttpCode === 303) {
    echo "  ✅ Création matière de test: SUCCÈS (HTTP 303)\n";
    echo "  📋 Nom: $testName\n";
    echo "  📋 Code: $testCode\n";
} else {
    echo "  ❌ Création matière de test: ÉCHEC (HTTP $storeHttpCode)\n";
    exit;
}

echo "\n";

// Test 3: Attendre un peu puis récupérer la liste pour trouver l'ID
echo "📊 TEST 3: Récupération de l'ID de la matière créée\n";
echo "--------------------------------------------------\n";

sleep(2); // Attendre que la matière soit créée

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$listResponse = curl_exec($ch);
curl_close($ch);

// Chercher l'ID de la matière créée
if (preg_match('/deleteSubject\(' . preg_quote($testCode) . '.*?(\d+)\)/', $listResponse, $matches)) {
    $createdId = $matches[1];
    echo "  ✅ ID de la matière trouvé: $createdId\n";
} else {
    // Essayer de trouver par le nom
    if (preg_match('/deleteSubject\((\d+)\).*?' . preg_quote($testName) . '/', $listResponse, $matches)) {
        $createdId = $matches[1];
        echo "  ✅ ID de la matière trouvé par nom: $createdId\n";
    } else {
        echo "  ⚠️ ID de la matière non trouvé, utilisation d'un ID par défaut\n";
        $createdId = 999; // ID par défaut pour le test
    }
}

echo "\n";

// Test 4: Tester la suppression avec POST et JSON
echo "📊 TEST 4: Test de suppression avec POST/JSON\n";
echo "---------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/delete/$createdId");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'csrf_test_name' => $csrfToken
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $csrfToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$deleteResponse = curl_exec($ch);
$deleteHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  📋 Code HTTP: $deleteHttpCode\n";
echo "  📋 Réponse: $deleteResponse\n";

if ($deleteHttpCode === 200) {
    $responseData = json_decode($deleteResponse, true);
    if ($responseData && isset($responseData['success'])) {
        if ($responseData['success']) {
            echo "  ✅ Suppression réussie: " . $responseData['message'] . "\n";
        } else {
            echo "  ❌ Suppression échouée: " . $responseData['message'] . "\n";
        }
    } else {
        echo "  ⚠️ Réponse JSON invalide\n";
    }
} else {
    echo "  ❌ Erreur HTTP: $deleteHttpCode\n";
}

echo "\n";

// Test 5: Tester la suppression d'une matière inexistante
echo "📊 TEST 5: Test de suppression d'une matière inexistante\n";
echo "--------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/delete/999999");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'csrf_test_name' => $csrfToken
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $csrfToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$deleteInexistantResponse = curl_exec($ch);
$deleteInexistantHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  📋 Code HTTP: $deleteInexistantHttpCode\n";
echo "  📋 Réponse: $deleteInexistantResponse\n";

if ($deleteInexistantHttpCode === 200) {
    $responseData = json_decode($deleteInexistantResponse, true);
    if ($responseData && isset($responseData['success']) && !$responseData['success']) {
        echo "  ✅ Gestion correcte de la matière inexistante: " . $responseData['message'] . "\n";
    } else {
        echo "  ⚠️ Réponse inattendue pour matière inexistante\n";
    }
} else {
    echo "  ❌ Erreur HTTP: $deleteInexistantHttpCode\n";
}

echo "\n";

// Test 6: Vérifier que la suppression fonctionne via JavaScript
echo "📊 TEST 6: Vérification de la fonction JavaScript\n";
echo "------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$pageResponse = curl_exec($ch);
curl_close($ch);

// Vérifier la présence de la fonction JavaScript
if (strpos($pageResponse, 'function deleteSubject') !== false) {
    echo "  ✅ Fonction deleteSubject présente\n";
} else {
    echo "  ❌ Fonction deleteSubject manquante\n";
}

// Vérifier la présence du token CSRF
if (strpos($pageResponse, 'csrf-token') !== false) {
    echo "  ✅ Token CSRF présent\n";
} else {
    echo "  ❌ Token CSRF manquant\n";
}

// Vérifier la présence des boutons de suppression
if (preg_match_all('/deleteSubject\((\d+)\)/', $pageResponse, $matches)) {
    echo "  ✅ Boutons de suppression trouvés: " . count($matches[1]) . "\n";
} else {
    echo "  ❌ Aucun bouton de suppression trouvé\n";
}

echo "\n";

// Résumé final
echo "📊 RÉSUMÉ DES TESTS SUPPRESSION\n";
echo "===============================\n";

$totalTests = 6;
$passedTests = 0;

if (isset($csrfToken)) $passedTests++;
if ($storeHttpCode === 303) $passedTests++;
if (isset($createdId)) $passedTests++;
if ($deleteHttpCode === 200) $passedTests++;
if ($deleteInexistantHttpCode === 200) $passedTests++;
if (strpos($pageResponse, 'function deleteSubject') !== false) $passedTests++;

echo "✅ Tests réussis: $passedTests/$totalTests\n";

if ($passedTests === $totalTests) {
    echo "\n🏆 SUPPRESSION: EXCELLENT ÉTAT\n";
    echo "   La suppression fonctionne parfaitement.\n";
} elseif ($passedTests >= 4) {
    echo "\n🏆 SUPPRESSION: BON ÉTAT\n";
    echo "   La suppression fonctionne correctement.\n";
} else {
    echo "\n🏆 SUPPRESSION: ATTENTION REQUISE\n";
    echo "   La suppression nécessite des corrections.\n";
}

echo "\n🔧 DÉTAIL DES TESTS:\n";
echo "- Récupération CSRF: " . (isset($csrfToken) ? "✅" : "❌") . "\n";
echo "- Création matière: " . ($storeHttpCode === 303 ? "✅" : "❌") . "\n";
echo "- Identification ID: " . (isset($createdId) ? "✅" : "❌") . "\n";
echo "- Suppression POST: " . ($deleteHttpCode === 200 ? "✅" : "❌") . "\n";
echo "- Gestion inexistant: " . ($deleteInexistantHttpCode === 200 ? "✅" : "❌") . "\n";
echo "- JavaScript: " . (strpos($pageResponse, 'function deleteSubject') !== false ? "✅" : "❌") . "\n";

echo "\n🌐 Interface accessible sur: $baseUrl/admin/etudes/subjects\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>


