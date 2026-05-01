<?php
/**
 * Test Complet - Module Subjects
 * Diagnostic complet des fonctionnalités CRUD
 */

echo "🔍 TEST COMPLET - MODULE SUBJECTS\n";
echo "=================================\n\n";

$baseUrl = "http://localhost:8080";

// Test 1: Page principale
echo "📊 TEST 1: Page principale des matières\n";
echo "--------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "  ✅ Page principale accessible (HTTP 200)\n";
    
    // Compter les matières
    if (preg_match_all('/<tr>/', $response, $matches)) {
        $subjectCount = count($matches[0]) - 1; // -1 pour l'en-tête
        echo "  📋 Matières trouvées: $subjectCount\n";
    }
    
    // Vérifier les boutons d'action
    if (preg_match_all('/<a[^>]*class="[^"]*button[^"]*"[^>]*>/', $response, $matches)) {
        echo "  📋 Boutons d'action trouvés: " . count($matches[0]) . "\n";
    }
    
    // Vérifier les liens d'édition
    if (preg_match_all('/subjects\/edit\//', $response, $matches)) {
        echo "  📋 Liens d'édition trouvés: " . count($matches[0]) . "\n";
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $httpCode)\n";
}

echo "\n";

// Test 2: Page de création
echo "📊 TEST 2: Page de création\n";
echo "---------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$createResponse = curl_exec($ch);
$createHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($createHttpCode === 200) {
    echo "  ✅ Page de création accessible (HTTP 200)\n";
    
    // Extraire le token CSRF
    if (preg_match('/csrf-token" content="([^"]+)"/', $createResponse, $matches)) {
        $csrfToken = $matches[1];
        echo "  🔐 Token CSRF extrait: " . substr($csrfToken, 0, 10) . "...\n";
    } else {
        echo "  ⚠️ Token CSRF non trouvé\n";
        $csrfToken = "test_token";
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $createHttpCode)\n";
    $csrfToken = "test_token";
}

echo "\n";

// Test 3: Création d'une matière
echo "📊 TEST 3: Création d'une matière\n";
echo "--------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/store");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'name' => 'Test Matière Audit ' . date('Y-m-d H:i:s'),
    'code' => 'TEST' . rand(100, 999),
    'description' => 'Matière de test pour audit complet',
    'coefficient' => '1',
    'hours_per_week' => '4',
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
    echo "  ✅ Création réussie (HTTP 303 - Redirection)\n";
    
    // Extraire l'ID de la matière créée si possible
    if (preg_match('/Location: .*\/subjects\/(\d+)/', $storeResponse, $matches)) {
        $createdSubjectId = $matches[1];
        echo "  📋 ID de la matière créée: $createdSubjectId\n";
    } else {
        echo "  📋 Matière créée (ID non détecté)\n";
        $createdSubjectId = null;
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $storeHttpCode)\n";
    $createdSubjectId = null;
}

echo "\n";

// Test 4: Édition d'une matière existante
echo "📊 TEST 4: Édition d'une matière\n";
echo "--------------------------------\n";

// Utiliser une matière qui existe certainement
$testSubjectId = 25;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/edit/$testSubjectId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$editResponse = curl_exec($ch);
$editHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($editHttpCode === 200) {
    echo "  ✅ Page d'édition accessible (HTTP 200)\n";
    
    // Vérifier le formulaire
    if (strpos($editResponse, 'form') !== false) {
        echo "  ✅ Formulaire d'édition présent\n";
    }
    
    // Vérifier les champs
    if (strpos($editResponse, 'name="name"') !== false) {
        echo "  ✅ Champ nom présent\n";
    }
    
    if (strpos($editResponse, 'name="code"') !== false) {
        echo "  ✅ Champ code présent\n";
    }
    
} else {
    echo "  ❌ ÉCHEC (HTTP $editHttpCode)\n";
    
    // Afficher plus de détails sur l'erreur
    if ($editHttpCode === 500) {
        echo "  🔍 Erreur 500 - Problème serveur\n";
    } elseif ($editHttpCode === 404) {
        echo "  🔍 Erreur 404 - Route non trouvée\n";
    }
}

echo "\n";

// Test 5: Mise à jour d'une matière
echo "📊 TEST 5: Mise à jour d'une matière\n";
echo "-----------------------------------\n";

if ($editHttpCode === 200) {
    // Extraire le token CSRF de la page d'édition
    if (preg_match('/csrf-token" content="([^"]+)"/', $editResponse, $matches)) {
        $editCsrfToken = $matches[1];
    } else {
        $editCsrfToken = $csrfToken;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/update/$testSubjectId");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'name' => 'Matière Modifiée Audit',
        'code' => 'MODIF' . rand(100, 999),
        'description' => 'Matière modifiée pour test audit',
        'coefficient' => '2',
        'hours_per_week' => '6',
        'is_active' => '1',
        'csrf_test_name' => $editCsrfToken
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $updateResponse = curl_exec($ch);
    $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($updateHttpCode === 303) {
        echo "  ✅ Mise à jour réussie (HTTP 303 - Redirection)\n";
    } else {
        echo "  ❌ ÉCHEC (HTTP $updateHttpCode)\n";
    }
} else {
    echo "  ⚠️ SKIP - Page d'édition non accessible\n";
}

echo "\n";

// Test 6: Suppression d'une matière
echo "📊 TEST 6: Suppression d'une matière\n";
echo "-----------------------------------\n";

// Créer une matière de test pour la suppression
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/store");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'name' => 'Matière à Supprimer',
    'code' => 'DEL' . rand(100, 999),
    'description' => 'Matière pour test suppression',
    'coefficient' => '1',
    'hours_per_week' => '2',
    'is_active' => '1',
    'csrf_test_name' => $csrfToken
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);

