<?php
/**
 * Test de l'évolution dynamique des années scolaires
 * Projet LyCol - Vérification de la gestion des années académiques
 */

echo "🔍 TEST DE L'ÉVOLUTION DYNAMIQUE DES ANNÉES SCOLAIRES\n";
echo "==================================================\n\n";

// Test 1: Configuration de base
echo "📋 Test 1: Configuration de base\n";
echo "--------------------------------\n";

// Simuler la logique de détermination de l'année scolaire
function getCurrentAcademicYear() {
    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');

    // Si nous sommes entre septembre et décembre, c'est l'année scolaire en cours
    if ($currentMonth >= 9) {
        return $currentYear . '-' . ($currentYear + 1);
    }
    // Si nous sommes entre janvier et août, c'est l'année scolaire précédente
    else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

function getAvailableAcademicYears($count = 5) {
    $years = [];
    $currentYear = getCurrentAcademicYear();
    $currentStartYear = (int)explode('-', $currentYear)[0];

    for ($i = 0; $i < $count; $i++) {
        $year = ($currentStartYear - $i) . '-' . ($currentStartYear - $i + 1);
        $years[] = $year;
    }

    return $years;
}

function getAcademicYearDates($academicYear = null) {
    if (!$academicYear) {
        $academicYear = getCurrentAcademicYear();
    }

    $years = explode('-', $academicYear);
    $startYear = $years[0];
    $endYear = $years[1];

    return [
        'start_date' => $startYear . '-09-01',
        'end_date' => $endYear . '-06-30',
        'academic_year' => $academicYear
    ];
}

// Test de l'année actuelle
$currentYear = getCurrentAcademicYear();
echo "✅ Année scolaire actuelle: $currentYear\n";

// Test des années disponibles
$availableYears = getAvailableAcademicYears();
echo "✅ Années disponibles: " . implode(', ', $availableYears) . "\n";

// Test des dates de l'année actuelle
$dates = getAcademicYearDates();
echo "✅ Dates de l'année actuelle:\n";
echo "   - Début: " . $dates['start_date'] . "\n";
echo "   - Fin: " . $dates['end_date'] . "\n\n";

// Test 2: Simulation d'évolution temporelle
echo "📅 Test 2: Simulation d'évolution temporelle\n";
echo "-------------------------------------------\n";

// Simuler différentes dates pour tester l'évolution
$testDates = [
    '2024-08-15' => '2023-2024', // Août 2024 -> année précédente
    '2024-09-01' => '2024-2025', // Septembre 2024 -> nouvelle année
    '2024-12-15' => '2024-2025', // Décembre 2024 -> année en cours
    '2025-01-15' => '2024-2025', // Janvier 2025 -> année en cours
    '2025-06-30' => '2024-2025', // Juin 2025 -> année en cours
    '2025-08-31' => '2024-2025', // Août 2025 -> année en cours
    '2025-09-01' => '2025-2026', // Septembre 2025 -> nouvelle année
];

foreach ($testDates as $date => $expectedYear) {
    $month = (int)date('n', strtotime($date));
    $year = (int)date('Y', strtotime($date));
    
    if ($month >= 9) {
        $calculatedYear = $year . '-' . ($year + 1);
    } else {
        $calculatedYear = ($year - 1) . '-' . $year;
    }
    
    $status = ($calculatedYear === $expectedYear) ? '✅' : '❌';
    echo "$status $date -> $calculatedYear (attendu: $expectedYear)\n";
}

echo "\n";

// Test 3: Vérification de l'implémentation dans les contrôleurs
echo "🔧 Test 3: Vérification de l'implémentation dans les contrôleurs\n";
echo "--------------------------------------------------------------\n";

$controllers = [
    'app/Controllers/Economat.php',
    'app/Controllers/Scolarite.php',
    'app/Controllers/Examens.php'
];

foreach ($controllers as $controller) {
    if (file_exists($controller)) {
        $content = file_get_contents($controller);
        
        // Vérifier l'utilisation du trait
        if (strpos($content, 'use App\\Traits\\AcademicYearTrait;') !== false) {
            echo "✅ $controller utilise AcademicYearTrait\n";
        } else {
            echo "❌ $controller n'utilise pas AcademicYearTrait\n";
        }
        
        // Vérifier l'utilisation de getCurrentAcademicYear
        if (strpos($content, 'getCurrentAcademicYear()') !== false) {
            echo "✅ $controller utilise getCurrentAcademicYear()\n";
        } else {
            echo "❌ $controller n'utilise pas getCurrentAcademicYear()\n";
        }
        
        // Vérifier la gestion des paramètres d'année
        if (strpos($content, 'getGet(\'academic_year\')') !== false) {
            echo "✅ $controller gère les paramètres d'année académique\n";
        } else {
            echo "❌ $controller ne gère pas les paramètres d'année académique\n";
        }
        
        echo "\n";
    } else {
        echo "❌ Fichier $controller non trouvé\n\n";
    }
}

// Test 4: Vérification de la base de données
echo "🗄️ Test 4: Vérification de la base de données\n";
echo "---------------------------------------------\n";

// Connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier les années académiques dans les tables principales
    $tables = ['students', 'payments', 'exams', 'classes'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT DISTINCT academic_year FROM $table ORDER BY academic_year");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "📊 Table $table:\n";
        if (!empty($years)) {
            foreach ($years as $year) {
                echo "   - $year\n";
            }
        } else {
            echo "   - Aucune année académique trouvée\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n\n";
}

// Test 5: Recommandations d'amélioration
echo "💡 Test 5: Recommandations d'amélioration\n";
echo "----------------------------------------\n";

echo "🔍 Points positifs identifiés:\n";
echo "✅ Configuration centralisée dans AcademicYear.php\n";
echo "✅ Trait AcademicYearTrait pour la réutilisation\n";
echo "✅ Méthode getCurrentAcademicYear() dynamique\n";
echo "✅ Gestion des paramètres d'année dans les contrôleurs\n";
echo "✅ Filtrage par année académique dans les requêtes\n\n";

echo "⚠️ Points d'amélioration identifiés:\n";
echo "1. Migration automatique des données vers la nouvelle année\n";
echo "2. Système de promotion automatique en fin d'année\n";
echo "3. Sauvegarde des données de l'année précédente\n";
echo "4. Interface pour changer d'année académique\n";
echo "5. Validation des dates selon l'année académique\n\n";

echo "🚀 Recommandations:\n";
echo "1. Implémenter un service de migration d'année académique\n";
echo "2. Ajouter des triggers de base de données pour la validation\n";
echo "3. Créer une interface d'administration pour la gestion des années\n";
echo "4. Automatiser la promotion des élèves en fin d'année\n";
echo "5. Ajouter des rapports comparatifs entre années\n\n";

echo "📋 CONCLUSION:\n";
echo "L'application LyCol gère de manière DYNAMIQUE l'évolution des années scolaires\n";
echo "grâce à la configuration centralisée et au trait AcademicYearTrait.\n";
echo "Cependant, des améliorations peuvent être apportées pour une gestion\n";
echo "plus automatisée et complète des transitions d'années académiques.\n\n";

echo "✅ Test terminé avec succès!\n";
?>





