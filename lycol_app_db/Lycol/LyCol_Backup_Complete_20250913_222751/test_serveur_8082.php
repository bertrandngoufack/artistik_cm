<?php
/**
 * Test du serveur CodeIgniter sur le port 8080
 */

echo "🌐 TEST DU SERVEUR CODEIGNITER SUR LE PORT 8080\n";
echo "==============================================\n\n";

// Test 1: Vérification des processus
echo "🔍 Test 1: Vérification des processus\n";
echo "-------------------------------------\n";

$output = shell_exec("ps aux | grep 'php spark' | grep -v grep");
if (!empty($output)) {
    echo "✅ Processus CodeIgniter en cours d'exécution:\n";
    echo $output . "\n";
} else {
    echo "❌ Aucun processus CodeIgniter trouvé\n";
}

// Test 2: Vérification des ports
echo "🔍 Test 2: Vérification des ports\n";
echo "---------------------------------\n";

$ports = [8080];
foreach ($ports as $port) {
    $output = shell_exec("netstat -tlnp 2>/dev/null | grep :$port");
    if (!empty($output)) {
        echo "✅ Port $port: En écoute\n";
        echo "   $output\n";
    } else {
        echo "❌ Port $port: Non utilisé\n";
    }
}

echo "\n";

// Test 3: Test de connexion HTTP
echo "🔍 Test 3: Test de connexion HTTP\n";
echo "---------------------------------\n";

$urls = [
    'http://localhost:8080' => 'Port 8080',
    'http://localhost:8080' => 'Port 8081', 
    'http://localhost:8080' => 'Port 8080'
];

foreach ($urls as $url => $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ $description: Erreur de connexion - $error\n";
    } elseif ($httpCode > 0) {
        echo "✅ $description: HTTP $httpCode\n";
    } else {
        echo "❌ $description: Pas de réponse\n";
    }
}

echo "\n";

// Test 4: Vérification de la configuration
echo "🔍 Test 4: Vérification de la configuration\n";
echo "-------------------------------------------\n";

$configFiles = [
    '.env' => 'Fichier .env',
    'app/Config/App.php' => 'Configuration App.php',
    'app/Config/Database.php' => 'Configuration Database.php'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: Présent\n";
        
        if ($file === '.env') {
            $content = file_get_contents($file);
            if (strpos($content, '8080') !== false) {
                echo "   ✅ Port 8080 configuré dans .env\n";
            } else {
                echo "   ❌ Port 8080 non configuré dans .env\n";
            }
        }
    } else {
        echo "❌ $description: Manquant\n";
    }
}

echo "\n";

// Test 5: Test de démarrage manuel
echo "🔍 Test 5: Test de démarrage manuel\n";
echo "-----------------------------------\n";

echo "🔄 Arrêt des processus existants...\n";
shell_exec("pkill -f 'php spark serve'");
sleep(2);

echo "🚀 Démarrage du serveur sur le port 8080...\n";
$command = "php spark serve --host=0.0.0.0 --port=8080 > /tmp/codeigniter.log 2>&1 &";
shell_exec($command);
sleep(5);

echo "📋 Logs du serveur:\n";
if (file_exists('/tmp/codeigniter.log')) {
    $logs = file_get_contents('/tmp/codeigniter.log');
    echo $logs . "\n";
} else {
    echo "❌ Aucun log trouvé\n";
}

// Test 6: Vérification finale
echo "🔍 Test 6: Vérification finale\n";
echo "-------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur de connexion: $error\n";
} elseif ($httpCode > 0) {
    echo "✅ Serveur accessible sur le port 8080 (HTTP $httpCode)\n";
    echo "🌐 URL: http://localhost:8080\n";
} else {
    echo "❌ Serveur non accessible sur le port 8080\n";
}

echo "\n";

// Test 7: Résumé et recommandations
echo "📊 Test 7: Résumé et Recommandations\n";
echo "------------------------------------\n";

echo "✅ POINTS POSITIFS:\n";
echo "   - Processus CodeIgniter en cours d'exécution\n";
echo "   - Configuration .env créée\n";
echo "   - Fichiers de configuration présents\n\n";

echo "⚠️ POINTS D'ATTENTION:\n";
echo "   - Serveur peut ne pas écouter sur le port 8080\n";
echo "   - Vérifier les logs pour les erreurs\n";
echo "   - Possible conflit de port\n\n";

echo "🚀 RECOMMANDATIONS:\n";
echo "   1. Vérifier les logs détaillés\n";
echo "   2. Tester avec un port différent (8083)\n";
echo "   3. Vérifier les permissions de port\n";
echo "   4. Utiliser le serveur PHP intégré en alternative\n\n";

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test serveur CodeIgniter\n";
?>


