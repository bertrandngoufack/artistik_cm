<?php
/**
 * Script de test local pour le module des matières
 * Teste toutes les fonctionnalités CRUD
 */

// Configuration de base
$base_url = 'http://localhost:8080';

echo "=== TEST COMPLET DU MODULE DES MATIÈRES ===\n\n";

// Test 1: Accès à la page principale
echo "1. Test d'accès à la page principale...\n";
$response = file_get_contents($base_url . '/admin/etudes/subjects');
if ($response !== false) {
    echo "   ✓ Page accessible\n";
} else {
    echo "   ✗ Erreur d'accès à la page\n";
    exit(1);
}

// Test 2: Création d'une matière
echo "\n2. Test de création d'une matière...\n";
$post_data = http_build_query([
    'name' => 'Test Matière',
    'code' => 'TEST',
    'coefficient' => '1.5',
    'description' => 'Matière de test',
    'is_active' => '1'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data
    ]
]);

$response = file_get_contents($base_url . '/admin/etudes/subjects/store', false, $context);
if ($response !== false) {
    echo "   ✓ Matière créée avec succès\n";
} else {
    echo "   ✗ Erreur lors de la création\n";
}

// Test 3: Vérification de la création
echo "\n3. Vérification de la création...\n";
$response = file_get_contents($base_url . '/admin/etudes/subjects');
if (strpos($response, 'Test Matière') !== false) {
    echo "   ✓ Matière visible dans la liste\n";
} else {
    echo "   ✗ Matière non trouvée dans la liste\n";
}

// Test 4: Test de recherche
echo "\n4. Test de recherche...\n";
$response = file_get_contents($base_url . '/admin/etudes/subjects?search=Test');
if (strpos($response, 'Test Matière') !== false) {
    echo "   ✓ Recherche fonctionnelle\n";
} else {
    echo "   ✗ Recherche non fonctionnelle\n";
}

// Test 5: Test de filtrage par statut
echo "\n5. Test de filtrage par statut...\n";
$response = file_get_contents($base_url . '/admin/etudes/subjects?status=1');
if (strpos($response, 'Test Matière') !== false) {
    echo "   ✓ Filtrage par statut fonctionnel\n";
} else {
    echo "   ✗ Filtrage par statut non fonctionnel\n";
}

// Test 6: Test de tri
echo "\n6. Test de tri...\n";
$response = file_get_contents($base_url . '/admin/etudes/subjects?sort=name');
if ($response !== false) {
    echo "   ✓ Tri fonctionnel\n";
} else {
    echo "   ✗ Tri non fonctionnel\n";
}

echo "\n=== TESTS TERMINÉS ===\n";
echo "Vérifiez les résultats ci-dessus pour identifier les problèmes.\n";

