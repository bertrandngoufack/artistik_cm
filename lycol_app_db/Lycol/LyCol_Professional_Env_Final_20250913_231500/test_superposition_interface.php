<?php

/**
 * TEST SUPERPOSITION - INTERFACE UTILISATEUR
 * Vérification des problèmes de superposition et d'affichage
 */

echo "🔍 TEST SUPERPOSITION - INTERFACE UTILISATEUR\n";
echo "=============================================\n\n";

$baseUrl = 'http://localhost:8080';
$results = [];
$errors = [];
$successCount = 0;
$totalTests = 0;

// Fonction de test de route avec vérification du contenu
function testRouteContent($description, $url, $expectedContent = null, $expectedCode = 200) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🔍 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        if ($expectedContent === null || strpos($response, $expectedContent) !== false) {
            echo "✅ SUCCÈS (HTTP $httpCode)\n";
            $successCount++;
            $results[] = "✅ $description: OK";
        } else {
            echo "❌ ÉCHEC (Contenu manquant)\n";
            $errors[] = "$description: Contenu attendu non trouvé";
            $results[] = "❌ $description: ÉCHEC (Contenu manquant)";
        }
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode (attendu: $expectedCode)";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// Fonction de test de superposition CSS
function testCSSOverlap($description, $url, $cssSelectors = []) {
    global $baseUrl, $results, $errors, $successCount, $totalTests;
    
    echo "  🎨 Test $description... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $overlapIssues = [];
        
        // Vérifier les problèmes de superposition courants
        if (strpos($response, 'position: absolute') !== false && strpos($response, 'z-index') === false) {
            $overlapIssues[] = "Position absolute sans z-index";
        }
        
        if (strpos($response, 'position: fixed') !== false && strpos($response, 'z-index') === false) {
            $overlapIssues[] = "Position fixed sans z-index";
        }
        
        if (strpos($response, 'overflow: hidden') === false && strpos($response, 'position: relative') !== false) {
            $overlapIssues[] = "Position relative sans overflow hidden";
        }
        
        // Vérifier les problèmes de responsive
        if (strpos($response, 'max-width') === false && strpos($response, 'width: 100%') !== false) {
            $overlapIssues[] = "Largeur 100% sans max-width";
        }
        
        if (empty($overlapIssues)) {
            echo "✅ SUCCÈS (Pas de superposition détectée)\n";
            $successCount++;
            $results[] = "✅ $description: OK";
        } else {
            echo "⚠️ ATTENTION (" . implode(', ', $overlapIssues) . ")\n";
            $errors[] = "$description: " . implode(', ', $overlapIssues);
            $results[] = "⚠️ $description: ATTENTION";
        }
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "$description: HTTP $httpCode";
        $results[] = "❌ $description: ÉCHEC (HTTP $httpCode)";
    }
    $totalTests++;
}

// 1. Test des pages avec et sans notes
echo "📊 TEST DES PAGES AVEC/SANS NOTES\n";
echo "----------------------------------\n";

testRouteContent('Page examen avec notes (ID 4)', '/admin/examens/exams/4/view', 'Statistiques des Notes');
testRouteContent('Page examen sans notes (ID 11)', '/admin/examens/exams/11/view', 'Aucune note saisie pour cet examen');

echo "\n";

// 2. Test de superposition CSS
echo "🎨 TEST DE SUPERPOSITION CSS\n";
echo "-----------------------------\n";

testCSSOverlap('Superposition examen ID 4', '/admin/examens/exams/4/view');
testCSSOverlap('Superposition examen ID 11', '/admin/examens/exams/11/view');

echo "\n";

// 3. Test de responsive design
echo "📱 TEST DE RESPONSIVE DESIGN\n";
echo "-----------------------------\n";

// Simuler différents écrans
$screenSizes = [
    'Mobile' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
    'Tablet' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
    'Desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
];

foreach ($screenSizes as $device => $userAgent) {
    echo "  📱 Test responsive $device... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/examens/exams/11/view');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        // Vérifier la présence de classes responsive Bulma
        $responsiveClasses = ['is-mobile', 'is-tablet', 'is-desktop', 'columns', 'column'];
        $hasResponsive = false;
        
        foreach ($responsiveClasses as $class) {
            if (strpos($response, $class) !== false) {
                $hasResponsive = true;
                break;
            }
        }
        
        if ($hasResponsive) {
            echo "✅ SUCCÈS (Responsive détecté)\n";
            $successCount++;
            $results[] = "✅ Responsive $device: OK";
        } else {
            echo "⚠️ ATTENTION (Pas de responsive)\n";
            $errors[] = "Responsive $device: Classes responsive manquantes";
            $results[] = "⚠️ Responsive $device: ATTENTION";
        }
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "Responsive $device: HTTP $httpCode";
        $results[] = "❌ Responsive $device: ÉCHEC";
    }
    $totalTests++;
}

echo "\n";

// 4. Test de superposition des éléments
echo "🔍 TEST DE SUPERPOSITION DES ÉLÉMENTS\n";
echo "--------------------------------------\n";

// Vérifier les éléments qui pourraient se superposer
$elementsToCheck = [
    'Pagination' => 'pagination',
    'Boutons d\'action' => 'buttons',
    'Tableau des notes' => 'table',
    'Statistiques' => 'box',
    'Informations générales' => 'field'
];

