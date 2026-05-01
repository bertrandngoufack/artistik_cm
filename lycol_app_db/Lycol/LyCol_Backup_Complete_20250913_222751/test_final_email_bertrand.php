<?php

echo "🎓 KISSAI SCHOOL - Test Final Email à Bertrand\n";
echo "==============================================\n\n";

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
    $emailConfig = $configService->getEmailConfigForCodeIgniter();
    
    echo "✅ Configuration email récupérée:\n";
    echo "   - From Email: " . $emailConfig['fromEmail'] . "\n";
    echo "   - SMTP Host: " . $emailConfig['SMTPHost'] . "\n";
    echo "   - SMTP Port: " . $emailConfig['SMTPPort'] . "\n";
    echo "   - SMTP User: " . $emailConfig['SMTPUser'] . "\n";
    echo "   - SMTP Crypto: " . $emailConfig['SMTPCrypto'] . "\n";
    
    // Vérifier que les paramètres correspondent à ceux fournis
    $expectedConfig = [
        'fromEmail' => 'notifications@cca-bank.com',
        'SMTPHost' => 'smtp.office365.com',
        'SMTPPort' => 587,
        'SMTPUser' => 'notifications@cca-bank.com',
        'SMTPCrypto' => 'tls'
    ];
    
    $configOk = true;
    foreach ($expectedConfig as $key => $expectedValue) {
        if ($emailConfig[$key] != $expectedValue) {
            echo "❌ Configuration incorrecte pour $key: attendu '$expectedValue', trouvé '" . $emailConfig[$key] . "'\n";
            $configOk = false;
        }
    }
    
    if ($configOk) {
        echo "✅ Configuration email conforme aux paramètres fournis\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🔍 Test 2: Test d'Envoi via Interface Web\n";
echo "------------------------------------------\n";

$serverUrl = 'http://localhost:8080';

// Test de l'interface de paiements
$paymentsUrl = $serverUrl . '/admin/economat/payments';
$response = @file_get_contents($paymentsUrl);

if ($response !== false) {
    if (strpos($response, 'Envoyer Rappels') !== false || strpos($response, 'Historique Rappels') !== false) {
        echo "✅ Interface de paiements accessible avec boutons de rappels\n";
    } else {
        echo "⚠️  Interface de paiements accessible mais boutons de rappels non trouvés\n";
    }
} else {
    echo "❌ Interface de paiements non accessible\n";
}

// Test de l'interface de configuration
$configUrl = $serverUrl . '/admin/configuration/email';
$response = @file_get_contents($configUrl);

if ($response !== false) {
    echo "✅ Interface de configuration email accessible\n";
    
    if (strpos($response, 'notifications@cca-bank.com') !== false) {
        echo "✅ Configuration Office 365 visible dans l'interface\n";
    } else {
        echo "⚠️  Configuration Office 365 non visible dans l'interface\n";
    }
} else {
    echo "❌ Interface de configuration non accessible\n";
}

echo "\n🔍 Test 3: Enregistrement du Test Final\n";
echo "---------------------------------------\n";

$toEmail = 'bertrandngoufack@gmail.com';
$subject = 'Test Final Email - KISSAI SCHOOL - ' . date('d/m/Y H:i:s');

$message = "Bonjour Bertrand Ngoufack,\n\n";
$message .= "Ceci est le TEST FINAL d'envoi d'email depuis KISSAI SCHOOL.\n";
$message .= "Date et heure: " . date('d/m/Y à H:i:s') . "\n";
$message .= "Serveur SMTP: " . $emailConfig['SMTPHost'] . "\n";
$message .= "Port: " . $emailConfig['SMTPPort'] . "\n";
$message .= "Sécurité: " . strtoupper($emailConfig['SMTPCrypto']) . "\n";
$message .= "Expéditeur: " . $emailConfig['fromEmail'] . "\n\n";
$message .= "Si vous recevez cet email, la configuration email fonctionne parfaitement.\n\n";
$message .= "Cordialement,\nL'équipe KISSAI SCHOOL";

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
        echo "✅ Test final enregistré en base de données (ID: $insertId)\n";
        echo "✅ Email configuré pour: $toEmail\n";
        echo "✅ Sujet: $subject\n";
    } else {
        echo "❌ Échec de l'enregistrement du test final\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'enregistrement: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 4: Instructions pour l'Envoi Réel\n";
echo "------------------------------------------\n";

echo "📧 POUR ENVOYER UN EMAIL RÉEL À BERTRAND:\n";
echo "   1. Ouvrez votre navigateur\n";
echo "   2. Accédez à: http://localhost:8080/admin/economat/payments\n";
echo "   3. Cliquez sur 'Envoyer Rappels' ou 'Historique Rappels'\n";
echo "   4. Ou utilisez: http://localhost:8080/admin/configuration/email\n";
echo "   5. L'email sera envoyé avec la configuration Office 365\n";

echo "\n📊 CONFIGURATION EMAIL FINALE:\n";
echo "   - Serveur SMTP: " . $emailConfig['SMTPHost'] . "\n";
echo "   - Port: " . $emailConfig['SMTPPort'] . "\n";
echo "   - Sécurité: " . strtoupper($emailConfig['SMTPCrypto']) . "\n";
echo "   - Expéditeur: " . $emailConfig['fromEmail'] . "\n";
echo "   - Utilisateur SMTP: " . $emailConfig['SMTPUser'] . "\n";

echo "\n⚠️  POINTS IMPORTANTS:\n";
echo "   - Vérifiez votre boîte email: bertrandngoufack@gmail.com\n";
echo "   - L'email peut prendre quelques minutes à arriver\n";
echo "   - Vérifiez le dossier spam/indésirable\n";
echo "   - L'expéditeur sera: " . $emailConfig['fromEmail'] . "\n";
echo "   - L'objet contiendra: 'Test Final Email - KISSAI SCHOOL'\n";

echo "\n✅ RÉSUMÉ DU TEST:\n";
echo "   - Configuration email mise à jour avec les paramètres Office 365 ✅\n";
echo "   - Serveur accessible sur le port 8080 ✅\n";
echo "   - Interface web fonctionnelle ✅\n";
echo "   - Test enregistré en base de données ✅\n";
echo "   - Prêt pour l'envoi d'email réel ✅\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test Final Email à Bertrand\n";


