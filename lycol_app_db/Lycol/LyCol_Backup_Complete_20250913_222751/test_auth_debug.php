<?php
/**
 * Script de test pour diagnostiquer les problèmes d'authentification
 */

echo "=== TEST AUTHENTIFICATION DEBUG ===\n\n";

$baseUrl = 'http://localhost:8080';

// Test 1: Vérifier si la page de connexion est accessible
echo "🔍 Test 1: Page de connexion\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page de connexion accessible\n";
} else {
    echo "❌ Page de connexion non accessible (Code: $httpCode)\n";
    echo "Réponse: " . substr($response, 0, 500) . "\n\n";
}

// Test 2: Tester l'authentification avec des données valides
echo "\n🔍 Test 2: Authentification avec données valides\n";
$postData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Headers:\n$headers\n";
echo "Body (premiers 500 caractères):\n" . substr($body, 0, 500) . "\n\n";

// Test 3: Tester l'authentification avec des données invalides
echo "\n🔍 Test 3: Authentification avec données invalides\n";
$postData = [
    'username' => 'invalid',
    'password' => 'invalid'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Headers:\n$headers\n";
echo "Body (premiers 500 caractères):\n" . substr($body, 0, 500) . "\n\n";

// Test 4: Tester l'authentification sans données
echo "\n🔍 Test 4: Authentification sans données\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Headers:\n$headers\n";
echo "Body (premiers 500 caractères):\n" . substr($body, 0, 500) . "\n\n";

// Test 5: Vérifier les logs d'erreur
echo "\n🔍 Test 5: Vérification des logs\n";
$logFiles = glob('writable/logs/*.log');
if (!empty($logFiles)) {
    echo "Fichiers de log trouvés:\n";
    foreach ($logFiles as $logFile) {
        echo "- $logFile\n";
        $lastLines = file($logFile);
        $lastLines = array_slice($lastLines, -10);
        echo "Dernières lignes:\n";
        foreach ($lastLines as $line) {
            echo "  " . trim($line) . "\n";
        }
        echo "\n";
    }
} else {
    echo "Aucun fichier de log trouvé\n";
}

echo "\n🎯 DIAGNOSTIC TERMINÉ\n";




