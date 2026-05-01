<?php
/**
 * Diagnostic du problème PDF
 */

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Récupérer un paiement d'exemple
    $stmt = $pdo->query("SELECT * FROM payments LIMIT 1");
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo "❌ Aucun paiement trouvé dans la base\n";
        exit;
    }
    
    echo "✅ Paiement trouvé : ID {$payment['id']}\n";
    
    // Récupérer l'élève
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$payment['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer le type de frais
    $stmt = $pdo->prepare("SELECT * FROM fee_types WHERE id = ?");
    $stmt->execute([$payment['fee_type_id']]);
    $feeType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer l'historique des paiements
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY payment_date DESC LIMIT 5");
    $stmt->execute([$payment['student_id']]);
    $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les types de frais pour l'historique
    $feeTypes = [];
    foreach ($paymentHistory as $histPayment) {
        $stmt = $pdo->prepare("SELECT * FROM fee_types WHERE id = ?");
        $stmt->execute([$histPayment['fee_type_id']]);
        $feeTypes[$histPayment['fee_type_id']] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Calculer les montants
    $totalAmount = $payment['amount_paid'];
    $paidAmount = $payment['amount_paid'];
    $remainingAmount = 0;
    
    if ($feeType) {
        $totalAmount = $feeType['amount'];
        $remainingAmount = $totalAmount - $paidAmount;
    }
    
    echo "✅ Données préparées pour le PDF\n";
    
    // Créer le HTML pour le PDF
    $html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Reçu de Paiement - KISSAI SCHOOL</title>
        <style>
            @page {
                margin: 0.8cm;
                size: A4 portrait;
            }
            
            body {
                font-family: Arial, sans-serif;
                font-size: 9px;
                line-height: 1.1;
                color: #333;
                margin: 0;
                padding: 8px;
                background: white;
            }
            
            .receipt-container {
                max-width: 100%;
                margin: 0 auto;
                background: white;
            }
            
            .receipt-header {
                text-align: center;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 2px solid #3273dc;
            }
            
            .school-logo {
                font-size: 18px;
                margin-bottom: 2px;
            }
            
            .receipt-title {
                font-size: 14px;
                font-weight: bold;
                color: #3273dc;
                margin-bottom: 2px;
            }
            
            .receipt-number {
                font-size: 10px;
                font-weight: bold;
                color: #333;
                margin-top: 6px;
                padding: 2px 6px;
                background: #f0f8ff;
                border: 1px solid #3273dc;
                display: inline-block;
            }
            
            .info-section {
                margin-bottom: 8px;
            }
            
            .info-title {
                font-size: 10px;
                font-weight: bold;
                color: #3273dc;
                margin-bottom: 4px;
                padding: 2px 0;
                border-bottom: 1px solid #ddd;
            }
            
            .info-grid {
                display: table;
                width: 100%;
            }
            
            .info-row {
                display: table-row;
            }
            
            .info-item {
                display: table-cell;
                padding: 1px 4px;
                vertical-align: top;
            }
            
            .amount-section {
                margin: 12px 0;
                padding: 8px;
                background: #f8f9fa;
                border: 1px solid #ddd;
            }
            
            .amount-title {
                font-size: 11px;
                font-weight: bold;
                color: #3273dc;
                margin-bottom: 6px;
                text-align: center;
            }
            
            .amount-grid {
                display: table;
                width: 100%;
            }
            
            .amount-row {
                display: table-row;
            }
            
            .amount-label {
                display: table-cell;
                padding: 2px 4px;
                font-size: 9px;
                font-weight: bold;
                text-align: right;
                width: 40%;
            }
            
            .amount-value {
                display: table-cell;
                padding: 2px 4px;
                font-size: 9px;
                text-align: left;
                width: 60%;
            }
            
            .history-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 7px;
                margin: 3px 0;
            }
            
            .history-table th,
            .history-table td {
                padding: 2px 3px;
                border: 1px solid #ddd;
                text-align: left;
            }
            
            .history-table th {
                background: #3273dc;
                color: white;
                font-weight: bold;
            }
            
            .footer-section {
                margin-top: 12px;
                padding-top: 8px;
                border-top: 1px solid #ddd;
            }
            
            .signature-row {
                display: table;
                width: 100%;
            }
            
            .signature-box {
                display: table-cell;
                text-align: center;
                padding: 6px;
            }
            
            .signature-line {
                width: 100px;
                height: 1px;
                background: #333;
                margin: 10px auto 2px;
            }
            
            .school-info {
                text-align: center;
                font-size: 7px;
                color: #666;
                line-height: 1.1;
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <!-- En-tête -->
            <div class="receipt-header">
                <div class="school-logo">🎓 KISSAI SCHOOL</div>
                <div class="receipt-title">Reçu de Paiement</div>
                <div class="receipt-subtitle">Établissement d\'enseignement privé</div>
                <div class="receipt-number">Réf: ' . ($payment['reference_number'] ?? 'PAY-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT)) . '</div>
            </div>

            <!-- Informations de l\'élève -->
            <div class="info-section">
                <div class="info-title">👤 Informations de l\'Élève</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item"><strong>Nom :</strong></div>
                        <div class="info-item">' . ($student['first_name'] ?? 'N/A') . ' ' . ($student['last_name'] ?? 'N/A') . '</div>
                        <div class="info-item"><strong>Matricule :</strong></div>
                        <div class="info-item">' . ($student['matricule'] ?? 'N/A') . '</div>
                    </div>
                </div>
            </div>

            <!-- Informations du paiement -->
            <div class="info-section">
                <div class="info-title">💳 Informations du Paiement</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item"><strong>Type de frais :</strong></div>
                        <div class="info-item">' . ($feeType['name'] ?? 'N/A') . '</div>
                        <div class="info-item"><strong>Date :</strong></div>
                        <div class="info-item">' . date('d/m/Y', strtotime($payment['payment_date'])) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-item"><strong>Méthode :</strong></div>
                        <div class="info-item">' . $payment['payment_method'] . '</div>
                        <div class="info-item"><strong>Année scolaire :</strong></div>
                        <div class="info-item">' . $payment['academic_year'] . '</div>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif financier -->
            <div class="amount-section">
                <div class="amount-title">💰 Récapitulatif Financier</div>
                <div class="amount-grid">
                    <div class="amount-row">
                        <div class="amount-label">Montant total à payer :</div>
                        <div class="amount-value">' . number_format($totalAmount, 0, ',', ' ') . ' FCFA</div>
                    </div>
                    <div class="amount-row">
                        <div class="amount-label">Montant versé :</div>
                        <div class="amount-value">' . number_format($paidAmount, 0, ',', ' ') . ' FCFA</div>
                    </div>
                    <div class="amount-row">
                        <div class="amount-label">Reste à payer :</div>
                        <div class="amount-value">' . number_format($remainingAmount, 0, ',', ' ') . ' FCFA</div>
                    </div>
                </div>
            </div>

            <!-- Historique des paiements -->
            <div class="info-section">
                <div class="info-title">📋 Historique des Paiements</div>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    if (!empty($paymentHistory)) {
        foreach ($paymentHistory as $histPayment) {
            $histFeeType = isset($feeTypes[$histPayment['fee_type_id']]) ? $feeTypes[$histPayment['fee_type_id']] : null;
            $isCurrentPayment = ($histPayment['id'] == $payment['id']);
            
            $html .= '
                        <tr class="' . ($isCurrentPayment ? 'highlight' : '') . '">
                            <td>' . date('d/m/Y', strtotime($histPayment['payment_date'])) . '</td>
                            <td>' . ($histFeeType['name'] ?? 'Frais de scolarité') . '</td>
                            <td style="text-align: right; ' . ($isCurrentPayment ? 'font-weight: bold;' : '') . '">' . number_format($histPayment['amount_paid'], 0, ',', ' ') . ' FCFA</td>
                            <td style="text-align: center;">
                                <span style="background: #48c774; color: white; padding: 1px 2px; border-radius: 1px; font-size: 6px;">
                                    ' . $histPayment['payment_method'] . '
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: #48c774; color: white; padding: 1px 2px; border-radius: 1px; font-size: 6px;">Payé</span>
                            </td>
                        </tr>';
        }
    } else {
        $html .= '
                        <tr class="highlight">
                            <td>' . date('d/m/Y', strtotime($payment['payment_date'])) . '</td>
                            <td>' . ($feeType['name'] ?? 'Frais de scolarité') . '</td>
                            <td style="text-align: right; font-weight: bold;">' . number_format($paidAmount, 0, ',', ' ') . ' FCFA</td>
                            <td style="text-align: center;">
                                <span style="background: #48c774; color: white; padding: 1px 2px; border-radius: 1px; font-size: 6px;">
                                    ' . $payment['payment_method'] . '
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: #48c774; color: white; padding: 1px 2px; border-radius: 1px; font-size: 6px;">Payé</span>
                            </td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>

            <!-- Pied de page -->
            <div class="footer-section">
                <div class="signature-section">
                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div style="font-size: 8px;"><strong>Signature du Payeur</strong></div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div style="font-size: 8px;"><strong>Signature du Caissier</strong></div>
                        </div>
                    </div>
                </div>
                
                <div class="school-info">
                    <p><strong>KISSAI SCHOOL</strong> - Établissement d\'enseignement privé</p>
                    <p>Yaoundé, Cameroun | Tél : +237 XXX XXX XXX</p>
                    <p>Email : contact@kissai-school.cm</p>
                    <div style="margin-top: 6px; font-style: italic; color: #999;">
                        Généré le ' . date('d/m/Y à H:i') . '
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    echo "✅ HTML généré (" . strlen($html) . " caractères)\n";
    
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
    file_put_contents('diagnostic_pdf_output.pdf', $output);
    
    echo "✅ PDF généré : diagnostic_pdf_output.pdf\n";
    echo "📄 Taille : " . number_format(strlen($output)) . " octets\n";
    
    // Vérifier le fichier
    $fileInfo = shell_exec('file diagnostic_pdf_output.pdf 2>/dev/null');
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
    } else {
        echo "❌ Contenu PDF invalide\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>


