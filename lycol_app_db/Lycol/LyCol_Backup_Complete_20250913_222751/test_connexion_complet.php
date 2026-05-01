<?php
/**
 * Test complet de connexion et diagnostic de licence
 * Vérification approfondie du système d'authentification
 */

echo "🔍 TEST COMPLET DE CONNEXION ET DIAGNOSTIC\n";
echo "=========================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$username = 'admin';
$password = 'admin123';

// Fonction pour effectuer une requête curl
function makeRequest($url, $method = 'GET', $data = null, $cookies = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test 1: Page de connexion
echo "1️⃣ Test de la page de connexion\n";
echo "-------------------------------\n";

$loginPage = makeRequest($baseUrl . '/auth/login');
echo "   URL: " . $baseUrl . '/auth/login' . "\n";
echo "   Code HTTP: " . $loginPage['http_code'] . "\n";

if ($loginPage['http_code'] == 200) {
    echo "   ✅ Page de connexion accessible\n";
    
    // Vérification du message d'erreur de licence
    if (strpos($loginPage['response'], 'Licence expirée') !== false || 
        strpos($loginPage['response'], 'License expired') !== false ||
        strpos($loginPage['response'], 'licence invalide') !== false) {
        echo "   ❌ Message d'erreur de licence détecté\n";
        echo "   🔍 Recherche du message exact...\n";
        
        // Recherche plus précise
        preg_match('/<div[^>]*class[^>]*alert[^>]*>.*?(Licence|License).*?<\/div>/si', $loginPage['response'], $matches);
        if ($matches) {
            echo "   📝 Message trouvé: " . strip_tags($matches[0]) . "\n";
        }
    } else {
        echo "   ✅ Aucun message d'erreur de licence\n";
    }
    
    // Vérification du formulaire
    if (strpos($loginPage['response'], 'username') !== false || 
        strpos($loginPage['response'], 'Nom d\'utilisateur') !== false) {
        echo "   ✅ Formulaire de connexion présent\n";
    } else {
        echo "   ❌ Formulaire de connexion manquant\n";
    }
    
} else {
    echo "   ❌ Page de connexion inaccessible\n";
    echo "   Erreur: " . $loginPage['error'] . "\n";
}

echo "\n";

// Test 2: Connexion avec authentification
echo "2️⃣ Test d'authentification\n";
echo "---------------------------\n";

// Première requête pour obtenir les cookies de session
$sessionRequest = makeRequest($baseUrl . '/auth/login');
$cookies = '';
if (preg_match('/Set-Cookie: ([^;]+)/', $sessionRequest['response'], $cookieMatches)) {
    $cookies = $cookieMatches[1];
}

// Données de connexion
$loginData = http_build_query([
    'username' => $username,
    'password' => $password,
    'csrf_test_name' => 'test_token'
]);

$authRequest = makeRequest($baseUrl . '/auth/authenticate', 'POST', $loginData, $cookies);
echo "   URL: " . $baseUrl . '/auth/authenticate' . "\n";
echo "   Code HTTP: " . $authRequest['http_code'] . "\n";

if ($authRequest['http_code'] == 200 || $authRequest['http_code'] == 302) {
    echo "   ✅ Authentification réussie\n";
} else {
    echo "   ❌ Échec de l'authentification\n";
    echo "   Erreur: " . $authRequest['error'] . "\n";
}

echo "\n";

// Test 3: Accès au dashboard
echo "3️⃣ Test d'accès au dashboard\n";
echo "----------------------------\n";

$dashboardRequest = makeRequest($baseUrl . '/admin/dashboard', 'GET', null, $cookies);
echo "   URL: " . $baseUrl . '/admin/dashboard' . "\n";
echo "   Code HTTP: " . $dashboardRequest['http_code'] . "\n";

if ($dashboardRequest['http_code'] == 200) {
    echo "   ✅ Dashboard accessible\n";
    
    // Vérification du contenu du dashboard
    if (strpos($dashboardRequest['response'], 'Tableau de bord') !== false || 
        strpos($dashboardRequest['response'], 'Dashboard') !== false) {
        echo "   ✅ Contenu du dashboard correct\n";
    } else {
        echo "   ⚠️ Contenu du dashboard inattendu\n";
    }
    
} else {
    echo "   ❌ Dashboard inaccessible\n";
    echo "   Erreur: " . $dashboardRequest['error'] . "\n";
}

echo "\n";

// Test 4: Vérification de la licence dans la base de données
echo "4️⃣ Vérification de la licence en base\n";
echo "-------------------------------------\n";

try {
    $pdo = new PDO("mysql:host=100.69.65.33;port=13306;dbname=lycol_db", "root", "Bateau123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM licenses WHERE id = 1");
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($license) {
        echo "   ✅ Licence trouvée en base\n";
        echo "   Type: " . $license['license_type'] . "\n";
        echo "   Statut: " . $license['status'] . "\n";
        echo "   Expiration: " . $license['expiry_date'] . "\n";
        
        if ($license['license_type'] === 'PERMANENT' && $license['status'] === 'ACTIVE') {
            echo "   ✅ Licence définitive active\n";
        } else {
            echo "   ❌ Licence non définitive ou inactive\n";
        }
    } else {
        echo "   ❌ Aucune licence trouvée\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Erreur de connexion à la base: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Vérification du cache et des sessions
echo "5️⃣ Vérification du cache et sessions\n";
echo "-------------------------------------\n";

// Vérification des fichiers de cache
$cacheDir = __DIR__ . '/writable/cache';
if (is_dir($cacheDir)) {
    $cacheFiles = glob($cacheDir . '/*');
    echo "   📁 Fichiers de cache: " . count($cacheFiles) . "\n";
    
    if (count($cacheFiles) > 0) {
        echo "   ⚠️ Cache non vide - possible problème de cache\n";
        echo "   💡 Suggestion: Vider le cache\n";
    } else {
        echo "   ✅ Cache vide\n";
    }
} else {
    echo "   ❌ Répertoire de cache introuvable\n";
}

// Vérification des sessions
$sessionDir = __DIR__ . '/writable/session';
if (is_dir($sessionDir)) {
    $sessionFiles = glob($sessionDir . '/*');
    echo "   📁 Fichiers de session: " . count($sessionFiles) . "\n";
} else {
    echo "   ❌ Répertoire de session introuvable\n";
}

echo "\n";

// Test 6: Vérification des logs
echo "6️⃣ Vérification des logs\n";
echo "------------------------\n";

$logDir = __DIR__ . '/writable/logs';
if (is_dir($logDir)) {
    $logFiles = glob($logDir . '/log-*.log');
    if (count($logFiles) > 0) {
        $latestLog = end($logFiles);
        echo "   📄 Dernier fichier de log: " . basename($latestLog) . "\n";
        
        // Lecture des dernières lignes du log
        $logContent = file_get_contents($latestLog);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -10);
        
        echo "   📝 Dernières lignes du log:\n";
        foreach ($recentLines as $line) {
            if (trim($line) && (strpos($line, 'ERROR') !== false || strpos($line, 'WARNING') !== false)) {
                echo "      " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ❌ Aucun fichier de log trouvé\n";
    }
} else {
    echo "   ❌ Répertoire de logs introuvable\n";
}

echo "\n";

// Résumé final
echo "🎯 RÉSUMÉ DU DIAGNOSTIC\n";
echo "======================\n";

$issues = [];
$successes = [];

if ($loginPage['http_code'] == 200) {
    $successes[] = "Page de connexion accessible";
} else {
    $issues[] = "Page de connexion inaccessible";
}

if (strpos($loginPage['response'], 'Licence expirée') !== false) {
    $issues[] = "Message d'erreur de licence présent";
} else {
    $successes[] = "Aucun message d'erreur de licence";
}

if ($authRequest['http_code'] == 200 || $authRequest['http_code'] == 302) {
    $successes[] = "Authentification réussie";
} else {
    $issues[] = "Échec de l'authentification";
}

if ($dashboardRequest['http_code'] == 200) {
    $successes[] = "Dashboard accessible";
} else {
    $issues[] = "Dashboard inaccessible";
}

echo "✅ Succès (" . count($successes) . "):\n";
foreach ($successes as $success) {
    echo "   • " . $success . "\n";
}

if (count($issues) > 0) {
    echo "\n❌ Problèmes (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "   • " . $issue . "\n";
    }
    
    echo "\n🔧 RECOMMANDATIONS:\n";
    echo "   1. Vider le cache: rm -rf writable/cache/*\n";
    echo "   2. Redémarrer le service: php spark serve --host=0.0.0.0 --port=8080\n";
    echo "   3. Vérifier la configuration de licence dans le code\n";
} else {
    echo "\n🎉 Tous les tests sont passés avec succès !\n";
    echo "   Le système est entièrement opérationnel.\n";
}

echo "\n🌐 Accès au système:\n";
echo "   URL: " . $baseUrl . "\n";
echo "   Utilisateur: " . $username . "\n";
echo "   Mot de passe: " . $password . "\n";
?>





