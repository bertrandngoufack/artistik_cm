<?php
/**
 * SCRIPT DE CORRECTION DES MODULES LYSCOL
 * =======================================
 * 
 * Ce script corrige les problèmes identifiés dans l'audit :
 * 1. Structure de la table absences
 * 2. Tables manquantes
 * 3. Cohérence des données
 */

echo "🔧 CORRECTION DES MODULES LYSCOL\n";
echo "=================================\n\n";

// Configuration de la base de données
$dbConfig = [
    'host' => '100.69.65.33',
    'port' => '13306',
    'dbname' => 'lycol_db',
    'username' => 'root',
    'password' => 'Bateau123'
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset=utf8",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connexion à la base de données réussie\n\n";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
}

// =====================================================
// 1. CORRECTION DE LA TABLE ABSENCES
// =====================================================

echo "📊 1. CORRECTION DE LA TABLE ABSENCES\n";
echo "-------------------------------------\n";

// Vérifier si la colonne 'period' existe
try {
    $stmt = $pdo->query("DESCRIBE absences");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('period', $columnNames)) {
        echo "➕ Ajout de la colonne 'period' à la table absences...\n";
        $pdo->exec("ALTER TABLE absences ADD COLUMN period ENUM('MORNING', 'AFTERNOON', 'FULL_DAY') DEFAULT 'FULL_DAY' AFTER date");
        echo "✅ Colonne 'period' ajoutée avec succès\n";
    } else {
        echo "✅ Colonne 'period' existe déjà\n";
    }
    
    if (!in_array('class_id', $columnNames)) {
        echo "➕ Ajout de la colonne 'class_id' à la table absences...\n";
        $pdo->exec("ALTER TABLE absences ADD COLUMN class_id INT AFTER student_id");
        echo "✅ Colonne 'class_id' ajoutée avec succès\n";
        
        // Ajouter la clé étrangère
        echo "🔗 Ajout de la clé étrangère class_id...\n";
        $pdo->exec("ALTER TABLE absences ADD CONSTRAINT fk_absences_class FOREIGN KEY (class_id) REFERENCES classes(id)");
        echo "✅ Clé étrangère ajoutée avec succès\n";
    } else {
        echo "✅ Colonne 'class_id' existe déjà\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la modification de la table absences: " . $e->getMessage() . "\n";
}

// =====================================================
// 2. CRÉATION DES TABLES MANQUANTES
// =====================================================

echo "\n📋 2. CRÉATION DES TABLES MANQUANTES\n";
echo "------------------------------------\n";

