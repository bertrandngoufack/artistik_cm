<?php
/**
 * Audit Complet du Module Sécurité - Test de Cohérence et Fonctionnalités
 * Vérification complète du CRUD et de la cohérence avec tous les modules
 */

class AuditCompletSecuriteFinal
{
    private $baseUrl = 'http://localhost:8080';
    private $testResults = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct()
    {
        echo "=== AUDIT COMPLET DU MODULE SÉCURITÉ ===\n";
        echo "URL de base: {$this->baseUrl}\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Vérifier que le serveur est accessible
        if (!$this->checkServerAccess()) {
            echo "❌ ERREUR: Le serveur n'est pas accessible sur {$this->baseUrl}\n";
            exit(1);
        }
    }
    
    public function run()
    {
        echo "🚀 Démarrage de l'audit complet...\n\n";
        
        // 1. Test des routes de base
        $this->testRoutesSecurite();
        
        // 2. Test des fonctionnalités CRUD utilisateurs
        $this->testCRUDUtilisateurs();
        
        // 3. Test des fonctionnalités CRUD rôles
        $this->testCRUDRoles();
        
        // 4. Test des permissions et audit
        $this->testPermissionsEtAudit();
        
        // 5. Test de cohérence avec les autres modules
        $this->testCohérenceModules();
        
        // 6. Test des fonctionnalités avancées
        $this->testFonctionnalitesAvancees();
        
        // 7. Génération du rapport
        $this->genererRapport();
    }
    
    private function checkServerAccess()
    {
        $response = $this->makeRequest('/');
        return $response['http_code'] === 200;
    }
    
    private function testRoutesSecurite()
    {
        echo "📋 TEST DES ROUTES SÉCURITÉ\n";
        echo str_repeat("-", 50) . "\n";
        
        $routesSecurite = [
            '/admin/securite' => 'Page principale sécurité',
            '/admin/securite/users' => 'Gestion des utilisateurs',
            '/admin/securite/users/create' => 'Création d\'utilisateur',
            '/admin/securite/roles' => 'Gestion des rôles',
            '/admin/securite/roles/create' => 'Création de rôle',
            '/admin/securite/logs' => 'Journaux d\'audit',
            '/admin/securite/permissions' => 'Gestion des permissions',
            '/admin/securite/audit' => 'Audit de sécurité'
        ];
        
        foreach ($routesSecurite as $route => $description) {
            $this->testRoute($description, $route);
        }
        
        echo "\n";
    }
    
    private function testCRUDUtilisateurs()
    {
        echo "👥 TEST CRUD UTILISATEURS\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test de la liste des utilisateurs
        $this->testRoute('Liste des utilisateurs', '/admin/securite/users');
        
        // Test de la création d'utilisateur
        $this->testRoute('Formulaire création utilisateur', '/admin/securite/users/create');
        
        // Test de la soumission du formulaire de création
        $this->testPostRequest('/admin/securite/users/store', [
            'username' => 'test_user_' . time(),
            'email' => 'test' . time() . '@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'role_id' => '1',
            'password' => 'password123',
            'password_confirm' => 'password123'
        ], 'Création d\'utilisateur');
        
        // Test de la liste après création
        $this->testRoute('Liste utilisateurs après création', '/admin/securite/users');
        
        echo "\n";
    }
    
    private function testCRUDRoles()
    {
        echo "🔐 TEST CRUD RÔLES\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test de la liste des rôles
        $this->testRoute('Liste des rôles', '/admin/securite/roles');
        
        // Test de la création de rôle
        $this->testRoute('Formulaire création rôle', '/admin/securite/roles/create');
        
        // Test de la soumission du formulaire de création de rôle
        $this->testPostRequest('/admin/securite/roles/store', [
            'name' => 'test_role_' . time(),
            'description' => 'Rôle de test pour audit',
            'permissions' => ['securite.view', 'securite.users']
        ], 'Création de rôle');
        
        // Test de la liste après création
        $this->testRoute('Liste rôles après création', '/admin/securite/roles');
        
        echo "\n";
    }
    
