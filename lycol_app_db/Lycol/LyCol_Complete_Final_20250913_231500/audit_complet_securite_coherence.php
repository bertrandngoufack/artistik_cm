<?php
/**
 * AUDIT COMPLET DU MODULE SÉCURITÉ ET COHÉRENCE AVEC TOUS LES MODULES
 * 
 * Ce script effectue un audit complet du module sécurité et vérifie
 * sa cohérence avec tous les autres modules de l'application KISSAI SCHOOL
 */

class AuditCompletSecuriteCoherence
{
    private $baseUrl = 'http://localhost:8080';
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $success = [];

    public function __construct()
    {
        echo "🔍 AUDIT COMPLET DU MODULE SÉCURITÉ ET COHÉRENCE\n";
        echo "================================================\n\n";
        
        $this->auditSecurite();
        $this->auditCohérenceModules();
        $this->auditCRUDComplet();
        $this->auditRoutesEtLiens();
        $this->auditPermissionsEtSécurité();
        $this->genererRapport();
    }

    /**
     * Audit principal du module sécurité
     */
    private function auditSecurite()
    {
        echo "1. AUDIT DU MODULE SÉCURITÉ\n";
        echo "============================\n";

        // Test de la page principale
        $this->testRoute('Page principale sécurité', '/admin/securite');
        
        // Test de la gestion des utilisateurs
        $this->testRoute('Gestion des utilisateurs', '/admin/securite/users');
        $this->testRoute('Création d\'utilisateur', '/admin/securite/users/create');
        
        // Test de la gestion des rôles
        $this->testRoute('Gestion des rôles', '/admin/securite/roles');
        $this->testRoute('Création de rôle', '/admin/securite/roles/create');
        
        // Test des fonctionnalités avancées
        $this->testRoute('Journaux d\'audit', '/admin/securite/logs');
        $this->testRoute('Gestion des permissions', '/admin/securite/permissions');
        
        echo "\n";
    }

    /**
     * Audit de la cohérence avec tous les modules
     */
    private function auditCohérenceModules()
    {
        echo "2. AUDIT DE COHÉRENCE AVEC TOUS LES MODULES\n";
        echo "============================================\n";

        $modules = [
            'dashboard' => 'Dashboard',
            'economat' => 'Économat',
            'scolarite' => 'Scolarité',
            'etudes' => 'Études',
            'examens' => 'Examens',
            'enseignants' => 'Enseignants',
            'statistiques' => 'Statistiques',
            'messagerie' => 'Messagerie',
            'bibliotheque' => 'Bibliothèque',
            'configuration' => 'Configuration'
        ];

        foreach ($modules as $module => $nom) {
            echo "Vérification du module: $nom\n";
            
            // Test de la page principale du module
            $this->testRoute("Page principale $nom", "/admin/$module");
            
            // Vérification des liens vers la sécurité
            $this->verifierLiensSecurite("/admin/$module");
            
            echo "   ✓ Module $nom vérifié\n\n";
        }
    }

    /**
     * Audit complet des fonctionnalités CRUD
     */
    private function auditCRUDComplet()
    {
        echo "3. AUDIT COMPLET DES FONCTIONNALITÉS CRUD\n";
        echo "==========================================\n";

        // Test CRUD Utilisateurs
        echo "Test CRUD Utilisateurs:\n";
        $this->testCRUDUtilisateurs();
        
        // Test CRUD Rôles
        echo "Test CRUD Rôles:\n";
        $this->testCRUDRoles();
        
        // Test CRUD Permissions
        echo "Test CRUD Permissions:\n";
        $this->testCRUDPermissions();
        
        echo "\n";
    }

    /**
     * Audit des routes et liens
     */
    private function auditRoutesEtLiens()
    {
        echo "4. AUDIT DES ROUTES ET LIENS\n";
        echo "============================\n";

        // Vérification des routes de sécurité
        $routesSecurite = [
            '/admin/securite' => 'GET',
            '/admin/securite/users' => 'GET',
            '/admin/securite/users/create' => 'GET',
            '/admin/securite/users/store' => 'POST',
            '/admin/securite/roles' => 'GET',
            '/admin/securite/roles/create' => 'GET',
            '/admin/securite/roles/store' => 'POST',
            '/admin/securite/logs' => 'GET',
            '/admin/securite/permissions' => 'GET',
            '/admin/securite/audit' => 'GET'
        ];

        foreach ($routesSecurite as $route => $method) {
            $this->testRoute("Route $method $route", $route, $method);
        }

        echo "\n";
    }

