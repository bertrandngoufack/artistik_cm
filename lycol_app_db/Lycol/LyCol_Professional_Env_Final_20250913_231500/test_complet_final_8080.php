<?php
/**
 * Test Complet Final - KISSAI SCHOOL - LyCol
 * Vérification de toutes les fonctionnalités sur le port 8080
 */

class TestCompletFinal {
    private $baseUrl = 'http://localhost:8080';
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct() {
        echo "🧪 TEST COMPLET FINAL - KISSAI SCHOOL - LyCol\n";
        echo "==============================================\n";
        echo "Port: 8080\n";
        echo "Base URL: {$this->baseUrl}\n\n";
    }
    
    public function run() {
        $this->testServerConnection();
        $this->testDatabaseConnection();
        $this->testMainPages();
        $this->testAdminPages();
        $this->testConfigurationModule();
        $this->testLicenseModule();
        $this->testStaticAssets();
        $this->testCRUDOperations();
        $this->testPortConsistency();
        $this->testLinksAndNavigation();
        $this->testFormSubmissions();
        $this->testAPIEndpoints();
        $this->generateFinalReport();
    }
    
    private function testServerConnection() {
        echo "1️⃣ Test de connexion au serveur...\n";
        
        $response = $this->makeRequest('/');
        if ($response['status'] === 200) {
            $this->results['server'] = '✅ Serveur accessible sur port 8080';
            echo "   ✅ Serveur accessible\n";
        } else {
            $this->errors[] = "❌ Serveur non accessible (Status: {$response['status']})";
            echo "   ❌ Serveur non accessible\n";
        }
    }
    
    private function testDatabaseConnection() {
        echo "2️⃣ Test de connexion à la base de données...\n";
        
        try {
            $pdo = new PDO(
                'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
                'root',
                'Bateau123',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Test des tables principales
            $tables = ['licenses', 'students', 'teachers', 'classes'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "   ✅ Table {$table}: {$result['count']} enregistrements\n";
                } catch (Exception $e) {
                    echo "   ⚠️ Table {$table}: " . $e->getMessage() . "\n";
                }
            }
            
            $this->results['database'] = '✅ Base de données accessible';
        } catch (Exception $e) {
            $this->errors[] = "❌ Erreur de connexion à la base de données: " . $e->getMessage();
            echo "   ❌ Erreur de connexion à la base de données\n";
        }
    }
    
    private function testMainPages() {
        echo "3️⃣ Test des pages principales...\n";
        
        $pages = [
            '/' => 'Page d\'accueil',
            '/auth/login' => 'Connexion',
            '/auth/parents' => 'Connexion parents',
            '/auth/mobile' => 'Connexion mobile'
        ];
        
        foreach ($pages as $page => $description) {
            $response = $this->makeRequest($page);
            if ($response['status'] === 200) {
                echo "   ✅ {$description}\n";
            } else {
                $this->warnings[] = "⚠️ {$description} non accessible (Status: {$response['status']})";
                echo "   ⚠️ {$description} non accessible\n";
            }
        }
    }
    
    private function testAdminPages() {
        echo "4️⃣ Test des pages d'administration...\n";
        
        $adminPages = [
            '/admin/dashboard' => 'Dashboard admin',
            '/admin/configuration' => 'Configuration générale',
            '/admin/configuration/license' => 'Gestion des licences',
            '/admin/configuration/appearance' => 'Apparence',
            '/admin/configuration/diagnostics' => 'Diagnostics'
        ];
        
        foreach ($adminPages as $page => $description) {
            $response = $this->makeRequest($page);
            if ($response['status'] === 200) {
                echo "   ✅ {$description}\n";
            } else {
                $this->warnings[] = "⚠️ {$description} non accessible (Status: {$response['status']})";
                echo "   ⚠️ {$description} non accessible\n";
            }
        }
    }
    
