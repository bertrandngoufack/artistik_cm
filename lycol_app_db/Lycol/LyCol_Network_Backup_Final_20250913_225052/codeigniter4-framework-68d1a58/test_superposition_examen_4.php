<?php

/**
 * TEST SPÉCIFIQUE - SUPERPOSITION PAGE EXAMEN ID 4
 * Vérification détaillée des problèmes de superposition
 */

echo "🔍 TEST SPÉCIFIQUE - SUPERPOSITION PAGE EXAMEN ID 4\n";
echo "==================================================\n\n";

$baseUrl = 'http://localhost:8080';
$url = '/admin/examens/exams/4/view';

echo "📊 ANALYSE DE LA PAGE EXAMEN ID 4\n";
echo "----------------------------------\n";

// Récupérer le contenu de la page
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page accessible (HTTP $httpCode)\n\n";
    
    // Analyser les problèmes de superposition
    echo "🔍 ANALYSE DES PROBLÈMES DE SUPERPOSITION\n";
    echo "------------------------------------------\n";
    
    $issues = [];
    
    // Vérifier les titres dupliqués
    $titleCount = substr_count($response, 'Détails de l\'Examen');
    if ($titleCount > 1) {
        $issues[] = "Titre 'Détails de l'Examen' dupliqué ($titleCount fois)";
    }
    
    // Vérifier les classes CSS problématiques
    if (strpos($response, 'position: absolute') !== false && strpos($response, 'z-index') === false) {
        $issues[] = "Position absolute sans z-index détectée";
    }
    
    if (strpos($response, 'position: fixed') !== false && strpos($response, 'z-index') === false) {
        $issues[] = "Position fixed sans z-index détectée";
    }
    
    // Vérifier les marges et paddings
    if (strpos($response, 'margin: 0') !== false && strpos($response, 'padding: 0') !== false) {
        $issues[] = "Margin et padding à 0 détectés";
    }
    
    // Vérifier les largeurs problématiques
    if (strpos($response, 'width: 100%') !== false && strpos($response, 'max-width') === false) {
        $issues[] = "Largeur 100% sans max-width détectée";
    }
    
    // Vérifier les éléments qui pourraient se superposer
    $elements = [
        'title' => 'Titres',
        'subtitle' => 'Sous-titres',
        'level' => 'Sections level',
        'box' => 'Boîtes de contenu',
        'buttons' => 'Boutons',
        'pagination' => 'Pagination'
    ];
    
    foreach ($elements as $class => $name) {
        $count = substr_count($response, "class=\"$class");
        if ($count > 0) {
            echo "  📊 $name: $count occurrence(s)\n";
        }
    }
    
    echo "\n";
    
    // Vérifier la structure HTML
    echo "🔍 ANALYSE DE LA STRUCTURE HTML\n";
    echo "--------------------------------\n";
    
    // Vérifier les balises h1, h2, h3
    $h1Count = substr_count($response, '<h1');
    $h2Count = substr_count($response, '<h2');
    $h3Count = substr_count($response, '<h3');
    
    echo "  📊 Balises H1: $h1Count\n";
    echo "  📊 Balises H2: $h2Count\n";
    echo "  📊 Balises H3: $h3Count\n";
    
    // Vérifier les divs avec des classes spécifiques
    $divCount = substr_count($response, '<div');
    $containerCount = substr_count($response, 'class="container');
    $levelCount = substr_count($response, 'class="level');
    $boxCount = substr_count($response, 'class="box');
    
    echo "  📊 Total DIV: $divCount\n";
    echo "  📊 Containers: $containerCount\n";
    echo "  📊 Levels: $levelCount\n";
    echo "  📊 Boxes: $boxCount\n";
    
    echo "\n";
    
    // Vérifier les styles CSS appliqués
    echo "🎨 ANALYSE DES STYLES CSS\n";
    echo "-------------------------\n";
    
    $cssIssues = [];
    
    // Vérifier si les styles de correction sont présents
    if (strpos($response, 'exam-details-page') !== false) {
        echo "  ✅ Classe CSS 'exam-details-page' appliquée\n";
    } else {
        $cssIssues[] = "Classe CSS 'exam-details-page' manquante";
    }
    
    if (strpos($response, 'max-width: 100%') !== false) {
        echo "  ✅ Max-width 100% appliqué\n";
    } else {
        $cssIssues[] = "Max-width 100% manquant";
    }
    
    if (strpos($response, 'overflow: hidden') !== false) {
        echo "  ✅ Overflow hidden appliqué\n";
    } else {
        $cssIssues[] = "Overflow hidden manquant";
    }
    
    if (strpos($response, 'z-index') !== false) {
        echo "  ✅ Z-index appliqué\n";
    } else {
        $cssIssues[] = "Z-index manquant";
    }
    
    echo "\n";
    
    // Afficher les problèmes détectés
    if (!empty($issues)) {
        echo "❌ PROBLÈMES DÉTECTÉS:\n";
        echo "---------------------\n";
        foreach ($issues as $issue) {
            echo "  • $issue\n";
        }
        echo "\n";
    }
    
    if (!empty($cssIssues)) {
        echo "⚠️ PROBLÈMES CSS:\n";
        echo "-----------------\n";
        foreach ($cssIssues as $issue) {
            echo "  • $issue\n";
        }
        echo "\n";
    }
    
    // Recommandations
    echo "🔧 RECOMMANDATIONS POUR CORRIGER LA SUPERPOSITION:\n";
    echo "-------------------------------------------------\n";
    
    if (empty($issues) && empty($cssIssues)) {
        echo "  ✅ Aucun problème de superposition détecté\n";
        echo "  ✅ La page semble bien structurée\n";
    } else {
        if ($titleCount > 1) {
            echo "  • Supprimer les titres dupliqués\n";
        }
        if (in_array("Largeur 100% sans max-width détectée", $issues)) {
            echo "  • Ajouter max-width: 100% aux éléments avec width: 100%\n";
        }
        if (in_array("Position absolute sans z-index détectée", $issues)) {
            echo "  • Ajouter z-index aux éléments avec position: absolute\n";
        }
        echo "  • Vérifier les marges et paddings des éléments\n";
        echo "  • Tester sur différents écrans et résolutions\n";
    }
    
    echo "\n";
    
    // Test de performance
    echo "⚡ TEST DE PERFORMANCE\n";
    echo "----------------------\n";
    
    $startTime = microtime(true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    $endTime = microtime(true);
    
    $loadTime = round(($endTime - $startTime) * 1000, 2);
    echo "  ⏱️ Temps de chargement: {$loadTime}ms\n";
    
    if ($loadTime < 1000) {
        echo "     ✅ Performance excellente\n";
    } elseif ($loadTime < 3000) {
        echo "     ✅ Performance acceptable\n";
    } else {
        echo "     ⚠️ Performance lente\n";
    }
    
} else {
    echo "❌ Erreur: Page non accessible (HTTP $httpCode)\n";
}

echo "\n";
echo "📊 RÉSUMÉ DE L'ANALYSE\n";
echo "=====================\n";

if ($httpCode == 200) {
    if (empty($issues) && empty($cssIssues)) {
        echo "✅ PAGE EXAMEN ID 4: EXCELLENT ÉTAT\n";
        echo "   Aucun problème de superposition détecté.\n";
    } elseif (count($issues) <= 2) {
        echo "✅ PAGE EXAMEN ID 4: BON ÉTAT\n";
        echo "   Quelques problèmes mineurs détectés.\n";
    } else {
        echo "⚠️ PAGE EXAMEN ID 4: PROBLÈMES DÉTECTÉS\n";
        echo "   Des corrections sont nécessaires.\n";
    }
} else {
    echo "❌ PAGE EXAMEN ID 4: INACCESSIBLE\n";
    echo "   Vérifier la configuration du serveur.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}{$url}\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


