<?php

// SCRIPT DE CORRECTION DE LA BOUCLE DE REDIRECTION
echo "=== CORRECTION DE LA BOUCLE DE REDIRECTION ===\n\n";

// 1. VÉRIFICATION DE L'ENVIRONNEMENT
echo "1. VÉRIFICATION DE L'ENVIRONNEMENT\n";
echo "==================================\n";

// Vérifier si on est dans le bon répertoire
$currentDir = getcwd();
echo "Répertoire courant: $currentDir\n";

// Vérifier la structure des fichiers
$requiredFiles = [
    'app/Config/Routes.php',
    'app/Config/App.php',
    'app/Config/Filters.php',
    'app/Controllers/Auth.php',
    'app/Filters/AuthFilter.php',
    'public/index.php',
    'public/.htaccess'
];

echo "\nFichiers requis:\n";
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file (MANQUANT)\n";
    }
}

// 2. VÉRIFICATION DE LA CONFIGURATION
echo "\n2. VÉRIFICATION DE LA CONFIGURATION\n";
echo "====================================\n";

// Vérifier app/Config/App.php
if (file_exists('app/Config/App.php')) {
    $appConfig = file_get_contents('app/Config/App.php');
    if (strpos($appConfig, "baseURL = 'http://localhost:8080/'") !== false) {
        echo "✅ Base URL configurée correctement\n";
    } else {
        echo "❌ Base URL mal configurée\n";
    }
} else {
    echo "❌ Fichier app/Config/App.php manquant\n";
}

// 3. VÉRIFICATION DES ROUTES
echo "\n3. VÉRIFICATION DES ROUTES\n";
echo "==========================\n";

if (file_exists('app/Config/Routes.php')) {
    $routesContent = file_get_contents('app/Config/Routes.php');
    
    // Vérifier que les routes auth ne sont pas protégées
    if (strpos($routesContent, "group('auth'") !== false && strpos($routesContent, "filter' => 'auth'") === false) {
        echo "✅ Routes auth non protégées par le filtre\n";
    } else {
        echo "❌ Routes auth protégées par le filtre (PROBLÈME)\n";
    }
    
    // Vérifier que les routes admin sont protégées
    if (strpos($routesContent, "group('admin'") !== false && strpos($routesContent, "filter' => 'auth'") !== false) {
        echo "✅ Routes admin protégées par le filtre\n";
    } else {
        echo "❌ Routes admin non protégées par le filtre\n";
    }
} else {
    echo "❌ Fichier app/Config/Routes.php manquant\n";
}

// 4. VÉRIFICATION DU CONTRÔLEUR AUTH
echo "\n4. VÉRIFICATION DU CONTRÔLEUR AUTH\n";
echo "===================================\n";

if (file_exists('app/Controllers/Auth.php')) {
    $authContent = file_get_contents('app/Controllers/Auth.php');
    
    // Vérifier la méthode login
    if (strpos($authContent, 'public function login()') !== false) {
        echo "✅ Méthode login présente\n";
        
        // Vérifier la logique de redirection
        if (strpos($authContent, 'session()->get(\'user_id\') && session()->get(\'user_role\')') !== false) {
            echo "✅ Logique de redirection corrigée\n";
        } else {
            echo "❌ Logique de redirection à corriger\n";
        }
    } else {
        echo "❌ Méthode login manquante\n";
    }
} else {
    echo "❌ Fichier app/Controllers/Auth.php manquant\n";
}

// 5. VÉRIFICATION DU FILTRE AUTH
echo "\n5. VÉRIFICATION DU FILTRE AUTH\n";
echo "==============================\n";

if (file_exists('app/Filters/AuthFilter.php')) {
    $filterContent = file_get_contents('app/Filters/AuthFilter.php');
    
    if (strpos($filterContent, 'redirect()->to(\'/auth/login\')') !== false) {
        echo "✅ Filtre redirige vers /auth/login\n";
    } else {
        echo "❌ Filtre ne redirige pas vers /auth/login\n";
    }
    
    if (strpos($filterContent, 'session()->has(\'user_id\')') !== false) {
        echo "✅ Filtre vérifie la session\n";
    } else {
        echo "❌ Filtre ne vérifie pas la session\n";
    }
} else {
    echo "❌ Fichier app/Filters/AuthFilter.php manquant\n";
}

// 6. TEST DE CONNEXION
echo "\n6. TEST DE CONNEXION\n";
echo "====================\n";

$testUrls = [
    'http://localhost:8080/' => 'Page d\'accueil',
    'http://localhost:8080/auth/login' => 'Page de connexion',
    'http://localhost:8080/admin/dashboard' => 'Dashboard admin'
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

// 7. RECOMMANDATIONS
echo "\n7. RECOMMANDATIONS\n";
echo "==================\n";

echo "🔧 ACTIONS À EFFECTUER:\n";
echo "1. Vérifier que le serveur PHP fonctionne correctement\n";
echo "2. Vérifier la configuration des routes\n";
echo "3. Vérifier que /auth/login n'est pas protégé par le filtre\n";
echo "4. Vérifier la logique de redirection dans Auth::login()\n";
echo "5. Tester l'accès à la page de connexion\n";

echo "\n=== FIN DU DIAGNOSTIC ===\n";
