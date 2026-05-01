<?php

// TEST SIMPLE DES ROUTES
echo "=== TEST SIMPLE DES ROUTES ===\n\n";

$testUrls = [
    'http://localhost:8080/' => 'Page d\'accueil',
    'http://localhost:8080/auth/login' => 'Page de connexion',
    'http://localhost:8080/index.php' => 'Index.php direct'
];

foreach ($testUrls as $url => $description) {
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
    
    if ($httpCode === 200 && $response) {
        echo "   Contenu: " . substr(strip_tags($response), 0, 100) . "...\n";
    }
}

echo "\n=== FIN DU TEST ===\n";
