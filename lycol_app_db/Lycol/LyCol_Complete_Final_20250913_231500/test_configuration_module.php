<?php
/**
 * Test et Correction du Module Configuration - KISSAI SCHOOL
 * Expert Senior PHP/CodeIgniter
 */

echo "🔧 TEST ET CORRECTION DU MODULE CONFIGURATION - KISSAI SCHOOL\n";
echo "==========================================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données établie\n\n";
    
    // ========================================
    // 1. VÉRIFICATION DES ROUTES
    // ========================================
    echo "🔍 1. VÉRIFICATION DES ROUTES\n";
    echo "=============================\n";
    
    $routesFile = 'app/Config/Routes.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        
        if (strpos($routesContent, "configuration") !== false) {
            echo "✅ Routes de configuration trouvées dans Routes.php\n";
        } else {
            echo "❌ Routes de configuration manquantes dans Routes.php\n";
        }
    } else {
        echo "❌ Fichier Routes.php introuvable\n";
    }
    
    // ========================================
    // 2. VÉRIFICATION DU CONTRÔLEUR
    // ========================================
    echo "\n🔍 2. VÉRIFICATION DU CONTRÔLEUR\n";
    echo "================================\n";
    
    $controllerFile = 'app/Controllers/Configuration.php';
    if (file_exists($controllerFile)) {
        echo "✅ Contrôleur Configuration.php trouvé\n";
        
        $controllerContent = file_get_contents($controllerFile);
        
        // Vérifier les méthodes principales
        $methods = ['index', 'general', 'email', 'sms', 'whatsapp', 'license', 'appearance', 'diagnostics'];
        foreach ($methods as $method) {
            if (strpos($controllerContent, "public function $method") !== false) {
                echo "   ✅ Méthode $method() trouvée\n";
            } else {
                echo "   ❌ Méthode $method() manquante\n";
            }
        }
    } else {
        echo "❌ Contrôleur Configuration.php introuvable\n";
    }
    
    // ========================================
    // 3. VÉRIFICATION DES VUES
    // ========================================
    echo "\n🔍 3. VÉRIFICATION DES VUES\n";
    echo "===========================\n";
    
    $viewsDir = 'app/Views/admin/configuration';
    if (is_dir($viewsDir)) {
        echo "✅ Dossier des vues de configuration trouvé\n";
        
        $views = ['index.php', 'general.php', 'email.php', 'sms.php', 'whatsapp.php', 'license.php', 'appearance.php', 'diagnostics.php'];
        foreach ($views as $view) {
            if (file_exists("$viewsDir/$view")) {
                echo "   ✅ Vue $view trouvée\n";
            } else {
                echo "   ❌ Vue $view manquante\n";
            }
        }
    } else {
        echo "❌ Dossier des vues de configuration introuvable\n";
    }
    
    // ========================================
    // 4. VÉRIFICATION DES MODÈLES
    // ========================================
    echo "\n🔍 4. VÉRIFICATION DES MODÈLES\n";
    echo "==============================\n";
    
    $licenseModelFile = 'app/Models/LicenseModel.php';
    if (file_exists($licenseModelFile)) {
        echo "✅ Modèle LicenseModel.php trouvé\n";
    } else {
        echo "❌ Modèle LicenseModel.php introuvable\n";
    }
    
    // ========================================
    // 5. VÉRIFICATION DES SERVICES
    // ========================================
    echo "\n🔍 5. VÉRIFICATION DES SERVICES\n";
    echo "================================\n";
    
    $cacheServiceFile = 'app/Services/CacheService.php';
    if (file_exists($cacheServiceFile)) {
        echo "✅ Service CacheService.php trouvé\n";
    } else {
        echo "❌ Service CacheService.php introuvable\n";
    }
    
    // ========================================
    // 6. VÉRIFICATION DES DONNÉES DE LICENCE
    // ========================================
    echo "\n🔍 6. VÉRIFICATION DES DONNÉES DE LICENCE\n";
    echo "==========================================\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM licenses");
    $licenseCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 Licences en base : $licenseCount\n";
    
    if ($licenseCount > 0) {
        $stmt = $pdo->query("SELECT * FROM licenses WHERE status = 'ACTIVE' LIMIT 1");
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($license) {
            echo "✅ Licence active trouvée :\n";
            echo "   - Type : {$license['license_type']}\n";
            echo "   - Clé : {$license['license_key']}\n";
            echo "   - Client : {$license['client_id']}\n";
            echo "   - Expiration : {$license['expiry_date']}\n";
        } else {
            echo "⚠️  Aucune licence active trouvée\n";
        }
    }
    
    // ========================================
    // 7. VÉRIFICATION DES STATISTIQUES
    // ========================================
    echo "\n🔍 7. VÉRIFICATION DES STATISTIQUES\n";
    echo "===================================\n";
    
    $tables = ['students', 'teachers', 'classes', 'users'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "📊 $table : $count enregistrements\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors du comptage de $table : " . $e->getMessage() . "\n";
        }
    }
    
    // ========================================
    // 8. TEST DES REQUÊTES CURL
    // ========================================
    echo "\n🔍 8. TEST DES REQUÊTES CURL\n";
    echo "============================\n";
    
    $baseUrl = 'http://localhost:8080';
    
    // Test de la page de connexion
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ Page de connexion accessible (HTTP $httpCode)\n";
    } else {
        echo "❌ Page de connexion inaccessible (HTTP $httpCode)\n";
    }
    
    // Test de la page de configuration (sans authentification)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/configuration');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ Page de configuration accessible (HTTP $httpCode)\n";
    } elseif ($httpCode == 302) {
        echo "⚠️  Page de configuration redirige vers la connexion (HTTP $httpCode)\n";
    } else {
        echo "❌ Page de configuration inaccessible (HTTP $httpCode)\n";
    }
    
    // ========================================
    // 9. CORRECTIONS NÉCESSAIRES
    // ========================================
    echo "\n🔧 9. CORRECTIONS NÉCESSAIRES\n";
    echo "=============================\n";
    
    // Vérifier si le filtre d'authentification existe
    $filterFile = 'app/Filters/Auth.php';
    if (file_exists($filterFile)) {
        echo "✅ Filtre d'authentification trouvé\n";
    } else {
        echo "❌ Filtre d'authentification manquant\n";
    }
    
    // Vérifier la configuration des filtres
    $filtersConfigFile = 'app/Config/Filters.php';
    if (file_exists($filtersConfigFile)) {
        echo "✅ Configuration des filtres trouvée\n";
    } else {
        echo "❌ Configuration des filtres manquante\n";
    }
    
    // ========================================
    // 10. RECOMMANDATIONS
    // ========================================
    echo "\n💡 10. RECOMMANDATIONS\n";
    echo "======================\n";
    
    echo "📋 Actions recommandées :\n";
    echo "1. Vérifier que l'utilisateur est connecté avant d'accéder à /admin/configuration\n";
    echo "2. S'assurer que le filtre d'authentification est correctement configuré\n";
    echo "3. Vérifier que toutes les vues de configuration existent\n";
    echo "4. Tester l'accès via l'interface web après connexion\n";
    echo "5. Vérifier les permissions des dossiers writable/\n";
    
    echo "\n🎯 Pour accéder au module configuration :\n";
    echo "1. Aller sur http://localhost:8080/admin/auth/login\n";
    echo "2. Se connecter avec admin/admin123\n";
    echo "3. Accéder à http://localhost:8080/admin/configuration\n";
    
    echo "\n✅ TEST TERMINÉ AVEC SUCCÈS !\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE CONNEXION: " . $e->getMessage() . "\n";
}
?>





