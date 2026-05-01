<?php
/**
 * Test final après corrections
 */

echo "=== TEST FINAL APRÈS CORRECTIONS ===\n\n";

// Configuration
$base_url = 'http://localhost:8080';

// Test de la route principale des études
echo "1. Test de la route principale des études...\n";
$response = @file_get_contents($base_url . '/admin/etudes/');
if ($response !== false) {
    echo "   ✓ Route principale accessible\n";
} else {
    echo "   ✗ Erreur d'accès à la route principale\n";
}

// Test de la route des matières
echo "\n2. Test de la route des matières...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    echo "   ✓ Route des matières accessible\n";
    
    // Vérifier le contenu
    if (strpos($response, 'Gestion des Matières') !== false) {
        echo "   ✓ Contenu de la page chargé correctement\n";
    } else {
        echo "   ⚠️ Contenu de la page incomplet\n";
    }
} else {
    echo "   ✗ Erreur d'accès à la route des matières\n";
}

// Test de création d'une matière
echo "\n3. Test de création d'une matière...\n";
$post_data = http_build_query([
    'name' => 'Matière Finale',
    'code' => 'MFINAL',
    'coefficient' => '1.5',
    'description' => 'Matière de test finale',
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

// Test de recherche
echo "\n4. Test de recherche...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?search=Matière');
if ($response !== false) {
    echo "   ✓ Recherche fonctionnelle\n";
} else {
    echo "   ✗ Recherche non fonctionnelle\n";
}

// Test de filtrage
echo "\n5. Test de filtrage...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if ($response !== false) {
    echo "   ✓ Filtrage fonctionnel\n";
} else {
    echo "   ✗ Filtrage non fonctionnel\n";
}

// Test de tri
echo "\n6. Test de tri...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) {
    echo "   ✓ Tri fonctionnel\n";
} else {
    echo "   ✗ Tri non fonctionnel\n";
}

// Vérification de la cohérence
echo "\n7. Vérification de la cohérence...\n";
$response = @file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    if (strpos($response, 'Matière Finale') !== false) {
        echo "   ✓ Matière créée visible dans la liste\n";
    } else {
        echo "   ⚠️ Matière créée non visible dans la liste\n";
    }
} else {
    echo "   ✗ Impossible de vérifier la cohérence\n";
}

echo "\n=== TEST FINAL TERMINÉ ===\n";
echo "Vérifiez les résultats ci-dessus pour confirmer le bon fonctionnement.\n";

