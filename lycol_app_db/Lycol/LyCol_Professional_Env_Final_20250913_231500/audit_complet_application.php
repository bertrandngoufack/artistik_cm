<?php

// AUDIT COMPLET DE L'APPLICATION KISSAI SCHOOL
echo "=== AUDIT COMPLET DE L'APPLICATION KISSAI SCHOOL ===\n\n";

// 1. VÉRIFICATION DE L'ENVIRONNEMENT
echo "1. VÉRIFICATION DE L'ENVIRONNEMENT\n";
echo "==================================\n";

$currentDir = getcwd();
echo "Répertoire courant: $currentDir\n";

// Vérifier la structure des fichiers
$requiredFiles = [
    'app/Config/Routes.php',
    'app/Config/App.php',
    'app/Config/Filters.php',
    'app/Controllers/Auth.php',
    'app/Controllers/Admin.php',
    'app/Filters/AuthFilter.php',
    'public/index.php',
    'public/.htaccess'
];

echo "\nFichiers de configuration:\n";
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file (MANQUANT)\n";
    }
}

// 2. VÉRIFICATION DES CONTRÔLEURS
echo "\n2. VÉRIFICATION DES CONTRÔLEURS\n";
echo "================================\n";

$controllers = [
    'app/Controllers/Admin.php',
    'app/Controllers/Auth.php',
    'app/Controllers/Economat.php',
    'app/Controllers/Scolarite.php',
    'app/Controllers/Etudes.php',
    'app/Controllers/Examens.php',
    'app/Controllers/Bibliotheque.php',
    'app/Controllers/Messagerie.php',
    'app/Controllers/Enseignants.php',
    'app/Controllers/Securite.php',
    'app/Controllers/Statistiques.php',
    'app/Controllers/Configuration.php'
];

foreach ($controllers as $controller) {
    if (file_exists($controller)) {
        echo "✅ $controller\n";
    } else {
        echo "❌ $controller (MANQUANT)\n";
    }
}

// 3. VÉRIFICATION DES VUES
echo "\n3. VÉRIFICATION DES VUES\n";
echo "=========================\n";

$views = [
    'app/Views/admin/dashboard.php',
    'app/Views/admin/layout.php',
    'app/Views/auth/login.php',
    'app/Views/home/index.php',
    'app/Views/economat/index.php',
    'app/Views/scolarite/index.php',
    'app/Views/etudes/index.php',
    'app/Views/examens/index.php',
    'app/Views/bibliotheque/index.php',
    'app/Views/messagerie/index.php',
    'app/Views/enseignants/index.php',
    'app/Views/securite/index.php',
    'app/Views/statistiques/index.php',
    'app/Views/configuration/index.php'
];

foreach ($views as $view) {
    if (file_exists($view)) {
        echo "✅ $view\n";
    } else {
        echo "❌ $view (MANQUANT)\n";
    }
}

// 4. VÉRIFICATION DES MODÈLES
echo "\n4. VÉRIFICATION DES MODÈLES\n";
echo "============================\n";

$models = [
    'app/Models/UserModel.php',
    'app/Models/StudentModel.php',
    'app/Models/ClassModel.php',
    'app/Models/SubjectModel.php',
    'app/Models/ExamModel.php',
    'app/Models/GradeModel.php',
    'app/Models/PaymentModel.php',
    'app/Models/AbsenceModel.php',
    'app/Models/BookModel.php',
    'app/Models/MessageModel.php',
    'app/Models/LicenseModel.php'
];

foreach ($models as $model) {
    if (file_exists($model)) {
        echo "✅ $model\n";
    } else {
        echo "❌ $model (MANQUANT)\n";
    }
}

// 5. TEST DES ROUTES
echo "\n5. TEST DES ROUTES\n";
echo "==================\n";

$testUrls = [
    'http://localhost:8080/' => 'Page d\'accueil',
    'http://localhost:8080/auth/login' => 'Page de connexion',
    'http://localhost:8080/admin/dashboard' => 'Dashboard admin',
    'http://localhost:8080/admin/economat' => 'Module Économat',
    'http://localhost:8080/admin/scolarite' => 'Module Scolarité',
    'http://localhost:8080/admin/etudes' => 'Module Études',
    'http://localhost:8080/admin/examens' => 'Module Examens',
    'http://localhost:8080/admin/bibliotheque' => 'Module Bibliothèque',
    'http://localhost:8080/admin/messagerie' => 'Module Messagerie',
    'http://localhost:8080/admin/enseignants' => 'Module Enseignants',
    'http://localhost:8080/admin/securite' => 'Module Sécurité',
    'http://localhost:8080/admin/statistiques' => 'Module Statistiques',
    'http://localhost:8080/admin/configuration' => 'Module Configuration'
];

foreach ($testUrls as $url => $description) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 3,
            'ignore_errors' => true
        ]
    ]);
    
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
    
    $status = $httpCode === 200 ? '✅' : ($httpCode === 302 ? '🔄' : '❌');
    echo "$status $description: HTTP $httpCode\n";
}

// 6. TEST D'AUTHENTIFICATION
echo "\n6. TEST D'AUTHENTIFICATION\n";
echo "===========================\n";

// Test de connexion
$authData = http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]);

$authContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $authData,
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$authResponse = @file_get_contents('http://localhost:8080/auth/authenticate', false, $authContext);
$authCode = 200;

if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP/') === 0) {
            $authCode = (int)substr($header, 9, 3);
            break;
        }
    }
}

$authStatus = $authCode === 303 ? '✅' : '❌';
echo "$authStatus Authentification admin/admin123: HTTP $authCode\n";

// 7. RECOMMANDATIONS
echo "\n7. RECOMMANDATIONS\n";
echo "==================\n";

echo "🔧 ACTIONS À EFFECTUER:\n";
echo "1. Créer les modèles manquants\n";
echo "2. Créer les contrôleurs manquants\n";
echo "3. Créer les vues manquantes\n";
echo "4. Tester toutes les routes avec authentification\n";
echo "5. Vérifier la cohérence entre les modules\n";
echo "6. Tester les opérations CRUD\n";

echo "\n=== FIN DE L'AUDIT ===\n";
