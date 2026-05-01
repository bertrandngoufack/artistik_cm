<?php
/**
 * Démonstration du message de rappel multi-canal
 */

echo "📧 DÉMONSTRATION DU MESSAGE DE RAPPEL MULTI-CANAL\n";
echo "================================================\n\n";

// Données d'exemple
$studentName = "Thomas Etoa";
$parentName = "M. Jean Etoa";
$feeType = "Frais de scolarité";
$amount = "150,000";
$reference = "PAY-2024010-43";
$parentPhone = "+237612345678";
$parentEmail = "jean.etoa@example.com";

echo "🎯 INFORMATIONS DU PAIEMENT\n";
echo "==========================\n";
echo "Élève : $studentName\n";
echo "Parent : $parentName\n";
echo "Type de frais : $feeType\n";
echo "Montant : $amount FCFA\n";
echo "Référence : $reference\n";
echo "Téléphone : $parentPhone\n";
echo "Email : $parentEmail\n\n";

// Message SMS/WhatsApp
echo "📱 MESSAGE SMS/WHATSAPP\n";
echo "======================\n";
$smsMessage = "Bonjour $parentName,\n\n";
$smsMessage .= "Nous vous rappelons que le paiement des frais de $feeType pour votre enfant $studentName ";
$smsMessage .= "d'un montant de $amount FCFA (Réf: $reference) est en retard.\n\n";
$smsMessage .= "Pour le bien-être et la continuité de la scolarité de votre enfant, ";
$smsMessage .= "nous vous prions de régulariser ce paiement dans les plus brefs délais.\n\n";
$smsMessage .= "Merci de votre compréhension.\n";
$smsMessage .= "KISSAI SCHOOL\n";
$smsMessage .= "Tél: +237 XXX XXX XXX";

echo $smsMessage . "\n\n";

// Message Email HTML
echo "📧 MESSAGE EMAIL HTML\n";
echo "====================\n";
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
                <li>Référence : $reference</li>
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

echo "Email HTML généré avec succès !\n\n";

// Canaux d'envoi
echo "📡 CANAUX D'ENVOI\n";
echo "=================\n";
echo "✅ SMS : $parentPhone\n";
echo "✅ Email : $parentEmail\n";
echo "✅ WhatsApp : $parentPhone\n\n";

// Statistiques
echo "📊 STATISTIQUES D'ENVOI\n";
echo "======================\n";
echo "📱 SMS : Envoyé avec succès\n";
echo "📧 Email : Envoyé avec succès\n";
echo "💬 WhatsApp : Envoyé avec succès\n";
echo "🗄️ Base de données : Enregistré\n\n";

echo "🎯 OBJECTIF DU MESSAGE\n";
echo "======================\n";
echo "✅ Rappeler poliment le retard de paiement\n";
echo "✅ Insister sur le bien-être de l'enfant\n";
echo "✅ Fournir tous les détails nécessaires\n";
echo "✅ Maintenir une relation professionnelle\n";
echo "✅ Encourager la régularisation rapide\n\n";

echo "🌟 AVANTAGES DU SYSTÈME MULTI-CANAL\n";
echo "===================================\n";
echo "✅ Couverture maximale (SMS + Email + WhatsApp)\n";
echo "✅ Messages personnalisés selon le canal\n";
echo "✅ Suivi complet des envois\n";
echo "✅ Historique détaillé\n";
echo "✅ Interface utilisateur intuitive\n";
echo "✅ Automatisation possible\n\n";

echo "📅 Démonstration effectuée le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Module Rappels Multi-Canal\n";
echo "🌟 Fonctionnalité : OPÉRATIONNELLE\n";
?>


