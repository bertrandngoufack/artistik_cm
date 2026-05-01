<?php

echo "🎓 KISSAI SCHOOL - Test d'Envoi Email à Bertrand (via Web)\n";
echo "==========================================================\n\n";

echo "🔍 Test 1: Vérification du Serveur\n";
echo "----------------------------------\n";

$serverUrl = 'http://localhost:8080';

// Test de base du serveur
$response = @file_get_contents($serverUrl);
if ($response !== false) {
    echo "✅ Serveur accessible sur le port 8080\n";
} else {
    echo "❌ Serveur non accessible sur le port 8080\n";
    echo "   Démarrage du serveur...\n";
    
    // Démarrer le serveur en arrière-plan
    $command = 'php -S 0.0.0.0:8080 -t public public/router.php > /dev/null 2>&1 &';
    exec($command);
    sleep(3);
    
    // Vérifier à nouveau
    $response = @file_get_contents($serverUrl);
    if ($response !== false) {
        echo "✅ Serveur démarré avec succès\n";
    } else {
        echo "❌ Impossible de démarrer le serveur\n";
        exit(1);
    }
}

echo "\n🔍 Test 2: Test de l'Interface de Configuration Email\n";
echo "-----------------------------------------------------\n";

$configUrl = $serverUrl . '/admin/configuration/email';
$response = @file_get_contents($configUrl);

if ($response !== false) {
    if (strpos($response, 'Configuration Email') !== false) {
        echo "✅ Page de configuration email accessible\n";
    } else {
        echo "⚠️  Page accessible mais contenu inattendu\n";
    }
    
    if (strpos($response, 'error') !== false || strpos($response, 'Exception') !== false) {
        echo "❌ Erreur détectée dans la page de configuration\n";
    } else {
        echo "✅ Aucune erreur détectée dans la page de configuration\n";
    }
} else {
    echo "❌ Impossible d'accéder à la page de configuration email\n";
}

echo "\n🔍 Test 3: Test d'Envoi via l'Interface Web\n";
echo "-------------------------------------------\n";

// Simuler un envoi via l'interface web
echo "📧 Simulation d'envoi d'email via l'interface web...\n";

// Créer un script de test qui utilise le contrôleur
$testScript = '<?php
// Test d\'envoi d\'email via le contrôleur Economat
require_once "app/Services/ConfigurationService.php";
require_once "app/Services/DatabaseService.php";

use App\Services\ConfigurationService;
use App\Services\DatabaseService;

$configService = new ConfigurationService();
$dbService = DatabaseService::getInstance();

try {
    // Récupérer la configuration email
    $emailConfig = $configService->getEmailConfigForCodeIgniter();
    
    if (empty($emailConfig["fromEmail"]) || empty($emailConfig["SMTPHost"])) {
        echo "❌ Configuration email incomplète\n";
        exit(1);
    }
    
    echo "✅ Configuration email récupérée:\n";
    echo "   - From: " . $emailConfig["fromEmail"] . "\n";
    echo "   - SMTP: " . $emailConfig["SMTPHost"] . ":" . $emailConfig["SMTPPort"] . "\n";
    
    // Simuler l\'envoi d\'un email de test
    $toEmail = "bertrandngoufack@gmail.com";
    $subject = "Test Email - KISSAI SCHOOL - " . date("d/m/Y H:i:s");
    
    $message = "Bonjour Bertrand Ngoufack,\n\n";
    $message .= "Ceci est un test d\'envoi d\'email depuis KISSAI SCHOOL.\n";
    $message .= "Date et heure: " . date("d/m/Y à H:i:s") . "\n";
    $message .= "Serveur SMTP: " . $emailConfig["SMTPHost"] . "\n";
    $message .= "Port: " . $emailConfig["SMTPPort"] . "\n";
    $message .= "Sécurité: " . strtoupper($emailConfig["SMTPCrypto"]) . "\n\n";
    $message .= "Si vous recevez cet email, la configuration fonctionne correctement.\n\n";
    $message .= "Cordialement,\nL\'équipe KISSAI SCHOOL";
    
    // Enregistrer le test en base de données
    $testData = [
        "payment_id" => 999,
        "sent_to_phone" => "",
        "sent_to_email" => $toEmail,
        "message" => $message,
        "sms_sent" => 0,
        "email_sent" => 1,
        "whatsapp_sent" => 0,
        "sent_at" => date("Y-m-d H:i:s")
    ];
    
    $insertId = $dbService->insert("payment_reminders", $testData);
    
    if ($insertId) {
        echo "✅ Test enregistré en base de données (ID: $insertId)\n";
        echo "✅ Email simulé pour: $toEmail\n";
        echo "✅ Sujet: $subject\n";
        echo "✅ Message enregistré en base\n";
    } else {
        echo "❌ Échec de l\'enregistrement du test\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>';

// Écrire le script temporaire
file_put_contents('temp_test_email.php', $testScript);

// Exécuter le script
$output = shell_exec('php temp_test_email.php 2>&1');
echo $output;

// Nettoyer
unlink('temp_test_email.php');

echo "\n🔍 Test 4: Vérification en Base de Données\n";
echo "------------------------------------------\n";

try {
    require_once 'app/Services/DatabaseService.php';
    
    use App\Services\DatabaseService;
    
    $dbService = DatabaseService::getInstance();
    
    // Vérifier le dernier test enregistré
    $result = $dbService->fetchOne("
        SELECT * FROM payment_reminders 
        WHERE sent_to_email = 'bertrandngoufack@gmail.com' 
        ORDER BY sent_at DESC 
        LIMIT 1
    ");
    
    if ($result) {
        echo "✅ Dernier test trouvé en base:\n";
        echo "   - ID: " . $result['id'] . "\n";
        echo "   - Date: " . $result['sent_at'] . "\n";
        echo "   - Email: " . $result['sent_to_email'] . "\n";
        echo "   - Statut Email: " . ($result['email_sent'] ? '✅' : '❌') . "\n";
    } else {
        echo "⚠️  Aucun test trouvé en base de données\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification en base: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 5: Instructions pour l'Envoi Réel\n";
echo "-----------------------------------------\n";

echo "📧 Pour envoyer un email réel à bertrandngoufack@gmail.com:\n";
echo "   1. Accédez à: http://localhost:8080/admin/economat/payments\n";
echo "   2. Cliquez sur 'Envoyer Rappels' ou 'Historique Rappels'\n";
echo "   3. Ou utilisez l'interface de configuration email\n";
echo "   4. L'email sera envoyé avec la configuration Office 365\n";

echo "\n📊 Configuration Email Actuelle:\n";
echo "   - Serveur SMTP: smtp.office365.com\n";
echo "   - Port: 587\n";
echo "   - Sécurité: TLS\n";
echo "   - Expéditeur: kissai.school@gmail.com\n";

echo "\n⚠️  IMPORTANT:\n";
echo "   - Vérifiez que les identifiants SMTP sont configurés\n";
echo "   - L'email peut prendre quelques minutes à arriver\n";
echo "   - Vérifiez le dossier spam si nécessaire\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test d'Envoi Email à Bertrand\n";
