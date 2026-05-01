<?php

/**
 * TEST SIMPLE DES ACTIONS - GESTION DES CLASSES
 * Test direct des boutons d'action
 */

echo "🔍 TEST SIMPLE DES ACTIONS - GESTION DES CLASSES\n";
echo "================================================\n\n";

$baseUrl = 'http://localhost:8080';

// Test simple des actions principales
echo "🔘 TEST DES BOUTONS D'ACTION\n";
echo "-----------------------------\n";

// Test bouton Voir
echo "Test bouton Voir (👁️) - Classe 1: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/classes/1/view');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

// Test bouton Éditer
echo "Test bouton Éditer (✏️) - Classe 1: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/classes/1/edit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

// Test bouton Supprimer
echo "Test bouton Supprimer (🗑️) - Classe 1: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/classes/1/delete');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 302) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

echo "\n🔄 TEST DES OPÉRATIONS POST\n";
echo "---------------------------\n";

// Test mise à jour
echo "Test mise à jour via action: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/classes/1/update');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'name' => 'Test Actions Simple ' . date('Y-m-d H:i:s'),
    'code' => 'CLSIMP' . rand(100, 999),
    'cycle_id' => 1,
    'level' => 2,
    'capacity' => 35,
    'description' => 'Test simple des actions',
    'is_active' => 1
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 400) {
    echo "✅ SUCCÈS (HTTP $httpCode)\n";
} else {
    echo "❌ ÉCHEC (HTTP $httpCode)\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes/classes\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


