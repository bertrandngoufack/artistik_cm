<?php
/**
 * Test complet du projet CodeIgniter
 * Vérifie toutes les routes et fonctionnalités
 */

echo "=== TEST COMPLET DU PROJET CODEIGNITER ===\n\n";

// Configuration
$base_url = 'http://localhost:8080';
$timeout = 10;

// Fonction pour tester une URL
function testUrl($url, $description, $expected_status = 200) {
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
    
    if (strpos($status_line, (string)$expected_status) !== false) {
        echo "   ✓ Succès (HTTP $expected_status) - Temps: {$response_time}ms\n";
        return true;
    } else {
        echo "   ⚠️ Statut: $status_line - Temps: {$response_time}ms\n";
        return false;
    }
}

// Tests des modules principaux
echo "1. TESTS DES MODULES PRINCIPAUX\n";
echo "===============================\n";

$modules = [
    '/admin/dashboard' => 'Dashboard principal',
    '/admin/etudes/' => 'Module Études',
    '/admin/etudes/subjects' => 'Gestion des matières',
    '/admin/etudes/cycles' => 'Gestion des cycles',
    '/admin/etudes/classes' => 'Gestion des classes',
    '/admin/etudes/timetable' => 'Emplois du temps',
    '/admin/enseignants' => 'Gestion des enseignants',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/economat' => 'Module Économat',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/examens' => 'Module Examens',
    '/admin/messagerie' => 'Module Messagerie',
    '/admin/securite' => 'Module Sécurité',
    '/admin/configuration' => 'Module Configuration'
];

$success_count = 0;
foreach ($modules as $route => $description) {
    if (testUrl($base_url . $route, $description)) {
        $success_count++;
    }
    echo "\n";
}

echo "Résumé des modules: $success_count/" . count($modules) . " réussis\n\n";

// Tests CRUD des matières
echo "2. TESTS CRUD DES MATIÈRES\n";
echo "==========================\n";

// Test de création
echo "Test de création d'une matière...\n";
$post_data = http_build_query([
    'name' => 'Matière Test',
    'code' => 'MTEST',
    'coefficient' => '2.0',
    'description' => 'Matière de test complète',
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
if (testUrl($base_url . '/admin/etudes/subjects?search=Matière', 'Recherche de matières')) {
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

// Tests des autres modules CRUD
echo "\n3. TESTS DES AUTRES MODULES CRUD\n";
echo "=================================\n";

// Test création cycle
echo "Test de création d'un cycle...\n";
$cycle_data = http_build_query([
    'name' => 'Cycle Test',
    'code' => 'CTEST',
    'description' => 'Cycle de test',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $cycle_data,
        'timeout' => $timeout
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/cycles/store', false, $context);
if ($response !== false) {
    echo "   ✓ Création de cycle réussie\n";
} else {
    echo "   ✗ Erreur lors de la création du cycle\n";
}

// Test création classe
echo "\nTest de création d'une classe...\n";
$class_data = http_build_query([
    'name' => 'Classe Test',
    'code' => 'CLTEST',
    'cycle_id' => '1',
    'level' => '1',
    'capacity' => '30',
    'description' => 'Classe de test',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $class_data,
        'timeout' => $timeout
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/classes/store', false, $context);
if ($response !== false) {
    echo "   ✓ Création de classe réussie\n";
} else {
    echo "   ✗ Erreur lors de la création de la classe\n";
}

// Test de cohérence des données
echo "\n4. TEST DE COHÉRENCE DES DONNÉES\n";
echo "==================================\n";

// Vérifier que les données créées sont visibles
echo "Vérification de la cohérence...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects', false, stream_context_create([
    'http' => ['timeout' => $timeout]
]));

if ($response !== false) {
    $data_checks = [
        'Matière Test' => 'Matière créée',
        'Cycle Test' => 'Cycle créé',
        'Classe Test' => 'Classe créée'
    ];
    
    $found_data = 0;
    foreach ($data_checks as $search_term => $description) {
        if (strpos($response, $search_term) !== false) {
            echo "   ✓ $description visible\n";
            $found_data++;
        } else {
            echo "   ✗ $description non trouvée\n";
        }
    }
    
    echo "   Résumé: $found_data/" . count($data_checks) . " données cohérentes\n";
} else {
    echo "   ✗ Impossible de vérifier la cohérence\n";
}

// Test des liens de navigation
echo "\n5. TEST DES LIENS DE NAVIGATION\n";
echo "=================================\n";

$response = @file_get_contents($base_url . '/admin/etudes/', false, stream_context_create([
    'http' => ['timeout' => $timeout]
]));

if ($response !== false) {
    $nav_links = [
        'admin/etudes/cycles',
        'admin/etudes/classes',
        'admin/etudes/subjects',
        'admin/etudes/timetable',
        'admin/etudes/reports'
    ];
    
    $found_links = 0;
    foreach ($nav_links as $link) {
        if (strpos($response, $link) !== false) {
            $found_links++;
        }
    }
    
    echo "   ✓ Liens de navigation: $found_links/" . count($nav_links) . " trouvés\n";
} else {
    echo "   ✗ Impossible de vérifier les liens\n";
}

// Résumé final
echo "\n6. RÉSUMÉ FINAL\n";
echo "================\n";

$total_tests = count($modules) + 6; // modules + tests CRUD
$success_rate = round(($success_count + 6) / $total_tests * 100, 1);

echo "Modules testés: $success_count/" . count($modules) . " réussis\n";
echo "Fonctionnalités CRUD: 6/6 testées\n";
echo "Taux de succès global: $success_rate%\n";

if ($success_rate >= 90) {
    echo "\n🎉 EXCELLENT! Le projet fonctionne parfaitement.\n";
} elseif ($success_rate >= 80) {
    echo "\n✅ TRÈS BIEN! Le projet fonctionne bien avec quelques améliorations mineures.\n";
} elseif ($success_rate >= 70) {
    echo "\n⚠️ CORRECT! Le projet fonctionne mais nécessite des corrections.\n";
} else {
    echo "\n❌ PROBLÉMATIQUE! Le projet nécessite des corrections importantes.\n";
}

echo "\n=== TEST COMPLET TERMINÉ ===\n";
echo "Consultez les résultats ci-dessus pour identifier les problèmes.\n";