    private function testPermissionsEtAudit()
    {
        echo "🔒 TEST PERMISSIONS ET AUDIT\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test des permissions
        $this->testRoute('Gestion des permissions', '/admin/securite/permissions');
        
        // Test des logs d'audit
        $this->testRoute('Journaux d\'audit', '/admin/securite/logs');
        
        // Test de l'audit de sécurité
        $this->testRoute('Audit de sécurité', '/admin/securite/audit');
        
        echo "\n";
    }
    
    private function testCohérenceModules()
    {
        echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
        echo str_repeat("-", 50) . "\n";
        
        $modules = [
            'dashboard' => 'Dashboard',
            'economat' => 'Économat',
            'scolarite' => 'Scolarité',
            'etudes' => 'Études',
            'examens' => 'Examens',
            'enseignants' => 'Enseignants',
            'statistiques' => 'Statistiques',
            'messagerie' => 'Messagerie'
        ];
        
        foreach ($modules as $module => $nom) {
            echo "🔍 Test de cohérence avec le module {$nom}...\n";
            
            // Vérifier l'accès au module
            $response = $this->makeRequest("/admin/{$module}");
            if ($response['http_code'] === 200) {
                echo "   ✅ Module {$nom} accessible\n";
                
                // Vérifier la présence de liens de sécurité
                $this->verifierLiensSecurite("/admin/{$module}");
            } else {
                echo "   ⚠️ Module {$nom} non accessible (HTTP {$response['http_code']})\n";
                $this->warnings[] = "Module {$nom} non accessible";
            }
        }
        
        echo "\n";
    }
    
    private function testFonctionnalitesAvancees()
    {
        echo "🚀 TEST FONCTIONNALITÉS AVANCÉES\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test des permissions utilisateur
        $this->testRoute('Permissions utilisateur', '/admin/securite/users/1/permissions');
        
        // Test des permissions de rôle
        $this->testRoute('Permissions de rôle', '/admin/securite/roles/1/permissions');
        
        // Test de la vue détaillée utilisateur
        $this->testRoute('Vue détaillée utilisateur', '/admin/securite/users/1');
        
        // Test de la vue détaillée rôle
        $this->testRoute('Vue détaillée rôle', '/admin/securite/roles/1');
        
        echo "\n";
    }
    
    private function testRoute($description, $route)
    {
        echo "🔍 Test: {$description}...\n";
        
        $response = $this->makeRequest($route);
        
        if ($response['http_code'] === 200) {
            echo "   ✅ {$description}: OK (HTTP 200)\n";
            $this->testResults[$route] = [
                'status' => 'success',
                'http_code' => $response['http_code'],
                'description' => $description
            ];
        } elseif ($response['http_code'] === 302 || $response['http_code'] === 303) {
            echo "   ⚠️ {$description}: Redirection (HTTP {$response['http_code']})\n";
            $this->testResults[$route] = [
                'status' => 'redirect',
                'http_code' => $response['http_code'],
                'description' => $description
            ];
        } else {
            echo "   ❌ {$description}: ÉCHEC (HTTP {$response['http_code']})\n";
            $this->testResults[$route] = [
                'status' => 'error',
                'http_code' => $response['http_code'],
                'description' => $description
            ];
            $this->errors[] = "Route {$route}: HTTP {$response['http_code']}";
        }
    }
    
    private function testPostRequest($route, $data, $description)
    {
        echo "📝 Test POST: {$description}...\n";
        
        $response = $this->makeRequest($route, 'POST', $data);
        
        if ($response['http_code'] === 302 || $response['http_code'] === 303) {
            echo "   ✅ {$description}: Redirection après POST (HTTP {$response['http_code']})\n";
            $this->testResults[$route] = [
                'status' => 'success',
                'http_code' => $response['http_code'],
                'description' => $description,
                'method' => 'POST'
            ];
        } elseif ($response['http_code'] === 200) {
            echo "   ⚠️ {$description}: Retour à la page (HTTP 200) - possible erreur de validation\n";
            $this->testResults[$route] = [
                'status' => 'warning',
                'http_code' => $response['http_code'],
                'description' => $description,
                'method' => 'POST'
            ];
        } else {
            echo "   ❌ {$description}: ÉCHEC (HTTP {$response['http_code']})\n";
            $this->testResults[$route] = [
                'status' => 'error',
                'http_code' => $response['http_code'],
                'description' => $description,
                'method' => 'POST'
            ];
            $this->errors[] = "POST {$route}: HTTP {$response['http_code']}";
        }
    }
    
