<?php
/**
 * Script pour vérifier si la colonne academic_year existe dans la table exams
 */

$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 VÉRIFICATION DE LA COLONNE academic_year DANS LA TABLE exams\n";
    echo "============================================================\n\n";
    
    // Vérifier la structure de la table exams
    echo "📝 STRUCTURE DE LA TABLE exams :\n";
    echo "--------------------------------\n";
    $stmt = $pdo->query("DESCRIBE exams");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasAcademicYear = false;
    foreach ($columns as $column) {
        echo "✅ {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}\n";
        if ($column['Field'] === 'academic_year') {
            $hasAcademicYear = true;
        }
    }
    
    if ($hasAcademicYear) {
        echo "\n✅ La colonne academic_year existe déjà dans la table exams\n";
        
        // Vérifier les données existantes
        echo "\n📊 DONNÉES EXISTANTES DANS LA TABLE exams :\n";
        echo "-------------------------------------------\n";
        $stmt = $pdo->query("SELECT id, name, academic_year, exam_date FROM exams LIMIT 5");
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($exams as $exam) {
            echo "✅ ID: {$exam['id']}, Nom: {$exam['name']}, Année: {$exam['academic_year']}, Date: {$exam['exam_date']}\n";
        }
        
        // Vérifier les valeurs uniques d'année académique
        echo "\n📅 ANNÉES ACADÉMIQUES PRÉSENTES :\n";
        echo "---------------------------------\n";
        $stmt = $pdo->query("SELECT DISTINCT academic_year, COUNT(*) as count FROM exams GROUP BY academic_year ORDER BY academic_year DESC");
        $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($years as $year) {
            echo "✅ {$year['academic_year']}: {$year['count']} examens\n";
        }
        
    } else {
        echo "\n❌ La colonne academic_year n'existe pas dans la table exams\n";
        echo "🔧 Ajout de la colonne academic_year...\n";
        
        // Ajouter la colonne academic_year
        $sql = "ALTER TABLE exams ADD COLUMN academic_year VARCHAR(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Format: 2024-2025' AFTER exam_date";
        $pdo->exec($sql);
        
        echo "✅ Colonne academic_year ajoutée avec succès\n";
        
        // Mettre à jour les données existantes
        echo "🔄 Mise à jour des données existantes...\n";
        
        // Déterminer l'année académique basée sur la date d'examen
        $updateSql = "UPDATE exams SET academic_year = CASE 
                        WHEN MONTH(exam_date) >= 9 THEN CONCAT(YEAR(exam_date), '-', YEAR(exam_date) + 1)
                        ELSE CONCAT(YEAR(exam_date) - 1, '-', YEAR(exam_date))
                      END";
        $pdo->exec($updateSql);
        
        echo "✅ Données mises à jour avec succès\n";
        
        // Vérifier le résultat
        echo "\n📊 DONNÉES APRÈS MISE À JOUR :\n";
        echo "-------------------------------\n";
        $stmt = $pdo->query("SELECT id, name, academic_year, exam_date FROM exams LIMIT 5");
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($exams as $exam) {
            echo "✅ ID: {$exam['id']}, Nom: {$exam['name']}, Année: {$exam['academic_year']}, Date: {$exam['exam_date']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>









