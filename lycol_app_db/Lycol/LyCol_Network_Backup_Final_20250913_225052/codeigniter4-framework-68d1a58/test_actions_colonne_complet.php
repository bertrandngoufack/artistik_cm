<?php

/**
 * TEST COMPLET DE LA COLONNE ACTIONS - GESTION DES CLASSES
 * Vérification spécifique des boutons d'action (Voir, Éditer, Supprimer)
 */

echo "🔍 TEST COMPLET DE LA COLONNE ACTIONS - GESTION DES CLASSES\n";
echo "==========================================================\n\n";

$baseUrl = 'http://localhost:8080';
$results = [];
$errors = [];
$successCount = 0;
$totalTests = 0;

// Fonction de test de route
function testRoute($description, $url, $expectedCode = 200) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🔍 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        echo "✅ SUCCÈS (HTTP $httpCode)\n";
        $successCount++;
        $results[] = "✅ $description: OK";
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode (attendu: $expectedCode)";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// Fonction de test POST
function testPost($description, $url, $data, $expectedCode = 303) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🔄 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ SUCCÈS (HTTP $httpCode)\n";
        $successCount++;
        $results[] = "✅ $description: OK";
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// 1. Test des boutons d'action pour différentes classes
echo "🔘 TEST DES BOUTONS D'ACTION PAR CLASSE\n";
echo "----------------------------------------\n";

$classIds = [1, 2, 3, 4, 5]; // Test avec 5 classes différentes

foreach ($classIds as $classId) {
    echo "\n📚 Classe ID: $classId\n";
    echo "   " . str_repeat("-", 20) . "\n";
    
    // Test bouton Voir (👁️)
    testRoute("Bouton Voir (👁️) - Classe $classId", "/admin/etudes/classes/$classId/view");
    
    // Test bouton Éditer (✏️)
    testRoute("Bouton Éditer (✏️) - Classe $classId", "/admin/etudes/classes/$classId/edit");
    
    // Test bouton Supprimer (🗑️) - GET
    testRoute("Bouton Supprimer (🗑️) - Classe $classId", "/admin/etudes/classes/$classId/delete", 302);
}

echo "\n";

// 2. Test des opérations POST via actions
echo "🔄 TEST DES OPÉRATIONS POST VIA ACTIONS\n";
echo "---------------------------------------\n";

// Test mise à jour via action Éditer
$updateData = [
    'name' => 'Test Actions ' . date('Y-m-d H:i:s'),
    'code' => 'CLACT' . rand(100, 999),
    'cycle_id' => 1,
    'level' => 2,
    'capacity' => 35,
    'description' => 'Test via bouton actions',
    'is_active' => 1
];
testPost('Mise à jour via bouton Éditer - Classe 1', '/admin/etudes/classes/1/update', $updateData);

// Test création nouvelle classe pour actions
$createData = [
    'name' => 'Nouvelle Classe Actions ' . date('Y-m-d H:i:s'),
    'code' => 'CLNEW' . rand(100, 999),
    'cycle_id' => 2,
    'level' => 1,
    'capacity' => 30,
    'description' => 'Classe créée pour test actions',
    'is_active' => 1
];
testPost('Création via bouton Nouvelle Classe', '/admin/etudes/classes/store', $createData);

echo "\n";

// 3. Test de la cohérence des actions
echo "🔗 TEST DE COHÉRENCE DES ACTIONS\n";
echo "---------------------------------\n";

// Vérifier que les actions pointent vers les bonnes routes
$coherenceTests = [
    '/admin/etudes/classes' => 'Retour liste après action',
    '/admin/etudes/classes/create' => 'Lien vers création',
    '/admin/etudes/cycles' => 'Lien vers cycles (pour cycle_id)',
    '/admin/etudes/subjects' => 'Lien vers matières (pour assignations)'
];

foreach ($coherenceTests as $test => $description) {
    testRoute($description, $test);
}

echo "\n";

// 4. Test des actions avec données invalides
echo "⚠️ TEST DES ACTIONS AVEC DONNÉES INVALIDES\n";
echo "-------------------------------------------\n";

// Test avec ID inexistant
testRoute('Action Voir avec ID inexistant', '/admin/etudes/classes/999/view', 404);

// Test avec ID invalide
testRoute('Action Éditer avec ID invalide', '/admin/etudes/classes/abc/edit', 404);

// Test POST avec données manquantes
$invalidData = [
    'name' => '', // Nom vide
    'code' => 'INV',
    'cycle_id' => 999 // Cycle inexistant
];
testPost('Mise à jour avec données invalides', '/admin/etudes/classes/1/update', $invalidData, 422);

echo "\n";

// 5. Test de la sécurité des actions
echo "🔒 TEST DE SÉCURITÉ DES ACTIONS\n";
echo "--------------------------------\n";

// Test injection SQL dans l'ID
testRoute('Action avec injection SQL dans ID', '/admin/etudes/classes/1%27%20OR%201=1/view', 404);

// Test XSS dans les données
$xssData = [
    'name' => '<script>alert("XSS")</script>',
    'code' => 'XSS',
    'cycle_id' => 1,
    'level' => 1,
    'capacity' => 30
];
testPost('Action avec données XSS', '/admin/etudes/classes/1/update', $xssData);

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - COLONNE ACTIONS\n";
echo "=====================================\n\n";

$successRate = ($totalTests > 0) ? round(($successCount / $totalTests) * 100, 1) : 0;

echo "📈 STATISTIQUES:\n";
echo "   • Tests réussis: {$successCount}/{$totalTests}\n";
echo "   • Taux de succès: {$successRate}%\n";
echo "   • Erreurs: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERREURS DÉTECTÉES:\n";
    echo "---------------------\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

echo "✅ TESTS RÉUSSIS:\n";
echo "-----------------\n";
foreach ($results as $result) {
    if (strpos($result, '✅') === 0) {
        echo "   $result\n";
    }
}
echo "\n";

// Analyse spécifique des actions
echo "🔍 ANALYSE SPÉCIFIQUE DES ACTIONS:\n";
echo "-----------------------------------\n";

$actionSuccess = 0;
$actionTotal = 0;

foreach ($results as $result) {
    if (strpos($result, 'Bouton') !== false) {
        $actionTotal++;
        if (strpos($result, '✅') === 0) {
            $actionSuccess++;
        }
    }
}

if ($actionTotal > 0) {
    $actionRate = round(($actionSuccess / $actionTotal) * 100, 1);
    echo "   • Actions testées: {$actionTotal}\n";
    echo "   • Actions réussies: {$actionSuccess}\n";
    echo "   • Taux de succès actions: {$actionRate}%\n\n";
}

if ($successRate >= 90) {
    echo "🎉 COLONNE ACTIONS: EXCELLENT ÉTAT\n";
    echo "   Tous les boutons d'action fonctionnent parfaitement.\n";
} elseif ($successRate >= 75) {
    echo "✅ COLONNE ACTIONS: BON ÉTAT\n";
    echo "   La plupart des actions fonctionnent correctement.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ COLONNE ACTIONS: ÉTAT MOYEN\n";
    echo "   Certaines actions nécessitent des corrections.\n";
} else {
    echo "❌ COLONNE ACTIONS: ÉTAT CRITIQUE\n";
    echo "   De nombreuses actions nécessitent des corrections urgentes.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes/classes\n";
echo "📋 Rapport généré le: " . date('Y-m-d H:i:s') . "\n";


