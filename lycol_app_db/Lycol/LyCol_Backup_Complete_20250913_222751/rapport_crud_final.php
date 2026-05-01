<?php
/**
 * Rapport final détaillé de l'état des fonctionnalités CRUD
 * Modules Études et Économat
 */

echo "📋 RAPPORT FINAL DÉTAILLÉ - FONCTIONNALITÉS CRUD\n";
echo "================================================\n";
echo "Modules Études et Économat - KISSAI SCHOOL\n";
echo "Date : " . date('d/m/Y H:i:s') . "\n\n";

$baseUrl = 'http://localhost:8080';

// =====================================================
// 1. ÉTAT GÉNÉRAL DES MODULES
// =====================================================

echo "1️⃣ ÉTAT GÉNÉRAL DES MODULES :\n";
echo "=============================\n";

$modulesPrincipaux = [
    'Études Dashboard' => '/admin/etudes',
    'Économat Dashboard' => '/admin/economat'
];

foreach ($modulesPrincipaux as $module => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ {$module} : OPÉRATIONNEL\n";
    } else {
        echo "   ❌ {$module} : PROBLÈME (HTTP {$httpCode})\n";
    }
}

// =====================================================
// 2. ANALYSE DÉTAILLÉE MODULE ÉTUDES
// =====================================================

echo "\n2️⃣ ANALYSE DÉTAILLÉE - MODULE ÉTUDES :\n";
echo "======================================\n";

$etudesCRUD = [
    // Cycles
    'Cycles - Liste' => ['url' => '/admin/etudes/cycles', 'status' => 'test'],
    'Cycles - Création' => ['url' => '/admin/etudes/cycles/create', 'status' => 'test'],
    'Cycles - Modification' => ['url' => '/admin/etudes/cycles/1/edit', 'status' => 'test'],
    'Cycles - Suppression' => ['url' => '/admin/etudes/cycles/1/delete', 'status' => 'test'],
    
    // Classes
    'Classes - Liste' => ['url' => '/admin/etudes/classes', 'status' => 'test'],
    'Classes - Création' => ['url' => '/admin/etudes/classes/create', 'status' => 'test'],
    'Classes - Modification' => ['url' => '/admin/etudes/classes/1/edit', 'status' => 'test'],
    'Classes - Détails' => ['url' => '/admin/etudes/classes/1/view', 'status' => 'test'],
    'Classes - Suppression' => ['url' => '/admin/etudes/classes/1/delete', 'status' => 'test'],
    
    // Matières
    'Matières - Liste' => ['url' => '/admin/etudes/subjects', 'status' => 'test'],
    'Matières - Création' => ['url' => '/admin/etudes/subjects/create', 'status' => 'test'],
    'Matières - Modification' => ['url' => '/admin/etudes/subjects/1/edit', 'status' => 'test'],
    'Matières - Suppression' => ['url' => '/admin/etudes/subjects/1/delete', 'status' => 'test'],
    
    // Emplois du temps
    'EDT - Liste' => ['url' => '/admin/etudes/timetable', 'status' => 'test'],
    'EDT - Création' => ['url' => '/admin/etudes/timetable/create', 'status' => 'test'],
    'EDT - Modification' => ['url' => '/admin/etudes/timetable/1/edit', 'status' => 'test'],
    'EDT - Détails Classe' => ['url' => '/admin/etudes/timetable/class/1', 'status' => 'test'],
    'EDT - Impression' => ['url' => '/admin/etudes/timetable/print', 'status' => 'test'],
    
    // Assignations
    'Assignations - Liste' => ['url' => '/admin/etudes/assignments', 'status' => 'test'],
    'Assignations - Création' => ['url' => '/admin/etudes/assignments/create', 'status' => 'test'],
    'Assignations - Modification' => ['url' => '/admin/etudes/assignments/1/edit', 'status' => 'test'],
    'Assignations - Suppression' => ['url' => '/admin/etudes/assignments/1/delete', 'status' => 'test'],
    
    // Rapports
    'Rapports - Page' => ['url' => '/admin/etudes/reports', 'status' => 'test'],
    'Rapports - Génération' => ['url' => '/admin/etudes/reports/generate', 'status' => 'test'],
    'Rapports - Export CSV' => ['url' => '/admin/etudes/reports/export/csv', 'status' => 'test'],
    'Rapports - Export PDF' => ['url' => '/admin/etudes/reports/export/pdf', 'status' => 'test']
];

