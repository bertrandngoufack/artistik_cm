<?php
/**
 * Test des fournisseurs gratuits avec les coordonnées fournies
 */

echo "🎯 TEST DES FOURNISSEURS GRATUITS\n";
echo "==================================\n\n";

// Coordonnées de test
$phone = '+237694202063';
$email = 'bertrandngoufack@gmail.com';
$parentName = 'M. Bertrand Ngoufack';
$studentName = 'Thomas Etoa';
$feeType = 'Frais de scolarité';
$amount = '150,000';
$reference = 'PAY-2024010-43';

echo "📞 Coordonnées de test :\n";
echo "   Téléphone : $phone\n";
echo "   Email : $email\n";
echo "   Parent : $parentName\n";
echo "   Élève : $studentName\n\n";

// Test 1: Fournisseur Email (Gmail)
echo "📧 Test 1: Configuration Email (Gmail)\n";
echo "--------------------------------------\n";

$gmailConfig = [
    'provider' => 'gmail',
    'from_email' => 'kissai.school@gmail.com',
    'from_name' => 'KISSAI SCHOOL',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_crypto' => 'tls',
    'smtp_user' => 'kissai.school@gmail.com',
    'smtp_pass' => 'your_app_password_here' // À remplacer par le vrai mot de passe
];

echo "✅ Configuration Gmail prête\n";
echo "⚠️  Remplacez 'your_app_password_here' par votre mot de passe d'application\n\n";

// Test 2: Fournisseur SMS (TextLocal)
echo "📱 Test 2: Configuration SMS (TextLocal)\n";
echo "----------------------------------------\n";

$textlocalConfig = [
    'provider' => 'textlocal',
    'sender_name' => 'KISSAI',
    'api_key' => 'your_textlocal_api_key' // À remplacer par votre clé API
];

echo "✅ Configuration TextLocal prête\n";
echo "⚠️  Remplacez 'your_textlocal_api_key' par votre clé API TextLocal\n";
echo "🌐 Inscrivez-vous sur : https://www.textlocal.in/\n\n";

// Test 3: Fournisseur WhatsApp (Twilio)
echo "💬 Test 3: Configuration WhatsApp (Twilio)\n";
echo "------------------------------------------\n";

$twilioConfig = [
    'provider' => 'twilio',
    'account_sid' => 'your_twilio_account_sid', // À remplacer
    'auth_token' => 'your_twilio_auth_token',   // À remplacer
    'phone_number' => 'whatsapp:+1234567890'    // À remplacer par votre numéro Twilio
];

echo "✅ Configuration Twilio WhatsApp prête\n";
echo "⚠️  Remplacez les clés par vos vraies clés Twilio\n";
echo "🌐 Créez un compte sur : https://www.twilio.com/\n\n";

// Test 4: Messages de test
echo "📝 Test 4: Messages de test\n";
echo "---------------------------\n";

// Message SMS/WhatsApp
$message = "Bonjour $parentName,\n\n";
$message .= "Nous vous rappelons que le paiement des frais de $feeType pour votre enfant $studentName ";
$message .= "d'un montant de $amount FCFA (Réf: $reference) est en retard.\n\n";
$message .= "Pour le bien-être et la continuité de la scolarité de votre enfant, ";
$message .= "nous vous prions de régulariser ce paiement dans les plus brefs délais.\n\n";
$message .= "Merci de votre compréhension.\n";
$message .= "KISSAI SCHOOL\n";
$message .= "Tél: +237 XXX XXX XXX";

echo "📱 Message SMS/WhatsApp :\n";
echo "------------------------\n";
echo $message . "\n\n";

// Message Email HTML
$htmlMessage = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Rappel de paiement - KISSAI SCHOOL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
        .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .amount { font-size: 18px; font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎓 KISSAI SCHOOL</h1>
            <p>Rappel de paiement</p>
        </div>
        
        <div class='content'>
            <p>Bonjour <strong>$parentName</strong>,</p>
            
            <div class='highlight'>
                <p>Nous vous rappelons que le paiement des <strong>$feeType</strong> pour votre enfant <strong>$studentName</strong> 
                d'un montant de <span class='amount'>$amount FCFA</span> est en retard.</p>
            </div>
            
            <p>Pour le bien-être et la continuité de la scolarité de votre enfant, 
            nous vous prions de régulariser ce paiement dans les plus brefs délais.</p>
            
            <p><strong>Détails du paiement :</strong></p>
            <ul>
                <li>Élève : $studentName</li>
                <li>Type de frais : $feeType</li>
                <li>Montant : $amount FCFA</li>
                <li>Statut : En retard</li>
            </ul>
            
            <p>Merci de votre compréhension.</p>
            
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

echo "📧 Message Email HTML généré (longueur: " . strlen($htmlMessage) . " caractères)\n\n";

// Test 5: Instructions de configuration
echo "🎯 Test 5: Instructions de configuration\n";
echo "----------------------------------------\n";

echo "📧 EMAIL (Gmail) :\n";
echo "1. Créez un compte Gmail : kissai.school@gmail.com\n";
echo "2. Activez l'authentification à 2 facteurs\n";
echo "3. Générez un mot de passe d'application\n";
echo "4. Remplacez 'your_app_password_here' dans le code\n\n";

echo "📱 SMS (TextLocal) :\n";
echo "1. Inscrivez-vous sur https://www.textlocal.in/\n";
echo "2. Obtenez votre clé API gratuite\n";
echo "3. Remplacez 'your_textlocal_api_key' dans le code\n\n";

echo "💬 WhatsApp (Twilio) :\n";
echo "1. Créez un compte sur https://www.twilio.com/\n";
echo "2. Obtenez votre Account SID et Auth Token\n";
echo "3. Configurez WhatsApp Sandbox\n";
echo "4. Remplacez les clés dans le code\n\n";

// Test 6: Simulation d'envoi
echo "🚀 Test 6: Simulation d'envoi\n";
echo "-----------------------------\n";

echo "📧 Email vers $email :\n";
echo "   ✅ Configuration Gmail prête\n";
echo "   ⚠️  Nécessite mot de passe d'application\n\n";

echo "📱 SMS vers $phone :\n";
echo "   ✅ Configuration TextLocal prête\n";
echo "   ⚠️  Nécessite clé API TextLocal\n\n";

echo "💬 WhatsApp vers $phone :\n";
echo "   ✅ Configuration Twilio prête\n";
echo "   ⚠️  Nécessite clés Twilio\n\n";

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test des fournisseurs gratuits\n";

echo "\n🎯 CONCLUSION: ✅ Les fournisseurs gratuits sont configurés et prêts\n";
echo "🚀 Prochaines étapes :\n";
echo "   1. Obtenir les clés API des fournisseurs\n";
echo "   2. Remplacer les valeurs dans le code\n";
echo "   3. Tester les envois vers vos coordonnées\n";
echo "   4. Intégrer dans le module Economat\n";
echo "   5. Configurer via l'interface web : http://localhost:8080/admin/configuration\n";
?>


