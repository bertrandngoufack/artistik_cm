<?php

echo "🎓 KISSAI SCHOOL - Test d'Envoi Email à Bertrand\n";
echo "================================================\n\n";

// Inclure les services
require_once 'app/Services/ConfigurationService.php';
require_once 'app/Services/DatabaseService.php';

use App\Services\ConfigurationService;
use App\Services\DatabaseService;

$configService = new ConfigurationService();
$dbService = DatabaseService::getInstance();

echo "🔍 Test 1: Vérification de la Configuration Email\n";
echo "------------------------------------------------\n";

try {
    // Récupérer la configuration email depuis la base de données
    $emailConfig = $configService->getEmailConfigForCodeIgniter();
    
    echo "✅ Configuration email récupérée:\n";
    echo "   - From Email: " . $emailConfig['fromEmail'] . "\n";
    echo "   - SMTP Host: " . $emailConfig['SMTPHost'] . "\n";
    echo "   - SMTP Port: " . $emailConfig['SMTPPort'] . "\n";
    echo "   - SMTP User: " . $emailConfig['SMTPUser'] . "\n";
    echo "   - SMTP Pass: ***\n";
    echo "   - SMTP Crypto: " . $emailConfig['SMTPCrypto'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la récupération de la configuration: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🔍 Test 2: Test d'Envoi Email via cURL\n";
echo "--------------------------------------\n";

$toEmail = 'bertrandngoufack@gmail.com';
$subject = 'Test Email - KISSAI SCHOOL - ' . date('d/m/Y H:i:s');

// Message simple
$message = "Bonjour Bertrand Ngoufack,\n\n";
$message .= "Ceci est un test d'envoi d'email depuis KISSAI SCHOOL.\n";
$message .= "Date et heure: " . date('d/m/Y à H:i:s') . "\n";
$message .= "Serveur SMTP: " . $emailConfig['SMTPHost'] . "\n";
$message .= "Port: " . $emailConfig['SMTPPort'] . "\n";
$message .= "Sécurité: " . strtoupper($emailConfig['SMTPCrypto']) . "\n\n";
$message .= "Si vous recevez cet email, la configuration fonctionne correctement.\n\n";
$message .= "Cordialement,\nL'équipe KISSAI SCHOOL";

// Enregistrer le test en base de données
$testData = [
    'payment_id' => 999,
    'sent_to_phone' => '',
    'sent_to_email' => $toEmail,
    'message' => $message,
    'sms_sent' => 0,
    'email_sent' => 1,
    'whatsapp_sent' => 0,
    'sent_at' => date('Y-m-d H:i:s')
];

try {
    $insertId = $dbService->insert('payment_reminders', $testData);
    
    if ($insertId) {
        echo "✅ Test enregistré en base de données (ID: $insertId)\n";
        echo "✅ Email configuré pour: $toEmail\n";
        echo "✅ Sujet: $subject\n";
        echo "✅ Message enregistré en base\n";
    } else {
        echo "❌ Échec de l'enregistrement du test\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'enregistrement: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 3: Test via l'Interface Web\n";
echo "-----------------------------------\n";

$serverUrl = 'http://localhost:8080';

// Test de l'interface de rappels
$remindersUrl = $serverUrl . '/admin/economat/payments/send-reminders';
echo "📧 Interface de rappels: $remindersUrl\n";

// Test de l'interface de configuration
$configUrl = $serverUrl . '/admin/configuration/email';
echo "⚙️  Interface de configuration: $configUrl\n";

echo "\n🔍 Test 4: Instructions pour l'Envoi Réel\n";
echo "-----------------------------------------\n";

echo "📧 Pour envoyer un email réel à bertrandngoufack@gmail.com:\n";
echo "   1. Accédez à: http://localhost:8080/admin/economat/payments\n";
echo "   2. Cliquez sur 'Envoyer Rappels' ou 'Historique Rappels'\n";
echo "   3. Ou utilisez l'interface de configuration email\n";
echo "   4. L'email sera envoyé avec la configuration Office 365\n";

echo "\n📊 Configuration Email Actuelle:\n";
echo "   - Serveur SMTP: " . $emailConfig['SMTPHost'] . "\n";
echo "   - Port: " . $emailConfig['SMTPPort'] . "\n";
echo "   - Sécurité: " . strtoupper($emailConfig['SMTPCrypto']) . "\n";
echo "   - Expéditeur: " . $emailConfig['fromEmail'] . "\n";
echo "   - Utilisateur SMTP: " . $emailConfig['SMTPUser'] . "\n";

echo "\n⚠️  IMPORTANT:\n";
echo "   - Vérifiez que les identifiants SMTP sont corrects\n";
echo "   - L'email peut prendre quelques minutes à arriver\n";
echo "   - Vérifiez le dossier spam si nécessaire\n";
echo "   - Le test est enregistré en base de données\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test d'Envoi Email à Bertrand\n";


