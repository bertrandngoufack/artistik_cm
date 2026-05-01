<?php
/**
 * 🔍 VÉRIFICATION RAPIDE DE L'ÉTAT DE LA LICENCE - KISSAI SCHOOL
 * 
 * Script de vérification rapide pour contrôler l'état de la licence
 * sans effectuer de modifications.
 * 
 * Usage : php VERIFICATION_LICENCE_RAPIDE.php
 */

echo "🔍 VÉRIFICATION RAPIDE DE L'ÉTAT DE LA LICENCE\n";
echo "==============================================\n\n";

// Configuration
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérification de la licence
    $stmt = $pdo->query("SELECT * FROM licenses WHERE id = 1");
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($license) {
        echo "📋 ÉTAT DE LA LICENCE ACTUELLE:\n";
        echo "--------------------------------\n";
        echo "   🔑 Clé de licence: " . $license['license_key'] . "\n";
        echo "   🏢 Client ID: " . $license['client_id'] . "\n";
        echo "   📅 Type de licence: " . $license['license_type'] . "\n";
        echo "   📆 Date d'émission: " . $license['issued_date'] . "\n";
        echo "   ⏰ Date d'expiration: " . $license['expiry_date'] . "\n";
        echo "   📊 Statut: " . $license['status'] . "\n";
        echo "   🕒 Dernière mise à jour: " . $license['updated_at'] . "\n\n";
        
        // Analyse de l'état
        $isDefinitive = ($license['license_type'] === 'PERMANENT');
        $isActive = ($license['status'] === 'ACTIVE');
        $isExpired = false;
        
        if ($license['expiry_date'] !== '2099-12-31') {
            $expiryDate = new DateTime($license['expiry_date']);
            $currentDate = new DateTime();
            $isExpired = ($expiryDate <= $currentDate);
        }
        
        echo "📊 ANALYSE DE L'ÉTAT:\n";
        echo "--------------------\n";
        
        if ($isDefinitive) {
            echo "   ✅ Type: PERMANENT (définitive)\n";
        } else {
            echo "   ❌ Type: " . $license['license_type'] . " (non définitive)\n";
        }
        
        if ($isActive) {
            echo "   ✅ Statut: ACTIVE\n";
        } else {
            echo "   ❌ Statut: " . $license['status'] . " (inactive)\n";
        }
        
        if ($license['expiry_date'] === '2099-12-31') {
            echo "   ✅ Expiration: 2099-12-31 (permanente)\n";
        } else {
            if ($isExpired) {
                echo "   ❌ Expiration: " . $license['expiry_date'] . " (EXPIRÉE)\n";
            } else {
                echo "   ⚠️ Expiration: " . $license['expiration_date'] . " (limite)\n";
            }
        }
        
        echo "\n🎯 RÉSUMÉ:\n";
        echo "----------\n";
        
        if ($isDefinitive && $isActive && $license['expiry_date'] === '2099-12-31') {
            echo "   🎉 LICENCE DÉFINITIVE ACTIVE ET OPÉRATIONNELLE\n";
            echo "   ✅ Le système fonctionne sans limitation\n";
            echo "   ✅ Aucune action requise\n";
        } elseif ($isActive && !$isExpired) {
            echo "   ⚠️ LICENCE TEMPORAIRE ACTIVE\n";
            echo "   ⚠️ Le système fonctionne mais avec limitation de temps\n";
            echo "   💡 Considérez l'activation d'une licence définitive\n";
        } else {
            echo "   ❌ PROBLÈME AVEC LA LICENCE\n";
            echo "   ❌ Le système peut avoir des limitations\n";
            echo "   🔧 Action requise: Activer une licence définitive\n";
        }
        
    } else {
        echo "❌ AUCUNE LICENCE TROUVÉE\n";
        echo "   Le système n'a pas de licence configurée\n";
        echo "   🔧 Action requise: Créer une licence\n";
    }
    
    // Test de l'application
    echo "\n🌐 TEST DE L'APPLICATION:\n";
    echo "------------------------\n";
    
    $baseUrl = 'http://localhost:8080';
    
    // Test de la page de connexion
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "   ✅ Page de connexion accessible\n";
        
        if (strpos($response, 'Licence expirée') !== false) {
            echo "   ❌ Message d'erreur de licence détecté\n";
        } else {
            echo "   ✅ Aucun message d'erreur de licence\n";
        }
    } else {
        echo "   ❌ Page de connexion inaccessible (HTTP $httpCode)\n";
    }
    
    echo "\n📞 INFORMATIONS DE CONNEXION:\n";
    echo "----------------------------\n";
    echo "   🌐 URL: $baseUrl\n";
    echo "   👤 Utilisateur: admin\n";
    echo "   🔑 Mot de passe: admin123\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE CONNEXION À LA BASE DE DONNÉES\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Vérifiez les paramètres de connexion\n";
}

echo "\n📄 FIN DE LA VÉRIFICATION\n";
echo "=========================\n";
?>





