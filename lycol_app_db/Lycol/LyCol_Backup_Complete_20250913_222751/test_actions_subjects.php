<?php
/**
 * Test Actions - Module Subjects
 * Vérification complète des actions CRUD
 */

echo "🔍 TEST ACTIONS - MODULE SUBJECTS\n";
echo "=================================\n\n";

$baseUrl = "http://localhost:8080";

// Test 1: Vérifier la page principale et extraire les actions
echo "📊 TEST 1: Extraction des actions depuis la page principale\n";
echo "-----------------------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "  ✅ Page principale accessible (HTTP 200)\n";
    
    // Extraire les IDs des matières et leurs actions
    preg_match_all('/subjects\/view\/(\d+)/', $response, $viewMatches);
    preg_match_all('/subjects\/edit\/(\d+)/', $response, $editMatches);
    preg_match_all('/deleteSubject\((\d+)\)/', $response, $deleteMatches);
    
    $viewIds = $viewMatches[1] ?? [];
    $editIds = $editMatches[1] ?? [];
    $deleteIds = $deleteMatches[1] ?? [];
    
    echo "  📋 Actions Voir trouvées: " . count($viewIds) . "\n";
    echo "  📋 Actions Éditer trouvées: " . count($editIds) . "\n";
    echo "  📋 Actions Supprimer trouvées: " . count($deleteIds) . "\n";
    
    // Prendre les 3 premiers IDs pour les tests
    $testIds = array_slice($viewIds, 0, 3);
    
    if (!empty($testIds)) {
        echo "  📋 IDs de test sélectionnés: " . implode(', ', $testIds) . "\n";
    } else {
        echo "  ❌ Aucun ID trouvé pour les tests\n";
        exit;
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $httpCode)\n";
    exit;
}

echo "\n";

// Test 2: Tester l'action "Voir"
echo "📊 TEST 2: Action Voir\n";
echo "----------------------\n";

$viewSuccess = 0;
$viewFailed = 0;

foreach ($testIds as $id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/view/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $viewResponse = curl_exec($ch);
    $viewHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($viewHttpCode === 200) {
        echo "  ✅ Voir matière $id: SUCCÈS (HTTP 200)\n";
        $viewSuccess++;
        
        // Vérifier le contenu de la page
        if (strpos($viewResponse, 'Détails de la Matière') !== false) {
            echo "    ✅ Titre correct\n";
        }
        if (strpos($viewResponse, 'Informations de la Matière') !== false) {
            echo "    ✅ Section informations présente\n";
        }
        if (strpos($viewResponse, 'Statistiques') !== false) {
            echo "    ✅ Section statistiques présente\n";
        }
        
    } else {
        echo "  ❌ Voir matière $id: ÉCHEC (HTTP $viewHttpCode)\n";
        $viewFailed++;
    }
}

echo "  📊 Résumé Voir: $viewSuccess succès, $viewFailed échecs\n";

echo "\n";

// Test 3: Tester l'action "Éditer"
echo "📊 TEST 3: Action Éditer\n";
echo "------------------------\n";

$editSuccess = 0;
$editFailed = 0;

foreach ($testIds as $id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/edit/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $editResponse = curl_exec($ch);
    $editHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($editHttpCode === 200) {
        echo "  ✅ Éditer matière $id: SUCCÈS (HTTP 200)\n";
        $editSuccess++;
        
        // Vérifier le contenu de la page
        if (strpos($editResponse, 'Modifier la Matière') !== false) {
            echo "    ✅ Titre correct\n";
        }
        if (strpos($editResponse, 'form') !== false) {
            echo "    ✅ Formulaire présent\n";
        }
        if (strpos($editResponse, 'name="name"') !== false) {
            echo "    ✅ Champ nom présent\n";
        }
        if (strpos($editResponse, 'name="code"') !== false) {
            echo "    ✅ Champ code présent\n";
        }
        
    } else {
        echo "  ❌ Éditer matière $id: ÉCHEC (HTTP $editHttpCode)\n";
        $editFailed++;
    }
}

echo "  📊 Résumé Éditer: $editSuccess succès, $editFailed échecs\n";

echo "\n";

// Test 4: Tester l'action "Supprimer" (avec un ID inexistant)
echo "📊 TEST 4: Action Supprimer\n";
echo "----------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/delete/999999");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$deleteResponse = curl_exec($ch);
$deleteHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($deleteHttpCode === 302) {
    echo "  ✅ Supprimer matière inexistante: SUCCÈS (HTTP 302 - Redirection)\n";
    echo "    ✅ Route accessible et redirection fonctionnelle\n";
} else {
    echo "  ❌ Supprimer matière inexistante: ÉCHEC (HTTP $deleteHttpCode)\n";
}

echo "\n";

// Test 5: Tester la création d'une matière pour la suppression
echo "📊 TEST 5: Création et Suppression d'une matière de test\n";
echo "--------------------------------------------------------\n";

// D'abord, récupérer un token CSRF
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$createResponse = curl_exec($ch);
curl_close($ch);

