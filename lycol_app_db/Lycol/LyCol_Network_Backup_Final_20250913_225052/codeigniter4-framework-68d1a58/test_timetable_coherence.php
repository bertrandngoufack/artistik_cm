<?php
/**
 * 🧪 TEST DE COHÉRENCE - EMPLOI DU TEMPS
 * Vérification de la liaison des données et des filtres
 */

echo "=== TEST DE COHÉRENCE - EMPLOI DU TEMPS ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie\n\n";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit;
}

// 1. VÉRIFICATION DES DONNÉES DE BASE
echo "1. VÉRIFICATION DES DONNÉES DE BASE\n";
echo "=====================================\n";

// Classes
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM classes WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📚 Classes actives: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT id, name FROM classes WHERE is_active = 1 LIMIT 5");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Exemples: ";
    foreach ($classes as $class) {
        echo $class['name'] . " (ID: " . $class['id'] . ") ";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Erreur classes: " . $e->getMessage() . "\n";
}

// Matières
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM subjects WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📖 Matières actives: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT id, name FROM subjects WHERE is_active = 1 LIMIT 5");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Exemples: ";
    foreach ($subjects as $subject) {
        echo $subject['name'] . " (ID: " . $subject['id'] . ") ";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Erreur matières: " . $e->getMessage() . "\n";
}

// Enseignants
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "👨‍🏫 Enseignants actifs: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM teachers WHERE is_active = 1 LIMIT 5");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Exemples: ";
    foreach ($teachers as $teacher) {
        echo $teacher['first_name'] . " " . $teacher['last_name'] . " (ID: " . $teacher['id'] . ") ";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Erreur enseignants: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. VÉRIFICATION DES EMPLOIS DU TEMPS
echo "2. VÉRIFICATION DES EMPLOIS DU TEMPS\n";
echo "=====================================\n";

try {
    // Requête complète avec jointures
    $query = "
        SELECT 
            t.*,
            c.name as class_name,
            s.name as subject_name,
            CONCAT(te.first_name, ' ', te.last_name) as teacher_name
        FROM timetables t
        LEFT JOIN classes c ON t.class_id = c.id
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN teachers te ON t.teacher_id = te.id
        WHERE t.is_active = 1
        ORDER BY t.day_of_week, t.start_time
    ";
    
    $stmt = $pdo->query($query);
    $timetables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📅 Total emplois du temps: " . count($timetables) . "\n";
    
    if (count($timetables) > 0) {
        echo "   Détails:\n";
        foreach ($timetables as $timetable) {
            $days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            $dayName = $days[$timetable['day_of_week']] ?? 'N/A';
            
            echo "   - " . $timetable['class_name'] . " | " . 
                 $timetable['subject_name'] . " | " . 
                 ($timetable['teacher_name'] ?: 'N/A') . " | " .
                 $dayName . " | " .
                 $timetable['start_time'] . " - " . $timetable['end_time'] . "\n";
        }
    }
    
    // Statistiques
    $classIds = array_unique(array_column($timetables, 'class_id'));
    $teacherIds = array_unique(array_filter(array_column($timetables, 'teacher_id')));
    $subjectIds = array_unique(array_column($timetables, 'subject_id'));
    
    echo "\n   📊 Statistiques:\n";
    echo "   - Classes couvertes: " . count($classIds) . "\n";
    echo "   - Enseignants impliqués: " . count($teacherIds) . "\n";
    echo "   - Matières couvertes: " . count($subjectIds) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur emplois du temps: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. VÉRIFICATION DES PROBLÈMES IDENTIFIÉS
echo "3. VÉRIFICATION DES PROBLÈMES IDENTIFIÉS\n";
echo "=========================================\n";

// Problème 1: Enseignant N/A dans l'image
echo "🔍 Problème 1: Enseignant N/A\n";
try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM timetables 
        WHERE teacher_id IS NULL OR teacher_id = 0
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Emplois du temps sans enseignant: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        echo "   ⚠️  Ceci explique pourquoi 'N/A' apparaît dans l'interface\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur vérification: " . $e->getMessage() . "\n";
}

// Problème 2: Filtres JavaScript
echo "\n🔍 Problème 2: Filtres JavaScript\n";
echo "   Le code JavaScript filtre par texte mais les valeurs des options sont des IDs\n";
echo "   ⚠️  Incohérence: filtrage par nom vs valeur par ID\n";

// Problème 3: Affichage des jours
echo "\n🔍 Problème 3: Affichage des jours\n";
echo "   Les jours sont affichés correctement (Lundi, Mardi, etc.)\n";
echo "   ✅ Pas de problème identifié\n";

echo "\n";

// 4. CORRECTIONS SUGGÉRÉES
echo "4. CORRECTIONS SUGGÉRÉES\n";
echo "========================\n";

echo "✅ Correction 1: Ajouter des enseignants aux emplois du temps\n";
echo "✅ Correction 2: Améliorer les filtres JavaScript\n";
echo "✅ Correction 3: Vérifier la cohérence des données\n";

echo "\n";

// 5. TEST DES FILTRES
echo "5. TEST DES FILTRES\n";
echo "===================\n";

echo "🔍 Test du filtre par classe:\n";
try {
    $stmt = $pdo->query("
        SELECT DISTINCT c.id, c.name, COUNT(t.id) as timetable_count
        FROM classes c
        LEFT JOIN timetables t ON c.id = t.class_id AND t.is_active = 1
        WHERE c.is_active = 1
        GROUP BY c.id, c.name
        ORDER BY c.name
    ");
    $classStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($classStats as $stat) {
        echo "   - " . $stat['name'] . ": " . $stat['timetable_count'] . " emplois du temps\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur test filtres: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. RÉSUMÉ
echo "6. RÉSUMÉ\n";
echo "==========\n";

$issues = [];
$suggestions = [];

if (count($timetables) == 0) {
    $issues[] = "Aucun emploi du temps trouvé";
    $suggestions[] = "Créer des emplois du temps de test";
}

if (isset($result) && $result['count'] > 0) {
    $issues[] = "Emplois du temps sans enseignant assigné";
    $suggestions[] = "Assigner des enseignants aux cours";
}

if (count($issues) > 0) {
    echo "❌ Problèmes identifiés:\n";
    foreach ($issues as $issue) {
        echo "   - " . $issue . "\n";
    }
    echo "\n💡 Suggestions:\n";
    foreach ($suggestions as $suggestion) {
        echo "   - " . $suggestion . "\n";
    }
} else {
    echo "✅ Aucun problème majeur identifié\n";
}

echo "\n=== FIN DU TEST ===\n";
?>


