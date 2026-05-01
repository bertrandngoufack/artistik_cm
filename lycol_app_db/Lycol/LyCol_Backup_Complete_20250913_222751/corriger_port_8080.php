<?php
/**
 * Script de correction automatique des références au port 8080 vers 8080
 * KISSAI SCHOOL - Correction des liens et routes
 */

echo "🔧 CORRECTION AUTOMATIQUE DES RÉFÉRENCES PORT 8080 → 8080\n";
echo "========================================================\n\n";

$corrections = 0;
$errors = 0;

/**
 * Fonction pour corriger un fichier
 */
function correctFile($filePath) {
    global $corrections, $errors;
    
    if (!file_exists($filePath)) {
        echo "❌ Fichier non trouvé: $filePath\n";
        $errors++;
        return;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Corrections à effectuer
    $replacements = [
        'http://localhost:8080' => 'http://localhost:8080',
        'localhost:8080' => 'localhost:8080',
        'port 8080' => 'port 8080',
        'port=8080' => 'port=8080',
        '--port=8080' => '--port=8080',
        '8080/' => '8080/',
        '8080?' => '8080?',
        '8080\\' => '8080\\',
        '8080:' => '8080:',
        '8080 ' => '8080 ',
        '8080\n' => '8080\n',
        '8080"' => '8080"',
        "8080'" => "8080'",
    ];
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            echo "✅ Corrigé: $filePath\n";
            $corrections++;
        } else {
            echo "❌ Erreur d'écriture: $filePath\n";
            $errors++;
        }
    }
}

/**
 * Fonction pour scanner un répertoire
 */
function scanDirectory($dir, $excludeDirs = []) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getPathname();
            $relativePath = str_replace(getcwd() . '/', '', $filePath);
            
            // Exclure les répertoires
            $exclude = false;
            foreach ($excludeDirs as $excludeDir) {
                if (strpos($relativePath, $excludeDir) === 0) {
                    $exclude = true;
                    break;
                }
            }
            
            if (!$exclude) {
                $files[] = $filePath;
            }
        }
    }
    
    return $files;
}

// Répertoires à exclure
$excludeDirs = [
    'vendor',
    'node_modules',
    '.git',
    'writable/cache',
    'writable/logs',
    'writable/uploads',
    'writable/debugbar'
];

// Extensions de fichiers à traiter
$extensions = ['php', 'js', 'css', 'html', 'htm', 'txt', 'md', 'sh', 'yml', 'yaml', 'json'];

echo "🔍 SCAN DES FICHIERS À CORRIGER\n";
echo str_repeat("-", 50) . "\n";

$files = scanDirectory('.', $excludeDirs);
$filesToProcess = [];

foreach ($files as $file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($extension, $extensions)) {
        $content = file_get_contents($file);
        if (strpos($content, '8080') !== false) {
            $filesToProcess[] = $file;
            echo "📄 Fichier à corriger: $file\n";
        }
    }
}

echo "\n🔧 CORRECTION DES FICHIERS\n";
echo str_repeat("-", 50) . "\n";

foreach ($filesToProcess as $file) {
    correctFile($file);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSULTATS DE LA CORRECTION\n";
echo str_repeat("=", 60) . "\n";
echo "Fichiers corrigés: $corrections\n";
echo "Erreurs: $errors\n";
echo "Fichiers traités: " . count($filesToProcess) . "\n";

if ($corrections > 0) {
    echo "\n✅ CORRECTION TERMINÉE AVEC SUCCÈS !\n";
    echo "Toutes les références au port 8080 ont été corrigées vers 8080.\n";
} else {
    echo "\nℹ️  AUCUNE CORRECTION NÉCESSAIRE\n";
    echo "Aucune référence au port 8080 trouvée.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
?>





