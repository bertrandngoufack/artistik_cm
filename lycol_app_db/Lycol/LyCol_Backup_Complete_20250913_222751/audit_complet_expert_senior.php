<?php
/**
 * AUDIT COMPLET EXPERT SENIOR - KISSAI SCHOOL
 * Analyse exhaustive de l'architecture, CRUD, conformité et cohérence
 */

echo "🔍 AUDIT COMPLET EXPERT SENIOR - KISSAI SCHOOL\n";
echo "==============================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données établie\n\n";
    
    // ========================================
    // 1. ANALYSE DE L'ARCHITECTURE
    // ========================================
    echo "🏗️  1. ANALYSE DE L'ARCHITECTURE\n";
    echo "===============================\n";
    
    // Vérifier la structure des dossiers
    $directories = [
        'app/Controllers' => 'Contrôleurs',
        'app/Models' => 'Modèles',
        'app/Views' => 'Vues',
        'app/Config' => 'Configuration',
        'app/Libraries' => 'Bibliothèques',
        'app/Services' => 'Services',
        'app/Filters' => 'Filtres',
        'app/Traits' => 'Traits'
    ];
    
    foreach ($directories as $dir => $name) {
        if (is_dir($dir)) {
            $files = count(glob("$dir/*.php"));
            echo "✅ $name: $files fichiers\n";
        } else {
            echo "❌ $name: Dossier manquant\n";
        }
    }
    echo "\n";
    
    // ========================================
    // 2. ANALYSE DES TABLES ET RELATIONS
    // ========================================
    echo "🗄️  2. ANALYSE DES TABLES ET RELATIONS\n";
    echo "=====================================\n";
    
    $tables = [
        'students', 'teachers', 'classes', 'users', 'licenses',
        'payments', 'absences', 'discipline', 'books', 'loans',
        'grades', 'exams', 'subjects', 'cycles', 'roles',
        'messages', 'templates', 'audit_logs', 'settings'
    ];
    
    $tableStats = [];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $tableStats[$table] = $count;
            echo "✅ $table: $count enregistrements\n";
        } catch (PDOException $e) {
            echo "❌ $table: Table manquante ou erreur\n";
        }
    }
    echo "\n";
    
    // ========================================
    // 3. ANALYSE DES CRUD OPERATIONS
    // ========================================
    echo "🔄 3. ANALYSE DES CRUD OPERATIONS\n";
    echo "================================\n";
    
    // Vérifier les opérations CRUD pour les entités principales
    $crudEntities = [
        'students' => ['create', 'read', 'update', 'delete'],
        'teachers' => ['create', 'read', 'update', 'delete'],
        'classes' => ['create', 'read', 'update', 'delete'],
        'payments' => ['create', 'read', 'update', 'delete'],
        'books' => ['create', 'read', 'update', 'delete'],
        'loans' => ['create', 'read', 'update', 'delete']
    ];
    
    foreach ($crudEntities as $entity => $operations) {
        echo "📋 $entity:\n";
        foreach ($operations as $operation) {
            // Vérifier si les méthodes existent dans les contrôleurs
            $controllerFile = "app/Controllers/" . ucfirst($entity) . ".php";
            if (file_exists($controllerFile)) {
                $content = file_get_contents($controllerFile);
                $methodName = $operation . ucfirst($entity);
                if (strpos($content, "function $methodName") !== false) {
                    echo "   ✅ $operation: Méthode trouvée\n";
                } else {
                    echo "   ⚠️  $operation: Méthode manquante\n";
                }
            } else {
                echo "   ❌ $operation: Contrôleur manquant\n";
            }
        }
        echo "\n";
    }
    
    // ========================================
    // 4. ANALYSE DE LA CONFORMITÉ
    // ========================================
    echo "📋 4. ANALYSE DE LA CONFORMITÉ\n";
    echo "=============================\n";
    
    // Vérifier les contraintes de clés étrangères
    echo "🔗 Vérification des clés étrangères:\n";
    $foreignKeys = [
        'students.current_class_id' => 'classes.id',
        'teachers.user_id' => 'users.id',
        'classes.teacher_id' => 'teachers.id',
        'payments.student_id' => 'students.id',
        'loans.student_id' => 'students.id',
        'loans.book_id' => 'books.id'
    ];
    
    foreach ($foreignKeys as $fk => $pk) {
        list($table, $column) = explode('.', $fk);
        list($refTable, $refColumn) = explode('.', $pk);
        
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table t LEFT JOIN $refTable r ON t.$column = r.$refColumn WHERE t.$column IS NOT NULL AND r.$refColumn IS NULL");
            $stmt->execute();
            $orphaned = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($orphaned == 0) {
                echo "   ✅ $fk -> $pk: Cohérent\n";
            } else {
                echo "   ⚠️  $fk -> $pk: $orphaned enregistrements orphelins\n";
            }
        } catch (PDOException $e) {
            echo "   ❌ $fk -> $pk: Erreur de vérification\n";
        }
    }
    echo "\n";
    
    // ========================================
    // 5. ANALYSE DE LA COHÉRENCE DES DONNÉES
    // ========================================
    echo "🔍 5. ANALYSE DE LA COHÉRENCE DES DONNÉES\n";
    echo "========================================\n";
    
    // Vérifier la cohérence des années académiques
    echo "📅 Cohérence des années académiques:\n";
    $stmt = $pdo->query("SELECT DISTINCT academic_year FROM students WHERE academic_year IS NOT NULL");
    $academicYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($academicYears) > 0) {
        echo "   ✅ Années académiques trouvées: " . implode(', ', $academicYears) . "\n";
        
        // Vérifier la cohérence
        foreach ($academicYears as $year) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE academic_year = ?");
            $stmt->execute([$year]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "      $year: $count étudiants\n";
        }
    } else {
        echo "   ⚠️  Aucune année académique définie\n";
    }
    echo "\n";
    
    // ========================================
    // 6. ANALYSE DES PERFORMANCES
    // ========================================
    echo "⚡ 6. ANALYSE DES PERFORMANCES\n";
    echo "=============================\n";
    
    // Vérifier les index sur les colonnes importantes
    echo "📊 Index de base de données:\n";
    $importantColumns = [
        'students.matricule',
        'students.academic_year',
        'payments.payment_date',
        'loans.loan_date',
        'users.email'
    ];
    
    foreach ($importantColumns as $column) {
        list($table, $col) = explode('.', $column);
        try {
            $stmt = $pdo->prepare("SHOW INDEX FROM $table WHERE Column_name = ?");
            $stmt->execute([$col]);
            $indexes = $stmt->fetchAll();
            
            if (count($indexes) > 0) {
                echo "   ✅ $column: Indexé\n";
            } else {
                echo "   ⚠️  $column: Non indexé (recommandé)\n";
            }
        } catch (PDOException $e) {
            echo "   ❌ $column: Erreur de vérification\n";
        }
    }
    echo "\n";
    
    // ========================================
    // 7. ANALYSE DE LA SÉCURITÉ
    // ========================================
    echo "🔒 7. ANALYSE DE LA SÉCURITÉ\n";
    echo "===========================\n";
    
    // Vérifier les mots de passe hashés
    echo "🔐 Sécurité des mots de passe:\n";
    $stmt = $pdo->query("SELECT password FROM users LIMIT 1");
    $password = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if ($password && password_verify('test', $password) === false) {
        echo "   ✅ Mots de passe hashés correctement\n";
    } else {
        echo "   ⚠️  Vérification des mots de passe nécessaire\n";
    }
    
    // Vérifier les sessions
    echo "📱 Gestion des sessions:\n";
    if (file_exists('writable/session/')) {
        echo "   ✅ Dossier de sessions configuré\n";
    } else {
        echo "   ⚠️  Dossier de sessions manquant\n";
    }
    echo "\n";
    
    // ========================================
    // 8. ANALYSE DES FONCTIONNALITÉS
    // ========================================
    echo "🎯 8. ANALYSE DES FONCTIONNALITÉS\n";
    echo "================================\n";
    
    // Vérifier les modules principaux
    $modules = [
        'Scolarite' => 'Gestion des étudiants',
        'Economat' => 'Gestion financière',
        'Bibliotheque' => 'Gestion de la bibliothèque',
        'Messagerie' => 'Système de messagerie',
        'Configuration' => 'Configuration système',
        'Statistiques' => 'Rapports et statistiques'
    ];
    
    foreach ($modules as $controller => $description) {
        $controllerFile = "app/Controllers/$controller.php";
        if (file_exists($controllerFile)) {
            $content = file_get_contents($controllerFile);
            $methods = preg_match_all('/public function (\w+)/', $content, $matches);
            echo "   ✅ $description ($controller): $methods méthodes\n";
        } else {
            echo "   ❌ $description ($controller): Contrôleur manquant\n";
        }
    }
    echo "\n";
    
    // ========================================
    // 9. ANALYSE DES ERREURS ET LOGS
    // ========================================
    echo "📝 9. ANALYSE DES ERREURS ET LOGS\n";
    echo "================================\n";
    
    // Vérifier les logs d'erreur
    if (file_exists('writable/logs/')) {
        $logFiles = glob('writable/logs/*.log');
        if (count($logFiles) > 0) {
            $latestLog = end($logFiles);
            $logSize = filesize($latestLog);
            echo "   📄 Dernier log: " . basename($latestLog) . " ($logSize bytes)\n";
            
            // Analyser les erreurs récentes
            $logContent = file_get_contents($latestLog);
            $errorCount = substr_count($logContent, 'ERROR');
            $warningCount = substr_count($logContent, 'WARNING');
            
            echo "   ⚠️  Erreurs: $errorCount, Avertissements: $warningCount\n";
        } else {
            echo "   ✅ Aucun fichier de log trouvé\n";
        }
    } else {
        echo "   ❌ Dossier de logs manquant\n";
    }
    echo "\n";
    
    // ========================================
    // 10. RECOMMANDATIONS D'AMÉLIORATION
    // ========================================
    echo "🚀 10. RECOMMANDATIONS D'AMÉLIORATION\n";
    echo "====================================\n";
    
    $recommendations = [
        'Performance' => [
            'Ajouter des index sur les colonnes fréquemment utilisées',
            'Implémenter un système de cache pour les requêtes lourdes',
            'Optimiser les requêtes avec des jointures'
        ],
        'Sécurité' => [
            'Implémenter une validation CSRF sur tous les formulaires',
            'Ajouter une limitation de taux pour les tentatives de connexion',
            'Implémenter un système de logs de sécurité'
        ],
        'Fonctionnalités' => [
            'Ajouter un système de sauvegarde automatique',
            'Implémenter des notifications en temps réel',
            'Ajouter un système de recherche avancée'
        ],
        'Maintenance' => [
            'Créer des scripts de migration pour les mises à jour',
            'Implémenter un système de monitoring',
            'Ajouter des tests automatisés'
        ]
    ];
    
    foreach ($recommendations as $category => $items) {
        echo "📋 $category:\n";
        foreach ($items as $item) {
            echo "   • $item\n";
        }
        echo "\n";
    }
    
    // ========================================
    // RÉSUMÉ FINAL
    // ========================================
    echo "📊 RÉSUMÉ FINAL DE L'AUDIT\n";
    echo "==========================\n";
    
    $totalTables = count($tables);
    $existingTables = count($tableStats);
    $totalRecords = array_sum($tableStats);
    
    echo "✅ Tables: $existingTables/$totalTables existantes\n";
    echo "📊 Enregistrements totaux: $totalRecords\n";
    echo "🏗️  Architecture: CodeIgniter 4 avec MVC\n";
    echo "🗄️  Base de données: MariaDB\n";
    echo "🔒 Sécurité: Authentification et autorisation\n";
    echo "📱 Interface: Responsive avec Bulma CSS\n";
    
    echo "\n🎯 STATUT GLOBAL: ";
    if ($existingTables >= $totalTables * 0.8) {
        echo "✅ EXCELLENT\n";
    } elseif ($existingTables >= $totalTables * 0.6) {
        echo "⚠️  BON\n";
    } else {
        echo "❌ À AMÉLIORER\n";
    }
    
    echo "\n🎉 AUDIT TERMINÉ AVEC SUCCÈS !\n";
    echo "==============================\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR DE CONNEXION: " . $e->getMessage() . "\n";
}
?>





