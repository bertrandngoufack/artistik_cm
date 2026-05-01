<?php

/**
 * AUDIT COMPLET MODULE ÉTUDES - EXPERT
 * Vérification complète du module Études avec tests CRUD
 */

class TestEtudesComplet {
    private $baseUrl = 'http://localhost:8080';
    private $results = [];
    private $errors = [];
    private $successCount = 0;
    private $totalTests = 0;

    public function __construct() {
        echo "🔍 AUDIT COMPLET MODULE ÉTUDES - EXPERT\n";
        echo "==========================================\n\n";
        
        // Vérifier que le serveur est accessible
        if (!$this->checkServer()) {
            echo "❌ ERREUR: Le serveur n'est pas accessible sur {$this->baseUrl}\n";
            exit(1);
        }
        
        echo "✅ Serveur accessible sur {$this->baseUrl}\n\n";
    }

    private function checkServer() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode >= 200 && $httpCode < 400;
    }

    public function runCompleteTest() {
        echo "📋 DÉBUT DES TESTS COMPLETS\n";
        echo "===========================\n\n";

        // 1. Test des routes principales
        $this->testMainRoutes();
        
        // 2. Test des vues et pages
        $this->testViews();
        
        // 3. Test des fonctionnalités CRUD
        $this->testCRUDOperations();
        
        // 4. Test des exports et rapports
        $this->testExports();
        
        // 5. Test de cohérence avec autres modules
        $this->testModuleCoherence();
        
        // 6. Test des liens et navigation
        $this->testNavigation();
        
        // 7. Test des formulaires
        $this->testForms();
        
        // Affichage des résultats
        $this->displayResults();
    }

    private function testMainRoutes() {
        echo "🔗 TEST DES ROUTES PRINCIPALES\n";
        echo "-----------------------------\n";
        
        $routes = [
            '/admin/etudes' => 'Dashboard Études',
            '/admin/etudes/cycles' => 'Gestion des Cycles',
            '/admin/etudes/classes' => 'Gestion des Classes',
            '/admin/etudes/subjects' => 'Gestion des Matières',
            '/admin/etudes/timetable' => 'Emplois du Temps',
            '/admin/etudes/assignments' => 'Assignations',
            '/admin/etudes/reports' => 'Rapports'
        ];

        foreach ($routes as $route => $description) {
            $this->testRoute($description, $route);
        }
        
        echo "\n";
    }

    private function testViews() {
        echo "👁️ TEST DES VUES ET PAGES\n";
        echo "-------------------------\n";
        
        $views = [
            '/admin/etudes/cycles/create' => 'Création Cycle',
            '/admin/etudes/classes/create' => 'Création Classe',
            '/admin/etudes/subjects/create' => 'Création Matière',
            '/admin/etudes/timetable/create' => 'Création EDT',
            '/admin/etudes/assignments/create' => 'Création Assignation',
            '/admin/etudes/reports/generate' => 'Génération Rapport'
        ];

        foreach ($views as $view => $description) {
            $this->testRoute($description, $view);
        }
        
        echo "\n";
    }

    private function testCRUDOperations() {
        echo "🔄 TEST DES OPÉRATIONS CRUD\n";
        echo "---------------------------\n";
        
        // Test création cycle
        $this->testCreateCycle();
        
        // Test création classe
        $this->testCreateClass();
        
        // Test création matière
        $this->testCreateSubject();
        
        // Test création assignation
        $this->testCreateAssignment();
        
        echo "\n";
    }

    private function testCreateCycle() {
        echo "  🔄 Test création cycle... ";
        
        $postData = [
            'name' => 'Test Cycle ' . date('Y-m-d H:i:s'),
            'code' => 'TC' . rand(100, 999),
            'description' => 'Cycle de test pour audit',
            'is_active' => 1
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/admin/etudes/cycles/store');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "✅ SUCCÈS\n";
            $this->successCount++;
        } else {
            echo "❌ ÉCHEC (HTTP $httpCode)\n";
            $this->errors[] = "Création cycle: HTTP $httpCode";
        }
        $this->totalTests++;
    }

    private function testCreateClass() {
        echo "  🔄 Test création classe... ";
        
        $postData = [
            'name' => 'Test Classe ' . date('Y-m-d H:i:s'),
            'code' => 'CL' . rand(100, 999),
            'cycle_id' => 1,
            'level' => 'Test',
            'capacity' => 30,
            'is_active' => 1
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/admin/etudes/classes/store');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "✅ SUCCÈS\n";
            $this->successCount++;
        } else {
            echo "❌ ÉCHEC (HTTP $httpCode)\n";
            $this->errors[] = "Création classe: HTTP $httpCode";
        }
        $this->totalTests++;
    }

    private function testCreateSubject() {
        echo "  🔄 Test création matière... ";
        
        $postData = [
            'name' => 'Test Matière ' . date('Y-m-d H:i:s'),
            'code' => 'MT' . rand(100, 999),
            'description' => 'Matière de test pour audit',
            'coefficient' => 2,
            'is_active' => 1
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/admin/etudes/subjects/store');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "✅ SUCCÈS\n";
            $this->successCount++;
        } else {
            echo "❌ ÉCHEC (HTTP $httpCode)\n";
            $this->errors[] = "Création matière: HTTP $httpCode";
        }
        $this->totalTests++;
    }

    private function testCreateAssignment() {
        echo "  🔄 Test création assignation... ";
        
        $postData = [
            'teacher_id' => 1,
            'class_id' => 1,
            'subject_id' => 1,
            'academic_year' => '2024-2025',
            'is_active' => 1
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/admin/etudes/assignments/store');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "✅ SUCCÈS\n";
            $this->successCount++;
        } else {
            echo "❌ ÉCHEC (HTTP $httpCode)\n";
            $this->errors[] = "Création assignation: HTTP $httpCode";
        }
        $this->totalTests++;
    }

    private function testExports() {
        echo "📊 TEST DES EXPORTS ET RAPPORTS\n";
        echo "-------------------------------\n";
        
        $exports = [
            '/admin/etudes/reports/export/csv' => 'Export CSV',
            '/admin/etudes/reports/export/pdf' => 'Export PDF'
        ];

        foreach ($exports as $export => $description) {
            $this->testRoute($description, $export);
        }
        
        echo "\n";
    }

    private function testModuleCoherence() {
        echo "🔗 TEST DE COHÉRENCE AVEC AUTRES MODULES\n";
        echo "----------------------------------------\n";
        
        $coherenceTests = [
            '/admin/etudes' => 'Lien vers Études depuis Admin',
            '/admin/enseignants' => 'Lien vers Enseignants (pour assignations)',
            '/admin/scolarite' => 'Lien vers Scolarité (pour classes)',
            '/admin/examens' => 'Lien vers Examens (pour matières)'
        ];

        foreach ($coherenceTests as $test => $description) {
            $this->testRoute($description, $test);
        }
        
        echo "\n";
    }

    private function testNavigation() {
        echo "🧭 TEST DE LA NAVIGATION\n";
        echo "------------------------\n";
        
        $navigationTests = [
            '/admin/etudes/cycles' => 'Navigation Cycles',
            '/admin/etudes/classes' => 'Navigation Classes',
            '/admin/etudes/subjects' => 'Navigation Matières',
            '/admin/etudes/timetable' => 'Navigation EDT',
            '/admin/etudes/assignments' => 'Navigation Assignations'
        ];

        foreach ($navigationTests as $test => $description) {
            $this->testRoute($description, $test);
        }
        
        echo "\n";
    }

    private function testForms() {
        echo "📝 TEST DES FORMULAIRES\n";
        echo "----------------------\n";
        
        $forms = [
            '/admin/etudes/cycles/create' => 'Formulaire Cycle',
            '/admin/etudes/classes/create' => 'Formulaire Classe',
            '/admin/etudes/subjects/create' => 'Formulaire Matière',
            '/admin/etudes/assignments/create' => 'Formulaire Assignation'
        ];

        foreach ($forms as $form => $description) {
            $this->testRoute($description, $form);
        }
        
        echo "\n";
    }

    private function testRoute($description, $route) {
        echo "  🔍 Test $description... ";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $route);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "✅ SUCCÈS\n";
            $this->successCount++;
            $this->results[] = "✅ $description: OK";
        } else {
            echo "❌ ÉCHEC (HTTP $httpCode)\n";
            $this->errors[] = "$description: HTTP $httpCode";
            $this->results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
        }
        $this->totalTests++;
    }

    private function displayResults() {
        echo "📊 RÉSULTATS FINAUX\n";
        echo "===================\n\n";
        
        $successRate = ($this->totalTests > 0) ? round(($this->successCount / $this->totalTests) * 100, 1) : 0;
        
        echo "📈 STATISTIQUES:\n";
        echo "   • Tests réussis: {$this->successCount}/{$this->totalTests}\n";
        echo "   • Taux de succès: {$successRate}%\n";
        echo "   • Erreurs: " . count($this->errors) . "\n\n";
        
        if (!empty($this->errors)) {
            echo "❌ ERREURS DÉTECTÉES:\n";
            echo "---------------------\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
            echo "\n";
        }
        
        echo "✅ TESTS RÉUSSIS:\n";
        echo "-----------------\n";
        foreach ($this->results as $result) {
            if (strpos($result, '✅') === 0) {
                echo "   $result\n";
            }
        }
        echo "\n";
        
        if ($successRate >= 90) {
            echo "🎉 MODULE ÉTUDES: EXCELLENT ÉTAT\n";
            echo "   Toutes les fonctionnalités principales sont opérationnelles.\n";
        } elseif ($successRate >= 75) {
            echo "✅ MODULE ÉTUDES: BON ÉTAT\n";
            echo "   La plupart des fonctionnalités fonctionnent correctement.\n";
        } elseif ($successRate >= 50) {
            echo "⚠️ MODULE ÉTUDES: ÉTAT MOYEN\n";
            echo "   Certaines fonctionnalités nécessitent des corrections.\n";
        } else {
            echo "❌ MODULE ÉTUDES: ÉTAT CRITIQUE\n";
            echo "   De nombreuses fonctionnalités nécessitent des corrections urgentes.\n";
        }
        
        echo "\n🌐 Interface accessible sur: {$this->baseUrl}/admin/etudes\n";
    }
}

// Exécution du test
$test = new TestEtudesComplet();
$test->runCompleteTest();


