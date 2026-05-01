<?php
/**
 * Test de Cohérence des Paramètres d'Apparence
 * Vérification que les changements sont pris en compte dans tout le projet
 */

class TestAppearanceCoherence {
    private $baseUrl = 'http://localhost:8080';
    private $db;
    
    public function __construct() {
        echo "🎨 TEST DE COHÉRENCE DES PARAMÈTRES D'APPEARANCE\n";
        echo "================================================\n";
        echo "Base URL: {$this->baseUrl}\n\n";
        
        $this->initDatabase();
    }
    
    private function initDatabase() {
        try {
            $this->db = new PDO(
                'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
                'root',
                'Bateau123',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Exception $e) {
            die("❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n");
        }
    }
    
    public function run() {
        $this->testDatabaseSettings();
        $this->testConfigurationPage();
        $this->testHomePage();
        $this->testAdminPages();
        $this->testLogoAndFavicon();
        $this->testColorConsistency();
        $this->generateReport();
    }
    
    private function testDatabaseSettings() {
        echo "1️⃣ Test des paramètres en base de données...\n";
        
        $stmt = $this->db->prepare("SELECT setting_key, setting_value, module FROM settings WHERE module = 'appearance' ORDER BY setting_key");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $expectedSettings = [
            'app_name' => 'KISSAI SCHOOL - Test Upload',
            'app_description' => 'Test avec upload de fichiers',
            'primary_color' => '#ff6600',
            'secondary_color' => '#00ccff'
        ];
        
        foreach ($expectedSettings as $key => $expectedValue) {
            $found = false;
            foreach ($settings as $setting) {
                if ($setting['setting_key'] === $key) {
                    $found = true;
                    if ($setting['setting_value'] === $expectedValue) {
                        echo "   ✅ {$key}: {$setting['setting_value']}\n";
                    } else {
                        echo "   ⚠️ {$key}: attendu '{$expectedValue}', trouvé '{$setting['setting_value']}'\n";
                    }
                    break;
                }
            }
            if (!$found) {
                echo "   ❌ {$key}: paramètre manquant\n";
            }
        }
    }
    
    private function testConfigurationPage() {
        echo "\n2️⃣ Test de la page de configuration...\n";
        
        $response = $this->makeRequest('/admin/configuration/appearance');
        if ($response['status'] === 200) {
            echo "   ✅ Page de configuration accessible\n";
            
            // Vérifier la présence des valeurs
            if (strpos($response['content'], 'KISSAI SCHOOL - Test Upload') !== false) {
                echo "   ✅ Nom de l'application reflété\n";
            } else {
                echo "   ⚠️ Nom de l'application non reflété\n";
            }
            
            if (strpos($response['content'], 'Test avec upload de fichiers') !== false) {
                echo "   ✅ Description reflétée\n";
            } else {
                echo "   ⚠️ Description non reflétée\n";
            }
            
            if (strpos($response['content'], '#ff6600') !== false) {
                echo "   ✅ Couleur primaire reflétée\n";
            } else {
                echo "   ⚠️ Couleur primaire non reflétée\n";
            }
        } else {
            echo "   ❌ Page de configuration non accessible (Status: {$response['status']})\n";
        }
    }
    
    private function testHomePage() {
        echo "\n3️⃣ Test de la page d'accueil...\n";
        
        $response = $this->makeRequest('/');
        if ($response['status'] === 200) {
            echo "   ✅ Page d'accueil accessible\n";
            
            // Vérifier si les paramètres d'apparence sont utilisés
            if (strpos($response['content'], 'KISSAI SCHOOL') !== false) {
                echo "   ✅ Nom de l'école présent\n";
            } else {
                echo "   ⚠️ Nom de l'école non trouvé\n";
            }
            
            // Vérifier la présence du logo
            if (strpos($response['content'], 'logo.png') !== false) {
                echo "   ✅ Logo référencé\n";
            } else {
                echo "   ⚠️ Logo non référencé\n";
            }
            
            // Vérifier la présence du favicon
            if (strpos($response['content'], 'favicon.ico') !== false) {
                echo "   ✅ Favicon référencé\n";
            } else {
                echo "   ⚠️ Favicon non référencé\n";
            }
        } else {
            echo "   ❌ Page d'accueil non accessible (Status: {$response['status']})\n";
        }
    }
    
    private function testAdminPages() {
        echo "\n4️⃣ Test des pages d'administration...\n";
        
        $adminPages = [
            '/admin/dashboard' => 'Dashboard',
            '/admin/configuration' => 'Configuration générale',
            '/admin/configuration/license' => 'Licences'
        ];
        
        foreach ($adminPages as $page => $description) {
            $response = $this->makeRequest($page);
            if ($response['status'] === 200) {
                echo "   ✅ {$description} accessible\n";
                
                // Vérifier la cohérence du titre
                if (strpos($response['content'], 'KISSAI SCHOOL') !== false) {
                    echo "     ✅ Titre cohérent\n";
                } else {
                    echo "     ⚠️ Titre non cohérent\n";
                }
            } else {
                echo "   ⚠️ {$description} non accessible (Status: {$response['status']})\n";
            }
        }
    }
    
    private function testLogoAndFavicon() {
        echo "\n5️⃣ Test des assets (logo et favicon)...\n";
        
        $assets = [
            '/assets/images/logo.png' => 'Logo',
            '/favicon.ico' => 'Favicon',
            '/assets/images/favicon.ico' => 'Favicon alternatif'
        ];
        
        foreach ($assets as $asset => $description) {
            $response = $this->makeRequest($asset);
            if ($response['status'] === 200) {
                echo "   ✅ {$description} accessible\n";
            } else {
                echo "   ⚠️ {$description} non accessible (Status: {$response['status']})\n";
            }
        }
    }
    
    private function testColorConsistency() {
        echo "\n6️⃣ Test de la cohérence des couleurs...\n";
        
        // Vérifier si les couleurs sont utilisées dans les CSS
        $cssFiles = [
            '/assets/css/style.css' => 'CSS personnalisé',
            '/assets/bulma/css/bulma.min.css' => 'CSS Bulma'
        ];
        
        foreach ($cssFiles as $css => $description) {
            $response = $this->makeRequest($css);
            if ($response['status'] === 200) {
                echo "   ✅ {$description} accessible\n";
                
                // Vérifier la présence des variables CSS
                if (strpos($response['content'], '--primary-color') !== false) {
                    echo "     ✅ Variables CSS présentes\n";
                } else {
                    echo "     ⚠️ Variables CSS manquantes\n";
                }
            } else {
                echo "   ⚠️ {$description} non accessible (Status: {$response['status']})\n";
            }
        }
    }
    
    private function makeRequest($url) {
        $fullUrl = $this->baseUrl . $url;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TestAppearance/1.0');
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'content' => $content
        ];
    }
    
    private function generateReport() {
        echo "\n📋 RAPPORT DE COHÉRENCE - PARAMÈTRES D'APPEARANCE\n";
        echo "==================================================\n\n";
        
        echo "🎯 RECOMMANDATIONS POUR AMÉLIORER LA COHÉRENCE:\n";
        echo "   1. S'assurer que toutes les pages utilisent les paramètres d'apparence\n";
        echo "   2. Implémenter un helper pour récupérer les paramètres d'apparence\n";
        echo "   3. Utiliser les variables CSS pour les couleurs\n";
        echo "   4. Mettre en place un système de cache intelligent\n";
        echo "   5. Tester la cohérence après chaque modification\n";
        
        echo "\n🔧 ACTIONS SUGGÉRÉES:\n";
        echo "   1. Créer un helper AppHelper pour centraliser l'accès aux paramètres\n";
        echo "   2. Modifier les vues pour utiliser les paramètres d'apparence\n";
        echo "   3. Implémenter un système de prévisualisation en temps réel\n";
        echo "   4. Ajouter des tests automatisés pour la cohérence\n";
        
        echo "\n🏁 Test de cohérence terminé !\n";
    }
}

// Exécuter le test
$test = new TestAppearanceCoherence();
$test->run();
?>




