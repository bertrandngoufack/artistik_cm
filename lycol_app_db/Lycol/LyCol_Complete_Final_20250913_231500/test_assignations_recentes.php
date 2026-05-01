<?php
/**
 * Script de test pour les assignations récentes
 * Ajoute des assignations de test et vérifie l'affichage
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérifier les données existantes
    echo "📊 Données existantes :\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teacher_assignments WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Total assignations actives : {$result['total']}\n";
    
    // Afficher les assignations existantes
    $stmt = $pdo->query("
        SELECT ta.*, t.first_name, t.last_name, c.name as class_name, s.name as subject_name 
        FROM teacher_assignments ta 
        JOIN teachers t ON ta.teacher_id = t.id 
        JOIN classes c ON ta.class_id = c.id 
        JOIN subjects s ON ta.subject_id = s.id 
        WHERE ta.is_active = 1 
        ORDER BY ta.created_at DESC 
        LIMIT 5
    ");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Assignations récentes actuelles :\n";
    foreach ($assignments as $assignment) {
        echo "   - {$assignment['first_name']} {$assignment['last_name']} : {$assignment['subject_name']} - {$assignment['class_name']}\n";
    }
    
    // Ajouter des assignations de test si nécessaire
    if (count($assignments) < 3) {
        echo "\n➕ Ajout d'assignations de test...\n";
        
        // Vérifier les enseignants disponibles
        $stmt = $pdo->query("SELECT id, first_name, last_name FROM teachers WHERE is_active = 1 LIMIT 5");
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Vérifier les classes disponibles
        $stmt = $pdo->query("SELECT id, name FROM classes WHERE is_active = 1 LIMIT 5");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Vérifier les matières disponibles
        $stmt = $pdo->query("SELECT id, name FROM subjects WHERE is_active = 1 LIMIT 5");
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($teachers) > 0 && count($classes) > 0 && count($subjects) > 0) {
            // Ajouter quelques assignations de test
            $testAssignments = [
                ['teacher_id' => $teachers[0]['id'], 'class_id' => $classes[0]['id'], 'subject_id' => $subjects[0]['id']],
                ['teacher_id' => $teachers[0]['id'], 'class_id' => $classes[0]['id'], 'subject_id' => $subjects[1]['id'] ?? $subjects[0]['id']],
            ];
            
            foreach ($testAssignments as $assignment) {
                // Vérifier si l'assignation existe déjà
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count FROM teacher_assignments 
                    WHERE teacher_id = ? AND class_id = ? AND subject_id = ? AND is_active = 1
                ");
                $stmt->execute([$assignment['teacher_id'], $assignment['class_id'], $assignment['subject_id']]);
                $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                
                if (!$exists) {
                    $stmt = $pdo->prepare("
                        INSERT INTO teacher_assignments (teacher_id, class_id, subject_id, is_principal, academic_year, is_active, created_at, updated_at)
                        VALUES (?, ?, ?, 0, '2024-2025', 1, NOW(), NOW())
                    ");
                    $stmt->execute([$assignment['teacher_id'], $assignment['class_id'], $assignment['subject_id']]);
                    echo "   ✅ Assignation ajoutée\n";
                } else {
                    echo "   ⚠️  Assignation existe déjà\n";
                }
            }
        }
    }
    
    // Vérifier le résultat final
    echo "\n📊 Vérification finale :\n";
    $stmt = $pdo->query("
        SELECT ta.*, t.first_name, t.last_name, c.name as class_name, s.name as subject_name 
        FROM teacher_assignments ta 
        JOIN teachers t ON ta.teacher_id = t.id 
        JOIN classes c ON ta.class_id = c.id 
        JOIN subjects s ON ta.subject_id = s.id 
        WHERE ta.is_active = 1 
        ORDER BY ta.created_at DESC 
        LIMIT 5
    ");
    $finalAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Assignations récentes (5 dernières) :\n";
    foreach ($finalAssignments as $assignment) {
        echo "   - {$assignment['first_name']} {$assignment['last_name']} : {$assignment['subject_name']} - {$assignment['class_name']}\n";
    }
    
    echo "\n✅ Test terminé avec succès !\n";
    echo "🌐 Vous pouvez maintenant vérifier l'interface : http://localhost:8080/admin/etudes\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>









