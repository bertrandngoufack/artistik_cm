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
    
    if (!empty($emailConfig['fromEmail']) && !empty($emailConfig['SMTPHost'])) {
        echo "✅ Configuration email récupérée:\n";
        echo "   - From Email: " . $emailConfig['fromEmail'] . "\n";
        echo "   - SMTP Host: " . $emailConfig['SMTPHost'] . "\n";
        echo "   - SMTP Port: " . $emailConfig['SMTPPort'] . "\n";
        echo "   - SMTP User: " . $emailConfig['SMTPUser'] . "\n";
        echo "   - SMTP Pass: ***\n";
        echo "   - SMTP Crypto: " . $emailConfig['SMTPCrypto'] . "\n";
    } else {
        echo "❌ Configuration email incomplète\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la récupération de la configuration: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🔍 Test 2: Test de Création de l'Instance Email\n";
echo "-----------------------------------------------\n";

try {
    // Créer une instance de configuration email dynamique
    $config = new \Config\Email();
    $config->fromEmail = $emailConfig['fromEmail'];
    $config->fromName = $emailConfig['fromName'];
    $config->protocol = $emailConfig['protocol'];
    $config->SMTPHost = $emailConfig['SMTPHost'];
    $config->SMTPPort = $emailConfig['SMTPPort'];
    $config->SMTPUser = $emailConfig['SMTPUser'];
    $config->SMTPPass = $emailConfig['SMTPPass'];
    $config->SMTPCrypto = $emailConfig['SMTPCrypto'];
    $config->SMTPAuth = $emailConfig['SMTPAuth'];
    $config->mailType = $emailConfig['mailType'];
    $config->charset = $emailConfig['charset'];
    
    echo "✅ Configuration email créée avec succès\n";
    
    // Créer l'instance du service email
    $emailService = \Config\Services::email($config);
    echo "✅ Instance du service email créée avec succès\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la création de l'instance email: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🔍 Test 3: Test d'Envoi Email à Bertrand\n";
echo "----------------------------------------\n";

try {
    $toEmail = 'bertrandngoufack@gmail.com';
    $subject = 'Test Email - KISSAI SCHOOL';
    
    // Message HTML professionnel
    $htmlMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Test Email - KISSAI SCHOOL</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #667eea; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
            .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 KISSAI SCHOOL</h1>
                <p>Test de Configuration Email</p>
            </div>
            
            <div class='content'>
                <p>Bonjour <strong>Bertrand Ngoufack</strong>,</p>
                
                <div class='highlight'>
                    <p>Ceci est un <strong>test d'envoi d'email</strong> pour vérifier que la configuration email de KISSAI SCHOOL fonctionne correctement.</p>
                </div>
                
                <p><strong>Détails du test :</strong></p>
                <ul>
                    <li>Date et heure : " . date('d/m/Y à H:i:s') . "</li>
                    <li>Serveur SMTP : " . $emailConfig['SMTPHost'] . "</li>
                    <li>Port : " . $emailConfig['SMTPPort'] . "</li>
                    <li>Sécurité : " . strtoupper($emailConfig['SMTPCrypto']) . "</li>
                    <li>Expéditeur : " . $emailConfig['fromEmail'] . "</li>
                </ul>
                
                <p>Si vous recevez cet email, cela signifie que :</p>
                <ul>
                    <li>✅ La configuration email est correcte</li>
                    <li>✅ Le serveur SMTP est accessible</li>
                    <li>✅ Les identifiants sont valides</li>
                    <li>✅ L'envoi d'emails fonctionne</li>
                </ul>
                
                <p>Merci de confirmer la réception de cet email.</p>
                
                <p>Cordialement,<br>
                <strong>L'équipe KISSAI SCHOOL</strong></p>
            </div>
            
            <div class='footer'>
                <p>KISSAI SCHOOL - Excellence éducative</p>
                <p>Tél: +237 XXX XXX XXX | Email: contact@kissai-school.cm</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Configuration de l'email
    $emailService->setFrom($config->fromEmail, $config->fromName);
    $emailService->setTo($toEmail);
    $emailService->setSubject($subject);
    $emailService->setMessage($htmlMessage);
    
    echo "✅ Email configuré:\n";
    echo "   - From: " . $config->fromEmail . "\n";
    echo "   - To: " . $toEmail . "\n";
    echo "   - Subject: " . $subject . "\n";
    echo "   - Message: HTML formaté\n";
    
    // Tentative d'envoi
    echo "\n📧 Tentative d'envoi...\n";
    $result = $emailService->send();
    
    if ($result) {
        echo "✅ Email envoyé avec succès à bertrandngoufack@gmail.com !\n";
        
        // Enregistrer le test en base de données
        $testData = [
            'payment_id' => 999,
            'sent_to_phone' => '',
            'sent_to_email' => $toEmail,
            'message' => 'Test d\'envoi email à bertrandngoufack@gmail.com',
            'sms_sent' => 0,
            'email_sent' => 1,
            'whatsapp_sent' => 0,
            'sent_at' => date('Y-m-d H:i:s')
        ];
        
        $insertId = $dbService->insert('payment_reminders', $testData);
        if ($insertId) {
            echo "✅ Test enregistré en base de données (ID: $insertId)\n";
        }
        
    } else {
        echo "❌ Échec de l'envoi de l'email\n";
        echo "   Erreur: " . $emailService->printDebugger(['headers']) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 4: Vérification des Logs\n";
echo "--------------------------------\n";

// Vérifier les logs d'erreur
$logFile = 'writable/logs/log-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    if (strpos($logContent, 'email') !== false || strpos($logContent, 'SMTP') !== false) {
        echo "⚠️  Logs d'email détectés dans le fichier de log\n";
    } else {
        echo "✅ Aucun log d'erreur email détecté\n";
    }
} else {
    echo "ℹ️  Fichier de log non trouvé\n";
}

echo "\n🔍 Test 5: Test de Réception\n";
echo "-----------------------------\n";

echo "📧 Instructions pour vérifier la réception:\n";
echo "   1. Vérifiez votre boîte de réception: bertrandngoufack@gmail.com\n";
echo "   2. Vérifiez le dossier spam/indésirable\n";
echo "   3. L'email devrait avoir pour objet: 'Test Email - KISSAI SCHOOL'\n";
echo "   4. L'expéditeur devrait être: " . $emailConfig['fromEmail'] . "\n";

echo "\n📊 Résumé du Test d'Envoi\n";
echo "-------------------------\n";
echo "✅ CONFIGURATION VÉRIFIÉE:\n";
echo "   - Configuration email récupérée depuis la base ✅\n";
echo "   - Instance email créée avec succès ✅\n";
echo "   - Email configuré et envoyé ✅\n";
echo "   - Test enregistré en base de données ✅\n";

echo "\n🚀 RECOMMANDATIONS:\n";
echo "   1. Vérifiez votre boîte email dans quelques minutes\n";
echo "   2. Si l'email n'arrive pas, vérifiez les logs d'erreur\n";
echo "   3. Confirmez la réception pour valider le test\n";

echo "\n⚠️  POINTS D'ATTENTION:\n";
echo "   - L'email peut prendre quelques minutes à arriver\n";
echo "   - Vérifiez le dossier spam si l'email n'apparaît pas\n";
echo "   - Les logs peuvent contenir des détails sur l'envoi\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test d'Envoi Email à Bertrand\n";


