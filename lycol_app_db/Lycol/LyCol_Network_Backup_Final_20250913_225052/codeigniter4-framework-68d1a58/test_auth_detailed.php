<?php
/**
 * Test détaillé de l'authentification
 */

echo "=== TEST AUTHENTIFICATION DÉTAILLÉ ===\n\n";

$baseUrl = 'http://localhost:8080';

// Test 1: Récupérer le token CSRF et les cookies
echo "🔍 Test 1: Récupération du token CSRF\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page de connexion accessible\n";
    
    // Extraire le token CSRF
    preg_match('/name="csrf_test_name" value="([^"]+)"/', $response, $matches);
    $csrfToken = $matches[1] ?? '';
    
    if ($csrfToken) {
        echo "✅ Token CSRF récupéré: " . substr($csrfToken, 0, 10) . "...\n";
    } else {
        echo "❌ Token CSRF non trouvé\n";
        exit(1);
    }
} else {
    echo "❌ Page de connexion non accessible (Code: $httpCode)\n";
    exit(1);
}

// Test 2: Authentification avec identifiants valides
echo "\n🔍 Test 2: Authentification avec identifiants valides\n";
$postData = [
    'username' => 'admin',
    'password' => 'admin123',
    'csrf_test_name' => $csrfToken
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_VERBOSE, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Headers:\n$headers\n";
echo "Body (premiers 500 caractères):\n" . substr($body, 0, 500) . "\n";

// Test 3: Vérifier les cookies de session
echo "\n🔍 Test 3: Vérification des cookies\n";
$cookieContent = file_get_contents('/tmp/cookies.txt');
echo "Cookies:\n$cookieContent\n";

// Test 4: Accès au dashboard
echo "\n🔍 Test 4: Accès au dashboard\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP dashboard: $httpCode\n";
echo "Headers dashboard:\n$headers\n";
echo "Body dashboard (premiers 500 caractères):\n" . substr($body, 0, 500) . "\n";

// Nettoyer
unlink('/tmp/cookies.txt');

echo "\n🎯 TEST TERMINÉ\n";




