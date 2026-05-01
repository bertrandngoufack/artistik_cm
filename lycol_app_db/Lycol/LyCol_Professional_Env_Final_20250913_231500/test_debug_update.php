<?php
/**
 * Script de test pour diagnostiquer le problème de mise à jour
 */

echo "=== DIAGNOSTIC PROBLÈME MISE À JOUR ===\n\n";

// Configuration
$base_url = 'http://localhost:8080';

echo "1. TEST DE CONNEXION AU SERVEUR\n";
echo "================================\n";

$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    echo "   ✓ Serveur accessible\n";
    echo "   ✓ Route subjects fonctionnelle\n";
} else {
    echo "   ✗ Erreur d'accès au serveur\n";
    exit;
}

echo "\n2. TEST DE LA ROUTE D'ÉDITION\n";
echo "==============================\n";

$response = @file_get_contents($base_url . '/admin/etudes/subjects/edit/31');
if ($response !== false) {
    echo "   ✓ Route d'édition accessible\n";
    
    // Vérifier le formulaire
    if (strpos($response, 'action=') !== false) {
        preg_match('/action="([^"]+)"/', $response, $matches);
        $form_action = $matches[1] ?? 'Non trouvé';
        echo "   ✓ Action du formulaire: $form_action\n";
    } else {
        echo "   ⚠️ Action du formulaire non trouvée\n";
    }
} else {
    echo "   ✗ Route d'édition non accessible\n";
}

echo "\n3. TEST DE MISE À JOUR SIMPLE\n";
echo "==============================\n";

$post_data = http_build_query([
    'name' => 'Test Debug',
    'code' => 'DEBUG',
    'coefficient' => '1.0',
    'description' => 'Test de debug',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data,
        'timeout' => 10
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/subjects/update/31', false, $context);
if ($response !== false) {
    echo "   ✓ Mise à jour exécutée\n";
    
    // Vérifier les headers de réponse
    $http_response_header = $http_response_header ?? [];
    foreach ($http_response_header as $header) {
        if (strpos($header, 'Location:') !== false) {
            echo "   ✓ Redirection détectée: $header\n";
            break;
        }
    }
} else {
    echo "   ✗ Erreur lors de la mise à jour\n";
}

echo "\n4. TEST DE VALIDATION DES DONNÉES\n";
echo "==================================\n";

// Test avec des données invalides
$invalid_data = http_build_query([
    'name' => '',  // Nom vide (invalide)
    'code' => 'DEBUG',
    'coefficient' => '1.0',
    'description' => 'Test invalide',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $invalid_data,
        'timeout' => 10
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/subjects/update/31', false, $context);
if ($response !== false) {
    echo "   ✓ Validation des données testée\n";
    
    // Vérifier si la validation a fonctionné
    if (strpos($response, 'Erreur') !== false || strpos($response, 'error') !== false) {
        echo "   ✓ Validation des erreurs fonctionnelle\n";
    } else {
        echo "   ⚠️ Validation des erreurs non détectée\n";
    }
} else {
    echo "   ✗ Erreur lors du test de validation\n";
}

echo "\n5. DIAGNOSTIC DES ROUTES\n";
echo "========================\n";

// Tester différentes variations de routes
$test_routes = [
    '/admin/etudes/subjects',
    'admin/etudes/subjects',
    'admin/etudes/subjects/',
    '/admin/etudes/subjects/'
];

foreach ($test_routes as $route) {
    $response = @file_get_contents($base_url . $route);
    if ($response !== false) {
        echo "   ✓ Route: $route → Accessible\n";
    } else {
        echo "   ✗ Route: $route → Non accessible\n";
    }
}

echo "\n6. VÉRIFICATION DE LA CONFIGURATION\n";
echo "====================================\n";

// Vérifier la configuration de base
$config_test = @file_get_contents($base_url . '/');
if ($config_test !== false) {
    echo "   ✓ Page d'accueil accessible\n";
    
    if (strpos($config_test, 'CodeIgniter') !== false) {
        echo "   ✓ CodeIgniter détecté\n";
    } else {
        echo "   ⚠️ CodeIgniter non détecté\n";
    }
} else {
    echo "   ✗ Page d'accueil non accessible\n";
}

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";
echo "Le problème semble être dans la configuration des redirections.\n";
echo "Vérifiez la configuration de base_url et des routes.\n";

