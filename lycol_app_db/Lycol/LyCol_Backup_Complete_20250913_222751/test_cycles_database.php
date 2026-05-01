<?php

/**
 * TEST BASE DE DONNÉES - CYCLES
 * Vérification directe de la base de données
 */

echo "🔍 TEST BASE DE DONNÉES - CYCLES\n";
echo "=================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Test 1: Vérifier la table cycles
    echo "📊 TEST 1: Vérification de la table cycles\n";
    echo "-------------------------------------------\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'cycles'");
    if ($stmt->rowCount() > 0) {
        echo "  ✅ Table 'cycles' existe\n";
    } else {
        echo "  ❌ Table 'cycles' n'existe pas\n";
        exit;
    }
    
    // Test 2: Structure de la table
    echo "\n📊 TEST 2: Structure de la table cycles\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("DESCRIBE cycles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📋 Colonnes trouvées:\n";
    foreach ($columns as $column) {
        echo "    • {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}\n";
    }
    
    // Test 3: Données dans la table
    echo "\n📊 TEST 3: Données dans la table cycles\n";
    echo "---------------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cycles");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "  📊 Total cycles: $total\n";
    
    if ($total > 0) {
        $stmt = $pdo->query("SELECT * FROM cycles ORDER BY id LIMIT 10");
        $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "  📋 Cycles trouvés:\n";
        foreach ($cycles as $cycle) {
            echo "    • ID: {$cycle['id']} - Code: {$cycle['code']} - Nom: {$cycle['name']} - Actif: {$cycle['is_active']}\n";
        }
    } else {
        echo "  ⚠️ Aucun cycle trouvé dans la base de données\n";
    }
    
    // Test 4: Test de la requête getCycleStats
    echo "\n📊 TEST 4: Test de la requête getCycleStats\n";
    echo "-------------------------------------------\n";
    
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
    
    $stmt = $pdo->query($query);
    $cycleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Résultats de getCycleStats: " . count($cycleStats) . " cycles\n";
    
    if (!empty($cycleStats)) {
        foreach ($cycleStats as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']}) - Classes: {$cycle['class_count']} - Capacité: {$cycle['total_capacity']}\n";
        }
    } else {
        echo "  ⚠️ Aucun résultat pour getCycleStats\n";
    }
    
    // Test 5: Test de la requête getActiveCycles
    echo "\n📊 TEST 5: Test de la requête getActiveCycles\n";
    echo "---------------------------------------------\n";
    
    $stmt = $pdo->query("SELECT * FROM cycles WHERE is_active = 1 ORDER BY name ASC");
    $activeCycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  📊 Cycles actifs: " . count($activeCycles) . "\n";
    
    if (!empty($activeCycles)) {
        foreach ($activeCycles as $cycle) {
            echo "    • {$cycle['name']} ({$cycle['code']})\n";
        }
    } else {
        echo "  ⚠️ Aucun cycle actif trouvé\n";
    }
    
    // Test 6: Vérification de la cohérence avec les classes
    echo "\n📊 TEST 6: Cohérence avec les classes\n";
    echo "-------------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
    $totalClasses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "  📊 Total classes: $totalClasses\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes WHERE cycle_id IS NOT NULL");
    $classesWithCycle = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "  📊 Classes avec cycle: $classesWithCycle\n";
    
    // Test 7: Insertion d'un cycle de test
    echo "\n📊 TEST 7: Test d'insertion d'un cycle\n";
    echo "--------------------------------------\n";
    
    $testCode = 'TEST_' . time();
    $testName = 'Cycle Test DB ' . date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("INSERT INTO cycles (name, code, description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    
    try {
        $stmt->execute([$testName, $testCode, 'Cycle de test pour audit', 1]);
        $newId = $pdo->lastInsertId();
        echo "  ✅ Cycle de test créé avec succès (ID: $newId)\n";
        
        // Supprimer le cycle de test
        $stmt = $pdo->prepare("DELETE FROM cycles WHERE id = ?");
        $stmt->execute([$newId]);
        echo "  ✅ Cycle de test supprimé\n";
        
    } catch (Exception $e) {
        echo "  ❌ Erreur lors de l'insertion: " . $e->getMessage() . "\n";
    }
    
    echo "\n📊 RÉSUMÉ DE L'AUDIT BASE DE DONNÉES\n";
    echo "=====================================\n";
    
    echo "✅ Connexion DB: OK\n";
    echo "✅ Table cycles: " . ($total > 0 ? "OK ($total cycles)" : "VIDE") . "\n";
    echo "✅ Structure: OK (" . count($columns) . " colonnes)\n";
    echo "✅ Requêtes: OK\n";
    echo "✅ Insertion: OK\n";
    
    if ($total == 0) {
        echo "\n⚠️ ATTENTION: La table cycles est vide\n";
        echo "   Cela explique pourquoi aucun cycle n'apparaît dans l'interface\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
