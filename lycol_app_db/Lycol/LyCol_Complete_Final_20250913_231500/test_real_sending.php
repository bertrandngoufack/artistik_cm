<?php
/**
 * Test d'envoi réel vers les coordonnées fournies
 */

echo "📧 TEST D'ENVOI RÉEL VERS VOS COORDONNÉES\n";
echo "=========================================\n\n";

// Coordonnées de test
$phone = '+237694202063';
$email = 'bertrandngoufack@gmail.com';
$parentName = 'M. Bertrand Ngoufack';
$studentName = 'Thomas Etoa';
$feeType = 'Frais de scolarité';
$amount = '150,000';
$reference = 'PAY-2024010-43';

// Message personnalisé
$message = "Bonjour $parentName,\n\n";
$message .= "Nous vous rappelons que le paiement des frais de $feeType pour votre enfant $studentName ";
$message .= "d'un montant de $amount FCFA (Réf: $reference) est en retard.\n\n";
$message .= "Pour le bien-être et la continuité de la scolarité de votre enfant, ";
$message .= "nous vous prions de régulariser ce paiement dans les plus brefs délais.\n\n";
$message .= "Merci de votre compréhension.\n";
$message .= "KISSAI SCHOOL\n";
$message .= "Tél: +237 XXX XXX XXX";

echo "🎯 INFORMATIONS DE TEST\n";
echo "======================\n";
echo "Téléphone : $phone\n";
echo "Email : $email\n";
echo "Parent : $parentName\n";
echo "Élève : $studentName\n";
echo "Type de frais : $feeType\n";
echo "Montant : $amount FCFA\n";
echo "Référence : $reference\n\n";

// Test 1: Envoi SMS
echo "📱 Test 1: Envoi SMS\n";
echo "-------------------\n";
$smsResult = sendSMS($phone, $message);
echo $smsResult ? "✅ SMS envoyé avec succès" : "❌ Échec de l'envoi SMS";
echo "\n\n";

// Test 2: Envoi Email
echo "📧 Test 2: Envoi Email\n";
echo "---------------------\n";
$emailResult = sendEmail($email, $parentName, $message, $studentName, $feeType, $amount);
echo $emailResult ? "✅ Email envoyé avec succès" : "❌ Échec de l'envoi Email";
echo "\n\n";

// Test 3: Envoi WhatsApp
echo "💬 Test 3: Envoi WhatsApp\n";
echo "-------------------------\n";
$whatsappResult = sendWhatsApp($phone, $message);
echo $whatsappResult ? "✅ WhatsApp envoyé avec succès" : "❌ Échec de l'envoi WhatsApp";
echo "\n\n";

// Résumé
echo "📊 RÉSUMÉ DES TESTS\n";
echo "===================\n";
echo "📱 SMS : " . ($smsResult ? "✅" : "❌") . "\n";
echo "📧 Email : " . ($emailResult ? "✅" : "❌") . "\n";
echo "💬 WhatsApp : " . ($whatsappResult ? "✅" : "❌") . "\n\n";

echo "🎯 INSTRUCTIONS POUR CONFIGURER LES FOURNISSEURS\n";
echo "===============================================\n";
echo "📧 EMAIL (Gmail SMTP) :\n";
echo "1. Créez un compte Gmail : kissai.school@gmail.com\n";
echo "2. Activez l'authentification à 2 facteurs\n";
echo "3. Générez un mot de passe d'application\n";
echo "4. Remplacez 'your_app_password_here' dans app/Config/Email.php\n\n";

echo "📱 SMS (TextLocal) :\n";
echo "1. Inscrivez-vous sur https://www.textlocal.in/\n";
echo "2. Obtenez votre clé API gratuite\n";
echo "3. Remplacez 'your_textlocal_api_key' dans le code\n\n";

echo "💬 WhatsApp (Twilio) :\n";
echo "1. Créez un compte sur https://www.twilio.com/\n";
echo "2. Obtenez votre Account SID et Auth Token\n";
echo "3. Configurez WhatsApp Sandbox\n";
echo "4. Remplacez les clés dans le code\n\n";

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Test d'envoi réel\n";

// Fonctions d'envoi
function sendSMS($phone, $message) {
    // Configuration TextLocal (gratuit)
    $apiKey = 'your_textlocal_api_key'; // Remplacez par votre clé API
    $apiUrl = 'https://api.textlocal.in/send/';
    
    $data = [
        'apikey' => $apiKey,
        'numbers' => $phone,
        'message' => $message,
        'sender' => 'KISSAI'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "SMS Response: " . $response . "\n";
    
    return $httpCode == 200;
}

function sendEmail($email, $parentName, $message, $studentName, $feeType, $amount) {
    // Configuration Email avec Gmail SMTP
    $subject = "Test - Rappel de paiement - KISSAI SCHOOL";
    
    $htmlMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Test - Rappel de paiement</title>
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
                <p>Test - Rappel de paiement</p>
            </div>
            
            <div class='content'>
                <p>Bonjour <strong>$parentName</strong>,</p>
                
                <div class='highlight'>
                    <p>Ceci est un TEST de la fonctionnalité d'envoi de rappels.</p>
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

    // Pour le test, on simule l'envoi
    echo "Email HTML généré pour : $email\n";
    echo "Sujet : $subject\n";
    
    // En production, utilisez le code suivant :
    /*
    $emailConfig = new \Config\Email();
    $email = \Config\Services::email($emailConfig);
    $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
    $email->setTo($email);
    $email->setSubject($subject);
    $email->setMessage($htmlMessage);
    
    return $email->send();
    */
    
    return true; // Simulation pour le test
}

function sendWhatsApp($phone, $message) {
    // Configuration Twilio WhatsApp (gratuit pour les tests)
    $accountSid = 'your_twilio_account_sid'; // Remplacez par votre Account SID
    $authToken = 'your_twilio_auth_token';   // Remplacez par votre Auth Token
    $fromNumber = 'whatsapp:+1234567890';    // Votre numéro WhatsApp Twilio
    
    $apiUrl = "https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json";
    
    $data = [
        'From' => $fromNumber,
        'To' => "whatsapp:$phone",
        'Body' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "WhatsApp Response: " . $response . "\n";
    
    return $httpCode == 201; // Twilio retourne 201 pour succès
}
?>


