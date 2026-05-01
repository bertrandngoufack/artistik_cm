<?php
/**
 * Script pour créer la table teachers et ajouter des données de test
 * KISSAI SCHOOL - Module Enseignants
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = 13306;
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier si la table teachers existe déjà
    $stmt = $pdo->query("SHOW TABLES LIKE 'teachers'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️  La table 'teachers' existe déjà\n";
        echo "Voulez-vous la supprimer et la recréer ? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim($line) !== 'y') {
            echo "❌ Opération annulée\n";
            exit;
        }
        
        $pdo->exec("DROP TABLE IF EXISTS teachers");
        echo "🗑️  Table 'teachers' supprimée\n";
    }
    
    // Créer la table teachers
    $sql = "
    CREATE TABLE teachers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_id INT NOT NULL DEFAULT 1,
        user_id INT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        specialization VARCHAR(200) NULL,
        qualification VARCHAR(200) NULL,
        hire_date DATE NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_school_id (school_id),
        INDEX idx_user_id (user_id),
        INDEX idx_specialization (specialization),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✅ Table 'teachers' créée avec succès\n";
    
    // Vérifier si la table classes a une colonne teacher_id
    $stmt = $pdo->query("SHOW COLUMNS FROM classes LIKE 'teacher_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE classes ADD COLUMN teacher_id INT NULL AFTER series_id");
        $pdo->exec("ALTER TABLE classes ADD INDEX idx_teacher_id (teacher_id)");
        echo "✅ Colonne 'teacher_id' ajoutée à la table 'classes'\n";
    } else {
        echo "ℹ️  La colonne 'teacher_id' existe déjà dans la table 'classes'\n";
    }
    
    // Vérifier si la table class_subjects existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'class_subjects'");
    if ($stmt->rowCount() == 0) {
        // Créer la table class_subjects
        $sql = "
        CREATE TABLE class_subjects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            class_id INT NOT NULL,
            subject_id INT NOT NULL,
            teacher_id INT NULL,
            weekly_hours DECIMAL(5,2) NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_class_subject (class_id, subject_id),
            INDEX idx_class_id (class_id),
            INDEX idx_subject_id (subject_id),
            INDEX idx_teacher_id (teacher_id),
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        echo "✅ Table 'class_subjects' créée avec succès\n";
    } else {
        // Vérifier si la table class_subjects a une colonne teacher_id
        $stmt = $pdo->query("SHOW COLUMNS FROM class_subjects LIKE 'teacher_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE class_subjects ADD COLUMN teacher_id INT NULL AFTER subject_id");
            $pdo->exec("ALTER TABLE class_subjects ADD INDEX idx_teacher_id (teacher_id)");
            echo "✅ Colonne 'teacher_id' ajoutée à la table 'class_subjects'\n";
        } else {
            echo "ℹ️  La colonne 'teacher_id' existe déjà dans la table 'class_subjects'\n";
        }
    }
    
    // Insérer des données de test
    $teachers = [
        [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@kissai-school.com',
            'phone' => '+237 6 12 34 56 78',
            'specialization' => 'Mathématiques',
            'qualification' => 'Master',
            'hire_date' => '2020-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@kissai-school.com',
            'phone' => '+237 6 23 45 67 89',
            'specialization' => 'Français',
            'qualification' => 'CAPES',
            'hire_date' => '2019-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Pierre',
            'last_name' => 'Bernard',
            'email' => 'pierre.bernard@kissai-school.com',
            'phone' => '+237 6 34 56 78 90',
            'specialization' => 'Physique-Chimie',
            'qualification' => 'Agrégation',
            'hire_date' => '2018-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Sophie',
            'last_name' => 'Petit',
            'email' => 'sophie.petit@kissai-school.com',
            'phone' => '+237 6 45 67 89 01',
            'specialization' => 'Histoire-Géographie',
            'qualification' => 'Master',
            'hire_date' => '2021-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Michel',
            'last_name' => 'Robert',
            'email' => 'michel.robert@kissai-school.com',
            'phone' => '+237 6 56 78 90 12',
            'specialization' => 'Anglais',
            'qualification' => 'Licence',
            'hire_date' => '2022-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Isabelle',
            'last_name' => 'Durand',
            'email' => 'isabelle.durand@kissai-school.com',
            'phone' => '+237 6 67 89 01 23',
            'specialization' => 'Sciences de la Vie et de la Terre',
            'qualification' => 'Master',
            'hire_date' => '2020-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'François',
            'last_name' => 'Moreau',
            'email' => 'francois.moreau@kissai-school.com',
            'phone' => '+237 6 78 90 12 34',
            'specialization' => 'Philosophie',
            'qualification' => 'Doctorat',
            'hire_date' => '2017-09-01',
            'is_active' => 1
        ],
        [
            'first_name' => 'Catherine',
            'last_name' => 'Leroy',
            'email' => 'catherine.leroy@kissai-school.com',
            'phone' => '+237 6 89 01 23 45',
            'specialization' => 'Éducation Physique et Sportive',
            'qualification' => 'Licence',
            'hire_date' => '2021-09-01',
            'is_active' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO teachers (school_id, first_name, last_name, email, phone, specialization, qualification, hire_date, is_active) 
        VALUES (1, :first_name, :last_name, :email, :phone, :specialization, :qualification, :hire_date, :is_active)
    ");
    
    foreach ($teachers as $teacher) {
        $stmt->execute($teacher);
    }
    
    echo "✅ " . count($teachers) . " enseignants de test ajoutés\n";
    
    // Afficher les statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT specialization, COUNT(*) as count FROM teachers WHERE is_active = 1 GROUP BY specialization");
    $specializations = $stmt->fetchAll();
    
    echo "\n📊 Statistiques de la table teachers:\n";
    echo "   - Total enseignants: $total\n";
    echo "   - Répartition par spécialisation:\n";
    
    foreach ($specializations as $spec) {
        echo "     • {$spec['specialization']}: {$spec['count']} enseignant(s)\n";
    }
    
    echo "\n🎉 Module Enseignants configuré avec succès !\n";
    echo "   Accédez au module via: http://localhost:8080/admin/enseignants\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
