<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Suite de Tests Automatisés - KISSAI SCHOOL
 * Expert Senior PHP/CodeIgniter
 */
class TestSuite extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $seed = 'TestSeeder';

    /**
     * Test de connexion à la base de données
     */
    public function testDatabaseConnection()
    {
        $db = \Config\Database::connect();
        $this->assertNotNull($db);
        
        // Test de requête simple
        $result = $db->query('SELECT 1 as test');
        $this->assertEquals(1, $result->getRow()->test);
    }

    /**
     * Test des tables principales
     */
    public function testMainTables()
    {
        $db = \Config\Database::connect();
        
        $tables = ['students', 'teachers', 'classes', 'payments', 'books', 'grades'];
        
        foreach ($tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            $this->assertNotEmpty($result->getResult(), "Table $table doit exister");
        }
    }

    /**
     * Test des nouvelles tables créées
     */
    public function testNewTables()
    {
        $db = \Config\Database::connect();
        
        // Test table loans
        $result = $db->query("SHOW TABLES LIKE 'loans'");
        $this->assertNotEmpty($result->getResult(), "Table loans doit exister");
        
        // Test table templates
        $result = $db->query("SHOW TABLES LIKE 'templates'");
        $this->assertNotEmpty($result->getResult(), "Table templates doit exister");
    }

    /**
     * Test des index de performance
     */
    public function testPerformanceIndexes()
    {
        $db = \Config\Database::connect();
        
        // Vérifier les index sur students
        $result = $db->query("SHOW INDEX FROM students WHERE Key_name LIKE 'idx_%'");
        $this->assertNotEmpty($result->getResult(), "Index de performance sur students");
        
        // Vérifier les index sur payments
        $result = $db->query("SHOW INDEX FROM payments WHERE Key_name LIKE 'idx_%'");
        $this->assertNotEmpty($result->getResult(), "Index de performance sur payments");
    }

    /**
     * Test de cohérence des données
     */
    public function testDataConsistency()
    {
        $db = \Config\Database::connect();
        
        // Test des enregistrements orphelins dans classes
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM classes c 
            LEFT JOIN teachers t ON c.teacher_id = t.id 
            WHERE c.teacher_id IS NOT NULL AND t.id IS NULL
        ");
        $this->assertEquals(0, $result->getRow()->count, "Aucun enregistrement orphelin dans classes");
        
        // Test des clés étrangères students -> classes
        $result = $db->query("
            SELECT COUNT(*) as count 
            FROM students s 
            LEFT JOIN classes c ON s.current_class_id = c.id 
            WHERE s.current_class_id IS NOT NULL AND c.id IS NULL
        ");
        $this->assertEquals(0, $result->getRow()->count, "Aucun étudiant avec classe invalide");
    }

    /**
     * Test du service de cache
     */
    public function testCacheService()
    {
        $cacheService = new \App\Services\CacheService();
        
        // Test de génération de clé
        $key = $cacheService->generateKey('test', ['param' => 'value']);
        $this->assertNotEmpty($key);
        
        // Test de cache remember
        $data = $cacheService->remember('test_key', function() {
            return ['test' => 'data'];
        }, 60);
        
        $this->assertEquals(['test' => 'data'], $data);
        
        // Test de suppression de cache
        $result = $cacheService->forget('test_key');
        $this->assertTrue($result);
    }

    /**
     * Test des modèles CRUD
     */
    public function testModelsCRUD()
    {
        // Test StudentModel
        $studentModel = new \App\Models\StudentModel();
        
        // Test de création
        $data = [
            'matricule' => 'TEST001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'M',
            'birth_date' => '2010-01-01',
            'nationality' => 'Camerounais',
            'parent_name' => 'Parent Test',
            'parent_phone' => '237123456789',
            'admission_date' => '2024-09-01',
            'current_class_id' => 1,
            'academic_year' => '2024-2025',
            'status' => 'ACTIVE'
        ];
        
        $id = $studentModel->insert($data);
        $this->assertIsInt($id);
        
        // Test de lecture
        $student = $studentModel->find($id);
        $this->assertEquals('TEST001', $student['matricule']);
        
        // Test de mise à jour
        $updateData = ['first_name' => 'Updated'];
        $result = $studentModel->update($id, $updateData);
        $this->assertTrue($result);
        
        // Test de suppression
        $result = $studentModel->delete($id);
        $this->assertTrue($result);
    }

    /**
     * Test des contrôleurs
     */
    public function testControllers()
    {
        // Test du contrôleur Scolarite
        $result = $this->get('/admin/scolarite');
        $this->assertTrue($result->isOK() || $result->getStatusCode() === 302); // OK ou redirect
        
        // Test du contrôleur Economat
        $result = $this->get('/admin/economat');
        $this->assertTrue($result->isOK() || $result->getStatusCode() === 302);
        
        // Test du contrôleur Bibliotheque
        $result = $this->get('/admin/bibliotheque');
        $this->assertTrue($result->isOK() || $result->getStatusCode() === 302);
    }

    /**
     * Test de validation des données
     */
    public function testDataValidation()
    {
        $studentModel = new \App\Models\StudentModel();
        
        // Test de validation échouée
        $invalidData = [
            'matricule' => '', // Requis
            'first_name' => 'A', // Trop court
            'gender' => 'X' // Valeur invalide
        ];
        
        $this->assertFalse($studentModel->validate($invalidData));
        
        // Test de validation réussie
        $validData = [
            'matricule' => 'TEST002',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'M',
            'birth_date' => '2010-01-01',
            'nationality' => 'Camerounais',
            'parent_name' => 'Parent Test',
            'parent_phone' => '237123456789',
            'admission_date' => '2024-09-01',
            'current_class_id' => 1,
            'academic_year' => '2024-2025',
            'status' => 'ACTIVE'
        ];
        
        $this->assertTrue($studentModel->validate($validData));
    }

    /**
     * Test de sécurité
     */
    public function testSecurity()
    {
        // Test de protection CSRF
        $result = $this->post('/admin/scolarite/students/store', [
            'first_name' => 'Test',
            'last_name' => 'Student'
        ]);
        
        // Doit échouer sans token CSRF
        $this->assertTrue($result->getStatusCode() === 400 || $result->getStatusCode() === 302);
        
        // Test de validation des entrées
        $result = $this->post('/admin/scolarite/students/store', [
            'first_name' => '<script>alert("xss")</script>',
            'last_name' => 'Student'
        ]);
        
        // Doit échouer avec entrée malveillante
        $this->assertTrue($result->getStatusCode() === 400 || $result->getStatusCode() === 302);
    }

    /**
     * Test de performance
     */
    public function testPerformance()
    {
        $db = \Config\Database::connect();
        
        // Test de requête avec index
        $start = microtime(true);
        $result = $db->query("SELECT * FROM students WHERE academic_year = '2024-2025'");
        $end = microtime(true);
        
        $executionTime = $end - $start;
        $this->assertLessThan(1.0, $executionTime, "Requête doit s'exécuter en moins d'1 seconde");
        
        // Test de requête complexe
        $start = microtime(true);
        $result = $db->query("
            SELECT s.first_name, s.last_name, c.name as class_name, 
                   COUNT(p.id) as payment_count, SUM(p.amount) as total_paid
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN payments p ON s.id = p.student_id
            WHERE s.academic_year = '2024-2025'
            GROUP BY s.id, s.first_name, s.last_name, c.name
        ");
        $end = microtime(true);
        
        $executionTime = $end - $start;
        $this->assertLessThan(2.0, $executionTime, "Requête complexe doit s'exécuter en moins de 2 secondes");
    }

    /**
     * Test de l'API de licence
     */
    public function testLicenseAPI()
    {
        $result = $this->get('/admin/configuration/check-license');
        $this->assertTrue($result->isOK());
        
        $response = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('valid', $response);
        $this->assertArrayHasKey('license', $response);
    }

    /**
     * Test des rapports
     */
    public function testReports()
    {
        $cacheService = new \App\Services\CacheService();
        
        // Test des statistiques d'étudiants
        $stats = $cacheService->getStudentStats('2024-2025');
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('male', $stats);
        $this->assertArrayHasKey('female', $stats);
        
        // Test des statistiques de paiements
        $paymentStats = $cacheService->getPaymentStats('2024-2025');
        $this->assertArrayHasKey('total_amount', $paymentStats);
        $this->assertArrayHasKey('total_payments', $paymentStats);
        
        // Test des statistiques de bibliothèque
        $libraryStats = $cacheService->getLibraryStats();
        $this->assertArrayHasKey('total_books', $libraryStats);
        $this->assertArrayHasKey('active_loans', $libraryStats);
    }

    /**
     * Test de nettoyage
     */
    public function testCleanup()
    {
        $db = \Config\Database::connect();
        
        // Nettoyer les données de test
        $db->query("DELETE FROM students WHERE matricule LIKE 'TEST%'");
        
        // Vérifier le nettoyage
        $result = $db->query("SELECT COUNT(*) as count FROM students WHERE matricule LIKE 'TEST%'");
        $this->assertEquals(0, $result->getRow()->count);
    }
}





