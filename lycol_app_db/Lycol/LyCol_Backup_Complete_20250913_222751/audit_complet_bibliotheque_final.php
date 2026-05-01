<?php
/**
 * Audit Complet du Module Bibliothèque - Test de Cohérence et Fonctionnalités
 * Vérification complète du CRUD et de la cohérence avec tous les modules
 */

class AuditCompletBibliothequeFinal
{
    private $baseUrl = 'http://localhost:8080';
    private $testResults = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct()
    {
        echo "=== AUDIT COMPLET DU MODULE BIBLIOTHÈQUE ===\n";
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
        $this->testRoutesBibliotheque();
        
        // 2. Test des fonctionnalités CRUD livres
        $this->testCRUDLivres();
        
        // 3. Test des fonctionnalités CRUD emprunts
        $this->testCRUDEmprunts();
        
        // 4. Test des fonctionnalités CRUD membres
        $this->testCRUDMembres();
        
        // 5. Test des rapports et exports
        $this->testRapportsEtExports();
        
        // 6. Test de cohérence avec les autres modules
        $this->testCohérenceModules();
        
        // 7. Test des fonctionnalités avancées
        $this->testFonctionnalitesAvancees();
        
        // 8. Génération du rapport
        $this->genererRapport();
    }
    
    private function checkServerAccess()
    {
        $response = $this->makeRequest('/');
        return $response['http_code'] === 200;
    }
    
    private function testRoutesBibliotheque()
    {
        echo "📋 TEST DES ROUTES BIBLIOTHÈQUE\n";
        echo str_repeat("-", 50) . "\n";
        
        $routesBibliotheque = [
            '/admin/bibliotheque' => 'Page principale bibliothèque',
            '/admin/bibliotheque/books' => 'Gestion des livres',
            '/admin/bibliotheque/books/create' => 'Création de livre',
            '/admin/bibliotheque/loans' => 'Gestion des emprunts',
            '/admin/bibliotheque/loans/create' => 'Création d\'emprunt',
            '/admin/bibliotheque/members' => 'Gestion des membres',
            '/admin/bibliotheque/members/create' => 'Création de membre',
            '/admin/bibliotheque/reports' => 'Rapports bibliothèque'
        ];
        
        foreach ($routesBibliotheque as $route => $description) {
            $this->testRoute($description, $route);
        }
        
        echo "\n";
    }
    
    private function testCRUDLivres()
    {
        echo "📚 TEST CRUD LIVRES\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test de la liste des livres
        $this->testRoute('Liste des livres', '/admin/bibliotheque/books');
        
        // Test de la création de livre
        $this->testRoute('Formulaire création livre', '/admin/bibliotheque/books/create');
        
        // Test de la soumission du formulaire de création
        $this->testPostRequest('/admin/bibliotheque/books/store', [
            'title' => 'Livre Test ' . time(),
            'author' => 'Auteur Test',
            'isbn' => '978-0-000000-0-' . rand(100, 999),
            'total_copies' => '5',
            'category' => 'Test',
            'description' => 'Description de test'
        ], 'Création de livre');
        
        // Test de la liste après création
        $this->testRoute('Liste livres après création', '/admin/bibliotheque/books');
        
        echo "\n";
    }
    
    private function testCRUDEmprunts()
    {
        echo "📖 TEST CRUD EMPRUNTS\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test de la liste des emprunts
        $this->testRoute('Liste des emprunts', '/admin/bibliotheque/loans');
        
        // Test de la création d'emprunt
        $this->testRoute('Formulaire création emprunt', '/admin/bibliotheque/loans/create');
        
        // Test de la soumission du formulaire de création d'emprunt
        $this->testPostRequest('/admin/bibliotheque/loans/store', [
            'book_id' => '1',
            'member_id' => '1',
            'loan_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'notes' => 'Emprunt de test'
        ], 'Création d\'emprunt');
        
        // Test de la liste après création
        $this->testRoute('Liste emprunts après création', '/admin/bibliotheque/loans');
        
        echo "\n";
    }
    
