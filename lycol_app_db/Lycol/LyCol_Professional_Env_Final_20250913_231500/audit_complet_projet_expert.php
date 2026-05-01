<?php
/**
 * AUDIT COMPLET PROJET - EXPERT CodeIgniter/PHP/MariaDB
 * Vérification exhaustive de tous les aspects critiques
 */

class AuditCompletProjetExpert {
    private $baseUrl = 'http://localhost:8080';
    private $db;
    private $issues = [];
    private $warnings = [];
    private $successes = [];
    
    public function __construct() {
        echo "🔍 AUDIT COMPLET PROJET - EXPERT CodeIgniter/PHP/MariaDB\n";
        echo "========================================================\n";
        echo "Vérification exhaustive de tous les aspects critiques\n";
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
        $this->auditInfrastructure();
        $this->auditRessourcesLocales();
        $this->auditCRUDModules();
        $this->auditConformiteCodeIgniter();
        $this->auditCohérenceApplication();
        $this->auditSecurite();
        $this->auditPerformance();
        $this->auditBaseDeDonnees();
        $this->generateExpertReport();
    }
    
    private function auditInfrastructure() {
        echo "1️⃣ AUDIT INFRASTRUCTURE\n";
        echo "=======================\n";
        
        // Test du serveur
        $response = $this->makeRequest('/');
        if ($response['status'] === 200) {
            $this->successes[] = "Serveur fonctionnel sur le port 8080";
            echo "   ✅ Serveur fonctionnel sur le port 8080\n";
        } else {
            $this->issues[] = "Serveur non accessible";
            echo "   ❌ Serveur non accessible (Status: {$response['status']})\n";
        }
        
        // Test de la base de données
        try {
            $stmt = $this->db->query("SELECT 1");
            if ($stmt) {
                $this->successes[] = "Connexion MariaDB réussie";
                echo "   ✅ Connexion MariaDB réussie\n";
            }
        } catch (Exception $e) {
            $this->issues[] = "Erreur de connexion à MariaDB: " . $e->getMessage();
            echo "   ❌ Erreur de connexion à MariaDB: " . $e->getMessage() . "\n";
        }
        
        // Vérifier les tables principales
        $tables = ['users', 'roles', 'students', 'teachers', 'classes', 'subjects', 'grades', 'payments', 'books', 'messages'];
        foreach ($tables as $table) {
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() > 0) {
                    $this->successes[] = "Table '{$table}' existe";
                    echo "   ✅ Table '{$table}' existe\n";
                } else {
                    $this->warnings[] = "Table '{$table}' manquante";
                    echo "   ⚠️ Table '{$table}' manquante\n";
                }
            } catch (Exception $e) {
                $this->issues[] = "Erreur vérification table '{$table}': " . $e->getMessage();
                echo "   ❌ Erreur vérification table '{$table}': " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function auditRessourcesLocales() {
        echo "\n2️⃣ AUDIT RESSOURCES LOCALES (CRITIQUE)\n";
        echo "=====================================\n";
        
        // Vérifier les fichiers CSS locaux
        $cssFiles = [
            'public/assets/bulma/css/bulma.min.css' => 'CSS Bulma local',
            'public/assets/css/style.css' => 'CSS personnalisé'
        ];
        
        foreach ($cssFiles as $file => $description) {
            if (file_exists($file)) {
                $size = filesize($file);
                if ($size > 0) {
                    $this->successes[] = "{$description} présent ({$size} bytes)";
                    echo "   ✅ {$description} présent ({$size} bytes)\n";
                } else {
                    $this->warnings[] = "{$description} vide";
                    echo "   ⚠️ {$description} vide\n";
                }
            } else {
                $this->issues[] = "{$description} manquant";
                echo "   ❌ {$description} manquant\n";
            }
        }
        
        // Vérifier les fichiers JS locaux
        $jsFiles = [
            'public/assets/bulma/js/bulma.js' => 'JavaScript Bulma local',
            'public/assets/js/app.js' => 'JavaScript application'
        ];
        
        foreach ($jsFiles as $file => $description) {
            if (file_exists($file)) {
                $size = filesize($file);
                if ($size > 1000) {
                    $this->successes[] = "{$description} présent ({$size} bytes)";
                    echo "   ✅ {$description} présent ({$size} bytes)\n";
                } else {
                    $this->warnings[] = "{$description} trop petit ({$size} bytes)";
                    echo "   ⚠️ {$description} trop petit ({$size} bytes)\n";
                }
            } else {
                $this->issues[] = "{$description} manquant";
                echo "   ❌ {$description} manquant\n";
            }
        }
        
        // Vérifier les images locales
        $imageFiles = [
            'public/assets/images/logo.png' => 'Logo local',
            'public/assets/images/favicon.ico' => 'Favicon local'
        ];
        
        foreach ($imageFiles as $file => $description) {
            if (file_exists($file)) {
                $size = filesize($file);
                if ($size > 100) {
                    $this->successes[] = "{$description} présent ({$size} bytes)";
                    echo "   ✅ {$description} présent ({$size} bytes)\n";
                } else {
                    $this->warnings[] = "{$description} trop petit ({$size} bytes)";
                    echo "   ⚠️ {$description} trop petit ({$size} bytes)\n";
                }
            } else {
                $this->warnings[] = "{$description} manquant";
                echo "   ⚠️ {$description} manquant\n";
            }
        }
        
        // Vérifier l'absence de CDN externes
        $response = $this->makeRequest('/');
        if ($response['status'] === 200) {
            $content = $response['content'];
            
            // Vérifier l'absence de CDN Font Awesome
            if (strpos($content, 'cdnjs.cloudflare.com') !== false) {
                $this->issues[] = "CDN Font Awesome détecté - DOIT ÊTRE LOCAL";
                echo "   ❌ CDN Font Awesome détecté - DOIT ÊTRE LOCAL\n";
            } else {
                $this->successes[] = "Aucun CDN Font Awesome détecté";
                echo "   ✅ Aucun CDN Font Awesome détecté\n";
            }
            
            // Vérifier l'absence d'autres CDN
            $cdnPatterns = [
                'cdnjs.cloudflare.com' => 'CDN Cloudflare',
                'unpkg.com' => 'CDN Unpkg',
                'jsdelivr.net' => 'CDN JSDelivr',
                'googleapis.com' => 'CDN Google',
                'bootstrapcdn.com' => 'CDN Bootstrap'
            ];
            
            foreach ($cdnPatterns as $pattern => $description) {
                if (strpos($content, $pattern) !== false) {
                    $this->issues[] = "{$description} détecté - DOIT ÊTRE LOCAL";
                    echo "   ❌ {$description} détecté - DOIT ÊTRE LOCAL\n";
                }
            }
        }
    }
    
    private function auditCRUDModules() {
        echo "\n3️⃣ AUDIT CRUD MODULES\n";
        echo "=====================\n";
        
        $modules = [
            '/admin/economat' => 'Module Économat',
            '/admin/scolarite' => 'Module Scolarité',
            '/admin/etudes' => 'Module Études',
            '/admin/examens' => 'Module Examens',
            '/admin/enseignants' => 'Module Enseignants',
            '/admin/statistiques' => 'Module Statistiques',
            '/admin/bibliotheque' => 'Module Bibliothèque',
            '/admin/messagerie' => 'Module Messagerie',
            '/admin/securite' => 'Module Sécurité',
            '/admin/configuration' => 'Module Configuration'
        ];
        
        foreach ($modules as $route => $description) {
            $response = $this->makeRequest($route);
            if ($response['status'] === 200) {
                $this->successes[] = "{$description} accessible";
                echo "   ✅ {$description} accessible\n";
                
                // Vérifier les éléments CRUD dans le contenu
                $content = $response['content'];
                $crudElements = [
                    'create' => 'CREATE',
                    'edit' => 'UPDATE',
                    'delete' => 'DELETE',
                    'list' => 'READ'
                ];
                
                foreach ($crudElements as $element => $operation) {
                    if (strpos($content, $element) !== false) {
                        echo "     ✅ {$operation} détecté\n";
                    } else {
                        echo "     ⚠️ {$operation} non détecté\n";
                    }
                }
                
            } else {
                $this->warnings[] = "{$description} non accessible (Status: {$response['status']})";
                echo "   ⚠️ {$description} non accessible (Status: {$response['status']})\n";
            }
        }
    }
    
    private function auditConformiteCodeIgniter() {
        echo "\n4️⃣ AUDIT CONFORMITÉ CodeIgniter\n";
        echo "===============================\n";
        
        // Vérifier la structure MVC
        $mvcFiles = [
            'app/Controllers/Admin.php' => 'Contrôleur Admin',
            'app/Models/UserModel.php' => 'Modèle User',
            'app/Views/admin/dashboard.php' => 'Vue Dashboard',
            'app/Config/Routes.php' => 'Configuration Routes',
            'app/Config/Database.php' => 'Configuration Database'
        ];
        
        foreach ($mvcFiles as $file => $description) {
            if (file_exists($file)) {
                $this->successes[] = "{$description} présent";
                echo "   ✅ {$description} présent\n";
            } else {
                $this->issues[] = "{$description} manquant";
                echo "   ❌ {$description} manquant\n";
            }
        }
        
        // Vérifier les helpers et libraries
        $helperFiles = [
            'app/Helpers/AppHelper.php' => 'Helper App',
            'app/Libraries/LicenseGenerator.php' => 'Library LicenseGenerator'
        ];
        
        foreach ($helperFiles as $file => $description) {
            if (file_exists($file)) {
                $this->successes[] = "{$description} présent";
                echo "   ✅ {$description} présent\n";
            } else {
                $this->warnings[] = "{$description} manquant";
                echo "   ⚠️ {$description} manquant\n";
            }
        }
        
        // Vérifier la configuration Autoload
        if (file_exists('app/Config/Autoload.php')) {
            $autoloadContent = file_get_contents('app/Config/Autoload.php');
            if (strpos($autoloadContent, "'app'") !== false) {
                $this->successes[] = "Helper 'app' configuré dans Autoload";
                echo "   ✅ Helper 'app' configuré dans Autoload\n";
            } else {
                $this->warnings[] = "Helper 'app' non configuré dans Autoload";
                echo "   ⚠️ Helper 'app' non configuré dans Autoload\n";
            }
        }
    }
    
    private function auditCohérenceApplication() {
        echo "\n5️⃣ AUDIT COHÉRENCE APPLICATION\n";
        echo "==============================\n";
        
        // Vérifier la cohérence du port 8080
        $response = $this->makeRequest('/admin/dashboard');
        if ($response['status'] === 200) {
            $content = $response['content'];
            
            if (strpos($content, 'localhost:8080') !== false) {
                $this->successes[] = "Port 8080 utilisé de manière cohérente";
                echo "   ✅ Port 8080 utilisé de manière cohérente\n";
            } else {
                $this->issues[] = "Port 8080 non utilisé de manière cohérente";
                echo "   ❌ Port 8080 non utilisé de manière cohérente\n";
            }
            
            // Vérifier la cohérence des URLs
            if (strpos($content, 'http://localhost:8080') !== false) {
                $this->successes[] = "URLs cohérentes avec localhost:8080";
                echo "   ✅ URLs cohérentes avec localhost:8080\n";
            } else {
                $this->warnings[] = "URLs non cohérentes avec localhost:8080";
                echo "   ⚠️ URLs non cohérentes avec localhost:8080\n";
            }
        }
        
        // Vérifier la navigation cohérente
        $modules = ['economat', 'scolarite', 'etudes', 'examens', 'enseignants', 'statistiques', 'bibliotheque', 'messagerie', 'securite', 'configuration'];
        
        foreach ($modules as $module) {
            $response = $this->makeRequest("/admin/{$module}");
            if ($response['status'] === 200) {
                $content = $response['content'];
                
                // Vérifier que tous les modules ont la même structure de navigation
                $navElements = ['sidebar', 'navbar', 'menu'];
                foreach ($navElements as $element) {
                    if (strpos($content, $element) !== false) {
                        echo "     ✅ Navigation '{$element}' présente dans {$module}\n";
                    } else {
                        echo "     ⚠️ Navigation '{$element}' manquante dans {$module}\n";
                    }
                }
            }
        }
    }
    
    private function auditSecurite() {
        echo "\n6️⃣ AUDIT SÉCURITÉ\n";
        echo "==================\n";
        
        // Vérifier la protection CSRF
        $response = $this->makeRequest('/admin/securite/users/create');
        if ($response['status'] === 200) {
            $content = $response['content'];
            
            if (strpos($content, 'csrf_test_name') !== false) {
                $this->successes[] = "Protection CSRF active";
                echo "   ✅ Protection CSRF active\n";
            } else {
                $this->issues[] = "Protection CSRF manquante";
                echo "   ❌ Protection CSRF manquante\n";
            }
        }
        
        // Vérifier la validation des données
        $response = $this->makeRequest('/admin/securite/users/create');
        if ($response['status'] === 200) {
            $content = $response['content'];
            
            if (strpos($content, 'required') !== false) {
                $this->successes[] = "Validation des champs requis active";
                echo "   ✅ Validation des champs requis active\n";
            } else {
                $this->warnings[] = "Validation des champs requis manquante";
                echo "   ⚠️ Validation des champs requis manquante\n";
            }
        }
        
        // Vérifier le hachage des mots de passe
        try {
            $stmt = $this->db->query("SELECT password FROM users LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['password']) {
                if (strlen($result['password']) > 20) {
                    $this->successes[] = "Mots de passe hachés correctement";
                    echo "   ✅ Mots de passe hachés correctement\n";
                } else {
                    $this->issues[] = "Mots de passe non hachés";
                    echo "   ❌ Mots de passe non hachés\n";
                }
            }
        } catch (Exception $e) {
            $this->warnings[] = "Impossible de vérifier le hachage des mots de passe";
            echo "   ⚠️ Impossible de vérifier le hachage des mots de passe\n";
        }
    }
    
    private function auditPerformance() {
        echo "\n7️⃣ AUDIT PERFORMANCE\n";
        echo "====================\n";
        
        // Vérifier la taille des fichiers CSS/JS
        $cssFile = 'public/assets/bulma/css/bulma.min.css';
        if (file_exists($cssFile)) {
            $size = filesize($cssFile);
            if ($size < 200000) { // Moins de 200KB
                $this->successes[] = "CSS Bulma optimisé ({$size} bytes)";
                echo "   ✅ CSS Bulma optimisé ({$size} bytes)\n";
            } else {
                $this->warnings[] = "CSS Bulma trop volumineux ({$size} bytes)";
                echo "   ⚠️ CSS Bulma trop volumineux ({$size} bytes)\n";
            }
        }
        
        $jsFile = 'public/assets/bulma/js/bulma.js';
        if (file_exists($jsFile)) {
            $size = filesize($jsFile);
            if ($size < 50000) { // Moins de 50KB
                $this->successes[] = "JavaScript Bulma optimisé ({$size} bytes)";
                echo "   ✅ JavaScript Bulma optimisé ({$size} bytes)\n";
            } else {
                $this->warnings[] = "JavaScript Bulma trop volumineux ({$size} bytes)";
                echo "   ⚠️ JavaScript Bulma trop volumineux ({$size} bytes)\n";
            }
        }
        
        // Vérifier les temps de réponse
        $start = microtime(true);
        $response = $this->makeRequest('/');
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000; // en millisecondes
        
        if ($responseTime < 500) {
            $this->successes[] = "Temps de réponse excellent ({$responseTime}ms)";
            echo "   ✅ Temps de réponse excellent ({$responseTime}ms)\n";
        } elseif ($responseTime < 1000) {
            $this->successes[] = "Temps de réponse bon ({$responseTime}ms)";
            echo "   ✅ Temps de réponse bon ({$responseTime}ms)\n";
        } else {
            $this->warnings[] = "Temps de réponse lent ({$responseTime}ms)";
            echo "   ⚠️ Temps de réponse lent ({$responseTime}ms)\n";
        }
    }
    
    private function auditBaseDeDonnees() {
        echo "\n8️⃣ AUDIT BASE DE DONNÉES\n";
        echo "========================\n";
        
        // Vérifier les relations entre tables
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->successes[] = "{$result['count']} utilisateurs dans la base";
            echo "   ✅ {$result['count']} utilisateurs dans la base\n";
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM roles");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->successes[] = "{$result['count']} rôles dans la base";
            echo "   ✅ {$result['count']} rôles dans la base\n";
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM students");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->successes[] = "{$result['count']} étudiants dans la base";
            echo "   ✅ {$result['count']} étudiants dans la base\n";
            
        } catch (Exception $e) {
            $this->warnings[] = "Erreur lors de la vérification des données: " . $e->getMessage();
            echo "   ⚠️ Erreur lors de la vérification des données: " . $e->getMessage() . "\n";
        }
        
        // Vérifier l'intégrité des données
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role_id NOT IN (SELECT id FROM roles)");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                $this->successes[] = "Intégrité des relations utilisateurs-rôles respectée";
                echo "   ✅ Intégrité des relations utilisateurs-rôles respectée\n";
            } else {
                $this->issues[] = "{$result['count']} utilisateurs avec des rôles invalides";
                echo "   ❌ {$result['count']} utilisateurs avec des rôles invalides\n";
            }
            
        } catch (Exception $e) {
            $this->warnings[] = "Impossible de vérifier l'intégrité des relations";
            echo "   ⚠️ Impossible de vérifier l'intégrité des relations\n";
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'AuditCompletProjet/1.0');
        
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
    
    private function generateExpertReport() {
        echo "\n📋 RAPPORT EXPERT - AUDIT COMPLET PROJET\n";
        echo "=========================================\n\n";
        
        echo "🎯 RÉSUMÉ EXÉCUTIF:\n";
        echo "   • Problèmes critiques: " . count($this->issues) . "\n";
        echo "   • Avertissements: " . count($this->warnings) . "\n";
        echo "   • Succès: " . count($this->successes) . "\n";
        
        if (count($this->issues) > 0) {
            echo "\n❌ PROBLÈMES CRITIQUES:\n";
            foreach ($this->issues as $issue) {
                echo "   • {$issue}\n";
            }
        }
        
        if (count($this->warnings) > 0) {
            echo "\n⚠️ AVERTISSEMENTS:\n";
            foreach ($this->warnings as $warning) {
                echo "   • {$warning}\n";
            }
        }
        
        echo "\n✅ POINTS D'EXCELLENCE:\n";
        foreach (array_slice($this->successes, 0, 10) as $success) {
            echo "   • {$success}\n";
        }
        
        if (count($this->successes) > 10) {
            echo "   • ... et " . (count($this->successes) - 10) . " autres succès\n";
        }
        
        // Score global
        $totalChecks = count($this->issues) + count($this->warnings) + count($this->successes);
        $score = $totalChecks > 0 ? round((count($this->successes) / $totalChecks) * 100) : 0;
        
        echo "\n🏆 SCORE GLOBAL: {$score}/100\n";
        
        if ($score >= 90) {
            echo "   🎉 EXCELLENT - Projet prêt pour la production\n";
        } elseif ($score >= 80) {
            echo "   ✅ BON - Quelques améliorations mineures nécessaires\n";
        } elseif ($score >= 70) {
            echo "   ⚠️ MOYEN - Améliorations importantes nécessaires\n";
        } else {
            echo "   ❌ CRITIQUE - Corrections majeures requises\n";
        }
        
        echo "\n🔧 RECOMMANDATIONS PRIORITAIRES:\n";
        
        if (count($this->issues) > 0) {
            echo "1. CORRIGER LES PROBLÈMES CRITIQUES (URGENT)\n";
            foreach (array_slice($this->issues, 0, 3) as $issue) {
                echo "   • {$issue}\n";
            }
        }
        
        if (count($this->warnings) > 0) {
            echo "2. ADRESSER LES AVERTISSEMENTS\n";
            foreach (array_slice($this->warnings, 0, 3) as $warning) {
                echo "   • {$warning}\n";
            }
        }
        
        echo "\n🏁 Audit complet terminé !\n";
    }
}

// Exécuter l'audit
$audit = new AuditCompletProjetExpert();
$audit->run();
?>




