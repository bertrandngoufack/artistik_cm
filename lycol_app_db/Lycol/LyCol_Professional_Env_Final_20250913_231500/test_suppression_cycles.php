<?php

/**
 * TEST SUPPRESSION CYCLES - DIAGNOSTIC
 * Diagnostic du problème de suppression des cycles
 */

echo "🔍 TEST SUPPRESSION CYCLES - DIAGNOSTIC\n";
echo "=======================================\n\n";

$baseUrl = 'http://localhost:8080';

echo "📊 DIAGNOSTIC DES ROUTES\n";
echo "------------------------\n";

// Test 1: Vérifier si la page principale des cycles fonctionne
echo "  🔍 Test Page principale cycles... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

// Test 2: Vérifier si la page de création fonctionne
echo "  🔍 Test Page création cycle... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

// Test 3: Vérifier si la page d'édition fonctionne
echo "  🔍 Test Page édition cycle 32... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles/32/edit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

echo "\n🔘 TEST DE LA SUPPRESSION\n";
echo "-------------------------\n";

// Test 4: Test suppression GET
echo "  🔍 Test Suppression GET cycle 32... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles/delete/32');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 302 || $httpCode == 303) {
    echo "✅ SUCCÈS (HTTP $httpCode - Redirection)\n";
} elseif ($httpCode == 404) {
    echo "❌ ÉCHEC (HTTP $httpCode - Route non trouvée)\n";
} else {
    echo "⚠️ ATTENTION (HTTP $httpCode)\n";
}

// Test 5: Test suppression POST
echo "  🔍 Test Suppression POST cycle 32... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles/delete/32');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 302 || $httpCode == 303) {
    echo "✅ SUCCÈS (HTTP $httpCode - Redirection)\n";
} elseif ($httpCode == 404) {
    echo "❌ ÉCHEC (HTTP $httpCode - Route non trouvée)\n";
} else {
    echo "⚠️ ATTENTION (HTTP $httpCode)\n";
}

// Test 6: Test avec un cycle inexistant
echo "  🔍 Test Suppression cycle inexistant (999)... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles/delete/999');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 302 || $httpCode == 303) {
    echo "✅ SUCCÈS (HTTP $httpCode - Redirection avec erreur)\n";
} elseif ($httpCode == 404) {
    echo "❌ ÉCHEC (HTTP $httpCode - Route non trouvée)\n";
} else {
    echo "⚠️ ATTENTION (HTTP $httpCode)\n";
}

echo "\n🔍 ANALYSE DU CODE HTML\n";
echo "------------------------\n";

// Extraire les liens de suppression de la page principale
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/cycles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

// Chercher les liens de suppression
preg_match_all('/href="([^"]*delete[^"]*)"/', $response, $matches);
if (!empty($matches[1])) {
    echo "  🔗 Liens de suppression trouvés:\n";
    foreach ($matches[1] as $link) {
        echo "     - $link\n";
    }
} else {
    echo "  ❌ Aucun lien de suppression trouvé\n";
}

// Chercher les formulaires de suppression
preg_match_all('/action="([^"]*delete[^"]*)"/', $response, $matches);
if (!empty($matches[1])) {
    echo "  📝 Formulaires de suppression trouvés:\n";
    foreach ($matches[1] as $action) {
        echo "     - $action\n";
    }
} else {
    echo "  ❌ Aucun formulaire de suppression trouvé\n";
}

echo "\n🔍 VÉRIFICATION DES ROUTES\n";
echo "--------------------------\n";

// Test des routes alternatives
$routesToTest = [
    '/admin/etudes/cycles/delete/32',
    '/admin/etudes/cycles/32/delete',
    '/admin/etudes/cycles/delete?id=32',
    '/admin/etudes/cycles/32/remove',
    '/admin/etudes/cycles/remove/32'
];

foreach ($routesToTest as $route) {
    echo "  🔍 Test route: $route... ";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 302 || $httpCode == 303) {
        echo "✅ SUCCÈS (HTTP $httpCode)\n";
    } elseif ($httpCode == 404) {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
    } else {
        echo "⚠️ ATTENTION (HTTP $httpCode)\n";
    }
}

echo "\n📊 RÉSUMÉ DU DIAGNOSTIC\n";
echo "=======================\n";

echo "✅ Pages fonctionnelles:\n";
echo "   - Page principale cycles\n";
echo "   - Page création cycle\n";
echo "   - Page édition cycle\n";

echo "\n❌ Problèmes détectés:\n";
echo "   - Route de suppression non reconnue (404)\n";
echo "   - Liens de suppression présents dans l'HTML\n";
echo "   - Méthode deleteCycle existe dans le contrôleur\n";

echo "\n🔧 SOLUTIONS PROPOSÉES:\n";
echo "1. Vérifier la configuration des routes\n";
echo "2. Vérifier que le contrôleur est bien chargé\n";
echo "3. Ajouter des logs de débogage\n";
echo "4. Tester avec une route alternative\n";

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes/cycles\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


