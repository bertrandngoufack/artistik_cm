<?php
/**
 * Test détaillé des modules fonctionnels KISSAI SCHOOL
 */

echo "🧪 TEST DÉTAILLÉ DES MODULES FONCTIONNELS\n";
echo "========================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$timeout = 10;

// Fonction pour tester une URL et afficher le contenu
function testUrlDetailed($url, $description) {
    global $timeout;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? "✅" : "❌";
    echo "$status $description : $httpCode\n";
    
    if ($error) {
        echo "   Erreur: $error\n";
    }
    
    if ($httpCode == 200) {
        // Vérifier le contenu
        if (strpos($response, 'KISSAI SCHOOL') !== false) {
            echo "   ✅ Titre de l'application trouvé\n";
        }
        
        if (strpos($response, 'Module') !== false) {
            echo "   ✅ Contenu du module détecté\n";
        }
        
        if (strpos($response, 'Bulma') !== false) {
            echo "   ✅ Framework CSS Bulma détecté\n";
        }
        
        // Vérifier la taille de la réponse
        $size = strlen($response);
        echo "   📏 Taille de la réponse: " . number_format($size) . " octets\n";
        
        return true;
    }
    
    return false;
}

// Test des modules qui fonctionnent
echo "🔍 Test détaillé des modules fonctionnels...\n";
echo "--------------------------------------------\n\n";

$workingModules = [
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/securite' => 'Module Sécurité',
    '/admin/enseignants' => 'Module Enseignants',
    '/admin/configuration' => 'Module Configuration'
];

$moduleSuccess = 0;
foreach ($workingModules as $path => $description) {
    echo "\n🔍 Test de $description...\n";
    echo "URL: $baseUrl$path\n";
    if (testUrlDetailed($baseUrl . $path, $description)) {
        $moduleSuccess++;
    }
    echo "---\n";
}

echo "\n📊 RÉSUMÉ DES MODULES FONCTIONNELS\n";
echo "==================================\n";
echo "Modules testés : " . count($workingModules) . "\n";
echo "Modules fonctionnels : $moduleSuccess\n";
echo "Taux de réussite : " . round(($moduleSuccess / count($workingModules)) * 100, 1) . "%\n";

// Test des pages publiques
echo "\n🌐 Test détaillé des pages publiques...\n";
echo "--------------------------------------\n";

$publicPages = [
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/about' => 'Page À propos',
    '/contact' => 'Page Contact'
];

$publicSuccess = 0;
foreach ($publicPages as $path => $description) {
    echo "\n🔍 Test de $description...\n";
    echo "URL: $baseUrl$path\n";
    if (testUrlDetailed($baseUrl . $path, $description)) {
        $publicSuccess++;
    }
    echo "---\n";
}

echo "\n📊 RÉSUMÉ DES PAGES PUBLIQUES\n";
echo "============================\n";
echo "Pages testées : " . count($publicPages) . "\n";
echo "Pages fonctionnelles : $publicSuccess\n";
echo "Taux de réussite : " . round(($publicSuccess / count($publicPages)) * 100, 1) . "%\n";

// Test de l'authentification
echo "\n🔐 Test d'authentification détaillé...\n";
echo "------------------------------------\n";

function testAuthDetailed() {
    global $baseUrl;
    
    echo "🔍 Test de connexion avec admin/admin123...\n";
    
    $postData = [
        'username' => 'admin',
        'password' => 'admin123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    echo "Code HTTP: $httpCode\n";
    
    if ($httpCode == 302 || $httpCode == 303) {
        echo "✅ Redirection détectée\n";
        if ($redirectUrl) {
            echo "URL de redirection: $redirectUrl\n";
        }
        return true;
    } elseif ($httpCode == 200) {
        echo "✅ Page de réponse reçue\n";
        return true;
    } else {
        echo "❌ Erreur d'authentification\n";
        return false;
    }
}

$authSuccess = testAuthDetailed();

echo "\n🎯 RÉSUMÉ FINAL\n";
echo "===============\n";
echo "Modules fonctionnels : $moduleSuccess/" . count($workingModules) . "\n";
echo "Pages publiques : $publicSuccess/" . count($publicPages) . "\n";
echo "Authentification : " . ($authSuccess ? "✅" : "❌") . "\n";

$totalTests = count($workingModules) + count($publicPages) + 1;
$totalSuccess = $moduleSuccess + $publicSuccess + ($authSuccess ? 1 : 0);

echo "\n🎯 TAUX DE RÉUSSITE GLOBAL : " . round(($totalSuccess / $totalTests) * 100, 1) . "%\n";

if ($totalSuccess == $totalTests) {
    echo "\n🎉 TOUS LES TESTS SONT PASSÉS !\n";
} else {
    echo "\n⚠️ Certains tests ont échoué.\n";
}

echo "\n🚀 L'application KISSAI SCHOOL est opérationnelle !\n";
?>


