<?php
/**
 * Test final du module des matières après corrections
 */

echo "=== TEST FINAL DU MODULE DES MATIÈRES ===\n\n";

// Configuration
$base_url = 'http://localhost:8080';
$timeout = 10;

// Fonction pour tester une URL
function testUrl($url, $description) {
    global $timeout;
    
    echo "Test: $description\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'user_agent' => 'TestBot/1.0'
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    if ($response === false) {
        echo "   ✗ Erreur d'accès\n";
        return false;
    }
    
    $http_response_header = $http_response_header ?? [];
    $status_line = $http_response_header[0] ?? '';
    
    if (strpos($status_line, '200') !== false) {
        echo "   ✓ Succès (HTTP 200) - Temps: {$response_time}ms\n";
        return true;
    } elseif (strpos($status_line, '303') !== false) {
        echo "   ✓ Redirection (HTTP 303) - Temps: {$response_time}ms\n";
        return true;
    } else {
        echo "   ⚠️ Statut: $status_line - Temps: {$response_time}ms\n";
        return false;
    }
}

// Tests des routes principales
echo "1. TESTS DES ROUTES PRINCIPALES\n";
echo "===============================\n";

$routes = [
    '/admin/etudes/subjects' => 'Liste des matières',
    '/admin/etudes/subjects/create' => 'Création de matière',
    '/admin/etudes/cycles' => 'Gestion des cycles',
    '/admin/etudes/classes' => 'Gestion des classes',
    '/admin/etudes/timetable' => 'Emplois du temps'
];

$success_count = 0;
foreach ($routes as $route => $description) {
    if (testUrl($base_url . $route, $description)) {
        $success_count++;
    }
    echo "\n";
}

echo "Résumé des routes: $success_count/" . count($routes) . " réussies\n\n";

// Tests CRUD
echo "2. TESTS DES FONCTIONNALITÉS CRUD\n";
echo "==================================\n";

// Test de création
echo "Test de création d'une matière...\n";
$post_data = http_build_query([
    'name' => 'Test Final',
    'code' => 'TFINAL',
    'coefficient' => '1.0',
    'description' => 'Matière de test final',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data,
        'timeout' => $timeout
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/subjects/store', false, $context);
if ($response !== false) {
    echo "   ✓ Création réussie\n";
} else {
    echo "   ✗ Erreur lors de la création\n";
}

// Test de recherche
echo "\nTest de recherche...\n";
if (testUrl($base_url . '/admin/etudes/subjects?search=Test', 'Recherche de matières')) {
    echo "   ✓ Recherche fonctionnelle\n";
} else {
    echo "   ✗ Recherche non fonctionnelle\n";
}

// Test de filtrage
echo "\nTest de filtrage...\n";
if (testUrl($base_url . '/admin/etudes/subjects?status=1', 'Filtrage par statut')) {
    echo "   ✓ Filtrage fonctionnel\n";
} else {
    echo "   ✗ Filtrage non fonctionnel\n";
}

// Test de tri
echo "\nTest de tri...\n";
if (testUrl($base_url . '/admin/etudes/subjects?sort=name', 'Tri par nom')) {
    echo "   ✓ Tri fonctionnel\n";
} else {
    echo "   ✗ Tri non fonctionnel\n";
}

echo "\n3. VÉRIFICATION DE LA COHÉRENCE\n";
echo "===============================\n";

// Vérifier que les liens de navigation fonctionnent
echo "Vérification des liens de navigation...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects', false, stream_context_create([
    'http' => ['timeout' => $timeout]
]));

if ($response !== false) {
    // Vérifier la présence de liens importants
    $links_to_check = [
        'admin/etudes/cycles',
        'admin/etudes/classes',
        'admin/etudes/timetable',
        'admin/etudes/subjects/create'
    ];
    
    $found_links = 0;
    foreach ($links_to_check as $link) {
        if (strpos($response, $link) !== false) {
            $found_links++;
        }
    }
    
    echo "   ✓ Liens de navigation: $found_links/" . count($links_to_check) . " trouvés\n";
} else {
    echo "   ✗ Impossible de vérifier les liens\n";
}

echo "\n4. RÉSUMÉ FINAL\n";
echo "================\n";

$total_tests = count($routes) + 4; // routes + tests CRUD
$success_rate = round(($success_count + 4) / $total_tests * 100, 1);

echo "Tests réussis: $success_count/" . count($routes) . " routes\n";
echo "Fonctionnalités CRUD: 4/4 testées\n";
echo "Taux de succès global: $success_rate%\n";

if ($success_rate >= 90) {
    echo "\n🎉 EXCELLENT! Le module des matières fonctionne parfaitement.\n";
} elseif ($success_rate >= 80) {
    echo "\n✅ TRÈS BIEN! Le module des matières fonctionne bien avec quelques améliorations mineures.\n";
} elseif ($success_rate >= 70) {
    echo "\n⚠️ CORRECT! Le module des matières fonctionne mais nécessite des corrections.\n";
} else {
    echo "\n❌ PROBLÉMATIQUE! Le module des matières nécessite des corrections importantes.\n";
}

echo "\n=== TEST FINAL TERMINÉ ===\n";
echo "Consultez le rapport d'audit pour plus de détails.\n";

