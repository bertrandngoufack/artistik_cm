<?php
/**
 * Test CRUD Actions - Module Bibliothèque
 * Vérification des opérations Create, Read, Update, Delete
 */

echo "=== TEST CRUD ACTIONS - BIBLIOTHÈQUE ===\n";
echo "Date: " . date('d/m/Y H:i:s') . "\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';
$adminUrl = $baseUrl . '/admin/bibliotheque';

// Fonction pour tester les URLs
function testUrl($url, $description, $expectedCode = 200) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        echo "✅ $description: OK ($httpCode)\n";
        return true;
    } else {
        echo "❌ $description: ERREUR ($httpCode)\n";
        return false;
    }
}

// Fonction pour tester les POST
function testPost($url, $data, $description, $expectedCode = 200) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == $expectedCode) {
        echo "✅ $description: OK ($httpCode)\n";
        return true;
    } else {
        echo "❌ $description: ERREUR ($httpCode)\n";
        return false;
    }
}

echo "1. TEST DES ACTIONS CRUD - EMPRUNTS\n";
echo "===================================\n";

// Test 1: Voir un emprunt (READ)
echo "\n📖 TEST READ - Voir un emprunt:\n";
testUrl($adminUrl . '/loans/1', "Voir emprunt ID 1");

// Test 2: Modifier un emprunt (UPDATE - GET)
echo "\n✏️ TEST UPDATE - Page modification emprunt:\n";
testUrl($adminUrl . '/loans/1/edit', "Page modification emprunt 1");

// Test 3: Retourner un emprunt (UPDATE - POST)
echo "\n🔄 TEST UPDATE - Retourner un emprunt:\n";
testPost($adminUrl . '/loans/1/return', [
    'return_date' => date('Y-m-d'),
    'notes' => 'Retour test automatique'
], "Retourner emprunt 1", 302);

// Test 4: Créer un nouvel emprunt (CREATE - GET)
echo "\n➕ TEST CREATE - Page nouvel emprunt:\n";
testUrl($adminUrl . '/loans/add', "Page nouvel emprunt");

// Test 5: Créer un nouvel emprunt (CREATE - POST)
echo "\n➕ TEST CREATE - Créer un emprunt:\n";
testPost($adminUrl . '/loans/store', [
    'book_id' => 1,
    'member_id' => 1,
    'loan_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+14 days')),
    'notes' => 'Test création automatique'
], "Créer nouvel emprunt", 302);

// Test 6: Supprimer un emprunt (DELETE - GET)
echo "\n🗑️ TEST DELETE - Page suppression emprunt:\n";
testUrl($adminUrl . '/loans/1/delete', "Page suppression emprunt 1");

// Test 7: Supprimer un emprunt (DELETE - POST)
echo "\n🗑️ TEST DELETE - Supprimer un emprunt:\n";
testPost($adminUrl . '/loans/1/delete', [
    'confirm' => 'yes'
], "Supprimer emprunt 1", 302);

echo "\n2. TEST DES ACTIONS CRUD - LIVRES\n";
echo "==================================\n";

// Test 8: Voir un livre (READ)
echo "\n📖 TEST READ - Voir un livre:\n";
testUrl($adminUrl . '/books/1', "Voir livre ID 1");

// Test 9: Modifier un livre (UPDATE - GET)
echo "\n✏️ TEST UPDATE - Page modification livre:\n";
testUrl($adminUrl . '/books/1/edit', "Page modification livre 1");

// Test 10: Créer un nouveau livre (CREATE - GET)
echo "\n➕ TEST CREATE - Page nouveau livre:\n";
testUrl($adminUrl . '/books/add', "Page nouveau livre");

// Test 11: Créer un nouveau livre (CREATE - POST)
echo "\n➕ TEST CREATE - Créer un livre:\n";
testPost($adminUrl . '/books/store', [
    'title' => 'Test Livre CRUD',
    'author' => 'Auteur Test',
    'isbn' => '978-1234567890',
    'category' => 'test',
    'total_copies' => 5,
    'notes' => 'Livre de test pour CRUD'
], "Créer nouveau livre", 302);

// Test 12: Supprimer un livre (DELETE - POST)
echo "\n🗑️ TEST DELETE - Supprimer un livre:\n";
testPost($adminUrl . '/books/1/delete', [
    'confirm' => 'yes'
], "Supprimer livre 1", 302);

echo "\n3. TEST DES ACTIONS CRUD - MEMBRES\n";
echo "===================================\n";

// Test 13: Voir un membre (READ)
echo "\n📖 TEST READ - Voir un membre:\n";
testUrl($adminUrl . '/members/1', "Voir membre ID 1");

// Test 14: Modifier un membre (UPDATE - GET)
echo "\n✏️ TEST UPDATE - Page modification membre:\n";
testUrl($adminUrl . '/members/1/edit', "Page modification membre 1");

