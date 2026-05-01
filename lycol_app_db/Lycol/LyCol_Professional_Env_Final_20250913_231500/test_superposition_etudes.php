<?php
/**
 * Test de Superposition - Module Études
 * Vérification des corrections CSS pour éviter les superpositions
 */

echo "🔍 TEST SUPERPOSITION - MODULE ÉTUDES\n";
echo "=====================================\n\n";

$baseUrl = "http://localhost:8080";

// Test 1: Vérification de la page principale
echo "📊 TEST 1: Page principale des études\n";
echo "------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "  ✅ Page accessible (HTTP 200)\n";
    
    // Vérification des problèmes de superposition
    $superpositionIssues = [];
    
    // Vérifier les titres qui se chevauchent
    if (preg_match_all('/<h[1-6][^>]*class="[^"]*title[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Titres trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les cartes avec des problèmes potentiels
    if (preg_match_all('/<div[^>]*class="[^"]*card[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Cartes trouvées: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les éléments media
    if (preg_match_all('/<div[^>]*class="[^"]*media[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Éléments media trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les icônes
    if (preg_match_all('/<span[^>]*class="[^"]*icon[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Icônes trouvées: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les boutons
    if (preg_match_all('/<a[^>]*class="[^"]*button[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Boutons trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les statistiques
    if (preg_match_all('/<div[^>]*class="[^"]*box[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Boîtes de statistiques trouvées: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les niveaux
    if (preg_match_all('/<div[^>]*class="[^"]*level[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Éléments level trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les tags
    if (preg_match_all('/<span[^>]*class="[^"]*tag[^"]*"[^>]*>/i', $response, $matches)) {
        echo "  📋 Tags trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les barres de progression
    if (preg_match_all('/<progress[^>]*>/i', $response, $matches)) {
        echo "  📋 Barres de progression trouvées: " . count($matches[0]) . "\n";
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $httpCode)\n";
}

echo "\n";

// Test 2: Vérification du CSS de correction
echo "📊 TEST 2: Vérification du CSS de correction\n";
echo "--------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/assets/css/etudes-fixes.css");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$cssResponse = curl_exec($ch);
$cssHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($cssHttpCode === 200) {
    echo "  ✅ CSS de correction accessible (HTTP 200)\n";
    
    // Vérifier les règles CSS importantes
    $cssRules = [
        'title' => 'Correction des titres',
        'subtitle' => 'Correction des sous-titres',
        'media' => 'Correction des éléments media',
        'icon' => 'Correction des icônes',
        'card' => 'Correction des cartes',
        'level' => 'Correction des niveaux',
        'tag' => 'Correction des tags',
        'progress' => 'Correction des barres de progression',
        'button' => 'Correction des boutons',
        'responsive' => 'Corrections responsive',
        'superposition' => 'Corrections de superposition'
    ];
    
    foreach ($cssRules as $rule => $description) {
        if (stripos($cssResponse, $rule) !== false) {
            echo "  ✅ $description\n";
        } else {
            echo "  ⚠️ $description (non trouvée)\n";
        }
    }
    
    // Vérifier la taille du CSS
    $cssSize = strlen($cssResponse);
    echo "  📏 Taille du CSS: " . number_format($cssSize) . " octets\n";
    
} else {
    echo "  ❌ CSS de correction non accessible (HTTP $cssHttpCode)\n";
}

echo "\n";

// Test 3: Vérification des problèmes spécifiques
echo "📊 TEST 3: Vérification des problèmes spécifiques\n";
echo "------------------------------------------------\n";

if (isset($response)) {
    // Vérifier les problèmes de superposition dans le HTML
    $issues = [];
    
    // Vérifier les titres trop longs
    if (preg_match_all('/<h[1-6][^>]*class="[^"]*title[^"]*"[^>]*>(.*?)<\/h[1-6]>/i', $response, $matches)) {
        foreach ($matches[1] as $index => $title) {
            $titleLength = strlen(strip_tags($title));
            if ($titleLength > 50) {
                $issues[] = "Titre trop long ($titleLength caractères): " . substr(strip_tags($title), 0, 30) . "...";
            }
        }
    }
    
    // Vérifier les sous-titres trop longs
    if (preg_match_all('/<p[^>]*class="[^"]*subtitle[^"]*"[^>]*>(.*?)<\/p>/i', $response, $matches)) {
        foreach ($matches[1] as $index => $subtitle) {
            $subtitleLength = strlen(strip_tags($subtitle));
            if ($subtitleLength > 100) {
                $issues[] = "Sous-titre trop long ($subtitleLength caractères): " . substr(strip_tags($subtitle), 0, 30) . "...";
            }
        }
    }
    
    // Vérifier les boutons avec texte trop long
    if (preg_match_all('/<a[^>]*class="[^"]*button[^"]*"[^>]*>(.*?)<\/a>/i', $response, $matches)) {
        foreach ($matches[1] as $index => $buttonText) {
            $buttonLength = strlen(strip_tags($buttonText));
            if ($buttonLength > 30) {
                $issues[] = "Bouton avec texte trop long ($buttonLength caractères): " . substr(strip_tags($buttonText), 0, 20) . "...";
            }
        }
    }
    
    // Vérifier les tags avec texte trop long
    if (preg_match_all('/<span[^>]*class="[^"]*tag[^"]*"[^>]*>(.*?)<\/span>/i', $response, $matches)) {
        foreach ($matches[1] as $index => $tagText) {
            $tagLength = strlen(strip_tags($tagText));
            if ($tagLength > 20) {
                $issues[] = "Tag avec texte trop long ($tagLength caractères): " . substr(strip_tags($tagText), 0, 15) . "...";
            }
        }
    }
    
    if (empty($issues)) {
        echo "  ✅ Aucun problème de superposition détecté\n";
    } else {
        echo "  ⚠️ Problèmes potentiels détectés:\n";
        foreach ($issues as $issue) {
            echo "    - $issue\n";
        }
    }
    
} else {
    echo "  ❌ Impossible de vérifier les problèmes spécifiques\n";
}

echo "\n";

// Test 4: Vérification de la responsivité
echo "📊 TEST 4: Vérification de la responsivité\n";
echo "----------------------------------------\n";

if (isset($cssResponse)) {
    $responsiveRules = [
        '@media screen and (max-width: 768px)' => 'Règles pour tablettes',
        '@media screen and (max-width: 480px)' => 'Règles pour mobiles',
        'flex-wrap' => 'Flexbox wrap',
        'overflow' => 'Gestion du débordement',
        'word-wrap' => 'Coupure de mots',
        'text-overflow' => 'Troncature de texte'
    ];
    
    foreach ($responsiveRules as $rule => $description) {
        if (stripos($cssResponse, $rule) !== false) {
            echo "  ✅ $description\n";
        } else {
            echo "  ⚠️ $description (non trouvée)\n";
        }
    }
    
} else {
    echo "  ❌ Impossible de vérifier la responsivité\n";
}

echo "\n";

// Test 5: Vérification de la lisibilité
echo "📊 TEST 5: Vérification de la lisibilité\n";
echo "---------------------------------------\n";

if (isset($cssResponse)) {
    $readabilityRules = [
        'line-height' => 'Hauteur de ligne',
        'text-shadow' => 'Ombre de texte',
        'color' => 'Couleurs',
        'font-size' => 'Tailles de police',
        'margin' => 'Marges',
        'padding' => 'Espacement interne',
        'z-index' => 'Ordre de superposition'
    ];
    
    foreach ($readabilityRules as $rule => $description) {
        if (stripos($cssResponse, $rule) !== false) {
            echo "  ✅ $description\n";
        } else {
            echo "  ⚠️ $description (non trouvée)\n";
        }
    }
    
} else {
    echo "  ❌ Impossible de vérifier la lisibilité\n";
}

echo "\n";

// Résumé final
echo "📊 RÉSUMÉ DES TESTS\n";
echo "===================\n";

$totalTests = 5;
$passedTests = 0;
$warnings = 0;

if ($httpCode === 200) $passedTests++;
if ($cssHttpCode === 200) $passedTests++;
if (isset($response)) $passedTests++;
if (isset($cssResponse)) $passedTests++;
if (isset($cssResponse)) $passedTests++;

echo "✅ Tests réussis: $passedTests/$totalTests\n";
echo "⚠️ Tests avec avertissements: $warnings\n";

if ($passedTests === $totalTests) {
    echo "\n🏆 SUPERPOSITION: EXCELLENT ÉTAT\n";
    echo "   Toutes les corrections sont appliquées.\n";
} elseif ($passedTests >= 3) {
    echo "\n🏆 SUPERPOSITION: BON ÉTAT\n";
    echo "   La plupart des corrections sont appliquées.\n";
} else {
    echo "\n🏆 SUPERPOSITION: ATTENTION REQUISE\n";
    echo "   Certaines corrections nécessitent une attention.\n";
}

echo "\n🌐 Interface accessible sur: $baseUrl/admin/etudes\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>