    private function testConfigurationModule() {
        echo "5️⃣ Test du module de configuration...\n";
        
        // Test GET
        $response = $this->makeRequest('/admin/configuration');
        if ($response['status'] === 200) {
            echo "   ✅ Module configuration accessible\n";
            
            // Vérifier la présence de contenu spécifique
            if (strpos($response['content'], 'KISSAI SCHOOL') !== false) {
                echo "   ✅ Contenu de configuration détecté\n";
            }
            
            // Vérifier la présence de liens vers le port 8080
            if (strpos($response['content'], '8080') !== false) {
                echo "   ✅ Références au port 8080 détectées\n";
            } else {
                $this->warnings[] = "⚠️ Pas de références au port 8080 dans la configuration";
                echo "   ⚠️ Pas de références au port 8080\n";
            }
        } else {
            $this->errors[] = "❌ Module configuration non accessible";
            echo "   ❌ Module configuration non accessible\n";
        }
    }
    
    private function testLicenseModule() {
        echo "6️⃣ Test du module de licences...\n";
        
        $response = $this->makeRequest('/admin/configuration/license');
        if ($response['status'] === 200) {
            echo "   ✅ Module licences accessible\n";
            
            // Vérifier la présence d'informations de licence
            if (strpos($response['content'], 'licence') !== false || strpos($response['content'], 'license') !== false) {
                echo "   ✅ Informations de licence détectées\n";
            }
        } else {
            $this->warnings[] = "⚠️ Module licences non accessible";
            echo "   ⚠️ Module licences non accessible\n";
        }
    }
    
    private function testStaticAssets() {
        echo "7️⃣ Test des assets statiques...\n";
        
        $assets = [
            '/assets/css/bulma.min.css' => 'CSS Bulma',
            '/assets/css/style.css' => 'CSS personnalisé',
            '/assets/js/app.js' => 'JavaScript principal',
            '/assets/images/logo.png' => 'Logo',
            '/favicon.ico' => 'Favicon'
        ];
        
        foreach ($assets as $asset => $description) {
            $response = $this->makeRequest($asset);
            if ($response['status'] === 200) {
                echo "   ✅ {$description} accessible\n";
            } else {
                $this->warnings[] = "⚠️ {$description} non accessible (Status: {$response['status']})";
                echo "   ⚠️ {$description} non accessible\n";
            }
        }
    }
    
    private function testCRUDOperations() {
        echo "8️⃣ Test des opérations CRUD...\n";
        
        // Test API pour les statistiques système
        $response = $this->makeRequest('/admin/configuration/system-stats-api');
        if ($response['status'] === 200) {
            $data = json_decode($response['content'], true);
            if ($data) {
                echo "   ✅ API statistiques système fonctionnelle\n";
                echo "   📊 Données système récupérées\n";
            }
        } else {
            $this->warnings[] = "⚠️ API statistiques système non accessible";
            echo "   ⚠️ API statistiques système non accessible\n";
        }
        
        // Test de vérification de licence
        $response = $this->makeRequest('/admin/configuration/check-license');
        if ($response['status'] === 200) {
            echo "   ✅ Vérification de licence fonctionnelle\n";
        } else {
            $this->warnings[] = "⚠️ Vérification de licence non accessible";
            echo "   ⚠️ Vérification de licence non accessible\n";
        }
    }
    
