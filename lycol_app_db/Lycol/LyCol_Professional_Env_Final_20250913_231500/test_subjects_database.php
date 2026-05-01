<?php
/**
 * Test Base de Données - Module Subjects
 * Diagnostic des problèmes de base de données
 */

echo "🔍 TEST BASE DE DONNÉES - MODULE SUBJECTS\n";
echo "========================================\n\n";

try {
    // Connexion à la base de données
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db;charset=utf8mb4',
        'root',
        'Bateau123',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Test 1: Vérifier la structure de la table subjects
    echo "📊 TEST 1: Structure de la table subjects\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("DESCRIBE subjects");
    $columns = $stmt->fetchAll();
    
    echo "📋 Colonnes trouvées:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})";
        if ($column['Null'] === 'NO') echo " NOT NULL";
        if ($column['Key'] === 'PRI') echo " PRIMARY KEY";
        if ($column['Extra']) echo " {$column['Extra']}";
        echo "\n";
    }
    
    // Vérifier si hours_per_week existe
    $hasHoursPerWeek = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'hours_per_week') {
            $hasHoursPerWeek = true;
            break;
        }
    }
    
    if ($hasHoursPerWeek) {
        echo "  ✅ Champ hours_per_week présent\n";
    } else {
        echo "  ❌ Champ hours_per_week manquant\n";
    }
    
    echo "\n";
    
    // Test 2: Vérifier les données existantes
    echo "📊 TEST 2: Données existantes\n";
    echo "-----------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subjects");
    $result = $stmt->fetch();
    echo "📋 Total matières: {$result['total']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM subjects WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "📋 Matières actives: {$result['active']}\n";
    
    // Afficher quelques matières
    $stmt = $pdo->query("SELECT id, name, code, coefficient FROM subjects LIMIT 5");
    $subjects = $stmt->fetchAll();
    
    echo "📋 Exemples de matières:\n";
    foreach ($subjects as $subject) {
        echo "  - ID {$subject['id']}: {$subject['name']} ({$subject['code']}) - Coef: {$subject['coefficient']}\n";
    }
    
    echo "\n";
    
    // Test 3: Tester une requête de sélection
    echo "📊 TEST 3: Test de sélection\n";
    echo "----------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([25]);
    $subject = $stmt->fetch();
    
    if ($subject) {
        echo "✅ Matière ID 25 trouvée:\n";
        foreach ($subject as $key => $value) {
            echo "  - $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
        }
    } else {
        echo "❌ Matière ID 25 non trouvée\n";
    }
    
    echo "\n";
    
    // Test 4: Vérifier les contraintes
    echo "📊 TEST 4: Contraintes de la table\n";
    echo "---------------------------------\n";
    
    $stmt = $pdo->query("SHOW CREATE TABLE subjects");
    $result = $stmt->fetch();
    $createTable = $result['Create Table'];
    
    if (strpos($createTable, 'UNIQUE KEY') !== false) {
        echo "✅ Contraintes d'unicité présentes\n";
    } else {
        echo "⚠️ Contraintes d'unicité non détectées\n";
    }
    
    if (strpos($createTable, 'FOREIGN KEY') !== false) {
        echo "✅ Clés étrangères présentes\n";
    } else {
        echo "ℹ️ Aucune clé étrangère détectée\n";
    }
    
    echo "\n";
    
    // Test 5: Tester l'insertion
    echo "📊 TEST 5: Test d'insertion\n";
    echo "---------------------------\n";
    
    $testCode = 'TEST' . rand(1000, 9999);
    $testName = 'Test Matière DB ' . date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO subjects (name, code, description, coefficient, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $testName,
            $testCode,
            'Matière de test pour diagnostic DB',
            1.0,
            1
        ]);
        
        if ($result) {
            $insertedId = $pdo->lastInsertId();
            echo "✅ Insertion réussie (ID: $insertedId)\n";
            
            // Supprimer la matière de test
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$insertedId]);
            echo "✅ Matière de test supprimée\n";
        } else {
            echo "❌ Échec de l'insertion\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'insertion: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 6: Vérifier les index
    echo "📊 TEST 6: Index de la table\n";
    echo "----------------------------\n";
    
    $stmt = $pdo->query("SHOW INDEX FROM subjects");
    $indexes = $stmt->fetchAll();
    
    echo "📋 Index trouvés:\n";
    foreach ($indexes as $index) {
        echo "  - {$index['Key_name']} sur {$index['Column_name']}\n";
    }
    
    echo "\n";
    
    // Résumé
    echo "📊 RÉSUMÉ DES TESTS\n";
    echo "===================\n";
    
    $totalTests = 6;
    $passedTests = 0;
    
    if ($hasHoursPerWeek) $passedTests++;
    if ($subject) $passedTests++;
    if (strpos($createTable, 'UNIQUE KEY') !== false) $passedTests++;
    if (isset($insertedId)) $passedTests++;
    if (count($indexes) > 0) $passedTests++;
    if (count($columns) > 0) $passedTests++;
    
    echo "✅ Tests réussis: $passedTests/$totalTests\n";
    
    if ($passedTests === $totalTests) {
        echo "\n🏆 BASE DE DONNÉES: EXCELLENT ÉTAT\n";
    } elseif ($passedTests >= 4) {
        echo "\n🏆 BASE DE DONNÉES: BON ÉTAT\n";
    } else {
        echo "\n🏆 BASE DE DONNÉES: ATTENTION REQUISE\n";
    }
    
    if (!$hasHoursPerWeek) {
        echo "\n🔧 RECOMMANDATION: Ajouter le champ hours_per_week à la table subjects\n";
        echo "   ALTER TABLE subjects ADD COLUMN hours_per_week DECIMAL(4,2) DEFAULT NULL;\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}

echo "\n📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>


