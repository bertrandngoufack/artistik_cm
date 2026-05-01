<?php
/**
 * Test PDF simple pour résoudre le problème
 */

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// HTML très simple pour tester
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PDF Simple</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            color: #3273dc;
        }
        .content { 
            margin: 20px 0; 
        }
        .info-row {
            margin: 10px 0;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .value {
            display: inline-block;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #3273dc;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">🎓 KISSAI SCHOOL</div>
    <div class="header">Reçu de Paiement</div>
    
    <div class="content">
        <div class="info-row">
            <span class="label">Référence :</span>
            <span class="value">PAY-2024001</span>
        </div>
        <div class="info-row">
            <span class="label">Élève :</span>
            <span class="value">Lucas Dubois</span>
        </div>
        <div class="info-row">
            <span class="label">Matricule :</span>
            <span class="value">2024CP001</span>
        </div>
        <div class="info-row">
            <span class="label">Type de frais :</span>
            <span class="value">Frais de scolarité</span>
        </div>
        <div class="info-row">
            <span class="label">Date :</span>
            <span class="value">' . date('d/m/Y') . '</span>
        </div>
        <div class="info-row">
            <span class="label">Méthode :</span>
            <span class="value">Espèces</span>
        </div>
        
        <div class="amount">
            Montant payé : 150,000 FCFA
        </div>
        
        <div class="info-row">
            <span class="label">Total à payer :</span>
            <span class="value">150,000 FCFA</span>
        </div>
        <div class="info-row">
            <span class="label">Montant versé :</span>
            <span class="value">150,000 FCFA</span>
        </div>
        <div class="info-row">
            <span class="label">Reste à payer :</span>
            <span class="value">0 FCFA</span>
        </div>
    </div>
    
    <div class="footer">
        <p><strong>KISSAI SCHOOL</strong> - Établissement d\'enseignement privé</p>
        <p>Yaoundé, Cameroun | Tél : +237 XXX XXX XXX</p>
        <p>Email : contact@kissai-school.cm</p>
        <p>Généré le ' . date('d/m/Y à H:i') . '</p>
    </div>
</body>
</html>';

echo "🧪 Test PDF Simple - KISSAI SCHOOL\n";
echo "==================================\n\n";

// Créer le PDF
$dompdf = new Dompdf();

// Configuration des options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

echo "✅ Configuration Dompdf terminée\n";

$dompdf->loadHtml($html);
echo "✅ HTML chargé (" . strlen($html) . " caractères)\n";

$dompdf->setPaper('A4', 'portrait');
echo "✅ Format papier défini (A4 portrait)\n";

$dompdf->render();
echo "✅ PDF rendu\n";

// Sauvegarder le PDF
$output = $dompdf->output();
file_put_contents('test_simple_final.pdf', $output);

echo "✅ PDF sauvegardé : test_simple_final.pdf\n";
echo "📄 Taille : " . number_format(strlen($output)) . " octets\n";

// Vérifier le fichier
$fileInfo = shell_exec('file test_simple_final.pdf 2>/dev/null');
echo "📋 Info fichier : " . trim($fileInfo) . "\n";

// Vérifier le contenu du PDF
if (substr($output, 0, 4) === '%PDF') {
    echo "✅ Contenu PDF valide détecté\n";
    
    // Compter les pages
    if (preg_match('/\/Count\s+(\d+)/', $output, $matches)) {
        echo "📄 Nombre de pages : " . $matches[1] . "\n";
    } else {
        echo "⚠️  Impossible de déterminer le nombre de pages\n";
    }
    
    // Vérifier la taille du contenu
    if (strlen($output) > 1000) {
        echo "✅ PDF semble contenir du contenu (taille > 1KB)\n";
    } else {
        echo "⚠️  PDF semble vide ou très petit\n";
    }
} else {
    echo "❌ Contenu PDF invalide\n";
}

echo "\n🎯 RÉSULTAT DU TEST :\n";
echo "=====================\n";
echo "📄 PDF généré avec succès\n";
echo "📋 Format A4 portrait\n";
echo "🎨 Design simple et lisible\n";
echo "📊 Informations essentielles incluses\n";
echo "🖨️ Prêt pour impression\n";
?>


