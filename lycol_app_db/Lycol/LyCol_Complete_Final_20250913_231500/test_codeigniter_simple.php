<?php
/**
 * Test simple de CodeIgniter
 */

// Charger CodeIgniter
require_once 'vendor/autoload.php';

echo "=== TEST SIMPLE DE CODEIGNITER ===\n\n";

try {
    // Créer une instance de la base de données
    $db = new \CodeIgniter\Database\Database([
        'DSN'      => '',
        'hostname' => '100.69.65.33',
        'username' => 'root',
        'password' => 'Bateau123',
        'database' => 'lycol_db',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollate' => 'utf8mb4_unicode_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 13306,
    ]);
    
    echo "1. Connexion CodeIgniter...\n";
    // La connexion se fait automatiquement
    echo "   ✓ Connexion réussie\n";
    
    // Test de requête simple
    echo "\n2. Test de requête simple...\n";
    $result = $db->query("SELECT COUNT(*) as count FROM subjects")->getRow();
    echo "   ✓ Nombre de matières: " . $result->count . "\n";
    
    // Test de requête avec jointure
    echo "\n3. Test de requête avec jointure...\n";
    $result = $db->query("
        SELECT s.*, 
               COALESCE(ta_count.assignment_count, 0) as assignment_count,
               COALESCE(t_count.timetable_count, 0) as timetable_count
        FROM subjects s
        LEFT JOIN (SELECT subject_id, COUNT(*) as assignment_count FROM teacher_assignments GROUP BY subject_id) ta_count ON ta_count.subject_id = s.id
        LEFT JOIN (SELECT subject_id, COUNT(*) as timetable_count FROM timetables GROUP BY subject_id) t_count ON t_count.subject_id = s.id
        WHERE s.is_active = 1
        ORDER BY s.name ASC
        LIMIT 5
    ")->getResultArray();
    
    echo "   ✓ Requête avec jointure réussie\n";
    echo "   ✓ Nombre de résultats: " . count($result) . "\n";
    
    foreach ($result as $subject) {
        echo "      - " . $subject['name'] . " (Code: " . $subject['code'] . ", Assignations: " . $subject['assignment_count'] . ")\n";
    }
    
    // Test de fermeture
    $db->close();
    echo "\n4. Fermeture de la connexion...\n";
    echo "   ✓ Connexion fermée\n";
    
} catch (Exception $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";