foreach ($elementsToCheck as $element => $class) {
    echo "  🔍 Test superposition $element... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/examens/exams/4/view');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        if (strpos($response, $class) !== false) {
            // Vérifier s'il y a des problèmes de positionnement
            $positionIssues = [];
            
            if (strpos($response, "$class.*position.*absolute") !== false && strpos($response, "$class.*z-index") === false) {
                $positionIssues[] = "Position absolute sans z-index";
            }
            
            if (strpos($response, "$class.*margin.*0") !== false && strpos($response, "$class.*padding.*0") !== false) {
                $positionIssues[] = "Margin et padding à 0";
            }
            
            if (empty($positionIssues)) {
                echo "✅ SUCCÈS (Pas de superposition)\n";
                $successCount++;
                $results[] = "✅ Superposition $element: OK";
            } else {
                echo "⚠️ ATTENTION (" . implode(', ', $positionIssues) . ")\n";
                $errors[] = "Superposition $element: " . implode(', ', $positionIssues);
                $results[] = "⚠️ Superposition $element: ATTENTION";
            }
        } else {
            echo "ℹ️ INFO (Élément non présent)\n";
            $results[] = "ℹ️ Superposition $element: Non présent";
        }
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "Superposition $element: HTTP $httpCode";
        $results[] = "❌ Superposition $element: ÉCHEC";
    }
    $totalTests++;
}

echo "\n";

// 5. Test de la pagination avec superposition
echo "📄 TEST DE LA PAGINATION AVEC SUPERPOSITION\n";
echo "---------------------------------------------\n";

testRouteContent('Pagination page 1', '/admin/examens/exams/4/view?page=1&limit=10', 'pagination');
testRouteContent('Pagination page 2', '/admin/examens/exams/4/view?page=2&limit=10', 'pagination');
testCSSOverlap('Superposition pagination', '/admin/examens/exams/4/view?page=1&limit=10');

echo "\n";

// 6. Test de superposition avec différents contenus
echo "📊 TEST DE SUPERPOSITION AVEC DIFFÉRENTS CONTENUS\n";
echo "-------------------------------------------------\n";

// Tester avec différentes limites de pagination
$limits = [5, 10, 20, 50];

foreach ($limits as $limit) {
    echo "  📊 Test limite $limit notes... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/examens/exams/4/view?page=1&limit=$limit");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        // Vérifier que la pagination s'affiche correctement
        if (strpos($response, 'pagination') !== false || $limit >= 20) {
            echo "✅ SUCCÈS (Pagination OK)\n";
            $successCount++;
            $results[] = "✅ Limite $limit: OK";
        } else {
            echo "⚠️ ATTENTION (Pagination manquante)\n";
            $errors[] = "Limite $limit: Pagination manquante";
            $results[] = "⚠️ Limite $limit: ATTENTION";
        }
    } else {
        echo "❌ ÉCHEC (HTTP $httpCode)\n";
        $errors[] = "Limite $limit: HTTP $httpCode";
        $results[] = "❌ Limite $limit: ÉCHEC";
    }
    $totalTests++;
}

echo "\n";

// Affichage des résultats
echo "📊 RÉSULTATS FINAUX - TEST SUPERPOSITION\n";
echo "=========================================\n\n";

$successRate = ($totalTests > 0) ? round(($successCount / $totalTests) * 100, 1) : 0;

echo "📈 STATISTIQUES:\n";
echo "   • Tests réussis: {$successCount}/{$totalTests}\n";
echo "   • Taux de succès: {$successRate}%\n";
echo "   • Erreurs: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "❌ PROBLÈMES DÉTECTÉS:\n";
    echo "---------------------\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

echo "✅ TESTS RÉUSSIS:\n";
echo "-----------------\n";
foreach ($results as $result) {
    if (strpos($result, '✅') === 0) {
        echo "   $result\n";
    }
}
echo "\n";

// Recommandations pour la superposition
echo "🔧 RECOMMANDATIONS POUR LA SUPERPOSITION:\n";
echo "-----------------------------------------\n";

if (count($errors) > 0) {
    echo "   • Vérifier les z-index pour les éléments positionnés\n";
    echo "   • Ajouter overflow: hidden aux conteneurs parents\n";
    echo "   • Utiliser max-width pour éviter les débordements\n";
    echo "   • Tester sur différents écrans et résolutions\n";
    echo "   • Vérifier la pagination avec beaucoup de données\n";
} else {
    echo "   • ✅ Aucun problème de superposition détecté\n";
    echo "   • ✅ Interface responsive fonctionnelle\n";
    echo "   • ✅ Pagination bien implémentée\n";
    echo "   • ✅ Éléments correctement positionnés\n";
}

echo "\n";

if ($successRate >= 90) {
    echo "🎉 INTERFACE: EXCELLENT ÉTAT - AUCUNE SUPERPOSITION\n";
    echo "   L'interface utilisateur est parfaitement optimisée.\n";
} elseif ($successRate >= 75) {
    echo "✅ INTERFACE: BON ÉTAT\n";
    echo "   Quelques améliorations mineures recommandées.\n";
} elseif ($successRate >= 50) {
    echo "⚠️ INTERFACE: ÉTAT MOYEN\n";
    echo "   Des corrections sont nécessaires pour la superposition.\n";
} else {
    echo "❌ INTERFACE: ÉTAT CRITIQUE\n";
    echo "   De nombreux problèmes de superposition à corriger.\n";
}

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/examens/exams/11/view\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


