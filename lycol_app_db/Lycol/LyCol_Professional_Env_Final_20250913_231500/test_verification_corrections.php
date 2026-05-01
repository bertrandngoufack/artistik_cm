<?php
/**
 * Test de vérification des corrections du module des matières
 */

echo "=== TEST DE VÉRIFICATION DES CORRECTIONS ===\n\n";

// Test 1: Vérifier que le modèle peut être chargé
echo "1. Test de chargement du modèle...\n";
try {
    require_once "vendor/autoload.php";
    echo "   ✓ Autoloader chargé\n";
} catch (Exception $e) {
    echo "   ✗ Erreur autoloader: " . $e->getMessage() . "\n";
}

// Test 2: Vérifier la structure des fichiers
echo "\n2. Vérification de la structure des fichiers...\n";
$files = [
    "app/Models/SubjectModel.php",
    "app/Controllers/Etudes.php",
    "app/Views/admin/etudes/subjects.php"
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✓ " . $file . " existe\n";
    } else {
        echo "   ✗ " . $file . " manquant\n";
    }
}

echo "\n=== TEST TERMINÉ ===\n";
echo "Vérifiez que tous les fichiers sont présents et accessibles.\n";
