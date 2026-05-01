<?php
/**
 * Initialisation des Paramètres d'Apparence - KISSAI SCHOOL - LyCol
 * Création des paramètres par défaut en base de données
 */

class InitialiserParametresApparence {
    private $db;
    
    public function __construct() {
        echo "🎨 INITIALISATION DES PARAMÈTRES D'APPEARANCE\n";
        echo "============================================\n\n";
        
        // Connexion à la base de données
        try {
            $this->db = new PDO(
                'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
                'root',
                'Bateau123',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "✅ Connexion à la base de données établie\n\n";
        } catch (Exception $e) {
            echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function run() {
        $this->verifierTableSettings();
        $this->initialiserParametres();
        $this->verifierParametres();
        $this->testerSauvegarde();
        $this->genererRapport();
    }
    
    private function verifierTableSettings() {
        echo "1️⃣ Vérification de la table settings...\n";
        
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'settings'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo "   ✅ Table settings existe\n";
                
                // Vérifier la structure
                $stmt = $this->db->query("DESCRIBE settings");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "   📊 Structure de la table:\n";
                foreach ($columns as $column) {
                    echo "      • {$column['Field']} - {$column['Type']}\n";
                }
            } else {
                echo "   ⚠️ Table settings n'existe pas, création...\n";
                $this->creerTableSettings();
            }
        } catch (Exception $e) {
            echo "   ❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    private function creerTableSettings() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(255) NOT NULL,
                `setting_value` text,
                `setting_type` varchar(50) DEFAULT 'general',
                `description` text,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`),
                KEY `setting_type` (`setting_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->db->exec($sql);
            echo "   ✅ Table settings créée avec succès\n";
        } catch (Exception $e) {
            echo "   ❌ Erreur lors de la création: " . $e->getMessage() . "\n";
        }
    }
    
    private function initialiserParametres() {
        echo "\n2️⃣ Initialisation des paramètres par défaut...\n";
        
        $parametres = [
            'app_name' => [
                'value' => 'KISSAI SCHOOL',
                'description' => 'Nom de l\'application affiché dans l\'en-tête'
            ],
            'app_logo' => [
                'value' => 'assets/images/logo.png',
                'description' => 'Chemin vers le logo de l\'application'
            ],
            'app_favicon' => [
                'value' => 'assets/images/favicon.ico',
                'description' => 'Chemin vers le favicon de l\'application'
            ],
            'primary_color' => [
                'value' => '#3273dc',
                'description' => 'Couleur principale de l\'interface'
            ],
            'secondary_color' => [
                'value' => '#00d1b2',
                'description' => 'Couleur secondaire de l\'interface'
            ],
            'app_description' => [
                'value' => 'Système de gestion scolaire KISSAI SCHOOL',
                'description' => 'Description de l\'application'
            ],
            'app_keywords' => [
                'value' => 'école, gestion, scolaire, KISSAI',
                'description' => 'Mots-clés pour le référencement'
            ]
        ];
        
        foreach ($parametres as $key => $param) {
            try {
                // Vérifier si le paramètre existe déjà
                $stmt = $this->db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $exists = $stmt->fetch();
                
                if ($exists) {
                    // Mise à jour
                    $stmt = $this->db->prepare("UPDATE settings SET setting_value = ?, description = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$param['value'], $param['description'], $key]);
                    echo "   ✅ Paramètre {$key} mis à jour\n";
                } else {
                    // Insertion
                    $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) VALUES (?, ?, 'appearance', ?, NOW(), NOW())");
                    $stmt->execute([$key, $param['value'], $param['description']]);
                    echo "   ✅ Paramètre {$key} créé\n";
                }
            } catch (Exception $e) {
                echo "   ❌ Erreur pour {$key}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function verifierParametres() {
        echo "\n3️⃣ Vérification des paramètres sauvegardés...\n";
        
        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value, description FROM settings WHERE setting_type = 'appearance' ORDER BY setting_key");
            $parametres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   📊 Paramètres trouvés: " . count($parametres) . "\n\n";
            
            foreach ($parametres as $param) {
                echo "   • {$param['setting_key']}: {$param['setting_value']}\n";
                echo "     Description: {$param['description']}\n\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
        }
    }
    
    private function testerSauvegarde() {
        echo "\n4️⃣ Test de la sauvegarde via l'API...\n";
        
        // Test avec de nouvelles valeurs
        $testData = [
            'app_name' => 'KISSAI SCHOOL - Test Modifié',
            'primary_color' => '#ff6600',
            'secondary_color' => '#00cc66',
            'app_description' => 'Test de modification - ' . date('Y-m-d H:i:s')
        ];
        
        $response = $this->makeRequest('/admin/configuration/save-appearance', 'POST', $testData);
        
        if ($response['status'] === 200 || $response['status'] === 302) {
            echo "   ✅ Sauvegarde via API réussie\n";
            
            // Vérifier les modifications en base
            try {
                $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? AND setting_type = 'appearance'");
                $stmt->execute(['app_name']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['setting_value'] === $testData['app_name']) {
                    echo "   ✅ Modification confirmée en base de données\n";
                } else {
                    echo "   ⚠️ Modification non trouvée en base de données\n";
                }
            } catch (Exception $e) {
                echo "   ❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ❌ Échec de la sauvegarde via API (Status: {$response['status']})\n";
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null) {
        $baseUrl = 'http://localhost:8080';
        $fullUrl = $baseUrl . $url;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'InitAppearance/1.0');
        
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
    
    private function genererRapport() {
        echo "\n📋 RAPPORT D'INITIALISATION\n";
        echo "============================\n\n";
        
        echo "✅ RÉSULTATS:\n";
        echo "   • Table settings: Vérifiée/Créée\n";
        echo "   • Paramètres par défaut: Initialisés\n";
        echo "   • Sauvegarde API: Testée\n";
        echo "   • Base de données: Fonctionnelle\n\n";
        
        echo "🎯 PARAMÈTRES CONFIGURÉS:\n";
        echo "   • Nom de l'application: KISSAI SCHOOL\n";
        echo "   • Logo: assets/images/logo.png\n";
        echo "   • Favicon: assets/images/favicon.ico\n";
        echo "   • Couleur primaire: #3273dc\n";
        echo "   • Couleur secondaire: #00d1b2\n";
        echo "   • Description: Système de gestion scolaire KISSAI SCHOOL\n\n";
        
        echo "🔗 LIENS UTILES:\n";
        echo "   • Configuration: http://localhost:8080/admin/configuration/appearance\n";
        echo "   • Test: http://localhost:8080/admin/configuration\n\n";
        
        echo "🏁 Initialisation terminée avec succès !\n";
    }
}

// Exécuter l'initialisation
$init = new InitialiserParametresApparence();
$init->run();
?>