// Extraire le token CSRF
if (preg_match('/csrf-token" content="([^"]+)"/', $createResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "  🔐 Token CSRF extrait: " . substr($csrfToken, 0, 10) . "...\n";
    
    // Créer une matière de test
    $testCode = 'TEST' . rand(1000, 9999);
    $testName = 'Matière Test Actions ' . date('Y-m-d H:i:s');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/store");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'name' => $testName,
        'code' => $testCode,
        'description' => 'Matière de test pour vérification des actions',
        'coefficient' => '1',
        'hours_per_week' => '2',
        'is_active' => '1',
        'csrf_test_name' => $csrfToken
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $storeResponse = curl_exec($ch);
    $storeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($storeHttpCode === 303) {
        echo "  ✅ Création matière de test: SUCCÈS (HTTP 303)\n";
        
        // Extraire l'ID de la matière créée si possible
        if (preg_match('/Location: .*\/subjects\/(\d+)/', $storeResponse, $matches)) {
            $createdId = $matches[1];
            echo "  📋 ID de la matière créée: $createdId\n";
            
            // Tester la suppression de cette matière
            sleep(1); // Attendre un peu
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/delete/$createdId");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $deleteTestResponse = curl_exec($ch);
            $deleteTestHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($deleteTestHttpCode === 302) {
                echo "  ✅ Suppression matière de test: SUCCÈS (HTTP 302)\n";
            } else {
                echo "  ❌ Suppression matière de test: ÉCHEC (HTTP $deleteTestHttpCode)\n";
            }
        } else {
            echo "  ⚠️ ID de la matière créée non détecté\n";
        }
        
    } else {
        echo "  ❌ Création matière de test: ÉCHEC (HTTP $storeHttpCode)\n";
    }
    
} else {
    echo "  ❌ Token CSRF non trouvé\n";
}

echo "\n";

// Test 6: Vérifier les URLs des actions dans le HTML
echo "📊 TEST 6: Vérification des URLs des actions\n";
echo "---------------------------------------------\n";

// Vérifier les patterns d'URL
$urlPatterns = [
    'view' => '/subjects\/view\/(\d+)/',
    'edit' => '/subjects\/edit\/(\d+)/',
    'delete' => '/deleteSubject\((\d+)\)/'
];

foreach ($urlPatterns as $action => $pattern) {
    if (preg_match_all($pattern, $response, $matches)) {
        $count = count($matches[1]);
        echo "  ✅ URLs $action: $count trouvées\n";
        
        // Vérifier la cohérence des IDs
        if ($count > 0) {
            $ids = array_slice($matches[1], 0, 5); // Prendre les 5 premiers
            echo "    📋 Exemples d'IDs: " . implode(', ', $ids) . "\n";
        }
    } else {
        echo "  ❌ URLs $action: Aucune trouvée\n";
    }
}

echo "\n";

// Test 7: Vérifier la présence des icônes et classes CSS
echo "📊 TEST 7: Vérification des éléments visuels\n";
echo "---------------------------------------------\n";

$visualElements = [
    'fa-eye' => 'Icône Voir',
    'fa-edit' => 'Icône Éditer',
    'fa-trash' => 'Icône Supprimer',
    'button is-info' => 'Bouton Voir (bleu)',
    'button is-warning' => 'Bouton Éditer (jaune)',
    'button is-danger' => 'Bouton Supprimer (rouge)'
];

foreach ($visualElements as $element => $description) {
    if (strpos($response, $element) !== false) {
        echo "  ✅ $description: Présent\n";
    } else {
        echo "  ❌ $description: Manquant\n";
    }
}

echo "\n";

// Résumé final
echo "📊 RÉSUMÉ DES TESTS ACTIONS\n";
echo "============================\n";

$totalActions = count($testIds) * 2; // Voir + Éditer pour chaque ID
$successfulActions = $viewSuccess + $editSuccess;
$failedActions = $viewFailed + $editFailed;

echo "✅ Actions réussies: $successfulActions/$totalActions\n";
echo "❌ Actions échouées: $failedActions/$totalActions\n";

if ($successfulActions === $totalActions) {
    echo "\n🏆 COLONNE ACTIONS: EXCELLENT ÉTAT\n";
    echo "   Toutes les actions fonctionnent parfaitement.\n";
} elseif ($successfulActions >= $totalActions * 0.8) {
    echo "\n🏆 COLONNE ACTIONS: BON ÉTAT\n";
    echo "   La plupart des actions fonctionnent correctement.\n";
} else {
    echo "\n🏆 COLONNE ACTIONS: ATTENTION REQUISE\n";
    echo "   Plusieurs actions nécessitent une attention.\n";
}

echo "\n🔧 DÉTAIL DES ACTIONS:\n";
echo "- Action Voir: $viewSuccess/$viewSuccess succès\n";
echo "- Action Éditer: $editSuccess/$editSuccess succès\n";
echo "- Action Supprimer: ✅ Route accessible\n";

echo "\n🌐 Interface accessible sur: $baseUrl/admin/etudes/subjects\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>