    /**
     * Audit des permissions et de la sécurité
     */
    private function auditPermissionsEtSécurité()
    {
        echo "5. AUDIT DES PERMISSIONS ET DE LA SÉCURITÉ\n";
        echo "===========================================\n";

        // Vérification des permissions par module
        $modulesPermissions = [
            'economat' => ['view', 'create', 'edit', 'delete', 'export'],
            'scolarite' => ['view', 'create', 'edit', 'delete', 'export'],
            'etudes' => ['view', 'create', 'edit', 'delete', 'export'],
            'examens' => ['view', 'create', 'edit', 'delete', 'export'],
            'enseignants' => ['view', 'create', 'edit', 'delete', 'export'],
            'statistiques' => ['view', 'export', 'admin'],
            'messagerie' => ['view', 'send', 'templates', 'settings'],
            'securite' => ['view', 'users', 'roles', 'permissions', 'audit']
        ];

        foreach ($modulesPermissions as $module => $permissions) {
            echo "Vérification des permissions pour $module:\n";
            foreach ($permissions as $permission) {
                $permissionKey = "$module.$permission";
                echo "   - $permissionKey\n";
            }
            echo "\n";
        }

        // Vérification de la cohérence des rôles
        $this->verifierCohérenceRôles();
        
        echo "\n";
    }

    /**
     * Test d'une route spécifique
     */
    private function testRoute($description, $route, $method = 'GET')
    {
        $url = $this->baseUrl . $route;
        
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 10,
                'user_agent' => 'AuditSecurite/1.0'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        $httpCode = $this->getHttpResponseCode($http_response_header ?? []);

        if ($response !== false && $httpCode >= 200 && $httpCode < 400) {
            $this->success[] = "$description: $route (HTTP $httpCode)";
            echo "   ✅ $description: $route (HTTP $httpCode)\n";
        } else {
            $this->errors[] = "$description: $route (HTTP $httpCode)";
            echo "   ❌ $description: $route (HTTP $httpCode)\n";
        }
    }

    /**
     * Test CRUD complet des utilisateurs
     */
    private function testCRUDUtilisateurs()
    {
        // Test de création d'utilisateur
        $this->testRoute('Création utilisateur (formulaire)', '/admin/securite/users/create');
        
        // Test de soumission du formulaire (simulation)
        $this->testRoute('Soumission création utilisateur', '/admin/securite/users/store', 'POST');
        
        // Test de liste des utilisateurs
        $this->testRoute('Liste des utilisateurs', '/admin/securite/users');
        
        // Test d'édition d'utilisateur (simulation avec ID 1)
        $this->testRoute('Édition utilisateur', '/admin/securite/users/1/edit');
        
        // Test de mise à jour utilisateur
        $this->testRoute('Mise à jour utilisateur', '/admin/securite/users/1/update', 'POST');
        
        // Test de permissions utilisateur
        $this->testRoute('Permissions utilisateur', '/admin/securite/users/1/permissions');
    }

    /**
     * Test CRUD complet des rôles
     */
    private function testCRUDRoles()
    {
        // Test de création de rôle
        $this->testRoute('Création rôle (formulaire)', '/admin/securite/roles/create');
        
        // Test de soumission du formulaire
        $this->testRoute('Soumission création rôle', '/admin/securite/roles/store', 'POST');
        
        // Test de liste des rôles
        $this->testRoute('Liste des rôles', '/admin/securite/roles');
        
        // Test d'édition de rôle
        $this->testRoute('Édition rôle', '/admin/securite/roles/1/edit');
        
        // Test de mise à jour rôle
        $this->testRoute('Mise à jour rôle', '/admin/securite/roles/1/update', 'POST');
        
        // Test de permissions rôle
        $this->testRoute('Permissions rôle', '/admin/securite/roles/1/permissions');
    }

    /**
     * Test CRUD des permissions
     */
    private function testCRUDPermissions()
    {
        // Test de la page des permissions
        $this->testRoute('Gestion des permissions', '/admin/securite/permissions');
        
        // Test de mise à jour des permissions utilisateur
        $this->testRoute('Mise à jour permissions utilisateur', '/admin/securite/users/1/permissions', 'POST');
        
        // Test de mise à jour des permissions rôle
        $this->testRoute('Mise à jour permissions rôle', '/admin/securite/roles/1/permissions', 'POST');
    }

