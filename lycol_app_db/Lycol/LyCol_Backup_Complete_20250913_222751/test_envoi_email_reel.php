<?php

// Inclure les services
require_once 'app/Services/DatabaseService.php';

use App\Services\DatabaseService;

echo "🎓 KISSAI SCHOOL - Test d'Envoi Email Réel\n";
echo "==========================================\n\n";

echo "🔍 Test 1: Vérification du Serveur\n";
echo "----------------------------------\n";

$serverUrl = 'http://localhost:8080';

// Vérifier si le serveur est en cours d'exécution
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

echo "\n🔍 Test 2: Test d'Envoi Email via Interface Web\n";
echo "-----------------------------------------------\n";

// URL pour envoyer des rappels
$sendRemindersUrl = $serverUrl . '/admin/economat/payments/send-reminders';

echo "📧 Tentative d'envoi d'email via l'interface web...\n";
echo "   URL: $sendRemindersUrl\n";

// Utiliser cURL pour simuler une requête POST
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sendRemindersUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'bertrandngoufack@gmail.com',
    'message' => 'Test d\'envoi email depuis KISSAI SCHOOL',
    'send_email' => '1'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: KISSAI-SCHOOL-Test'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Requête envoyée avec succès (HTTP 200)\n";
    
    if (strpos($response, 'succès') !== false || strpos($response, 'envoyé') !== false) {
        echo "✅ Email envoyé avec succès\n";
    } else {
        echo "⚠️  Réponse reçue mais statut incertain\n";
    }
} else {
    echo "❌ Erreur HTTP: $httpCode\n";
}

echo "\n🔍 Test 3: Vérification en Base de Données\n";
echo "------------------------------------------\n";

try {
    $dbService = DatabaseService::getInstance();
    
    // Vérifier les derniers rappels envoyés
    $results = $dbService->fetchAll("
        SELECT * FROM payment_reminders 
        WHERE sent_to_email = 'bertrandngoufack@gmail.com' 
        ORDER BY sent_at DESC 
        LIMIT 5
    ");
    
    if ($results) {
        echo "✅ Derniers tests trouvés en base:\n";
        foreach ($results as $result) {
            echo "   - ID: " . $result['id'] . " | Date: " . $result['sent_at'] . " | Email: " . ($result['email_sent'] ? '✅' : '❌') . "\n";
        }
    } else {
        echo "⚠️  Aucun test trouvé en base de données\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification en base: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test 4: Test de l'Interface de Configuration\n";
echo "-----------------------------------------------\n";

$configUrl = $serverUrl . '/admin/configuration/email';
$response = @file_get_contents($configUrl);

if ($response !== false) {
    if (strpos($response, 'Configuration Email') !== false) {
        echo "✅ Page de configuration email accessible\n";
    } else {
        echo "⚠️  Page accessible mais contenu inattendu\n";
    }
    
    if (strpos($response, 'notifications@cca-bank.com') !== false) {
        echo "✅ Configuration Office 365 détectée\n";
    } else {
        echo "⚠️  Configuration Office 365 non détectée\n";
    }
} else {
    echo "❌ Impossible d'accéder à la page de configuration\n";
}

echo "\n🔍 Test 5: Instructions Finales\n";
echo "-------------------------------\n";

echo "📧 Pour envoyer un email réel à bertrandngoufack@gmail.com:\n";
echo "   1. Ouvrez votre navigateur\n";
echo "   2. Accédez à: http://localhost:8080/admin/economat/payments\n";
echo "   3. Cliquez sur 'Envoyer Rappels' ou 'Historique Rappels'\n";
echo "   4. Ou utilisez: http://localhost:8080/admin/configuration/email\n";
echo "   5. L'email sera envoyé avec la configuration Office 365\n";

echo "\n📊 Configuration Email Actuelle:\n";
echo "   - Serveur SMTP: smtp.office365.com\n";
echo "   - Port: 587\n";
echo "   - Sécurité: TLS\n";
echo "   - Expéditeur: notifications@cca-bank.com\n";
echo "   - Utilisateur SMTP: notifications@cca-bank.com\n";

echo "\n⚠️  POINTS IMPORTANTS:\n";
echo "   - Vérifiez votre boîte email: bertrandngoufack@gmail.com\n";
echo "   - L'email peut prendre quelques minutes à arriver\n";
echo "   - Vérifiez le dossier spam/indésirable\n";
echo "   - L'expéditeur sera: notifications@cca-bank.com\n";
echo "   - L'objet contiendra: 'Test Email - KISSAI SCHOOL'\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test d'Envoi Email Réel\n";
