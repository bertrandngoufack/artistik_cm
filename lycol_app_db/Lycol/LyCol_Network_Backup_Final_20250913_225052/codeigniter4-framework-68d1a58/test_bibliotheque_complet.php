<?php
/**
 * Test Complet du Module Bibliothèque - LyCol
 * Vérification des fonctionnalités CRUD et cohérence avec les autres modules
 */

echo "=== TEST COMPLET MODULE BIBLIOTHÈQUE LYSCOL ===\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$adminUrl = $baseUrl . '/admin/bibliotheque';

// Fonction pour tester les URLs
function testUrl($url, $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = $httpCode == 200 ? "✅ OK" : "❌ ERREUR ($httpCode)";
    echo "$description: $status\n";
    
    return $httpCode == 200;
}

// Fonction pour tester les requêtes POST
function testPost($url, $data, $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200 || $httpCode == 302) ? "✅ OK" : "❌ ERREUR ($httpCode)";
    echo "$description: $status\n";
    
    return ($httpCode == 200 || $httpCode == 302);
}

echo "1. TEST DES PAGES PRINCIPALES\n";
echo "============================\n";

$pages = [
    $adminUrl => "Page principale bibliothèque",
    $adminUrl . '/books' => "Gestion des livres",
    $adminUrl . '/books/add' => "Ajout de livre",
    $adminUrl . '/books/create' => "Création de livre",
    $adminUrl . '/loans' => "Gestion des emprunts",
    $adminUrl . '/loans/create' => "Création d'emprunt",
    $adminUrl . '/members' => "Gestion des membres",
    $adminUrl . '/reports' => "Rapports bibliothèque"
];

$successCount = 0;
foreach ($pages as $url => $description) {
    if (testUrl($url, $description)) {
        $successCount++;
    }
}

echo "\nRésultat: $successCount/" . count($pages) . " pages fonctionnelles\n\n";

echo "2. TEST DES FONCTIONNALITÉS CRUD\n";
echo "================================\n";

// Test d'ajout de livre
$bookData = [
    'title' => 'Test Livre CRUD',
    'author' => 'Test Auteur',
    'isbn' => '1234567890123',
    'copies' => '5'
];

$crudTests = [
    ['url' => $adminUrl . '/books/store', 'data' => $bookData, 'description' => 'Ajout de livre (POST)'],
    ['url' => $adminUrl . '/loans/store', 'data' => [
        'book_id' => '1',
        'borrower_name' => 'Test Emprunteur',
        'loan_date' => '2025-08-25',
        'due_date' => '2025-09-25'
    ], 'description' => 'Ajout d\'emprunt (POST)']
];

$crudSuccessCount = 0;
foreach ($crudTests as $test) {
    if (testPost($test['url'], $test['data'], $test['description'])) {
        $crudSuccessCount++;
    }
}

echo "\nRésultat: $crudSuccessCount/" . count($crudTests) . " opérations CRUD fonctionnelles\n\n";

echo "3. VÉRIFICATION DE LA COHÉRENCE AVEC LES AUTRES MODULES\n";
echo "======================================================\n";

$modules = [
    '/admin/economat' => 'Module Économat',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/enseignants' => 'Module Enseignants',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/messagerie' => 'Module Messagerie',
    '/admin/securite' => 'Module Sécurité'
];

$coherenceCount = 0;
foreach ($modules as $url => $description) {
    if (testUrl($baseUrl . $url, "Cohérence avec $description")) {
        $coherenceCount++;
    }
}

echo "\nRésultat: $coherenceCount/" . count($modules) . " modules cohérents\n\n";

echo "4. VÉRIFICATION DES LIENS DE NAVIGATION\n";
echo "======================================\n";

// Test des liens de navigation dans la bibliothèque
$navLinks = [
    $baseUrl . '/admin/dashboard' => 'Dashboard principal',
    $baseUrl . '/admin/statistiques' => 'Statistiques',
    $baseUrl . '/admin/scolarite' => 'Scolarité'
];

$navCount = 0;
foreach ($navLinks as $url => $description) {
    if (testUrl($url, "Navigation vers $description")) {
        $navCount++;
    }
}

echo "\nRésultat: $navCount/" . count($navLinks) . " liens de navigation fonctionnels\n\n";

echo "5. RÉSUMÉ FINAL\n";
echo "===============\n";

$totalTests = count($pages) + count($crudTests) + count($modules) + count($navLinks);
$totalSuccess = $successCount + $crudSuccessCount + $coherenceCount + $navCount;

echo "Tests totaux: $totalTests\n";
echo "Tests réussis: $totalSuccess\n";
echo "Taux de réussite: " . round(($totalSuccess / $totalTests) * 100, 1) . "%\n\n";

if ($totalSuccess == $totalTests) {
    echo "🎉 MODULE BIBLIOTHÈQUE 100% FONCTIONNEL !\n";
} elseif ($totalSuccess >= $totalTests * 0.8) {
    echo "✅ MODULE BIBLIOTHÈQUE FONCTIONNEL (quelques améliorations possibles)\n";
} else {
    echo "⚠️ MODULE BIBLIOTHÈQUE NÉCESSITE DES CORRECTIONS\n";
}

echo "\n=== FIN DU TEST ===\n";
?>