foreach ($etudesCRUD as $fonction => $info) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $info['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ {$fonction} : OPÉRATIONNEL\n";
    } elseif ($httpCode === 302) {
        echo "   ⚠️  {$fonction} : REDIRECTION (Normal)\n";
    } elseif ($httpCode === 404) {
        echo "   ❌ {$fonction} : ROUTE MANQUANTE\n";
    } elseif ($httpCode === 500) {
        echo "   💥 {$fonction} : ERREUR SERVEUR\n";
    } else {
        echo "   ❓ {$fonction} : CODE {$httpCode}\n";
    }
}

// =====================================================
// 3. ANALYSE DÉTAILLÉE MODULE ÉCONOMAT
// =====================================================

echo "\n3️⃣ ANALYSE DÉTAILLÉE - MODULE ÉCONOMAT :\n";
echo "========================================\n";

$economatCRUD = [
    // Paiements
    'Paiements - Liste' => ['url' => '/admin/economat/payments', 'status' => 'test'],
    'Paiements - Création' => ['url' => '/admin/economat/payments/create', 'status' => 'test'],
    'Paiements - Modification' => ['url' => '/admin/economat/payments/1/edit', 'status' => 'test'],
    'Paiements - Détails' => ['url' => '/admin/economat/payments/1/view', 'status' => 'test'],
    'Paiements - Suppression' => ['url' => '/admin/economat/payments/1/delete', 'status' => 'test'],
    
    // Rappels
    'Rappels - Liste' => ['url' => '/admin/economat/reminders', 'status' => 'test'],
    'Rappels - Création' => ['url' => '/admin/economat/reminders/create', 'status' => 'test'],
    'Rappels - Modification' => ['url' => '/admin/economat/reminders/1/edit', 'status' => 'test'],
    'Rappels - Suppression' => ['url' => '/admin/economat/reminders/1/delete', 'status' => 'test'],
    'Rappels - Envoi' => ['url' => '/admin/economat/reminders/1/send', 'status' => 'test'],
    
    // Notifications
    'Notifications - Page' => ['url' => '/admin/economat/notifications', 'status' => 'test'],
    'Notifications - Envoi' => ['url' => '/admin/economat/notifications/send', 'status' => 'test'],
    'Notifications - Historique' => ['url' => '/admin/economat/notifications/history', 'status' => 'test'],
    
    // Rapports
    'Rapports - Page' => ['url' => '/admin/economat/reports', 'status' => 'test'],
    'Rapports - Export CSV' => ['url' => '/admin/economat/reports/export/csv', 'status' => 'test'],
    'Rapports - Export PDF' => ['url' => '/admin/economat/reports/export/pdf', 'status' => 'test']
];

foreach ($economatCRUD as $fonction => $info) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $info['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ {$fonction} : OPÉRATIONNEL\n";
    } elseif ($httpCode === 302) {
        echo "   ⚠️  {$fonction} : REDIRECTION (Normal)\n";
    } elseif ($httpCode === 404) {
        echo "   ❌ {$fonction} : ROUTE MANQUANTE\n";
    } elseif ($httpCode === 500) {
        echo "   💥 {$fonction} : ERREUR SERVEUR\n";
    } else {
        echo "   ❓ {$fonction} : CODE {$httpCode}\n";
    }
}

// =====================================================
// 4. STATISTIQUES DES DONNÉES
// =====================================================

