<?php
/**
 * Test de la vérification de licence
 */

echo "=== TEST VÉRIFICATION LICENCE ===\n\n";

// Inclure CodeIgniter
require_once 'vendor/autoload.php';

// Configuration de base
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');

try {
    // Charger CodeIgniter
    $app = new \CodeIgniter\CodeIgniter(new \Config\Paths());
    $app->initialize();
    
    // Créer une instance du contrôleur Auth
    $auth = new \App\Controllers\Auth();
    
    // Tester la méthode checkLicense
    echo "🔍 Test de la méthode checkLicense...\n";
    
    // Utiliser la réflexion pour accéder à la méthode privée
    $reflection = new ReflectionClass($auth);
    $method = $reflection->getMethod('checkLicense');
    $method->setAccessible(true);
    
    $result = $method->invoke($auth);
    
    echo "Résultat: " . ($result ? 'true' : 'false') . "\n";
    
    if ($result) {
        echo "✅ Licence valide\n";
    } else {
        echo "❌ Licence invalide\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n�� TEST TERMINÉ\n";
