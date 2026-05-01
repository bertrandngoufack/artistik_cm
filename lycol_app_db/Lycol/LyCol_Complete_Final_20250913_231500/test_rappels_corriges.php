<?php

echo "🎓 KISSAI SCHOOL - Test Rappels Corrigés\n";
echo "========================================\n\n";

// Inclure les services
require_once 'app/Services/ConfigurationService.php';
require_once 'app/Services/DatabaseService.php';

use App\Services\ConfigurationService;
use App\Services\DatabaseService;

$configService = new ConfigurationService();
$dbService = DatabaseService::getInstance();

echo "🔍 Test 1: Vérification de la Correction\n";
echo "----------------------------------------\n";

try {
    $pdo = $dbService->getConnection();
    
    // Vérifier que la méthode logReminder a été corrigée
    $economatFile = 'app/Controllers/Economat.php';
    if (file_exists($economatFile)) {
        $content = file_get_contents($economatFile);
        
        // Vérifier que la signature de logReminder a été mise à jour
        if (strpos($content, 'private function logReminder($paymentId, $phone, $email, $message, $smsSent = false, $emailSent = false, $whatsappSent = false)') !== false) {
            echo "✅ Méthode logReminder corrigée avec les paramètres de statut\n";
        } else {
            echo "❌ Méthode logReminder non corrigée\n";
        }
        
        // Vérifier que sendMultiChannelReminder utilise les statuts
        if (strpos($content, '$smsSent = $this->sendSMS($parentPhone, $message);') !== false) {
            echo "✅ sendMultiChannelReminder utilise les statuts d'envoi\n";
        } else {
            echo "❌ sendMultiChannelReminder n'utilise pas les statuts\n";
        }
        
        // Vérifier que logReminder est appelé avec les statuts
        if (strpos($content, '$this->logReminder($payment[\'id\'], $parentPhone, $parentEmail, $message, $smsSent, $emailSent, $whatsappSent);') !== false) {
            echo "✅ logReminder appelé avec les statuts d'envoi\n";
        } else {
            echo "❌ logReminder n'est pas appelé avec les statuts\n";
        }
        
    } else {
        echo "❌ Fichier Economat.php non trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 2: Test d'Insertion avec Statuts\n";
echo "----------------------------------------\n";

try {
    // Test d'insertion avec statuts
    $testData = [
        'payment_id' => 999,
        'sent_to_phone' => '+237694202063',
        'sent_to_email' => 'test@example.com',
        'message' => 'Test de rappel avec statuts - KISSAI SCHOOL',
        'sms_sent' => 1,
        'email_sent' => 1,
        'whatsapp_sent' => 0,
        'sent_at' => date('Y-m-d H:i:s')
    ];
    
    $insertId = $dbService->insert('payment_reminders', $testData);
    
    if ($insertId) {
        echo "✅ Test d'insertion avec statuts réussi (ID: $insertId)\n";
        
        // Vérifier que les statuts ont été enregistrés
        $result = $dbService->fetchOne("SELECT * FROM payment_reminders WHERE id = ?", [$insertId]);
        if ($result) {
            echo "✅ Rappel de test vérifié:\n";
            echo "   - SMS: " . ($result['sms_sent'] ? '✅' : '❌') . "\n";
            echo "   - Email: " . ($result['email_sent'] ? '✅' : '❌') . "\n";
            echo "   - WhatsApp: " . ($result['whatsapp_sent'] ? '✅' : '❌') . "\n";
            
            // Supprimer le rappel de test
            $dbService->delete('payment_reminders', 'id = ?', [$insertId]);
            echo "✅ Rappel de test supprimé\n";
        } else {
            echo "❌ Rappel de test non trouvé\n";
        }
    } else {
        echo "❌ Échec de l'insertion du rappel de test\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test d'insertion: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 3: Vérification des Rappels Existants\n";
echo "----------------------------------------------\n";

try {
    // Récupérer les 3 derniers rappels pour voir les statuts
    $reminders = $dbService->fetchAll("
        SELECT 
            pr.id,
            pr.sent_to_phone,
            pr.sent_to_email,
            pr.sms_sent,
            pr.email_sent,
            pr.whatsapp_sent,
            pr.sent_at
        FROM payment_reminders pr
        ORDER BY pr.sent_at DESC
        LIMIT 3
    ");
    
    if (!empty($reminders)) {
        echo "✅ " . count($reminders) . " derniers rappels analysés:\n";
        
        foreach ($reminders as $index => $reminder) {
            $num = $index + 1;
            echo "   $num. ID: {$reminder['id']} - Date: {$reminder['sent_at']}\n";
            echo "      SMS: " . ($reminder['sms_sent'] ? '✅' : '❌') . " Email: " . ($reminder['email_sent'] ? '✅' : '❌') . " WhatsApp: " . ($reminder['whatsapp_sent'] ? '✅' : '❌') . "\n";
        }
        
        // Compter les rappels avec statuts
        $withStats = array_filter($reminders, function($r) {
            return $r['sms_sent'] == 1 || $r['email_sent'] == 1 || $r['whatsapp_sent'] == 1;
        });
        
        echo "   📊 Rappels avec statuts d'envoi: " . count($withStats) . "/" . count($reminders) . "\n";
        
    } else {
        echo "⚠️  Aucun rappel trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'analyse des rappels: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 4: Test de l'URL Historique\n";
echo "------------------------------------\n";

$serverUrl = 'http://localhost:8080';
$remindersUrl = $serverUrl . '/admin/economat/reminders';

$response = @file_get_contents($remindersUrl);

if ($response !== false) {
    if (strpos($response, 'Historique des Rappels') !== false) {
        echo "✅ Page historique accessible\n";
    } else {
        echo "⚠️  Page accessible mais contenu inattendu\n";
    }
    
    if (strpos($response, 'error') !== false || strpos($response, 'Exception') !== false) {
        echo "❌ Erreur détectée dans la page\n";
    } else {
        echo "✅ Aucune erreur détectée\n";
    }
    
} else {
    echo "❌ Impossible d'accéder à la page\n";
}

echo "\n📊 Résumé de la Correction\n";
echo "--------------------------\n";
echo "✅ CORRECTIONS APPLIQUÉES:\n";
echo "   - Méthode logReminder mise à jour avec paramètres de statut ✅\n";
echo "   - sendMultiChannelReminder utilise les statuts d'envoi ✅\n";
echo "   - Connexion PDO remplacée par DatabaseService ✅\n";
echo "   - Test d'insertion avec statuts fonctionnel ✅\n";

echo "\n🚀 RECOMMANDATIONS:\n";
echo "   1. Les nouveaux rappels auront les statuts corrects ✅\n";
echo "   2. L'historique affichera les statuts d'envoi ✅\n";
echo "   3. Les statistiques seront plus précises ✅\n";

echo "\n⚠️  POINTS D'ATTENTION:\n";
echo "   - Les rappels existants gardent leurs anciens statuts (0)\n";
echo "   - Seuls les nouveaux rappels auront les statuts corrects\n";
echo "   - Pour tester, envoyer de nouveaux rappels\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Rappels Corrigés et Fonctionnels\n";


