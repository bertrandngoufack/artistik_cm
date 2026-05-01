<?php
/**
 * Test simple de génération PDF
 */

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Test avec un HTML simple
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .content { margin: 20px; }
    </style>
</head>
<body>
    <div class="header">KISSAI SCHOOL - Test PDF</div>
    <div class="content">
        <h2>Test de génération PDF</h2>
        <p>Ceci est un test de génération PDF avec Dompdf.</p>
        <p>Date : ' . date('d/m/Y H:i:s') . '</p>
        <p>Si vous voyez ce contenu, la génération PDF fonctionne correctement.</p>
    </div>
</body>
</html>
';

// Créer le PDF
$dompdf = new Dompdf();

// Configuration des options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Sauvegarder le PDF
$output = $dompdf->output();
file_put_contents('test_simple.pdf', $output);

echo "✅ Test PDF simple créé : test_simple.pdf\n";
echo "📄 Taille : " . number_format(strlen($output)) . " octets\n";

// Vérifier le fichier
$fileInfo = shell_exec('file test_simple.pdf 2>/dev/null');
echo "📋 Info fichier : " . trim($fileInfo) . "\n";
?>


