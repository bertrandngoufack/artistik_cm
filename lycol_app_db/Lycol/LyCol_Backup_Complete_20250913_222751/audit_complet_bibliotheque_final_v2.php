<?php
/**
 * Audit Complet du Module Bibliothèque - Version Finale
 * Teste toutes les routes après les corrections
 */

echo "=== AUDIT COMPLET DU MODULE BIBLIOTHÈQUE - VERSION FINALE ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$baseUrl = 'http://localhost:8080';
$routes = [
    // Routes principales
    '/admin/bibliotheque' => 'GET',
    '/admin/bibliotheque/books' => 'GET',
    '/admin/bibliotheque/books/create' => 'GET',
    '/admin/bibliotheque/loans' => 'GET',
    '/admin/bibliotheque/loans/create' => 'GET',
    '/admin/bibliotheque/members' => 'GET',
    '/admin/bibliotheque/members/create' => 'GET',
    
    // Routes des rapports
    '/admin/bibliotheque/reports' => 'GET',
    '/admin/bibliotheque/reports/books' => 'GET',
    '/admin/bibliotheque/reports/loans' => 'GET',
    '/admin/bibliotheque/reports/members' => 'GET',
    
    // Routes de détail (si elles existent)
    '/admin/bibliotheque/books/1' => 'GET',
    '/admin/bibliotheque/books/1/edit' => 'GET',
    '/admin/bibliotheque/loans/1' => 'GET',
    '/admin/bibliotheque/loans/1/edit' => 'GET',
    '/admin/bibliotheque/members/1' => 'GET',
    '/admin/bibliotheque/members/1/edit' => 'GET',
];

$results = [];
$totalRoutes = count($routes);
$successCount = 0;
$errorCount = 0;

echo "Test de {$totalRoutes} routes...\n\n";

foreach ($routes as $route => $method) {
    $url = $baseUrl . $route;
    echo "Testing: {$method} {$route}... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "ERREUR CURL: {$error}\n";
        $results[$route] = ['status' => 'ERROR', 'code' => 0, 'error' => $error];
        $errorCount++;
    } else {
        $lines = explode("\n", $response);
        $firstLine = $lines[0];
        
        if (strpos($firstLine, 'HTTP/1.1') !== false) {
            $statusCode = (int) substr($firstLine, 9, 3);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                echo "OK ({$statusCode})\n";
                $results[$route] = ['status' => 'SUCCESS', 'code' => $statusCode];
                $successCount++;
            } elseif ($statusCode >= 300 && $statusCode < 400) {
                echo "REDIRECTION ({$statusCode})\n";
                $results[$route] = ['status' => 'REDIRECT', 'code' => $statusCode];
                $successCount++;
            } elseif ($statusCode >= 400 && $statusCode < 500) {
                echo "ERREUR CLIENT ({$statusCode})\n";
                $results[$route] = ['status' => 'CLIENT_ERROR', 'code' => $statusCode];
                $errorCount++;
            } elseif ($statusCode >= 500) {
                echo "ERREUR SERVEUR ({$statusCode})\n";
                $results[$route] = ['status' => 'SERVER_ERROR', 'code' => $statusCode];
                $errorCount++;
            } else {
                echo "CODE INCONNU ({$statusCode})\n";
                $results[$route] = ['status' => 'UNKNOWN', 'code' => $statusCode];
                $errorCount++;
            }
        } else {
            echo "REPONSE INVALIDE\n";
            $results[$route] = ['status' => 'INVALID_RESPONSE', 'code' => 0];
            $errorCount++;
        }
    }
}

echo "\n=== RÉSULTATS DE L'AUDIT ===\n";
echo "Total des routes testées: {$totalRoutes}\n";
echo "Succès: {$successCount}\n";
echo "Erreurs: {$errorCount}\n";
echo "Taux de succès: " . round(($successCount / $totalRoutes) * 100, 2) . "%\n\n";

echo "=== DÉTAIL DES RÉSULTATS ===\n";
foreach ($results as $route => $result) {
    $status = $result['status'];
    $code = $result['code'];
    
    switch ($status) {
        case 'SUCCESS':
            echo "✅ {$route} - {$status} ({$code})\n";
            break;
        case 'REDIRECT':
            echo "🔄 {$route} - {$status} ({$code})\n";
            break;
        case 'CLIENT_ERROR':
            echo "⚠️  {$route} - {$status} ({$code})\n";
            break;
        case 'SERVER_ERROR':
            echo "❌ {$route} - {$status} ({$code})\n";
            break;
        default:
            echo "❓ {$route} - {$status} ({$code})\n";
            break;
    }
}

echo "\n=== ANALYSE DES ERREURS ===\n";
$serverErrors = array_filter($results, function($r) { return $r['status'] === 'SERVER_ERROR'; });
$clientErrors = array_filter($results, function($r) { return $r['status'] === 'CLIENT_ERROR'; });

if (!empty($serverErrors)) {
    echo "Erreurs serveur (HTTP 5xx):\n";
    foreach ($serverErrors as $route => $result) {
        echo "  - {$route} (Code: {$result['code']})\n";
    }
}

if (!empty($clientErrors)) {
    echo "Erreurs client (HTTP 4xx):\n";
    foreach ($clientErrors as $route => $result) {
        echo "  - {$route} (Code: {$result['code']})\n";
    }
}

echo "\n=== RECOMMANDATIONS ===\n";
if ($successCount === $totalRoutes) {
    echo "🎉 Toutes les routes fonctionnent parfaitement !\n";
} elseif ($successCount >= ($totalRoutes * 0.8)) {
    echo "✅ La plupart des routes fonctionnent. Quelques corrections mineures nécessaires.\n";
} elseif ($successCount >= ($totalRoutes * 0.6)) {
    echo "⚠️  Plusieurs routes ont des problèmes. Révision importante nécessaire.\n";
} else {
    echo "❌ Nombreux problèmes détectés. Révision majeure requise.\n";
}

echo "\n=== FIN DE L'AUDIT ===\n";
?>








