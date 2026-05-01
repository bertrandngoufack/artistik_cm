<?php

// TEST COMPLET DE TOUTES LES ROUTES - APPLICATION LYCOL
echo "=== TEST COMPLET DE TOUTES LES ROUTES ===\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$timeout = 5;

// Fonction pour tester une URL
function testUrl($url, $method = 'GET', $data = null) {
    global $timeout;
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => $timeout,
            'ignore_errors' => true,
            'header' => $method === 'POST' ? 'Content-Type: application/x-www-form-urlencoded' : ''
        ]
    ]);
    
    if ($data && $method === 'POST') {
        $context["http"]["content"] = http_build_query($data);
    }
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    
    return $httpCode;
}

// 1. TEST DES ROUTES PUBLIQUES
echo "1. ROUTES PUBLIQUES\n";
echo "===================\n";

$publicRoutes = [
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/auth/register' => 'Page d\'inscription',
    '/auth/forgot-password' => 'Mot de passe oublié'
];

foreach ($publicRoutes as $route => $description) {
    $url = $baseUrl . $route;
    $code = testUrl($url);
    $status = $code === 200 ? '✅' : ($code === 302 ? '🔄' : '❌');
    echo "$status $description ($route): HTTP $code\n";
}

echo "\n";

// 2. TEST DES ROUTES ADMIN (sans authentification - doivent rediriger)
echo "2. ROUTES ADMIN (sans authentification)\n";
echo "========================================\n";

$adminRoutes = [
    '/admin/dashboard' => 'Tableau de bord admin',
    '/admin/economat' => 'Module économat',
    '/admin/scolarite' => 'Module scolarité',
    '/admin/etudes' => 'Module études',
    '/admin/examens' => 'Module examens',
    '/admin/bibliotheque' => 'Module bibliothèque',
    '/admin/messagerie' => 'Module messagerie',
    '/admin/enseignants' => 'Module enseignants',
    '/admin/securite' => 'Module sécurité',
    '/admin/statistiques' => 'Module statistiques',
    '/admin/configuration' => 'Module configuration'
];

foreach ($adminRoutes as $route => $description) {
    $url = $baseUrl . $route;
    $code = testUrl($url);
    $status = $code === 302 ? '✅' : ($code === 200 ? '⚠️' : '❌');
    echo "$status $description ($route): HTTP $code\n";
}

echo "\n";

// 3. TEST DES ROUTES API
echo "3. ROUTES API\n";
echo "=============\n";

$apiRoutes = [
    '/api/students' => 'API étudiants',
    '/api/teachers' => 'API enseignants',
    '/api/classes' => 'API classes',
    '/api/payments' => 'API paiements',
    '/api/exams' => 'API examens'
];

foreach ($apiRoutes as $route => $description) {
    $url = $baseUrl . $route;
    $code = testUrl($url);
    $status = $code === 200 ? '✅' : ($code === 404 ? '❌' : '⚠️');
    echo "$status $description ($route): HTTP $code\n";
}

echo "\n";

// 4. TEST DES OPÉRATIONS CRUD (POST)
echo "4. OPÉRATIONS CRUD (POST)\n";
echo "=========================\n";

$crudTests = [
    [
        'url' => '/auth/authenticate',
        'data' => ['username' => 'admin', 'password' => 'admin123', 'csrf_test_name' => 'test'],
        'description' => 'Authentification'
    ],
    [
        'url' => '/admin/economat/payments/store',
        'data' => ['student_id' => '1', 'amount' => '50000', 'payment_type' => 'tuition', 'csrf_test_name' => 'test'],
        'description' => 'Création paiement'
    ],
    [
        'url' => '/admin/scolarite/students/store',
        'data' => ['first_name' => 'Test', 'last_name' => 'Student', 'email' => 'test@test.com', 'csrf_test_name' => 'test'],
        'description' => 'Création étudiant'
    ],
    [
        'url' => '/admin/examens/exams/store',
        'data' => ['name' => 'Test Exam', 'subject_id' => '1', 'date' => '2025-01-01', 'csrf_test_name' => 'test'],
        'description' => 'Création examen'
    ]
];

foreach ($crudTests as $test) {
    $url = $baseUrl . $test['url'];
    $code = testUrl($url, 'POST', $test['data']);
    $status = $code === 303 ? '✅' : ($code === 302 ? '🔄' : '❌');
    echo "$status {$test['description']} ({$test['url']}): HTTP $code\n";
}

echo "\n";

// 5. TEST DE COHÉRENCE DES MODULES
echo "5. COHÉRENCE DES MODULES\n";
echo "========================\n";

$modules = [
    'Économat' => ['/admin/economat', '/admin/economat/payments', '/admin/economat/reminders'],
    'Scolarité' => ['/admin/scolarite', '/admin/scolarite/students', '/admin/scolarite/classes'],
    'Études' => ['/admin/etudes', '/admin/etudes/subjects', '/admin/etudes/schedule'],
    'Examens' => ['/admin/examens', '/admin/examens/exams', '/admin/examens/grades'],
    'Bibliothèque' => ['/admin/bibliotheque', '/admin/bibliotheque/books', '/admin/bibliotheque/loans'],
    'Messagerie' => ['/admin/messagerie', '/admin/messagerie/messages', '/admin/messagerie/sent'],
    'Enseignants' => ['/admin/enseignants', '/admin/enseignants/teachers', '/admin/enseignants/schedule'],
    'Sécurité' => ['/admin/securite', '/admin/securite/users', '/admin/securite/logs'],
    'Statistiques' => ['/admin/statistiques', '/admin/statistiques/dashboard', '/admin/statistiques/reports'],
    'Configuration' => ['/admin/configuration', '/admin/configuration/general', '/admin/configuration/database']
];

foreach ($modules as $module => $routes) {
    echo "$module:\n";
    $working = 0;
    foreach ($routes as $route) {
        $url = $baseUrl . $route;
        $code = testUrl($url);
        $status = $code === 302 ? '✅' : ($code === 200 ? '✅' : '❌');
        if ($code === 302 || $code === 200) $working++;
        echo "  $status $route: HTTP $code\n";
    }
    $percentage = round(($working / count($routes)) * 100);
    echo "  📊 Fonctionnel: $working/" . count($routes) . " ($percentage%)\n\n";
}

echo "\n";

// 6. RÉSUMÉ ET RECOMMANDATIONS
echo "6. RÉSUMÉ ET RECOMMANDATIONS\n";
echo "============================\n";

echo "🔍 POINTS À VÉRIFIER:\n";
echo "1. Toutes les routes admin doivent rediriger (HTTP 302) sans authentification\n";
echo "2. Les routes publiques doivent être accessibles (HTTP 200)\n";
echo "3. Les opérations CRUD doivent rediriger après traitement (HTTP 303/302)\n";
echo "4. La cohérence entre les modules doit être maintenue\n";
echo "5. Toutes les vues doivent être présentes et fonctionnelles\n";

echo "\n=== FIN DU TEST COMPLET ===\n";