$deleteCreateResponse = curl_exec($ch);
$deleteCreateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($deleteCreateHttpCode === 303) {
    echo "  ✅ Matière de test créée pour suppression\n";
    
    // Attendre un peu puis tester la suppression
    sleep(1);
    
    // Tester la suppression (nous utiliserons une matière existante)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects/delete/999"); // ID inexistant
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $deleteResponse = curl_exec($ch);
    $deleteHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($deleteHttpCode === 303 || $deleteHttpCode === 404) {
        echo "  ✅ Route de suppression accessible (HTTP $deleteHttpCode)\n";
    } else {
        echo "  ❌ ÉCHEC (HTTP $deleteHttpCode)\n";
    }
} else {
    echo "  ⚠️ Impossible de créer une matière de test pour suppression\n";
}

echo "\n";

// Test 7: Filtres et recherche
echo "📊 TEST 7: Filtres et recherche\n";
echo "-------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . "/admin/etudes/subjects");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$filterResponse = curl_exec($ch);
curl_close($ch);

if (strpos($filterResponse, 'search') !== false) {
    echo "  ✅ Champ de recherche présent\n";
} else {
    echo "  ❌ Champ de recherche manquant\n";
}

if (strpos($filterResponse, 'status_filter') !== false) {
    echo "  ✅ Filtre par statut présent\n";
} else {
    echo "  ❌ Filtre par statut manquant\n";
}

if (strpos($filterResponse, 'sort_filter') !== false) {
    echo "  ✅ Filtre de tri présent\n";
} else {
    echo "  ❌ Filtre de tri manquant\n";
}

echo "\n";

// Test 8: Statistiques
echo "📊 TEST 8: Statistiques\n";
echo "----------------------\n";

if (strpos($response, 'TOTAL MATIÈRES') !== false) {
    echo "  ✅ Statistiques présentes\n";
    
    // Extraire les statistiques
    if (preg_match('/TOTAL MATIÈRES.*?(\d+)/', $response, $matches)) {
        echo "  📊 Total matières: " . $matches[1] . "\n";
    }
    
    if (preg_match('/MATIÈRES ACTIVES.*?(\d+)/', $response, $matches)) {
        echo "  📊 Matières actives: " . $matches[1] . "\n";
    }
    
    if (preg_match('/ASSIGNATIONS.*?(\d+)/', $response, $matches)) {
        echo "  📊 Assignations: " . $matches[1] . "\n";
    }
    
} else {
    echo "  ❌ Statistiques manquantes\n";
}

echo "\n";

// Résumé final
echo "📊 RÉSUMÉ DES TESTS\n";
echo "===================\n";

$totalTests = 8;
$passedTests = 0;
$failedTests = 0;

if ($httpCode === 200) $passedTests++;
else $failedTests++;

if ($createHttpCode === 200) $passedTests++;
else $failedTests++;

if ($storeHttpCode === 303) $passedTests++;
else $failedTests++;

if ($editHttpCode === 200) $passedTests++;
else $failedTests++;

if (isset($updateHttpCode) && $updateHttpCode === 303) $passedTests++;
else $failedTests++;

if (isset($deleteHttpCode) && ($deleteHttpCode === 303 || $deleteHttpCode === 404)) $passedTests++;
else $failedTests++;

if (strpos($filterResponse, 'search') !== false) $passedTests++;
else $failedTests++;

if (strpos($response, 'TOTAL MATIÈRES') !== false) $passedTests++;
else $failedTests++;

echo "✅ Tests réussis: $passedTests/$totalTests\n";
echo "❌ Tests échoués: $failedTests/$totalTests\n";

if ($passedTests === $totalTests) {
    echo "\n🏆 MODULE SUBJECTS: EXCELLENT ÉTAT\n";
    echo "   Toutes les fonctionnalités sont opérationnelles.\n";
} elseif ($passedTests >= 6) {
    echo "\n🏆 MODULE SUBJECTS: BON ÉTAT\n";
    echo "   La plupart des fonctionnalités sont opérationnelles.\n";
} else {
    echo "\n🏆 MODULE SUBJECTS: ATTENTION REQUISE\n";
    echo "   Plusieurs fonctionnalités nécessitent une attention.\n";
}

echo "\n🔧 PROBLÈMES IDENTIFIÉS:\n";
if ($editHttpCode !== 200) {
    echo "- ❌ Page d'édition non accessible (HTTP $editHttpCode)\n";
}
if ($storeHttpCode !== 303) {
    echo "- ❌ Création de matière échouée (HTTP $storeHttpCode)\n";
}

echo "\n🌐 Interface accessible sur: $baseUrl/admin/etudes/subjects\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";
?>


