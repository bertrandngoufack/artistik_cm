<?php

/**
 * DEBUG - MODÈLE CYCLE
 * Test direct du modèle CycleModel
 */

// Charger CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

echo "🔍 DEBUG - MODÈLE CYCLE\n";
echo "========================\n\n";

try {
    // Initialiser CodeIgniter
    $app = new \CodeIgniter\CodeIgniter(new \Config\App());
    $app->initialize();
    
    echo "✅ CodeIgniter initialisé\n\n";
    
    // Créer une instance du modèle
    $cycleModel = new \App\Models\CycleModel();
    echo "✅ Modèle CycleModel créé\n\n";
    
    // Test 1: getActiveCycles()
    echo "📊 TEST 1: getActiveCycles()\n";
    echo "----------------------------\n";
    
    $activeCycles = $cycleModel->getActiveCycles();
    echo "  📊 Résultats: " . count($activeCycles) . " cycles\n";
    
    if (!empty($activeCycles)) {
        foreach (array_slice($activeCycles, 0, 5) as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']}) - ID: {$cycle['id']}\n";
        }
    } else {
        echo "  ⚠️ Aucun cycle actif trouvé\n";
    }
    
    // Test 2: getCycleStats()
    echo "\n📊 TEST 2: getCycleStats()\n";
    echo "---------------------------\n";
    
    $cycleStats = $cycleModel->getCycleStats();
    echo "  📊 Résultats: " . count($cycleStats) . " cycles\n";
    
    if (!empty($cycleStats)) {
        foreach (array_slice($cycleStats, 0, 5) as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']}) - Classes: {$cycle['class_count']} - Capacité: {$cycle['total_capacity']}\n";
        }
    } else {
        echo "  ⚠️ Aucun cycle trouvé dans les statistiques\n";
    }
    
    // Test 3: find() - Test d'un cycle spécifique
    echo "\n📊 TEST 3: find() - Cycle ID 1\n";
    echo "--------------------------------\n";
    
    $cycle = $cycleModel->find(1);
    if ($cycle) {
        echo "  ✅ Cycle trouvé: {$cycle['name']} ({$cycle['code']})\n";
    } else {
        echo "  ❌ Cycle ID 1 non trouvé\n";
    }
    
    // Test 4: Test de la requête SQL directe
    echo "\n📊 TEST 4: Requête SQL directe\n";
    echo "-------------------------------\n";
    
    $db = \Config\Database::connect();
    $query = "
        SELECT cycles.*, 
               COUNT(classes.id) as class_count, 
               SUM(classes.capacity) as total_capacity
        FROM cycles 
        LEFT JOIN classes ON classes.cycle_id = cycles.id 
        WHERE cycles.is_active = 1 
        GROUP BY cycles.id 
        ORDER BY cycles.name ASC
    ";
    
    $result = $db->query($query);
    $cycles = $result->getResultArray();
    
    echo "  📊 Résultats SQL directe: " . count($cycles) . " cycles\n";
    
    if (!empty($cycles)) {
        foreach (array_slice($cycles, 0, 5) as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']}) - Classes: {$cycle['class_count']} - Capacité: {$cycle['total_capacity']}\n";
        }
    } else {
        echo "  ⚠️ Aucun résultat SQL directe\n";
    }
    
    // Test 5: Test de la méthode findAll()
    echo "\n📊 TEST 5: findAll()\n";
    echo "--------------------\n";
    
    $allCycles = $cycleModel->findAll();
    echo "  📊 Tous les cycles: " . count($allCycles) . " cycles\n";
    
    if (!empty($allCycles)) {
        foreach (array_slice($allCycles, 0, 5) as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']}) - Actif: {$cycle['is_active']}\n";
        }
    } else {
        echo "  ⚠️ Aucun cycle trouvé\n";
    }
    
    echo "\n📊 RÉSUMÉ DU DEBUG\n";
    echo "==================\n";
    
    echo "✅ CodeIgniter: OK\n";
    echo "✅ Modèle: OK\n";
    echo "✅ getActiveCycles: " . count($activeCycles) . " cycles\n";
    echo "✅ getCycleStats: " . count($cycleStats) . " cycles\n";
    echo "✅ SQL directe: " . count($cycles) . " cycles\n";
    echo "✅ findAll: " . count($allCycles) . " cycles\n";
    
    if (count($cycleStats) == 0) {
        echo "\n⚠️ PROBLÈME IDENTIFIÉ: getCycleStats() retourne 0 cycles\n";
        echo "   Cela explique pourquoi la page cycles est vide\n";
    } else {
        echo "\n✅ getCycleStats() fonctionne correctement\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n📋 Debug terminé le: " . date('Y-m-d H:i:s') . "\n";