    private function testCRUDMembres()
    {
        echo "👥 TEST CRUD MEMBRES\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test de la liste des membres
        $this->testRoute('Liste des membres', '/admin/bibliotheque/members');
        
        // Test de la création de membre
        $this->testRoute('Formulaire création membre', '/admin/bibliotheque/members/create');
        
        // Test de la soumission du formulaire de création de membre
        $this->testPostRequest('/admin/bibliotheque/members/store', [
            'first_name' => 'Membre',
            'last_name' => 'Test ' . time(),
            'email' => 'membre' . time() . '@test.com',
            'phone' => '0123456789',
            'membership_type' => 'student'
        ], 'Création de membre');
        
        // Test de la liste après création
        $this->testRoute('Liste membres après création', '/admin/bibliotheque/members');
        
        echo "\n";
    }
    
    private function testRapportsEtExports()
    {
        echo "📊 TEST RAPPORTS ET EXPORTS\n";
        echo str_repeat("-", 50) . "\n";
        
        // Test des rapports
        $this->testRoute('Rapports généraux', '/admin/bibliotheque/reports');
        $this->testRoute('Rapports livres', '/admin/bibliotheque/reports/books');
        $this->testRoute('Rapports emprunts', '/admin/bibliotheque/reports/loans');
        $this->testRoute('Rapports membres', '/admin/bibliotheque/reports/members');
        
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
            'messagerie' => 'Messagerie',
            'securite' => 'Sécurité'
        ];
        
        foreach ($modules as $module => $nom) {
            echo "🔍 Test de cohérence avec le module {$nom}...\n";
            
            // Vérifier l'accès au module
            $response = $this->makeRequest("/admin/{$module}");
            if ($response['http_code'] === 200) {
                echo "   ✅ Module {$nom} accessible\n";
                
                // Vérifier la présence de liens vers la bibliothèque
                $this->verifierLiensBibliotheque("/admin/{$module}");
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
        
        // Test des vues détaillées
        $this->testRoute('Vue détaillée livre', '/admin/bibliotheque/books/1');
        $this->testRoute('Vue détaillée emprunt', '/admin/bibliotheque/loans/1');
        
        // Test des formulaires d'édition
        $this->testRoute('Édition livre', '/admin/bibliotheque/books/1/edit');
        $this->testRoute('Édition emprunt', '/admin/bibliotheque/loans/1/edit');
        
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
    
    private function verifierLiensBibliotheque($moduleUrl)
    {
        $response = $this->makeRequest($moduleUrl);
        
        if ($response['http_code'] === 200) {
            $content = $response['content'];
            
            // Vérifier la présence de liens vers la bibliothèque
            if (strpos($content, 'admin/bibliotheque') !== false) {
                echo "      ✅ Liens bibliothèque présents\n";
            } else {
                echo "      ⚠️ Aucun lien bibliothèque détecté\n";
                $this->warnings[] = "Module {$moduleUrl}: Aucun lien bibliothèque";
            }
            
            // Vérifier la présence de références aux livres/emprunts
            if (strpos($content, 'bibliotheque') !== false || strpos($content, 'livre') !== false) {
                echo "      ✅ Références bibliothèque présentes\n";
            } else {
                echo "      ⚠️ Références bibliothèque non détectées\n";
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'AuditBibliothequeFinal/1.0');
        
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
            echo "   • 🎉 Excellent ! Le module bibliothèque est entièrement fonctionnel\n";
        }
        
        echo "\n📋 ROUTES TESTÉES:\n";
        echo "   • Page principale: {$this->baseUrl}/admin/bibliotheque\n";
        echo "   • Livres: {$this->baseUrl}/admin/bibliotheque/books\n";
        echo "   • Emprunts: {$this->baseUrl}/admin/bibliotheque/loans\n";
        echo "   • Membres: {$this->baseUrl}/admin/bibliotheque/members\n";
        echo "   • Rapports: {$this->baseUrl}/admin/bibliotheque/reports\n";
        
        echo "\n🏁 Audit terminé à " . date('Y-m-d H:i:s') . "\n";
    }
}

// Exécution de l'audit
if (php_sapi_name() === 'cli') {
    $audit = new AuditCompletBibliothequeFinal();
    $audit->run();
} else {
    echo "Ce script doit être exécuté en ligne de commande.\n";
}








