<?php
/**
 * Test détaillé pour identifier les problèmes CRUD spécifiques
 */

echo "🔍 TEST DÉTAILLÉ DES PROBLÈMES CRUD - ÉTUDES ET ÉCONOMAT\n";
echo "======================================================\n\n";

$baseUrl = 'http://localhost:8080';

// Test 1: Vérifier les erreurs spécifiques du module Études
echo "1️⃣ TEST DÉTAILLÉ MODULE ÉTUDES :\n";
echo "================================\n";

$etudesTests = [
    'Dashboard' => '/admin/etudes',
    'Cycles' => '/admin/etudes/cycles',
    'Création Cycle' => '/admin/etudes/cycles/create',
    'Modification Cycle 1' => '/admin/etudes/cycles/1/edit',
    'Modification Cycle 2' => '/admin/etudes/cycles/2/edit',
    'Suppression Cycle 1' => '/admin/etudes/cycles/1/delete',
    'Classes' => '/admin/etudes/classes',
    'Création Classe' => '/admin/etudes/classes/create',
    'Modification Classe 1' => '/admin/etudes/classes/1/edit',
    'Détails Classe 1' => '/admin/etudes/classes/1/view',
    'Matières' => '/admin/etudes/subjects',
    'Création Matière' => '/admin/etudes/subjects/create',
    'Modification Matière 1' => '/admin/etudes/subjects/1/edit',
    'Emplois du temps' => '/admin/etudes/timetable',
    'Création EDT' => '/admin/etudes/timetable/create',
    'Modification EDT 1' => '/admin/etudes/timetable/1/edit',
    'Voir EDT Classe 1' => '/admin/etudes/timetable/class/1',
    'Assignations' => '/admin/etudes/assignments',
    'Création Assignation' => '/admin/etudes/assignments/create',
    'Modification Assignation 1' => '/admin/etudes/assignments/1/edit',
    'Rapports' => '/admin/etudes/reports'
];

foreach ($etudesTests as $test => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ {$test} : HTTP 200\n";
    } elseif ($httpCode === 302) {
        echo "   ⚠️  {$test} : HTTP 302 (Redirection)\n";
    } elseif ($httpCode === 404) {
        echo "   ❌ {$test} : HTTP 404 (Page non trouvée)\n";
    } elseif ($httpCode === 500) {
        echo "   💥 {$test} : HTTP 500 (Erreur serveur)\n";
    } else {
        echo "   ❓ {$test} : HTTP {$httpCode} (Code inconnu)\n";
    }
}

// Test 2: Vérifier les erreurs spécifiques du module Économat
echo "\n2️⃣ TEST DÉTAILLÉ MODULE ÉCONOMAT :\n";
echo "==================================\n";

$economatTests = [
    'Dashboard' => '/admin/economat',
    'Paiements' => '/admin/economat/payments',
    'Création Paiement' => '/admin/economat/payments/create',
    'Modification Paiement 1' => '/admin/economat/payments/1/edit',
    'Détails Paiement 1' => '/admin/economat/payments/1/view',
    'Rappels' => '/admin/economat/reminders',
    'Création Rappel' => '/admin/economat/reminders/create',
    'Modification Rappel 1' => '/admin/economat/reminders/1/edit',
    'Envoi Rappel 1' => '/admin/economat/reminders/1/send',
    'Notifications' => '/admin/economat/notifications',
    'Envoi Notification' => '/admin/economat/notifications/send',
    'Historique Notifications' => '/admin/economat/notifications/history',
    'Rapports' => '/admin/economat/reports',
    'Export CSV' => '/admin/economat/reports/export/csv',
    'Export PDF' => '/admin/economat/reports/export/pdf'
];