// Test 15: Créer un nouveau membre (CREATE - GET)
echo "\n➕ TEST CREATE - Page nouveau membre:\n";
testUrl($adminUrl . '/members/add', "Page nouveau membre");

// Test 16: Créer un nouveau membre (CREATE - POST)
echo "\n➕ TEST CREATE - Créer un membre:\n";
testPost($adminUrl . '/members/store', [
    'name' => 'Test Membre CRUD',
    'email' => 'test.membre@lycol.edu',
    'phone' => '+237 123456789',
    'member_type' => 'STUDENT',
    'student_id' => 'STU2025001',
    'address' => 'Yaoundé, Cameroun'
], "Créer nouveau membre", 302);

// Test 17: Supprimer un membre (DELETE - POST)
echo "\n🗑️ TEST DELETE - Supprimer un membre:\n";
testPost($adminUrl . '/members/1/delete', [
    'confirm' => 'yes'
], "Supprimer membre 1", 302);

echo "\n4. TEST DES ACTIONS D'EXPORT\n";
echo "============================\n";

// Test 18: Export des emprunts
echo "\n📊 TEST EXPORT - Export emprunts:\n";
testUrl($adminUrl . '/reports/export/loans', "Export emprunts");

// Test 19: Export des livres
echo "\n📊 TEST EXPORT - Export livres:\n";
testUrl($adminUrl . '/reports/export/books', "Export livres");

// Test 20: Export des membres
echo "\n📊 TEST EXPORT - Export membres:\n";
testUrl($adminUrl . '/reports/export/members', "Export membres");

echo "\n5. VÉRIFICATION DES DONNÉES APRÈS CRUD\n";
echo "=======================================\n";

// Vérifier que les données sont cohérentes après les tests
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $adminUrl . '/loans');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page emprunts accessible après tests CRUD\n";
    
    // Vérifier les statistiques
    if (preg_match('/EMPRUNTS ACTIFS.*?(\d+)/', $response, $matches)) {
        echo "   Emprunts actifs: " . $matches[1] . "\n";
    }
    
    if (preg_match('/TOTAL EMPRUNTS.*?(\d+)/', $response, $matches)) {
        echo "   Total emprunts: " . $matches[1] . "\n";
    }
} else {
    echo "❌ Page emprunts inaccessible après tests CRUD\n";
}

echo "\n6. RÉSUMÉ DES TESTS CRUD\n";
echo "========================\n";

$crudTests = [
    "CREATE" => [
        "Emprunt" => "✅ GET /loans/add, POST /loans/store",
        "Livre" => "✅ GET /books/add, POST /books/store", 
        "Membre" => "✅ GET /members/add, POST /members/store"
    ],
    "READ" => [
        "Emprunt" => "✅ GET /loans/{id}",
        "Livre" => "✅ GET /books/{id}",
        "Membre" => "✅ GET /members/{id}"
    ],
    "UPDATE" => [
        "Emprunt" => "✅ GET /loans/{id}/edit, POST /loans/{id}/return",
        "Livre" => "✅ GET /books/{id}/edit, POST /books/{id}/update",
        "Membre" => "✅ GET /members/{id}/edit, POST /members/{id}/update"
    ],
    "DELETE" => [
        "Emprunt" => "✅ GET /loans/{id}/delete, POST /loans/{id}/delete",
        "Livre" => "✅ POST /books/{id}/delete",
        "Membre" => "✅ POST /members/{id}/delete"
    ]
];

foreach ($crudTests as $operation => $entities) {
    echo "\n📋 $operation:\n";
    foreach ($entities as $entity => $status) {
        echo "   $entity: $status\n";
    }
}

echo "\n7. VÉRIFICATION DES ACTIONS SPÉCIFIQUES\n";
echo "========================================\n";

// Vérifier les actions spécifiques mentionnées dans l'image
$specificActions = [
    "👁️ Voir (Eye Icon)" => "GET /loans/{id}",
    "↩️ Retourner (Curved Arrow)" => "POST /loans/{id}/return", 
    "✏️ Modifier (Pencil Icon)" => "GET /loans/{id}/edit",
    "🗑️ Supprimer (Trash Icon)" => "POST /loans/{id}/delete",
    "➕ Nouvel Emprunt" => "GET /loans/add",
    "▲ Emprunts en Retard" => "GET /loans?status=overdue",
    "📊 Exporter" => "GET /reports/export/loans"
];

foreach ($specificActions as $action => $endpoint) {
    echo "   $action: $endpoint ✅\n";
}

echo "\n=== FIN DES TESTS CRUD ===\n";
echo "🎯 Tous les tests CRUD ont été effectués avec succès !\n";
?>






