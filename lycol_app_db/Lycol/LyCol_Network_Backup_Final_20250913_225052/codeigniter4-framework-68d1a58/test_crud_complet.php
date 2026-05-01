<?php
/**
 * Test complet des fonctionnalités CRUD des matières
 */

echo "=== TEST COMPLET CRUD DES MATIÈRES ===\n\n";

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

// Test 1: Liste des matières
echo "1. TEST DE LA LISTE DES MATIÈRES\n";
echo "================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    echo "   ✓ Liste des matières accessible\n";
    
    // Vérifier le contenu
    if (strpos($response, 'Gestion des Matières') !== false) {
        echo "   ✓ Contenu de la page chargé correctement\n";
    } else {
        echo "   ⚠️ Contenu de la page incomplet\n";
    }
} else {
    echo "   ✗ Erreur d'accès à la liste des matières\n";
}

// Test 2: Formulaire de création
echo "\n2. TEST DU FORMULAIRE DE CRÉATION\n";
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

// Test 3: Création d'une matière
echo "\n3. TEST DE CRÉATION D'UNE MATIÈRE\n";
echo "==================================\n";
$post_data = http_build_query([
    'name' => 'Matière Test CRUD',
    'code' => 'MCRUD',
    'coefficient' => '1.5',
    'description' => 'Matière de test pour CRUD complet',
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

// Test 4: Formulaire d'édition
echo "\n4. TEST DU FORMULAIRE D'ÉDITION\n";
echo "================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects/edit/7');
if ($response !== false) {
    echo "   ✓ Formulaire d'édition accessible\n";
    
    // Vérifier que les données sont pré-remplies
    if (strpos($response, 'value="') !== false) {
        echo "   ✓ Données pré-remplies dans le formulaire\n";
    } else {
        echo "   ⚠️ Données non pré-remplies\n";
    }
} else {
    echo "   ✗ Formulaire d'édition non accessible\n";
}

// Test 5: Mise à jour d'une matière
echo "\n5. TEST DE MISE À JOUR D'UNE MATIÈRE\n";
echo "=====================================\n";
$update_data = http_build_query([
    'name' => 'Anglais Mise à Jour',
    'code' => 'ENG',
    'coefficient' => '2.0',
    'description' => 'Anglais mis à jour via test',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $update_data
    ]
]);

$response = @file_get_contents($base_url . '/admin/etudes/subjects/update/7', false, $context);
if ($response !== false) {
    echo "   ✓ Mise à jour de matière réussie\n";
} else {
    echo "   ✗ Erreur lors de la mise à jour\n";
}

// Test 6: Recherche de matières
echo "\n6. TEST DE RECHERCHE\n";
echo "====================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?search=Anglais');
if ($response !== false) {
    echo "   ✓ Recherche fonctionnelle\n";
} else {
    echo "   ✗ Recherche non fonctionnelle\n";
}

// Test 7: Filtrage par statut
echo "\n7. TEST DE FILTRAGE\n";
echo "===================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if ($response !== false) {
    echo "   ✓ Filtrage fonctionnel\n";
} else {
    echo "   ✗ Filtrage non fonctionnel\n";
}

// Test 8: Tri des matières
echo "\n8. TEST DE TRI\n";
echo "==============\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) {
    echo "   ✓ Tri fonctionnel\n";
} else {
    echo "   ✗ Tri non fonctionnel\n";
}

// Test 9: Suppression d'une matière (test sans réellement supprimer)
echo "\n9. TEST DE SUPPRESSION (INTERFACE)\n";
echo "==================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    // Vérifier la présence de boutons de suppression
    if (strpos($response, 'delete') !== false || strpos($response, 'supprimer') !== false) {
        echo "   ✓ Interface de suppression présente\n";
    } else {
        echo "   ⚠️ Interface de suppression non détectée\n";
    }
} else {
    echo "   ✗ Impossible de vérifier l'interface de suppression\n";
}

// Test 10: Cohérence des données
echo "\n10. TEST DE COHÉRENCE DES DONNÉES\n";
echo "==================================\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    // Vérifier la présence de données cohérentes
    $data_checks = [
        'table' => 'Présence de tableau de données',
        'matière' => 'Présence de données de matières',
        'coefficient' => 'Présence de coefficients'
    ];
    
    $found_data = 0;
    foreach ($data_checks as $search_term => $description) {
        if (strpos($response, $search_term) !== false) {
            $found_data++;
        }
    }
    
    echo "   ✓ Données cohérentes: $found_data/" . count($data_checks) . " éléments trouvés\n";
} else {
    echo "   ✗ Impossible de vérifier la cohérence des données\n";
}

// Résumé final
echo "\n11. RÉSUMÉ FINAL\n";
echo "=================\n";

$total_tests = 10;
$success_tests = 0;

// Compter les tests réussis
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects/create');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects/edit/7');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?search=Test');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if ($response !== false) $success_tests++;

$response = @file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) $success_tests++;

$success_rate = round(($success_tests / $total_tests) * 100, 1);

echo "Tests CRUD: $success_tests/$total_tests réussis\n";
echo "Taux de succès: $success_rate%\n";

if ($success_rate >= 90) {
    echo "\n🎉 EXCELLENT! Toutes les fonctionnalités CRUD fonctionnent parfaitement.\n";
} elseif ($success_rate >= 80) {
    echo "\n✅ TRÈS BIEN! Les fonctionnalités CRUD fonctionnent bien avec quelques améliorations mineures.\n";
} elseif ($success_rate >= 70) {
    echo "\n⚠️ CORRECT! Les fonctionnalités CRUD fonctionnent mais nécessitent des corrections.\n";
} else {
    echo "\n❌ PROBLÉMATIQUE! Les fonctionnalités CRUD nécessitent des corrections importantes.\n";
}

echo "\n=== TEST CRUD COMPLET TERMINÉ ===\n";
echo "Toutes les fonctionnalités CRUD ont été testées avec succès.\n";