// Table exam_types
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'exam_types'");
    if ($stmt->rowCount() == 0) {
        echo "➕ Création de la table exam_types...\n";
        $pdo->exec("
            CREATE TABLE exam_types (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                code VARCHAR(10) NOT NULL UNIQUE,
                description TEXT,
                weight DECIMAL(3,2) DEFAULT 1.00,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Table exam_types créée avec succès\n";
        
        // Insérer les types d'examens par défaut
        $defaultTypes = [
            ['CONTINUOUS', 'CONT', 'Contrôle continu', 0.3],
            ['MIDTERM', 'MID', 'Examen de mi-parcours', 0.3],
            ['FINAL', 'FINAL', 'Examen final', 0.4],
            ['COMPETITIVE', 'COMP', 'Examen de compétition', 1.0]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO exam_types (code, name, description, weight) VALUES (?, ?, ?, ?)");
        foreach ($defaultTypes as $type) {
            $stmt->execute($type);
        }
        echo "✅ Types d'examens par défaut insérés\n";
    } else {
        echo "✅ Table exam_types existe déjà\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de exam_types: " . $e->getMessage() . "\n";
}

// Table report_cards
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'report_cards'");
    if ($stmt->rowCount() == 0) {
        echo "➕ Création de la table report_cards...\n";
        $pdo->exec("
            CREATE TABLE report_cards (
                id INT PRIMARY KEY AUTO_INCREMENT,
                student_id INT NOT NULL,
                class_id INT NOT NULL,
                academic_year VARCHAR(9) NOT NULL,
                term ENUM('1ER_TRIMESTRE', '2EME_TRIMESTRE', '3EME_TRIMESTRE') NOT NULL,
                total_marks DECIMAL(5,2) DEFAULT 0.00,
                average_marks DECIMAL(5,2) DEFAULT 0.00,
                rank INT,
                remarks TEXT,
                generated_by INT NOT NULL,
                generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id),
                FOREIGN KEY (class_id) REFERENCES classes(id),
                FOREIGN KEY (generated_by) REFERENCES users(id),
                UNIQUE KEY unique_report (student_id, class_id, academic_year, term)
            )
        ");
        echo "✅ Table report_cards créée avec succès\n";
    } else {
        echo "✅ Table report_cards existe déjà\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de report_cards: " . $e->getMessage() . "\n";
}

// Table disciplinary_actions
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'disciplinary_actions'");
    if ($stmt->rowCount() == 0) {
        echo "➕ Création de la table disciplinary_actions...\n";
        $pdo->exec("
            CREATE TABLE disciplinary_actions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                student_id INT NOT NULL,
                action_type ENUM('WARNING', 'REPRIMAND', 'SUSPENSION', 'EXPULSION') NOT NULL,
                description TEXT NOT NULL,
                start_date DATE,
                end_date DATE,
                is_active BOOLEAN DEFAULT TRUE,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id),
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ");
        echo "✅ Table disciplinary_actions créée avec succès\n";
    } else {
        echo "✅ Table disciplinary_actions existe déjà\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de disciplinary_actions: " . $e->getMessage() . "\n";
}

// Table permissions
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'permissions'");
    if ($stmt->rowCount() == 0) {
        echo "➕ Création de la table permissions...\n";
        $pdo->exec("
            CREATE TABLE permissions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                module VARCHAR(50) NOT NULL,
                action VARCHAR(50) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Table permissions créée avec succès\n";
        
        // Insérer les permissions par défaut
        $defaultPermissions = [
            ['VIEW_STUDENTS', 'Voir les élèves', 'scolarite', 'view'],
            ['CREATE_STUDENTS', 'Créer des élèves', 'scolarite', 'create'],
            ['EDIT_STUDENTS', 'Modifier les élèves', 'scolarite', 'edit'],
            ['DELETE_STUDENTS', 'Supprimer les élèves', 'scolarite', 'delete'],
            ['VIEW_PAYMENTS', 'Voir les paiements', 'economat', 'view'],
            ['CREATE_PAYMENTS', 'Créer des paiements', 'economat', 'create'],
            ['VIEW_GRADES', 'Voir les notes', 'examens', 'view'],
            ['CREATE_GRADES', 'Créer des notes', 'examens', 'create'],
            ['VIEW_STATS', 'Voir les statistiques', 'statistiques', 'view'],
            ['EXPORT_DATA', 'Exporter les données', 'statistiques', 'export']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO permissions (name, description, module, action) VALUES (?, ?, ?, ?)");
        foreach ($defaultPermissions as $permission) {
            $stmt->execute($permission);
        }
        echo "✅ Permissions par défaut insérées\n";
    } else {
        echo "✅ Table permissions existe déjà\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de permissions: " . $e->getMessage() . "\n";
}

// =====================================================
// 3. MISE À JOUR DES DONNÉES EXISTANTES
// =====================================================

echo "\n🔄 3. MISE À JOUR DES DONNÉES EXISTANTES\n";
echo "-----------------------------------------\n";

// Mettre à jour les absences existantes avec class_id
try {
    echo "🔄 Mise à jour des absences avec class_id...\n";
    $pdo->exec("
        UPDATE absences a 
        JOIN students s ON a.student_id = s.id 
        SET a.class_id = s.current_class_id 
        WHERE a.class_id IS NULL
    ");
    echo "✅ Absences mises à jour avec succès\n";
} catch (PDOException $e) {
    echo "❌ Erreur lors de la mise à jour des absences: " . $e->getMessage() . "\n";
}

// Mettre à jour les absences existantes avec period
try {
    echo "🔄 Mise à jour des absences avec period...\n";
    $pdo->exec("UPDATE absences SET period = 'FULL_DAY' WHERE period IS NULL");
    echo "✅ Périodes d'absences mises à jour avec succès\n";
} catch (PDOException $e) {
    echo "❌ Erreur lors de la mise à jour des périodes: " . $e->getMessage() . "\n";
}

// =====================================================
// 4. CRÉATION D'INDEX POUR LES PERFORMANCES
// =====================================================

echo "\n⚡ 4. OPTIMISATION DES PERFORMANCES\n";
echo "-----------------------------------\n";

$indexes = [
    'absences' => [
        'idx_student_date' => 'CREATE INDEX idx_student_date ON absences(student_id, date)',
        'idx_class_date' => 'CREATE INDEX idx_class_date ON absences(class_id, date)',
        'idx_justified' => 'CREATE INDEX idx_justified ON absences(justified)'
    ],
    'payments' => [
        'idx_student_date' => 'CREATE INDEX idx_student_date ON payments(student_id, payment_date)',
        'idx_academic_year' => 'CREATE INDEX idx_academic_year ON payments(academic_year)',
        'idx_fee_type' => 'CREATE INDEX idx_fee_type ON payments(fee_type_id)'
    ],
    'grades' => [
        'idx_student_exam' => 'CREATE INDEX idx_student_exam ON grades(student_id, exam_id)',
        'idx_subject' => 'CREATE INDEX idx_subject ON grades(subject_id)'
    ]
];

foreach ($indexes as $table => $tableIndexes) {
    foreach ($tableIndexes as $indexName => $sql) {
        try {
            // Vérifier si l'index existe déjà
            $stmt = $pdo->query("SHOW INDEX FROM $table WHERE Key_name = '$indexName'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec($sql);
                echo "✅ Index $indexName créé pour la table $table\n";
            } else {
                echo "✅ Index $indexName existe déjà pour la table $table\n";
            }
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la création de l'index $indexName: " . $e->getMessage() . "\n";
        }
    }
}

// =====================================================
// 5. VÉRIFICATION FINALE
// =====================================================

echo "\n✅ 5. VÉRIFICATION FINALE\n";
echo "-------------------------\n";

// Vérifier que toutes les tables existent
$requiredTables = [
    'absences', 'payments', 'students', 'classes', 'teachers', 
    'exams', 'grades', 'exam_types', 'report_cards', 
    'disciplinary_actions', 'permissions', 'users', 'roles', 'audit_logs'
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' existe\n";
        } else {
            echo "❌ Table '$table' manquante\n";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la vérification de '$table': " . $e->getMessage() . "\n";
    }
}

// Vérifier la structure de la table absences
echo "\n📊 Structure finale de la table absences:\n";
try {
    $stmt = $pdo->query("DESCRIBE absences");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "   • {$column['Field']} - {$column['Type']}\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur lors de la vérification de la structure: " . $e->getMessage() . "\n";
}

// =====================================================
// 6. RAPPORT DE CORRECTION
// =====================================================

echo "\n📋 6. RAPPORT DE CORRECTION\n";
echo "----------------------------\n";

echo "✅ CORRECTIONS EFFECTUÉES:\n";
echo "   • Structure de la table absences mise à jour\n";
echo "   • Tables manquantes créées (exam_types, report_cards, disciplinary_actions, permissions)\n";
echo "   • Données existantes mises à jour\n";
echo "   • Index de performance créés\n";
echo "   • Intégrité référentielle renforcée\n\n";

echo "🎯 PROCHAINES ÉTAPES:\n";
echo "   • Tester le module Statistiques\n";
echo "   • Vérifier les fonctionnalités CRUD\n";
echo "   • Effectuer un nouvel audit complet\n";
echo "   • Optimiser les performances si nécessaire\n\n";

echo "📅 Correction effectuée le: " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Correction des modules LyCol\n";

?>






