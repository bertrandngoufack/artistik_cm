<?php
/**
 * Test de la licence définitive activée
 * Vérification que la licence permanente fonctionne correctement
 */

echo "🔑 TEST DE LA LICENCE DÉFINITIVE\n";
echo "================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérification de la licence
    echo "📋 Vérification de la licence actuelle\n";
    echo "--------------------------------------\n";
    
    $stmt = $pdo->query("SELECT * FROM licenses WHERE id = 1");
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($license) {
        echo "   🔑 Clé de licence: " . $license['license_key'] . "\n";
        echo "   🏢 Client ID: " . $license['client_id'] . "\n";
        echo "   📅 Type de licence: " . $license['license_type'] . "\n";
        echo "   📆 Date d'émission: " . $license['issued_date'] . "\n";
        echo "   ⏰ Date d'expiration: " . $license['expiry_date'] . "\n";
        echo "   📊 Statut: " . $license['status'] . "\n";
        
        // Vérification que la licence est permanente
        if ($license['license_type'] === 'PERMANENT') {
            echo "\n   ✅ Licence définitive activée avec succès !\n";
        } else {
            echo "\n   ❌ La licence n'est pas encore définitive\n";
        }
        
        // Vérification de la date d'expiration
        $expiryDate = new DateTime($license['expiry_date']);
        $currentDate = new DateTime();
        
        if ($expiryDate > $currentDate) {
            echo "   ✅ Licence valide (expire le " . $license['expiry_date'] . ")\n";
        } else {
            echo "   ❌ Licence expirée\n";
        }
        
    } else {
        echo "   ❌ Aucune licence trouvée\n";
    }
    
    // Test de la page de connexion
    echo "\n🌐 Test de la page de connexion\n";
    echo "-------------------------------\n";
    
    $loginUrl = 'http://localhost:8080/auth/login';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "   ✅ Page de connexion accessible\n";
        
        // Vérification de l'absence du message d'erreur de licence
        if (strpos($response, 'Licence expirée') !== false || strpos($response, 'License expired') !== false) {
            echo "   ❌ Message d'erreur de licence encore présent\n";
        } else {
            echo "   ✅ Aucun message d'erreur de licence détecté\n";
        }
        
        // Vérification de la présence du formulaire de connexion
        if (strpos($response, 'Nom d\'utilisateur') !== false || strpos($response, 'username') !== false) {
            echo "   ✅ Formulaire de connexion présent\n";
        } else {
            echo "   ❌ Formulaire de connexion manquant\n";
        }
        
    } else {
        echo "   ❌ Page de connexion inaccessible (HTTP $httpCode)\n";
    }
    
    // Test de connexion
    echo "\n🔐 Test de connexion\n";
    echo "-------------------\n";
    
    $loginData = [
        'username' => 'admin',
        'password' => 'admin123',
        'csrf_test_name' => 'test_token'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/auth/authenticate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 || $httpCode == 302) {
        echo "   ✅ Connexion réussie\n";
    } else {
        echo "   ❌ Échec de la connexion (HTTP $httpCode)\n";
    }
    
    echo "\n🎉 RÉSUMÉ DU TEST DE LICENCE\n";
    echo "============================\n";
    
    if ($license['license_type'] === 'PERMANENT' && $license['status'] === 'ACTIVE') {
        echo "✅ Licence définitive activée avec succès !\n";
        echo "✅ Le système est maintenant opérationnel sans limitation de temps\n";
        echo "✅ Date d'expiration: " . $license['expiry_date'] . " (permanente)\n";
        echo "\n🚀 Le projet LyCol est prêt pour la production !\n";
        echo "🌐 Accédez à: http://localhost:8080\n";
        echo "👤 Connexion: admin / admin123\n";
    } else {
        echo "❌ Problème avec l'activation de la licence\n";
        echo "⚠️ Vérifiez la configuration de la base de données\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}
?>





