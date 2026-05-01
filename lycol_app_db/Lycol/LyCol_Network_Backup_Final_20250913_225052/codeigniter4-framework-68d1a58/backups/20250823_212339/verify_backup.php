<?php
/**
 * 🧪 SCRIPT DE VÉRIFICATION DE LA SAUVEGARDE
 * KISSAI SCHOOL - CodeIgniter 4
 * Date: 23 Août 2025
 */

echo "=== VÉRIFICATION DE LA SAUVEGARDE KISSAI SCHOOL ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Configuration
$backupDir = __DIR__;
$projectArchive = $backupDir . '/codeigniter4_project.tar.gz';
$databaseBackup = $backupDir . '/database_backup.sql';

// Couleurs pour la sortie
$colors = [
    'red' => "\033[0;31m",
    'green' => "\033[0;32m",
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'reset' => "\033[0m"
];

function printStatus($message, $type = 'info') {
    global $colors;
    $color = $colors[$type] ?? $colors['blue'];
    echo $color . "[$type]" . $colors['reset'] . " $message\n";
}

// 1. VÉRIFICATION DES FICHIERS
printStatus("Vérification des fichiers de sauvegarde...", 'info');

if (!file_exists($projectArchive)) {
    printStatus("❌ Fichier codeigniter4_project.tar.gz introuvable!", 'red');
    exit(1);
}

if (!file_exists($databaseBackup)) {
    printStatus("❌ Fichier database_backup.sql introuvable!", 'red');
    exit(1);
}

printStatus("✅ Fichiers de sauvegarde trouvés", 'green');

// 2. VÉRIFICATION DE LA TAILLE DES FICHIERS
printStatus("Vérification de la taille des fichiers...", 'info');

$projectSize = filesize($projectArchive);
$databaseSize = filesize($databaseBackup);

echo "📦 Code source: " . number_format($projectSize / 1024 / 1024, 2) . " MB\n";
echo "🗄️  Base de données: " . number_format($databaseSize / 1024, 2) . " KB\n";

if ($projectSize < 1000000) { // Moins de 1MB
    printStatus("⚠️  Le fichier du projet semble trop petit", 'yellow');
}

if ($databaseSize < 1000) { // Moins de 1KB
    printStatus("⚠️  Le fichier de base de données semble trop petit", 'yellow');
}

// 3. VÉRIFICATION DE L'INTÉGRITÉ DE L'ARCHIVE
printStatus("Vérification de l'intégrité de l'archive...", 'info');

$output = [];
$returnCode = 0;
exec("tar -tzf '$projectArchive' 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    printStatus("✅ Archive tar.gz valide", 'green');
    
    // Compter les fichiers essentiels
    $essentialFiles = [
        'app/Config/Routes.php',
        'app/Controllers/',
        'app/Models/',
        'app/Views/',
        'public/index.php',
        '.env'
    ];
    
    $foundFiles = 0;
    foreach ($output as $line) {
        foreach ($essentialFiles as $file) {
            if (strpos($line, $file) !== false) {
                $foundFiles++;
                break;
            }
        }
    }
    
    echo "📁 Fichiers essentiels trouvés: $foundFiles/" . count($essentialFiles) . "\n";
    
    if ($foundFiles >= count($essentialFiles) - 1) { // -1 pour .env qui peut ne pas être inclus
        printStatus("✅ Structure du projet valide", 'green');
    } else {
        printStatus("⚠️  Certains fichiers essentiels manquent", 'yellow');
    }
} else {
    printStatus("❌ Archive tar.gz corrompue", 'red');
    echo "Erreur: " . implode("\n", $output) . "\n";
}

// 4. VÉRIFICATION DE LA BASE DE DONNÉES
printStatus("Vérification de la sauvegarde de la base de données...", 'info');

$sqlContent = file_get_contents($databaseBackup);
if ($sqlContent === false) {
    printStatus("❌ Impossible de lire le fichier de base de données", 'red');
} else {
    // Vérifier les tables essentielles
    $essentialTables = [
        'system_settings',
        'students',
        'classes',
        'teachers',
        'subjects',
        'cycles',
        'payments'
    ];
    
    $foundTables = 0;
    foreach ($essentialTables as $table) {
        if (strpos($sqlContent, "CREATE TABLE `$table`") !== false || 
            strpos($sqlContent, "INSERT INTO `$table`") !== false) {
            $foundTables++;
        }
    }
    
    echo "🗃️  Tables essentielles trouvées: $foundTables/" . count($essentialTables) . "\n";
    
    if ($foundTables >= count($essentialTables) - 2) { // Tolérance pour 2 tables manquantes
        printStatus("✅ Structure de la base de données valide", 'green');
    } else {
        printStatus("⚠️  Certaines tables essentielles manquent", 'yellow');
    }
    
    // Vérifier les données
    $insertCount = substr_count($sqlContent, 'INSERT INTO');
    echo "📊 Nombre d'insertions: $insertCount\n";
    
    if ($insertCount > 0) {
        printStatus("✅ Données présentes dans la sauvegarde", 'green');
    } else {
        printStatus("⚠️  Aucune donnée trouvée dans la sauvegarde", 'yellow');
    }
}

// 5. VÉRIFICATION DE LA CONNEXION À LA BASE DE DONNÉES
printStatus("Test de connexion à la base de données...", 'info');

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db;charset=utf8mb4',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    printStatus("✅ Connexion à la base de données réussie", 'green');
    
    // Vérifier les tables existantes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "🗃️  Tables existantes: " . count($tables) . "\n";
    
    // Vérifier les données
    $stats = [];
    foreach ($essentialTables as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            $stats[$table] = $count;
        }
    }
    
    echo "📊 Statistiques de la base:\n";
    foreach ($stats as $table => $count) {
        echo "  - $table: $count enregistrements\n";
    }
    
} catch (PDOException $e) {
    printStatus("❌ Erreur de connexion à la base de données", 'red');
    echo "Erreur: " . $e->getMessage() . "\n";
}

// 6. RÉSUMÉ FINAL
echo "\n=== RÉSUMÉ DE LA VÉRIFICATION ===\n";

$checks = [
    'Fichiers de sauvegarde' => file_exists($projectArchive) && file_exists($databaseBackup),
    'Taille du projet' => $projectSize > 1000000,
    'Taille de la base' => $databaseSize > 1000,
    'Intégrité archive' => $returnCode === 0,
    'Structure projet' => $foundFiles >= count($essentialFiles) - 1,
    'Structure base' => $foundTables >= count($essentialTables) - 2,
    'Données présentes' => $insertCount > 0
];

$passed = 0;
$total = count($checks);

foreach ($checks as $check => $result) {
    $status = $result ? "✅" : "❌";
    $color = $result ? 'green' : 'red';
    printStatus("$status $check", $color);
    if ($result) $passed++;
}

echo "\n📈 Score: $passed/$total tests réussis\n";

if ($passed === $total) {
    printStatus("🎉 SAUVEGARDE 100% VALIDE!", 'green');
} elseif ($passed >= $total * 0.8) {
    printStatus("✅ SAUVEGARDE VALIDE (avec quelques avertissements)", 'green');
} else {
    printStatus("⚠️  SAUVEGARDE PROBLÉMATIQUE", 'yellow');
}

echo "\n=== FIN DE LA VÉRIFICATION ===\n";
?>


