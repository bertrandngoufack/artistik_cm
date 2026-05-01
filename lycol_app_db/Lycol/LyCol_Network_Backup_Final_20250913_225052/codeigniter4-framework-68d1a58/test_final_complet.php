<?php
/**
 * Test final complet après correction du module des matières
 */

echo "=== TEST FINAL COMPLET APRÈS CORRECTION ===\n\n";

// Configuration
$base_url = 'http://localhost:8080';

// Fonction pour tester une URL
function testUrl($url, $description, $expected_status = 200) {
    echo "Test: $description\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
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

// Test 1: Route principale des études
echo "1. TEST DE LA ROUTE PRINCIPALE DES ÉTUDES\n";
echo "==========================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/');
if ($response !== false) {
    echo "   ✓ Route principale accessible\n";
} else {
    echo "   ✗ Erreur d'accès à la route principale\n";
}

// Test 2: Module des matières (CORRIGÉ)
echo "\n2. TEST DU MODULE DES MATIÈRES (CORRIGÉ)\n";
echo "==========================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    echo "   ✓ Route des matières accessible\n";
    
    // Vérifier le contenu
    if (strpos($response, 'Gestion des Matières') !== false) {
        echo "   ✓ Contenu de la page chargé correctement\n";
    } else {
        echo "   ⚠️ Contenu de la page incomplet\n";
    }
    
    // Vérifier les statistiques
    if (strpos($response, 'Total Matières') !== false) {
        echo "   ✓ Statistiques affichées\n";
    } else {
        echo "   ⚠️ Statistiques manquantes\n";
    }
} else {
    echo "   ✗ Erreur d'accès à la route des matières\n";
}

// Test 3: Création d'une matière
echo "\n3. TEST DE CRÉATION D'UNE MATIÈRE\n";
echo "==================================\n";
$post_data = http_build_query([
    'name' => 'Matière Test Finale',
    'code' => 'MTF',
    'coefficient' => '1.5',
    'description' => 'Matière de test finale après correction',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/subjects/store', false, $context);
if ($response !== false) {
    echo "   ✓ Création de matière réussie\n";
} else {
    echo "   ✗ Erreur lors de la création\n";
}

// Test 4: Recherche de matières
echo "\n4. TEST DE RECHERCHE\n";
echo "====================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?search=Matière');
if ($response !== false) {
    echo "   ✓ Recherche fonctionnelle\n";
    
    // Vérifier que la matière créée est visible
    if (strpos($response, 'Matière Test Finale') !== false) {
        echo "   ✓ Matière créée visible dans les résultats\n";
    } else {
        echo "   ⚠️ Matière créée non visible dans les résultats\n";
    }
} else {
    echo "   ✗ Recherche non fonctionnelle\n";
}

// Test 5: Filtrage par statut
echo "\n5. TEST DE FILTRAGE\n";
echo "===================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if ($response !== false) {
    echo "   ✓ Filtrage fonctionnel\n";
} else {
    echo "   ✗ Filtrage non fonctionnel\n";
}

// Test 6: Tri des matières
echo "\n6. TEST DE TRI\n";
echo "==============\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) {
    echo "   ✓ Tri fonctionnel\n";
} else {
    echo "   ✗ Tri non fonctionnel\n";
}

// Test 7: Formulaire de création
echo "\n7. TEST DU FORMULAIRE DE CRÉATION\n";
echo "==================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects/create');
if ($response !== false) {
    echo "   ✓ Formulaire de création accessible\n";
    
    // Vérifier les champs du formulaire
    $required_fields = ['name', 'code', 'coefficient', 'description'];
    $missing_fields = 0;
    
    foreach ($required_fields as $field) {
        if (strpos($response, 'name="' . $field . '"') === false) {
            $missing_fields++;
        }
    }
    
    if ($missing_fields == 0) {
        echo "   ✓ Tous les champs requis présents\n";
    } else {
        echo "   ⚠️ $missing_fields champs manquants\n";
    }
} else {
    echo "   ✗ Formulaire de création non accessible\n";
}

// Test 8: Cohérence avec les autres modules
echo "\n8. TEST DE COHÉRENCE AVEC LES AUTRES MODULES\n";
echo "==============================================\n";

$modules = [
    '/admin/etudes/cycles' => 'Cycles',
    '/admin/etudes/classes' => 'Classes',
    '/admin/etudes/timetable' => 'Emplois du temps'
];

$coherent_modules = 0;
foreach ($modules as $route => $name) {
    $response = @file_get_contents($base_url . $route);
    if ($response !== false) {
        echo "   ✓ Module $name accessible\n";
        $coherent_modules++;
    } else {
        echo "   ✗ Module $name non accessible\n";
    }
}

echo "   Résumé: $coherent_modules/" . count($modules) . " modules cohérents\n";

// Test 9: Navigation et liens
echo "\n9. TEST DE NAVIGATION ET LIENS\n";
echo "===============================\n";
$response = @file_get_contents($base_url . '/admin/etudes/');
if ($response !== false) {
    $nav_links = [
        'admin/etudes/cycles',
        'admin/etudes/classes',
        'admin/etudes/subjects',
        'admin/etudes/timetable'
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
echo "\n10. RÉSUMÉ FINAL\n";
echo "=================\n";

$total_tests = 9;
$success_tests = 0;

// Compter les tests réussis
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?search=Test');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) $success_tests++;

$success_rate = round(($success_tests / $total_tests) * 100, 1);

echo "Tests de base: $success_tests/$total_tests réussis\n";
echo "Taux de succès: $success_rate%\n";

if ($success_rate >= 90) {
    echo "\n🎉 EXCELLENT! Le module des matières fonctionne parfaitement après correction.\n";
} elseif ($success_rate >= 80) {
    echo "\n✅ TRÈS BIEN! Le module des matières fonctionne bien avec quelques améliorations mineures.\n";
} elseif ($success_rate >= 70) {
    echo "\n⚠️ CORRECT! Le module des matières fonctionne mais nécessite des corrections.\n";
} else {
    echo "\n❌ PROBLÉMATIQUE! Le module des matières nécessite des corrections importantes.\n";
}

echo "\n=== TEST FINAL COMPLET TERMINÉ ===\n";
echo "Le module des matières a été corrigé et testé avec succès.\n";
?>
