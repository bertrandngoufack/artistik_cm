<?php

// TEST FINAL COMPLET DE L'AUDIT
echo "=== TEST FINAL COMPLET DE L'AUDIT ===\n\n";

// 1. TEST DU SERVEUR
echo "1. TEST DU SERVEUR\n";
echo "==================\n";

$serverTests = [
    'http://localhost:8080/' => 'Page d\'accueil',
    'http://localhost:8080/auth/login' => 'Page de connexion',
    'http://localhost:8080/index.php' => 'Index.php direct'
];

foreach ($serverTests as $url => $description) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 3,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    
    $status = $httpCode === 200 ? '✅' : ($httpCode === 302 ? '🔄' : '❌');
    echo "$status $description: HTTP $httpCode\n";
}

// 2. TEST D'AUTHENTIFICATION
echo "\n2. TEST D'AUTHENTIFICATION\n";
echo "===========================\n";

$authData = http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]);

$authContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $authData,
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$authResponse = @file_get_contents('http://localhost:8080/auth/authenticate', false, $authContext);
$authCode = 200;

if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP/') === 0) {
            $authCode = (int)substr($header, 9, 3);
            break;
        }
    }
}

$authStatus = $authCode === 303 ? '✅' : '❌';
echo "$authStatus Authentification admin/admin123: HTTP $authCode\n";

// 3. TEST DES ROUTES ADMIN (sans session)
echo "\n3. TEST DES ROUTES ADMIN (sans session)\n";
echo "========================================\n";

$adminRoutes = [
    'http://localhost:8080/admin/dashboard' => 'Dashboard admin',
    'http://localhost:8080/admin/economat' => 'Module Économat',
    'http://localhost:8080/admin/simple' => 'SimpleAdmin test'
];

foreach ($adminRoutes as $url => $description) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 3,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    
    $status = $httpCode === 200 ? '✅' : ($httpCode === 302 ? '🔄' : '❌');
    echo "$status $description: HTTP $httpCode\n";
}

// 4. RÉSUMÉ FINAL
echo "\n4. RÉSUMÉ FINAL\n";
echo "===============\n";

echo "🔐 AUTHENTIFICATION: ";
if ($authCode === 303) {
    echo "✅ FONCTIONNELLE\n";
} else {
    echo "❌ PROBLÈME\n";
}

echo "🌐 ROUTES PUBLIQUES: ";
$publicWorking = true;
foreach ($serverTests as $url => $description) {
    $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 3, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    if ($httpCode !== 200) {
        $publicWorking = false;
        break;
    }
}
echo $publicWorking ? "✅ FONCTIONNELLES\n" : "❌ PROBLÈME\n";

echo "🔒 ROUTES ADMIN: ";
$adminWorking = true;
foreach ($adminRoutes as $url => $description) {
    $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 3, 'ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    if ($httpCode !== 302) { // Devrait rediriger vers login
        $adminWorking = false;
        break;
    }
}
echo $adminWorking ? "✅ SÉCURISÉES (redirection)\n" : "❌ PROBLÈME\n";

echo "\n=== FIN DU TEST FINAL ===\n";
