<?php

// DIAGNOSTIC COMPLET DE L'APPLICATION LYCOL
echo "=== DIAGNOSTIC COMPLET - APPLICATION LYCOL ===\n\n";

// 1. VÉRIFICATION DE L'ENVIRONNEMENT
echo "1. ENVIRONNEMENT\n";
echo "================\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OS: " . php_uname() . "\n";
echo "Répertoire courant: " . getcwd() . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 2. VÉRIFICATION DES FICHIERS DE CONFIGURATION
echo "2. CONFIGURATION\n";
echo "================\n";

$configFiles = [
    'app/Config/App.php' => 'Configuration principale',
    'app/Config/Routes.php' => 'Configuration des routes',
    'app/Config/Database.php' => 'Configuration de la base de données',
    'app/Config/Filters.php' => 'Configuration des filtres',
    'public/.htaccess' => 'Configuration Apache',
    'public/index.php' => 'Point d\'entrée de l\'application'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// 3. VÉRIFICATION DES CONTRÔLEURS
echo "3. CONTRÔLEURS\n";
echo "==============\n";

$controllers = [
    'app/Controllers/Home.php' => 'Contrôleur principal',
    'app/Controllers/Auth.php' => 'Contrôleur d\'authentification',
    'app/Controllers/Admin.php' => 'Contrôleur admin',
    'app/Controllers/Economat.php' => 'Contrôleur économat',
    'app/Controllers/Scolarite.php' => 'Contrôleur scolarité',
    'app/Controllers/Etudes.php' => 'Contrôleur études',
    'app/Controllers/Examens.php' => 'Contrôleur examens',
    'app/Controllers/Bibliotheque.php' => 'Contrôleur bibliothèque',
    'app/Controllers/Messagerie.php' => 'Contrôleur messagerie',
    'app/Controllers/Enseignants.php' => 'Contrôleur enseignants',
    'app/Controllers/Securite.php' => 'Contrôleur sécurité',
    'app/Controllers/Statistiques.php' => 'Contrôleur statistiques',
    'app/Controllers/Configuration.php' => 'Contrôleur configuration'
];

foreach ($controllers as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// 4. VÉRIFICATION DES MODÈLES
echo "4. MODÈLES\n";
echo "==========\n";

$models = [
    'app/Models/UserModel.php' => 'Modèle utilisateur',
    'app/Models/StudentModel.php' => 'Modèle étudiant',
    'app/Models/PaymentModel.php' => 'Modèle paiement',
    'app/Models/ClassModel.php' => 'Modèle classe',
    'app/Models/ExamModel.php' => 'Modèle examen',
    'app/Models/LicenseModel.php' => 'Modèle licence'
];

foreach ($models as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// 5. VÉRIFICATION DES VUES
echo "5. VUES\n";
echo "=======\n";

$views = [
    'app/Views/auth/login.php' => 'Vue de connexion',
    'app/Views/home/index.php' => 'Vue d\'accueil',
    'app/Views/admin/dashboard.php' => 'Vue tableau de bord admin',
    'app/Views/economat/index.php' => 'Vue économat',
    'app/Views/scolarite/index.php' => 'Vue scolarité',
    'app/Views/etudes/index.php' => 'Vue études',
    'app/Views/examens/index.php' => 'Vue examens',
    'app/Views/bibliotheque/index.php' => 'Vue bibliothèque',
    'app/Views/messagerie/index.php' => 'Vue messagerie',
    'app/Views/enseignants/index.php' => 'Vue enseignants',
    'app/Views/securite/index.php' => 'Vue sécurité',
    'app/Views/statistiques/index.php' => 'Vue statistiques',
    'app/Views/configuration/index.php' => 'Vue configuration'
];

foreach ($views as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// 6. VÉRIFICATION DES FILTRES
echo "6. FILTRES\n";
echo "==========\n";

$filters = [
    'app/Filters/AuthFilter.php' => 'Filtre d\'authentification',
    'app/Filters/ParentFilter.php' => 'Filtre parent',
    'app/Filters/MobileFilter.php' => 'Filtre mobile'
];

foreach ($filters as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MANQUANT)\n";
    }
}

echo "\n";

// 7. TEST DE CONNEXION AU SERVEUR
echo "7. TEST DE CONNEXION\n";
echo "====================\n";

$testUrls = [
    'http://localhost:8080/' => 'Page d\'accueil',
    'http://localhost:8080/auth/login' => 'Page de connexion',
    'http://localhost:8080/admin/economat' => 'Module économat',
    'http://localhost:8080/index.php' => 'Index PHP direct'
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
    
    echo "$description: HTTP $httpCode\n";
}

echo "\n";

// 8. VÉRIFICATION DES PERMISSIONS
echo "8. PERMISSIONS\n";
echo "==============\n";

$directories = [
    'app' => 'Répertoire application',
    'public' => 'Répertoire public',
    'writable' => 'Répertoire writable',
    'app/Views' => 'Répertoire vues',
    'app/Controllers' => 'Répertoire contrôleurs',
    'app/Models' => 'Répertoire modèles'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "✅ $description: $dir (Permissions: $perms)\n";
    } else {
        echo "❌ $description: $dir (MANQUANT)\n";
    }
}

echo "\n";

// 9. VÉRIFICATION DE LA BASE DE DONNÉES
echo "9. BASE DE DONNÉES\n";
echo "==================\n";

if (file_exists('app/Config/Database.php')) {
    echo "✅ Fichier de configuration de base de données présent\n";
    
    // Essayer de lire la configuration
    try {
        $config = include 'app/Config/Database.php';
        if (isset($config['default'])) {
            $dbConfig = $config['default'];
            echo "✅ Configuration de base de données valide\n";
            echo "   - Host: " . ($dbConfig['hostname'] ?? 'N/A') . "\n";
            echo "   - Database: " . ($dbConfig['database'] ?? 'N/A') . "\n";
            echo "   - Username: " . ($dbConfig['username'] ?? 'N/A') . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Erreur de lecture de la configuration: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Fichier de configuration de base de données manquant\n";
}

echo "\n";

// 10. RECOMMANDATIONS
echo "10. RECOMMANDATIONS\n";
echo "===================\n";

echo "🔧 ACTIONS À EFFECTUER:\n";
echo "1. Vérifier que le serveur PHP fonctionne correctement\n";
echo "2. Vérifier la configuration des routes\n";
echo "3. Créer les vues manquantes\n";
echo "4. Créer les contrôleurs manquants\n";
echo "5. Créer les modèles manquants\n";
echo "6. Tester toutes les routes avec curl\n";
echo "7. Vérifier la cohérence entre les modules\n";

echo "\n=== FIN DU DIAGNOSTIC ===\n";