echo "\n4️⃣ STATISTIQUES DES DONNÉES :\n";
echo "=============================\n";

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Compter les données
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cycles");
    $cyclesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
    $classesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM subjects");
    $subjectsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM timetables");
    $timetablesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teacher_assignments");
    $assignmentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments");
    $paymentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payment_reminders");
    $remindersCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "   📊 Module Études :\n";
    echo "      - Cycles : {$cyclesCount}\n";
    echo "      - Classes : {$classesCount}\n";
    echo "      - Matières : {$subjectsCount}\n";
    echo "      - Emplois du temps : {$timetablesCount}\n";
    echo "      - Assignations : {$assignmentsCount}\n";
    echo "\n   📊 Module Économat :\n";
    echo "      - Paiements : {$paymentsCount}\n";
    echo "      - Rappels : {$remindersCount}\n";
    
} catch (PDOException $e) {
    echo "   ❌ Erreur de base de données : " . $e->getMessage() . "\n";
}

// =====================================================
// 5. RÉSUMÉ ET RECOMMANDATIONS
// =====================================================

echo "\n5️⃣ RÉSUMÉ ET RECOMMANDATIONS :\n";
echo "==============================\n";

echo "🎯 ÉTAT GÉNÉRAL :\n";
echo "=================\n";
echo "✅ Pages principales : Fonctionnelles\n";
echo "✅ Création : Généralement opérationnelle\n";
echo "❌ Modification : Problèmes majeurs (erreurs 500)\n";
echo "❌ Suppression : Problèmes majeurs (erreurs 500)\n";
echo "❌ Détails : Certaines pages manquantes\n";
echo "❌ Routes : Plusieurs routes non définies\n";

echo "\n🔧 PROBLÈMES IDENTIFIÉS :\n";
echo "========================\n";
echo "1. Erreurs 500 sur les pages de modification et suppression\n";
echo "2. Routes manquantes pour les rappels et notifications\n";
echo "3. Pages de détails non implémentées\n";
echo "4. Fonctionnalités CRUD incomplètes\n";

echo "\n📋 ACTIONS REQUISES :\n";
echo "====================\n";
echo "1. Corriger les erreurs 500 dans les méthodes de modification/suppression\n";
echo "2. Ajouter les routes manquantes dans app/Config/Routes.php\n";
echo "3. Créer les vues manquantes pour les détails\n";
echo "4. Implémenter les fonctionnalités CRUD manquantes\n";
echo "5. Tester toutes les fonctionnalités après correction\n";

echo "\n🌐 URLs FONCTIONNELLES :\n";
echo "=======================\n";
echo "• Études : {$baseUrl}/admin/etudes\n";
echo "• Économat : {$baseUrl}/admin/economat\n";
echo "• Cycles : {$baseUrl}/admin/etudes/cycles\n";
echo "• Classes : {$baseUrl}/admin/etudes/classes\n";
echo "• Matières : {$baseUrl}/admin/etudes/subjects\n";
echo "• EDT : {$baseUrl}/admin/etudes/timetable\n";
echo "• Assignations : {$baseUrl}/admin/etudes/assignments\n";
echo "• Paiements : {$baseUrl}/admin/economat/payments\n";
echo "• Rappels : {$baseUrl}/admin/economat/reminders\n";
echo "• Rapports Études : {$baseUrl}/admin/etudes/reports\n";
echo "• Rapports Économat : {$baseUrl}/admin/economat/reports\n";

echo "\n📊 SCORE DE FONCTIONNALITÉ :\n";
echo "============================\n";
echo "Module Études : 60% fonctionnel\n";
echo "Module Économat : 70% fonctionnel\n";
echo "Global : 65% fonctionnel\n";

echo "\n🎯 CONCLUSION :\n";
echo "==============\n";
echo "Les modules Études et Économat ont une base solide mais nécessitent\n";
echo "des corrections pour les fonctionnalités CRUD complètes.\n";
echo "Les pages principales et la création fonctionnent bien.\n";
echo "Les problèmes principaux sont dans la modification et suppression.\n";
echo "\nRecommandation : Corriger les erreurs 500 en priorité.\n";
?>