    private function verifierLiensSecurite($moduleUrl)
    {
        $response = $this->makeRequest($moduleUrl);
        
        if ($response['http_code'] === 200) {
            $content = $response['content'];
            
            // Vérifier la présence de liens vers la sécurité
            if (strpos($content, 'admin/securite') !== false) {
                echo "      ✅ Liens de sécurité présents\n";
            } else {
                echo "      ⚠️ Aucun lien de sécurité détecté\n";
                $this->warnings[] = "Module {$moduleUrl}: Aucun lien de sécurité";
            }
            
            // Vérifier la présence de contrôles d'accès
            if (strpos($content, 'permission') !== false || strpos($content, 'role') !== false) {
                echo "      ✅ Contrôles d'accès présents\n";
            } else {
                echo "      ⚠️ Contrôles d'accès non détectés\n";
            }
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null)
    {
        $fullUrl = $this->baseUrl . $url;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AuditSecuriteFinal/1.0');
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['http_code' => 0, 'content' => '', 'error' => $error];
        }
        
        // Séparer les en-têtes du contenu
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $content = substr($response, $headerSize);
        
        return [
            'http_code' => $httpCode,
            'content' => $content,
            'headers' => $headers
        ];
    }
    
    private function genererRapport()
    {
        echo "📊 GÉNÉRATION DU RAPPORT FINAL\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalTests = count($this->testResults);
        $successTests = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'success';
        }));
        $errorTests = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'error';
        }));
        $warningTests = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'warning';
        }));
        
        echo "📈 RÉSUMÉ DES TESTS\n";
        echo "Total des tests: {$totalTests}\n";
        echo "✅ Succès: {$successTests}\n";
        echo "⚠️ Avertissements: {$warningTests}\n";
        echo "❌ Erreurs: {$errorTests}\n";
        echo "Taux de succès: " . round(($successTests / $totalTests) * 100, 2) . "%\n\n";
        
        if (!empty($this->errors)) {
            echo "❌ ERREURS DÉTECTÉES:\n";
            foreach ($this->errors as $error) {
                echo "   • {$error}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️ AVERTISSEMENTS:\n";
            foreach ($this->warnings as $warning) {
                echo "   • {$warning}\n";
            }
            echo "\n";
        }
        
        echo "🔍 DÉTAIL DES TESTS PAR ROUTE:\n";
        foreach ($this->testResults as $route => $result) {
            $statusIcon = $result['status'] === 'success' ? '✅' : ($result['status'] === 'warning' ? '⚠️' : '❌');
            echo "   {$statusIcon} {$route} - {$result['description']} (HTTP {$result['http_code']})\n";
        }
        
        echo "\n🎯 RECOMMANDATIONS:\n";
        
        if ($errorTests > 0) {
            echo "   • Corriger les routes en erreur avant la mise en production\n";
        }
        
        if ($warningTests > 0) {
            echo "   • Vérifier les redirections et les formulaires\n";
        }
        
        if (empty($this->errors) && empty($this->warnings)) {
            echo "   • 🎉 Excellent ! Le module sécurité est entièrement fonctionnel\n";
        }
        
        echo "\n📋 ROUTES TESTÉES:\n";
        echo "   • Page principale: {$this->baseUrl}/admin/securite\n";
        echo "   • Utilisateurs: {$this->baseUrl}/admin/securite/users\n";
        echo "   • Rôles: {$this->baseUrl}/admin/securite/roles\n";
        echo "   • Permissions: {$this->baseUrl}/admin/securite/permissions\n";
        echo "   • Logs: {$this->baseUrl}/admin/securite/logs\n";
        echo "   • Audit: {$this->baseUrl}/admin/securite/audit\n";
        
        echo "\n🏁 Audit terminé à " . date('Y-m-d H:i:s') . "\n";
    }
}

// Exécution de l'audit
if (php_sapi_name() === 'cli') {
    $audit = new AuditCompletSecuriteFinal();
    $audit->run();
} else {
    echo "Ce script doit être exécuté en ligne de commande.\n";
}