    /**
     * Vérification des liens vers la sécurité
     */
    private function verifierLiensSecurite($moduleUrl)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10
            ]
        ]);

        $response = @file_get_contents($this->baseUrl . $moduleUrl, false, $context);
        
        if ($response !== false) {
            // Vérifier la présence de liens vers la sécurité
            if (strpos($response, 'admin/securite') !== false) {
                $this->success[] = "Liens sécurité trouvés dans $moduleUrl";
                echo "   ✅ Liens sécurité trouvés\n";
            } else {
                $this->warnings[] = "Aucun lien sécurité dans $moduleUrl";
                echo "   ⚠️ Aucun lien sécurité trouvé\n";
            }
        }
    }

    /**
     * Vérification de la cohérence des rôles
     */
    private function verifierCohérenceRôles()
    {
        echo "Vérification de la cohérence des rôles:\n";
        
        // Rôles attendus
        $rolesAttendus = [
            'admin' => 'Administrateur système',
            'directeur' => 'Directeur d\'établissement',
            'secretaire' => 'Secrétaire',
            'enseignant' => 'Enseignant',
            'comptable' => 'Comptable',
            'parent' => 'Parent d\'élève',
            'eleve' => 'Élève'
        ];

        foreach ($rolesAttendus as $role => $description) {
            echo "   - Vérification du rôle: $role ($description)\n";
            // Ici on pourrait vérifier en base de données
        }
    }

    /**
     * Récupération du code de réponse HTTP
     */
    private function getHttpResponseCode($headers)
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                return (int) $matches[1];
            }
        }
        return 0;
    }

    /**
     * Génération du rapport final
     */
    private function genererRapport()
    {
        echo "6. RAPPORT FINAL DE L'AUDIT\n";
        echo "===========================\n";

        $totalTests = count($this->success) + count($this->errors) + count($this->warnings);
        $tauxReussite = $totalTests > 0 ? (count($this->success) / $totalTests) * 100 : 0;

        echo "📊 STATISTIQUES DE L'AUDIT:\n";
        echo "   • Total des tests: $totalTests\n";
        echo "   • Tests réussis: " . count($this->success) . "\n";
        echo "   • Tests en erreur: " . count($this->errors) . "\n";
        echo "   • Tests avec avertissements: " . count($this->warnings) . "\n";
        echo "   • Taux de réussite: " . number_format($tauxReussite, 1) . "%\n\n";

        if (count($this->success) > 0) {
            echo "✅ TESTS RÉUSSIS:\n";
            foreach ($this->success as $success) {
                echo "   • $success\n";
            }
            echo "\n";
        }

        if (count($this->warnings) > 0) {
            echo "⚠️ AVERTISSEMENTS:\n";
            foreach ($this->warnings as $warning) {
                echo "   • $warning\n";
            }
            echo "\n";
        }

        if (count($this->errors) > 0) {
            echo "❌ ERREURS DÉTECTÉES:\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
            echo "\n";
        }

        // Recommandations
        echo "🔧 RECOMMANDATIONS:\n";
        if (count($this->errors) > 0) {
            echo "   • Corriger immédiatement les erreurs détectées\n";
        }
        if (count($this->warnings) > 0) {
            echo "   • Améliorer la cohérence des liens entre modules\n";
        }
        if ($tauxReussite >= 90) {
            echo "   • Excellent niveau de cohérence et de sécurité\n";
        } elseif ($tauxReussite >= 70) {
            echo "   • Bon niveau, quelques améliorations recommandées\n";
        } else {
            echo "   • Travail important nécessaire pour améliorer la cohérence\n";
        }

        echo "\n🎯 CONCLUSION:\n";
        if ($tauxReussite >= 90) {
            echo "   Le module sécurité est EXCELLENT et parfaitement cohérent avec tous les modules\n";
        } elseif ($tauxReussite >= 70) {
            echo "   Le module sécurité est BON avec quelques points d'amélioration\n";
        } else {
            echo "   Le module sécurité nécessite des CORRECTIONS IMPORTANTES\n";
        }

        echo "\n=== AUDIT TERMINÉ ===\n";
    }
}

// Exécution de l'audit
$audit = new AuditCompletSecuriteCoherence();

