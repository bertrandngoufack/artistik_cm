<?php
/**
 * Test direct de l'authentification
 */

echo "=== TEST AUTHENTIFICATION DIRECT ===\n\n";

// Simuler une requête POST
$_POST['username'] = 'admin';
$_POST['password'] = 'admin123';

// Inclure CodeIgniter
require_once 'vendor/autoload.php';

// Configuration de base
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');

// Charger CodeIgniter
$app = new \CodeIgniter\CodeIgniter(new \Config\Paths());
$app->initialize();

// Créer une instance du contrôleur Auth
$auth = new \App\Controllers\Auth();

// Tester la méthode authenticate
try {
    echo "🔍 Test de la méthode authenticate...\n";
    
    // Simuler la requête
    $request = new \CodeIgniter\HTTP\IncomingRequest(
        new \Config\App(),
        new \CodeIgniter\HTTP\URI(),
        null,
        new \CodeIgniter\HTTP\UserAgent()
    );
    
    // Définir les données POST
    $request->setMethod('post');
    $request->setBody(http_build_query($_POST));
    
    // Injecter la requête dans le contrôleur
    $auth->request = $request;
    
    // Appeler la méthode authenticate
    $result = $auth->authenticate();
    
    echo "✅ Méthode authenticate exécutée avec succès\n";
    echo "Résultat: " . get_class($result) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🎯 TEST TERMINÉ\n";




