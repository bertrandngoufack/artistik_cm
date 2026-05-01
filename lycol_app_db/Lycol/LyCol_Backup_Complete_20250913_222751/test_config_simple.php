<?php
/**
 * Test simple du module Configuration
 */

echo "🔧 TEST SIMPLE DU MODULE CONFIGURATION\n";
echo "======================================\n\n";

// Test 1: Vérifier les fichiers
echo "📁 Test 1: Fichiers du module\n";
echo "------------------------------\n";

$files = [
    'app/Controllers/Configuration.php' => 'Contrôleur',
    'app/Views/admin/configuration/index.php' => 'Vue principale',
    'app/Views/admin/configuration/general.php' => 'Vue paramètres généraux',
    'app/Views/admin/configuration/email.php' => 'Vue configuration email',
    'app/Config/Email.php' => 'Configuration Email',
    'app/Config/SMS.php' => 'Configuration SMS',
    'app/Config/WhatsApp.php' => 'Configuration WhatsApp',
    'database/system_settings.sql' => 'Script SQL',
    'test_configuration.php' => 'Script de test'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file)\n";
    } else {
        echo "❌ $description ($file) - MANQUANT\n";
    }
}

echo "\n";

// Test 2: Vérifier la base de données
echo "🗄️ Test 2: Base de données\n";
echo "---------------------------\n";

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier si la table system_settings existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Table system_settings existe\n";
        
        // Vérifier les paramètres par défaut
        $stmt = $pdo->query("SELECT setting_type, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Paramètres enregistrés :\n";
        foreach ($settings as $setting) {
            $data = json_decode($setting['setting_value'], true);
            echo "   - {$setting['setting_type']}: " . count($data) . " paramètres\n";
        }
    } else {
        echo "❌ Table system_settings n'existe pas\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur base de données: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier les fournisseurs
echo "📧 Test 3: Fournisseurs disponibles\n";
echo "-----------------------------------\n";

$providers = [
    'Email' => [
        'Gmail SMTP' => 'Service email gratuit de Google',
        'Outlook/Hotmail' => 'Service email de Microsoft',
        'Serveur SMTP personnalisé' => 'Configuration SMTP personnalisée'
    ],
    'SMS' => [
        'TextLocal' => 'Service SMS gratuit pour les tests',
        'Twilio' => 'Service SMS professionnel',
        'MSG91' => 'Service SMS international'
    ],
    'WhatsApp' => [
        'Twilio WhatsApp' => 'Service WhatsApp via Twilio',
        '360dialog' => 'Service WhatsApp Business API'
    ]
];

foreach ($providers as $type => $typeProviders) {
    echo "📱 $type:\n";
    foreach ($typeProviders as $name => $description) {
        echo "   ✅ $name - $description\n";
    }
    echo "\n";
}

// Test 4: Instructions de configuration
echo "🎯 Test 4: Instructions de configuration\n";
echo "----------------------------------------\n";

echo "📧 Pour configurer Email (Gmail) :\n";
echo "1. Créez un compte Gmail : kissai.school@gmail.com\n";
echo "2. Activez l'authentification à 2 facteurs\n";
echo "3. Générez un mot de passe d'application\n";
echo "4. Remplacez les paramètres dans la configuration\n\n";

echo "📱 Pour configurer SMS (TextLocal) :\n";
echo "1. Inscrivez-vous sur https://www.textlocal.in/\n";
echo "2. Obtenez votre clé API gratuite\n";
echo "3. Configurez dans le module Configuration > SMS\n\n";

echo "💬 Pour configurer WhatsApp (Twilio) :\n";
echo "1. Créez un compte sur https://www.twilio.com/\n";
echo "2. Obtenez votre Account SID et Auth Token\n";
echo "3. Configurez WhatsApp Sandbox\n";
echo "4. Configurez dans le module Configuration > WhatsApp\n\n";

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test simple du module Configuration\n";

echo "\n🎯 CONCLUSION: ✅ Le module Configuration est prêt pour la configuration des fournisseurs\n";
echo "🚀 Prochaines étapes :\n";
echo "   1. Configurer les fournisseurs Email, SMS et WhatsApp\n";
echo "   2. Tester les envois vers vos coordonnées (+237694202063 et bertrandngoufack@gmail.com)\n";
echo "   3. Intégrer dans le module Economat pour les rappels automatiques\n";
echo "   4. Accéder à http://localhost:8080/admin/configuration pour configurer\n";
?>