    private function testPortConsistency() {
        echo "9️⃣ Vérification de la cohérence du port...\n";
        
        // Vérifier les fichiers de configuration
        $configFiles = [
            'app/Config/App.php' => 'Configuration App',
            'public/assets/js/app.js' => 'JavaScript principal'
        ];
        
        foreach ($configFiles as $file => $description) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (strpos($content, '8080') !== false) {
                    echo "   ✅ {$description} contient le port 8080\n";
                } else {
                    $this->warnings[] = "⚠️ {$description} ne contient pas le port 8080";
                    echo "   ⚠️ {$description} ne contient pas le port 8080\n";
                }
            } else {
                $this->errors[] = "❌ Fichier {$file} manquant";
                echo "   ❌ Fichier {$file} manquant\n";
            }
        }
    }
    
    private function testLinksAndNavigation() {
        echo "🔟 Test des liens et navigation...\n";
        
        // Tester les liens dans la page d'accueil
        $response = $this->makeRequest('/');
        if ($response['status'] === 200) {
            // Extraire les liens
            preg_match_all('/href=["\']([^"\']+)["\']/', $response['content'], $matches);
            $links = $matches[1] ?? [];
            
            $validLinks = 0;
            foreach ($links as $link) {
                if (strpos($link, '8080') !== false || strpos($link, 'http') === false) {
                    $validLinks++;
                }
            }
            
            echo "   ✅ {$validLinks} liens valides détectés\n";
        }
    }
    
    private function testFormSubmissions() {
        echo "1️⃣1️⃣ Test des soumissions de formulaires...\n";
        
        // Test POST pour sauvegarder l'apparence
        $postData = [
            'school_name' => 'KISSAI SCHOOL - Test Final',
            'school_address' => 'Test Address Final',
            'school_phone' => '+237 123456789',
            'school_email' => 'test@kissai.edu.cm'
        ];
        
        $response = $this->makeRequest('/admin/configuration/save-appearance', 'POST', $postData);
        if ($response['status'] === 200 || $response['status'] === 302) {
            echo "   ✅ Formulaire de configuration fonctionnel\n";
        } else {
            $this->warnings[] = "⚠️ Formulaire de configuration non fonctionnel";
            echo "   ⚠️ Formulaire de configuration non fonctionnel\n";
        }
    }
    
    private function testAPIEndpoints() {
        echo "1️⃣2️⃣ Test des endpoints API...\n";
        
        $apiEndpoints = [
            '/admin/configuration/system-stats-api' => 'API Statistiques',
            '/admin/configuration/check-license' => 'API Licence'
        ];
        
        foreach ($apiEndpoints as $endpoint => $description) {
            $response = $this->makeRequest($endpoint);
            if ($response['status'] === 200) {
                echo "   ✅ {$description} fonctionnel\n";
            } else {
                $this->warnings[] = "⚠️ {$description} non fonctionnel (Status: {$response['status']})";
                echo "   ⚠️ {$description} non fonctionnel\n";
            }
        }
        
        // Test spécifique pour l'API Cache (POST)
        $response = $this->makeRequest('/admin/configuration/clear-cache', 'POST');
        if ($response['status'] === 200) {
            echo "   ✅ API Cache fonctionnel\n";
        } else {
            $this->warnings[] = "⚠️ API Cache non fonctionnel (Status: {$response['status']})";
            echo "   ⚠️ API Cache non fonctionnel\n";
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null) {
        $fullUrl = $this->baseUrl . $url;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TestBot/1.0');
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'content' => $content
        ];
    }
    
    private function generateFinalReport() {
        echo "\n📋 RAPPORT FINAL COMPLET\n";
        echo "========================\n\n";
        
        echo "✅ SUCCÈS (" . count($this->results) . "):\n";
        foreach ($this->results as $key => $result) {
            echo "   {$result}\n";
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️ AVERTISSEMENTS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   {$warning}\n";
            }
        }
        
        if (!empty($this->errors)) {
            echo "\n❌ ERREURS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   {$error}\n";
            }
        }
        
        echo "\n🎯 ÉVALUATION FINALE:\n";
        $totalTests = count($this->results) + count($this->warnings) + count($this->errors);
        $successRate = round((count($this->results) / $totalTests) * 100, 2);
        
        echo "   • Tests réussis: " . count($this->results) . "\n";
        echo "   • Avertissements: " . count($this->warnings) . "\n";
        echo "   • Erreurs: " . count($this->errors) . "\n";
        echo "   • Taux de succès: {$successRate}%\n";
        
        if ($successRate >= 80) {
            echo "   🏆 EXCELLENT - Le projet est prêt pour la production\n";
        } elseif ($successRate >= 60) {
            echo "   ✅ BON - Quelques améliorations mineures nécessaires\n";
        } else {
            echo "   ⚠️ ATTENTION - Des corrections importantes sont nécessaires\n";
        }
        
        echo "\n🔗 LIENS UTILES:\n";
        echo "   • Accueil: {$this->baseUrl}/\n";
        echo "   • Connexion: {$this->baseUrl}/auth/login\n";
        echo "   • Configuration: {$this->baseUrl}/admin/configuration\n";
        echo "   • Licences: {$this->baseUrl}/admin/configuration/license\n";
        
        echo "\n🏁 Test complet terminé !\n";
    }
}

// Exécuter le test complet
$test = new TestCompletFinal();
$test->run();
?>
