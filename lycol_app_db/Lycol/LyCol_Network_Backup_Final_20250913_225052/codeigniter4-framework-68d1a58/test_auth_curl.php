<?php
/**
 * Test complet de l'authentification avec curl
 */

echo "=== TEST AUTHENTIFICATION CURL ===\n\n";

$baseUrl = 'http://localhost:8080';

// Test 1: Récupérer le token CSRF
echo "🔍 Test 1: Récupération du token CSRF\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
$response = curl_exec($ch);
curl_close($ch);

// Extraire le token CSRF
preg_match('/name="csrf_test_name" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

if ($csrfToken) {
    echo "✅ Token CSRF récupéré: " . substr($csrfToken, 0, 10) . "...\n";
} else {
    echo "❌ Token CSRF non trouvé\n";
    exit(1);
}

// Test 2: Authentification avec des identifiants valides
echo "\n🔍 Test 2: Authentification avec identifiants valides\n";
$postData = [
    'username' => 'admin',
    'password' => 'admin123', // Mot de passe corrigé
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
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
if (strpos($headers, 'Location:') !== false) {
    preg_match('/Location: ([^\r\n]+)/', $headers, $locationMatches);
    $location = $locationMatches[1] ?? '';
    echo "Redirection vers: $location\n";
}

// Test 3: Vérifier si on peut accéder au dashboard
echo "\n🔍 Test 3: Accès au dashboard après authentification\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP dashboard: $httpCode\n";
if ($httpCode == 200) {
    echo "✅ Dashboard accessible après authentification\n";
    if (strpos($response, 'KISSAI SCHOOL') !== false) {
        echo "✅ Nom de l'application correctement affiché\n";
    }
} else {
    echo "❌ Dashboard non accessible (Code: $httpCode)\n";
}

// Test 4: Authentification avec des identifiants invalides
echo "\n🔍 Test 4: Authentification avec identifiants invalides\n";
$postData = [
    'username' => 'invalid',
    'password' => 'invalid',
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
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
if ($httpCode == 303) {
    echo "✅ Redirection correcte pour identifiants invalides\n";
} else {
    echo "❌ Comportement inattendu pour identifiants invalides\n";
}

// Test 5: Déconnexion
echo "\n🔍 Test 5: Test de déconnexion\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/logout');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP logout: $httpCode\n";
if ($httpCode == 303) {
    echo "✅ Déconnexion réussie\n";
} else {
    echo "❌ Problème avec la déconnexion\n";
}

// Nettoyer les cookies
unlink('/tmp/cookies.txt');

echo "\n🎯 TEST AUTHENTIFICATION TERMINÉ\n";
