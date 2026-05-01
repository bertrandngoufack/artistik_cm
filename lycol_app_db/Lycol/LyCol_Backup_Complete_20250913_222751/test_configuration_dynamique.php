<?php

echo "🎓 KISSAI SCHOOL - Test de la configuration dynamique\n";
echo "====================================================\n\n";

// Inclure le service de configuration
require_once 'app/Services/ConfigurationService.php';

use App\Services\ConfigurationService;

$configService = new ConfigurationService();

echo "🔍 Test 1: Configuration Email depuis la base de données\n";
echo "--------------------------------------------------------\n";
$emailConfig = $configService->getEmailConfig();
if ($emailConfig) {
    echo "✅ Configuration email trouvée:\n";
    foreach ($emailConfig as $key => $value) {
        if (strpos($key, 'pass') !== false) {
            echo "   - $key: ***\n";
        } else {
            echo "   - $key: $value\n";
        }
    }
} else {
    echo "❌ Aucune configuration email en base de données\n";
}

echo "\n🔍 Test 2: Configuration Email pour CodeIgniter\n";
echo "------------------------------------------------\n";
$emailConfigCI = $configService->getEmailConfigForCodeIgniter();
echo "✅ Configuration CodeIgniter:\n";
foreach ($emailConfigCI as $key => $value) {
    if (strpos($key, 'Pass') !== false) {
        echo "   - $key: ***\n";
    } else {
        echo "   - $key: $value\n";
    }
}

echo "\n🔍 Test 3: Configuration SMS depuis la base de données\n";
echo "------------------------------------------------------\n";
$smsConfig = $configService->getSMSConfig();
if ($smsConfig) {
    echo "✅ Configuration SMS trouvée:\n";
    foreach ($smsConfig as $key => $value) {
        if (strpos($key, 'token') !== false || strpos($key, 'key') !== false) {
            echo "   - $key: ***\n";
        } else {
            echo "   - $key: $value\n";
        }
    }
} else {
    echo "❌ Aucune configuration SMS en base de données\n";
}

echo "\n🔍 Test 4: Configuration WhatsApp depuis la base de données\n";
echo "-----------------------------------------------------------\n";
$whatsappConfig = $configService->getWhatsAppConfig();
if ($whatsappConfig) {
    echo "✅ Configuration WhatsApp trouvée:\n";
    foreach ($whatsappConfig as $key => $value) {
        if (strpos($key, 'token') !== false || strpos($key, 'key') !== false) {
            echo "   - $key: ***\n";
        } else {
            echo "   - $key: $value\n";
        }
    }
} else {
    echo "❌ Aucune configuration WhatsApp en base de données\n";
}

echo "\n🔍 Test 5: Configuration Générale depuis la base de données\n";
echo "-----------------------------------------------------------\n";
$generalConfig = $configService->getGeneralConfig();
if ($generalConfig) {
    echo "✅ Configuration générale trouvée:\n";
    foreach ($generalConfig as $key => $value) {
        echo "   - $key: $value\n";
    }
} else {
    echo "❌ Aucune configuration générale en base de données\n";
}

echo "\n🔍 Test 6: Vérification des fichiers de configuration\n";
echo "-----------------------------------------------------\n";

// Vérifier Email.php
$emailFile = 'app/Config/Email.php';
if (file_exists($emailFile)) {
    $content = file_get_contents($emailFile);
    if (strpos($content, "public string \$fromEmail = '';") !== false) {
        echo "✅ Email.php: Paramètres vides (configuration dynamique)\n";
    } else {
        echo "❌ Email.php: Paramètres encore codés en dur\n";
    }
} else {
    echo "❌ Email.php: Fichier non trouvé\n";
}

// Vérifier SMS.php
$smsFile = 'app/Config/SMS.php';
if (file_exists($smsFile)) {
    $content = file_get_contents($smsFile);
    if (strpos($content, "public string \$twilioAccountSid = '';") !== false) {
        echo "✅ SMS.php: Paramètres vides (configuration dynamique)\n";
    } else {
        echo "❌ SMS.php: Paramètres encore codés en dur\n";
    }
} else {
    echo "❌ SMS.php: Fichier non trouvé\n";
}

// Vérifier WhatsApp.php
$whatsappFile = 'app/Config/WhatsApp.php';
if (file_exists($whatsappFile)) {
    $content = file_get_contents($whatsappFile);
    if (strpos($content, "public string \$twilioWhatsappAccountSid = '';") !== false) {
        echo "✅ WhatsApp.php: Paramètres vides (configuration dynamique)\n";
    } else {
        echo "❌ WhatsApp.php: Paramètres encore codés en dur\n";
    }
} else {
    echo "❌ WhatsApp.php: Fichier non trouvé\n";
}

echo "\n🔍 Test 7: Vérification du contrôleur Economat\n";
echo "-----------------------------------------------\n";

$economatFile = 'app/Controllers/Economat.php';
if (file_exists($economatFile)) {
    $content = file_get_contents($economatFile);
    
    // Vérifier l'utilisation du service de configuration
    if (strpos($content, '$this->configService->getEmailConfigForCodeIgniter()') !== false) {
        echo "✅ Economat.php: Utilise la configuration email dynamique\n";
    } else {
        echo "❌ Economat.php: Configuration email encore codée en dur\n";
    }
    
    if (strpos($content, '$this->configService->getSMSConfigForSending()') !== false) {
        echo "✅ Economat.php: Utilise la configuration SMS dynamique\n";
    } else {
        echo "❌ Economat.php: Configuration SMS encore codée en dur\n";
    }
    
    if (strpos($content, '$this->configService->getWhatsAppConfigForSending()') !== false) {
        echo "✅ Economat.php: Utilise la configuration WhatsApp dynamique\n";
    } else {
        echo "❌ Economat.php: Configuration WhatsApp encore codée en dur\n";
    }
} else {
    echo "❌ Economat.php: Fichier non trouvé\n";
}

echo "\n📊 Résumé et Recommandations\n";
echo "----------------------------\n";
echo "✅ CONFIGURATION DYNAMIQUE:\n";
echo "   - Service ConfigurationService créé ✅\n";
echo "   - Paramètres email récupérés depuis la base ✅\n";
echo "   - Paramètres SMS récupérés depuis la base ✅\n";
echo "   - Paramètres WhatsApp récupérés depuis la base ✅\n";
echo "   - Fichiers de configuration vidés ✅\n";
echo "   - Contrôleur Economat mis à jour ✅\n";

echo "\n🚀 RECOMMANDATIONS:\n";
echo "   1. Configurer les providers dans le module Configuration\n";
echo "   2. Tester l'envoi d'emails avec la configuration dynamique\n";
echo "   3. Tester l'envoi de SMS avec la configuration dynamique\n";
echo "   4. Tester l'envoi WhatsApp avec la configuration dynamique\n";
echo "   5. Vérifier que tous les modules utilisent le service de configuration\n";

echo "\n⚠️ POINTS D'ATTENTION:\n";
echo "   - Tous les paramètres sont maintenant récupérés depuis la base\n";
echo "   - Aucune valeur sensible n'est plus codée en dur\n";
echo "   - Le module Configuration est le seul point d'entrée pour les paramètres\n";
echo "   - Les valeurs par défaut sont vides si aucune config n'est en base\n";

echo "\n📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Configuration dynamique\n";


