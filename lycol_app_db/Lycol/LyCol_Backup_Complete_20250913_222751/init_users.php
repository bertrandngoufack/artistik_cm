<?php

/**
 * Script d'initialisation des utilisateurs par défaut pour LyCol
 * 
 * Ce script crée les comptes utilisateurs de base avec leurs rôles
 * et permissions appropriées.
 */

// Configuration de la base de données
$dbConfig = [
    'hostname' => '100.69.65.33',
    'port' => 13306,
    'username' => 'root',
    'password' => 'Bateau123',
    'database' => 'lycol_db'
];

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "🎓 LYCOL - Initialisation des utilisateurs par défaut\n";
    echo "====================================================\n\n";
    
    // Vérification de la connexion
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // 1. Vérification des rôles existants
    echo "1. Vérification des rôles existants\n";
    echo "----------------------------------\n";
    
    $stmt = $pdo->query("SELECT id, name FROM roles ORDER BY id");
    $roles = $stmt->fetchAll();
    
    $roleMap = [];
    foreach ($roles as $role) {
        $roleMap[$role['name']] = $role['id'];
        echo "   - {$role['name']} (ID: {$role['id']})\n";
    }
    echo "\n";
    
    // 2. Suppression des utilisateurs existants (si nécessaire)
    echo "2. Nettoyage des utilisateurs existants\n";
    echo "--------------------------------------\n";
    
    $existingUsers = ['admin', 'directeur', 'secretaire', 'enseignant'];
    foreach ($existingUsers as $username) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$username]);
        echo "   - Utilisateur '$username' supprimé (s'il existait)\n";
    }
    echo "\n";
    
    // 3. Création des utilisateurs par défaut
    echo "3. Création des utilisateurs par défaut\n";
    echo "--------------------------------------\n";
    
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@lycol.cm',
            'password' => 'admin123',
            'first_name' => 'Administrateur',
            'last_name' => 'Principal',
            'phone' => '+237 123 456 789',
            'role' => 'SUPER_ADMIN'
        ],
        [
            'username' => 'directeur',
            'email' => 'directeur@ecole.cm',
            'password' => 'directeur123',
            'first_name' => 'Jean',
            'last_name' => 'Kamga',
            'phone' => '+237 234 567 890',
            'role' => 'DIRECTEUR'
        ],
        [
            'username' => 'secretaire',
            'email' => 'secretaire@ecole.cm',
            'password' => 'secretaire123',
            'first_name' => 'Marie',
            'last_name' => 'Nguemo',
            'phone' => '+237 345 678 901',
            'role' => 'SECRETAIRE'
        ],
        [
            'username' => 'enseignant',
            'email' => 'enseignant@ecole.cm',
            'password' => 'enseignant123',
            'first_name' => 'Pierre',
            'last_name' => 'Tchokouani',
            'phone' => '+237 456 789 012',
            'role' => 'ENSEIGNANT'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, phone, role_id, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    foreach ($users as $user) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $roleId = $roleMap[$user['role']];
        
        $stmt->execute([
            $user['username'],
            $user['email'],
            $hashedPassword,
            $user['first_name'],
            $user['last_name'],
            $user['phone'],
            $roleId
        ]);
        
        echo "   ✅ Utilisateur '{$user['username']}' créé avec succès\n";
        echo "      - Nom: {$user['first_name']} {$user['last_name']}\n";
        echo "      - Email: {$user['email']}\n";
        echo "      - Rôle: {$user['role']}\n";
        echo "      - Mot de passe: {$user['password']}\n";
        echo "\n";
    }
    
    // 4. Création d'un établissement par défaut
    echo "4. Création de l'établissement par défaut\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO schools (name, code, type, address, phone, email, director_name, director_phone, academic_year, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $schoolData = [
        'Lycée Moderne de Douala',
        'LMD2025',
        'SECONDAIRE',
        'Douala, Cameroun',
        '+237 123 456 789',
        'contact@lycee-moderne.cm',
        'Jean Kamga',
        '+237 234 567 890',
        '2024-2025'
    ];
    
    $stmt->execute($schoolData);
    echo "   ✅ Établissement 'Lycée Moderne de Douala' créé\n";
    echo "      - Code: LMD2025\n";
    echo "      - Type: Secondaire\n";
    echo "      - Année académique: 2024-2025\n\n";
    
    // 5. Création d'élèves de test
    echo "5. Création d'élèves de test\n";
    echo "----------------------------\n";
    
    // Récupérer une classe par défaut
    $stmt = $pdo->query("SELECT id FROM classes LIMIT 1");
    $class = $stmt->fetch();
    
    if ($class) {
        $testStudents = [
            [
                'matricule' => '2024-001',
                'first_name' => 'Alice',
                'last_name' => 'Mvondo',
                'birth_date' => '2008-03-15',
                'gender' => 'F',
                'parent_name' => 'Paul Mvondo',
                'parent_phone' => '+237 111 222 333'
            ],
            [
                'matricule' => '2024-002',
                'first_name' => 'Boris',
                'last_name' => 'Etoa',
                'birth_date' => '2007-07-22',
                'gender' => 'M',
                'parent_name' => 'Sophie Etoa',
                'parent_phone' => '+237 222 333 444'
            ],
            [
                'matricule' => '2024-003',
                'first_name' => 'Claire',
                'last_name' => 'Nkoudou',
                'birth_date' => '2008-11-08',
                'gender' => 'F',
                'parent_name' => 'Marc Nkoudou',
                'parent_phone' => '+237 333 444 555'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO students (matricule, first_name, last_name, birth_date, gender, parent_name, parent_phone, admission_date, current_class_id, academic_year, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW())
        ");
        
        foreach ($testStudents as $student) {
            $stmt->execute([
                $student['matricule'],
                $student['first_name'],
                $student['last_name'],
                $student['birth_date'],
                $student['gender'],
                $student['parent_name'],
                $student['parent_phone'],
                '2024-09-01',
                $class['id'],
                '2024-2025'
            ]);
            
            echo "   ✅ Élève '{$student['first_name']} {$student['last_name']}' créé\n";
            echo "      - Matricule: {$student['matricule']}\n";
            echo "      - Année de naissance: " . substr($student['birth_date'], 0, 4) . "\n";
        }
    }
    echo "\n";
    
    // 6. Affichage des informations d'accès
    echo "🔐 INFORMATIONS D'ACCÈS\n";
    echo "======================\n\n";
    
    echo "📱 Console d'administration:\n";
    echo "   URL: http://localhost:8081/admin\n";
    echo "   Utilisateur: admin\n";
    echo "   Mot de passe: admin123\n\n";
    
    echo "👨‍💼 Comptes créés:\n";
    echo "   - admin/admin123 (SUPER_ADMIN)\n";
    echo "   - directeur/directeur123 (DIRECTEUR)\n";
    echo "   - secretaire/secretaire123 (SECRETAIRE)\n";
    echo "   - enseignant/enseignant123 (ENSEIGNANT)\n\n";
    
    echo "👨‍👩‍👧‍👦 Espace parents:\n";
    echo "   URL: http://localhost:8081/parents\n";
    echo "   Matricule: 2024-001, 2024-002, 2024-003\n";
    echo "   Année de naissance: 2008, 2007, 2008\n\n";
    
    echo "📱 Interface mobile:\n";
    echo "   URL: http://localhost:8081/mobile\n";
    echo "   Code enseignant: enseignant\n\n";
    
    echo "📚 API Documentation:\n";
    echo "   URL: http://localhost:8081/api/docs\n\n";
    
    echo "⚠️  IMPORTANT:\n";
    echo "   - Changez les mots de passe après la première connexion\n";
    echo "   - Configurez les paramètres SMTP pour les emails\n";
    echo "   - Configurez les fournisseurs SMS/WhatsApp\n";
    echo "   - Activez une licence dans la console d'administration\n\n";
    
    echo "🎉 Initialisation terminée avec succès !\n";
    echo "Le système LyCol est prêt à être utilisé.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
    echo "Vérifiez que:\n";
    echo "   - La base de données lycol_db existe\n";
    echo "   - Le script lycol_schema.sql a été exécuté\n";
    echo "   - Les paramètres de connexion sont corrects\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}