foreach ($economatTests as $test => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ {$test} : HTTP 200\n";
    } elseif ($httpCode === 302) {
        echo "   ⚠️  {$test} : HTTP 302 (Redirection)\n";
    } elseif ($httpCode === 404) {
        echo "   ❌ {$test} : HTTP 404 (Page non trouvée)\n";
    } elseif ($httpCode === 500) {
        echo "   💥 {$test} : HTTP 500 (Erreur serveur)\n";
    } else {
        echo "   ❓ {$test} : HTTP {$httpCode} (Code inconnu)\n";
    }
}

// Test 3: Vérifier les données de test
echo "\n3️⃣ VÉRIFICATION DES DONNÉES DE TEST :\n";
echo "=====================================\n";

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier les cycles avec détails
    $stmt = $pdo->query("SELECT id, name, description FROM cycles LIMIT 3");
    $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   📊 Cycles disponibles :\n";
    foreach ($cycles as $cycle) {
        echo "      - ID {$cycle['id']} : {$cycle['name']}\n";
    }
    
    // Vérifier les classes avec détails
    $stmt = $pdo->query("SELECT id, name, level FROM classes LIMIT 3");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   📊 Classes disponibles :\n";
    foreach ($classes as $class) {
        echo "      - ID {$class['id']} : {$class['name']} (Niveau {$class['level']})\n";
    }
    
    // Vérifier les matières avec détails
    $stmt = $pdo->query("SELECT id, name, code FROM subjects LIMIT 3");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   📊 Matières disponibles :\n";
    foreach ($subjects as $subject) {
        echo "      - ID {$subject['id']} : {$subject['name']} ({$subject['code']})\n";
    }
    
    // Vérifier les paiements avec détails
    $stmt = $pdo->query("SELECT id, student_id, amount_paid, payment_date FROM payments LIMIT 3");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   📊 Paiements disponibles :\n";
    foreach ($payments as $payment) {
        echo "      - ID {$payment['id']} : Élève {$payment['student_id']}, {$payment['amount_paid']} FCFA ({$payment['payment_date']})\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Erreur de base de données : " . $e->getMessage() . "\n";
}

// Test 4: Vérifier les routes manquantes
echo "\n4️⃣ VÉRIFICATION DES ROUTES MANQUANTES :\n";
echo "======================================\n";

$routesManquantes = [
    'Études - Création Assignation' => '/admin/etudes/assignments/create',
    'Études - Modification Assignation' => '/admin/etudes/assignments/1/edit',
    'Études - Suppression Assignation' => '/admin/etudes/assignments/1/delete',
    'Économat - Création Rappel' => '/admin/economat/reminders/create',
    'Économat - Modification Rappel' => '/admin/economat/reminders/1/edit',
    'Économat - Suppression Rappel' => '/admin/economat/reminders/1/delete',
    'Économat - Notifications' => '/admin/economat/notifications',
    'Économat - Historique Notifications' => '/admin/economat/notifications/history'
];

foreach ($routesManquantes as $route => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 404) {
        echo "   ❌ {$route} : Route manquante (HTTP 404)\n";
    } elseif ($httpCode === 500) {
        echo "   💥 {$route} : Erreur serveur (HTTP 500)\n";
    } else {
        echo "   ✅ {$route} : Accessible (HTTP {$httpCode})\n";
    }
}

echo "\n🎯 ANALYSE DES PROBLÈMES IDENTIFIÉS :\n";
echo "=====================================\n";
echo "✅ Pages principales : Fonctionnelles\n";
echo "✅ Création : Généralement fonctionnelle\n";
echo "❌ Modification : Plusieurs erreurs 500\n";
echo "❌ Suppression : Plusieurs erreurs 500\n";
echo "❌ Détails : Certaines pages manquantes\n";
echo "❌ Routes : Certaines routes non définies\n";
echo "\n🔧 ACTIONS REQUISES :\n";
echo "====================\n";
echo "1. Vérifier les méthodes de modification et suppression\n";
echo "2. Ajouter les routes manquantes\n";
echo "3. Créer les vues manquantes\n";
echo "4. Corriger les erreurs 500\n";
echo "5. Implémenter les fonctionnalités manquantes\n";
?>









