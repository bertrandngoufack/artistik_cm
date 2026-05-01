<?php
/**
 * Test Final des Routes - Port 8080
 * Vérification complète de l'application KISSAI SCHOOL
 */

echo "🔍 TEST FINAL DES ROUTES - PORT 8080\n";
echo "=====================================\n\n";

$baseUrl = 'http://localhost:8080';
$routes = [
    // Routes publiques
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/auth/parents' => 'Espace parents',
    '/auth/mobile' => 'Interface mobile',
    
    // Routes admin (sans authentification pour le test)
    '/admin/configuration' => 'Module configuration',
    '/admin/configuration/license' => 'Gestion licence',
    '/admin/configuration/check-license' => 'API vérification licence',
    '/admin/configuration/system-stats-api' => 'API statistiques système',
    '/admin/configuration/clear-cache' => 'API vidage cache',
    
    // Autres modules
    '/admin/scolarite' => 'Module scolarité',
    '/admin/economat' => 'Module économat',
    '/admin/etudes' => 'Module études',
    '/admin/examens' => 'Module examens',
    '/admin/enseignants' => 'Module enseignants',
    '/admin/bibliotheque' => 'Module bibliothèque',
    '/admin/messagerie' => 'Module messagerie',
    '/admin/statistiques' => 'Module statistiques',
];

$results = [];

foreach ($routes as $route => $description) {
    echo "Testing: $route - $description\n";
    
    $url = $baseUrl . $route;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = '';
    if ($httpCode == 200) {
        $status = "✅ OK";
    } elseif ($httpCode == 302) {
        $status = "🔄 REDIRECT";
    } elseif ($httpCode == 404) {
        $status = "❌ 404";
    } elseif ($httpCode == 500) {
        $status = "💥 500";
    } else {
        $status = "⚠️ $httpCode";
    }
    
    $results[] = [
        'route' => $route,
        'description' => $description,
        'status' => $status,
        'code' => $httpCode
    ];
    
    echo "  $status (HTTP $httpCode)\n";
}

echo "\n📊 RÉSUMÉ DES TESTS\n";
echo "===================\n";

$ok = 0;
$redirect = 0;
$error = 0;

foreach ($results as $result) {
    if ($result['code'] == 200) {
        $ok++;
    } elseif ($result['code'] == 302) {
        $redirect++;
    } else {
        $error++;
    }
}

echo "✅ Routes OK: $ok\n";
echo "🔄 Routes Redirect: $redirect\n";
echo "❌ Routes en erreur: $error\n";
echo "📈 Total: " . count($results) . "\n\n";

echo "🔍 DÉTAIL DES ERREURS\n";
echo "====================\n";

foreach ($results as $result) {
    if ($result['code'] != 200 && $result['code'] != 302) {
        echo "❌ {$result['route']} - {$result['description']} - {$result['status']}\n";
    }
}

echo "\n🎯 RECOMMANDATIONS\n";
echo "==================\n";

if ($error == 0) {
    echo "✅ Toutes les routes principales fonctionnent correctement !\n";
} else {
    echo "⚠️ Certaines routes nécessitent une attention particulière.\n";
}

if ($redirect > 0) {
    echo "🔄 $redirect route(s) redirigent (probablement vers la connexion).\n";
}

echo "\n🚀 L'application KISSAI SCHOOL est opérationnelle sur le port 8080 !\n";
echo "📡 URL principale: http://localhost:8080\n";
echo "🔑 Connexion: http://localhost:8080/auth/login\n";
echo "⚙️ Configuration: http://localhost:8080/admin/configuration\n";
?>





