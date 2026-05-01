<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Paiement - KISSAI SCHOOL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
            .receipt-container { box-shadow: none !important; border: 2px solid #000 !important; }
            .receipt-header { background-color: #f5f5f5 !important; -webkit-print-color-adjust: exact; }
            .amount-section { background-color: #f0f8ff !important; -webkit-print-color-adjust: exact; }
            .footer-section { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #3273dc;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #3273dc 0%, #209cee 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .school-logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .receipt-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .receipt-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-top: 15px;
            font-weight: bold;
        }
        
        .receipt-body {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #3273dc;
            border-bottom: 2px solid #3273dc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            min-width: 120px;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .amount-section {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            border: 2px solid #3273dc;
        }
        
        .amount-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #3273dc;
            margin-bottom: 25px;
        }
        
        .amount-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
        }
        
        .amount-item {
            padding: 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .amount-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .amount-value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .total-amount .amount-value {
            color: #3273dc;
        }
        
        .paid-amount .amount-value {
            color: #48c774;
        }
        
        .remaining-amount .amount-value {
            color: #f14668;
        }
        
        .payment-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        
        .payment-method {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
        }
        
        .payment-method.cash { background-color: #48c774; }
        .payment-method.card { background-color: #3273dc; }
        .payment-method.transfer { background-color: #ffdd57; color: #333; }
        .payment-method.mobile { background-color: #f14668; }
        
        .footer-section {
            background: #f9f9f9;
            padding: 25px;
            text-align: center;
            border-top: 2px solid #ddd;
        }
        
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px;
            border-top: 2px solid #ddd;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background: #333;
            margin: 20px auto 10px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 8rem;
            color: rgba(50, 115, 220, 0.1);
            pointer-events: none;
            z-index: -1;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="button is-primary is-large" onclick="window.print()">
            <span class="icon"><i class="fas fa-print"></i></span>
            <span>Imprimer le Reçu</span>
        </button>
    </div>

    <div class="watermark">KISSAI SCHOOL</div>

    <div class="receipt-container">
        <!-- En-tête du reçu -->
        <div class="receipt-header">
            <div class="school-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="receipt-title">KISSAI SCHOOL</div>
            <div class="receipt-subtitle">Établissement Scolaire d'Excellence</div>
            <div class="receipt-subtitle">Yaoundé, Cameroun</div>
            <div class="receipt-number">
                Reçu N° <?= $payment['reference'] ?? 'PAY-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?>
            </div>
        </div>

        <!-- Corps du reçu -->
        <div class="receipt-body">
            <!-- Informations de l'élève -->
            <div class="info-section">
                <div class="info-title">
                    <i class="fas fa-user-graduate"></i> Informations de l'Élève
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nom complet :</span>
                        <span class="info-value"><?= $student['first_name'] ?? 'Lucas' ?> <?= $student['last_name'] ?? 'Dubois' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Matricule :</span>
                        <span class="info-value"><?= $student['matricule'] ?? '2024CP001' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Classe :</span>
                        <span class="info-value"><?= $student['class'] ?? 'CP A' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date de naissance :</span>
                        <span class="info-value"><?= $student['birth_date'] ?? '15/03/2018' ?></span>
                    </div>
                </div>
            </div>

            <!-- Informations du paiement -->
            <div class="info-section">
                <div class="info-title">
                    <i class="fas fa-credit-card"></i> Détails du Paiement
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Type de frais :</span>
                        <span class="info-value"><?= $feeType['name'] ?? 'Frais de scolarité' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date de paiement :</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Méthode :</span>
                        <span class="info-value">
                            <span class="payment-method <?= strtolower($payment['payment_method']) ?>">
                                <?= $payment['payment_method'] === 'CASH' ? 'Espèces' : 
                                    ($payment['payment_method'] === 'CARD' ? 'Carte' : 
                                    ($payment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile Money')) ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Référence :</span>
                        <span class="info-value"><?= $payment['reference'] ?? 'REF-' . $payment['id'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Section des montants -->
            <div class="amount-section">
                <div class="amount-title">
                    <i class="fas fa-calculator"></i> Récapitulatif Financier
                </div>
                <div class="amount-grid">
                    <div class="amount-item total-amount">
                        <div class="amount-label">Total à Payer</div>
                        <div class="amount-value"><?= number_format($totalAmount, 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="amount-item paid-amount">
                        <div class="amount-label">Versement Actuel</div>
                        <div class="amount-value"><?= number_format($paidAmount, 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="amount-item remaining-amount">
                        <div class="amount-label">Reste à Payer</div>
                        <div class="amount-value"><?= number_format($remainingAmount, 0, ',', ' ') ?> FCFA</div>
                    </div>
                </div>
            </div>

            <!-- Historique des paiements -->
            <div class="info-section">
                <div class="info-title">
                    <i class="fas fa-history"></i> Historique des Paiements
                </div>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($paymentHistory) && !empty($paymentHistory)): ?>
                                <?php foreach ($paymentHistory as $histPayment): ?>
                                    <?php 
                                    $histFeeType = isset($feeTypes[$histPayment['fee_type_id']]) ? $feeTypes[$histPayment['fee_type_id']] : null;
                                    $isCurrentPayment = ($histPayment['id'] == $payment['id']);
                                    ?>
                                    <tr class="<?= $isCurrentPayment ? 'has-background-info-light' : '' ?>">
                                        <td><?= date('d/m/Y', strtotime($histPayment['payment_date'])) ?></td>
                                        <td><?= $histFeeType['name'] ?? 'Frais de scolarité' ?></td>
                                        <td class="<?= $isCurrentPayment ? 'has-text-weight-bold' : '' ?>"><?= number_format($histPayment['amount_paid'], 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <span class="tag <?= $histPayment['payment_method'] === 'CASH' ? 'is-success' : ($histPayment['payment_method'] === 'CARD' ? 'is-info' : 'is-danger') ?>">
                                                <?= $histPayment['payment_method'] === 'CASH' ? 'Espèces' : 
                                                    ($histPayment['payment_method'] === 'CARD' ? 'Carte' : 
                                                    ($histPayment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="tag is-success">Payé</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="has-background-info-light">
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><?= $feeType['name'] ?? 'Frais de scolarité' ?></td>
                                    <td class="has-text-weight-bold"><?= number_format($paidAmount, 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="tag is-success">
                                            <?= $payment['payment_method'] === 'CASH' ? 'Espèces' : 
                                                ($payment['payment_method'] === 'CARD' ? 'Carte' : 
                                                ($payment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag is-success">Payé</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($payment['notes'])): ?>
            <div class="info-section">
                <div class="info-title">
                    <i class="fas fa-sticky-note"></i> Notes
                </div>
                <div class="content">
                    <?= $payment['notes'] ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pied de page -->
        <div class="footer-section">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div><strong>Signature du Payeur</strong></div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div><strong>Signature du Caissier</strong></div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p><strong>KISSAI SCHOOL</strong> - Établissement d'enseignement privé</p>
                <p>Adresse : Yaoundé, Cameroun | Tél : +237 XXX XXX XXX</p>
                <p>Email : contact@kissai-school.cm | Site : www.kissai-school.cm</p>
                <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                    Ce reçu est généré automatiquement le <?= date('d/m/Y à H:i') ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-impression si demandé
        if (window.location.search.includes('autoprint=1')) {
            window.print();
        }
        
        // Ajouter des styles pour l'impression
        document.addEventListener('DOMContentLoaded', function() {
            const style = document.createElement('style');
            style.textContent = `
                @media print {
                    @page { margin: 1cm; }
                    body { font-size: 12pt; }
                    .receipt-container { page-break-inside: avoid; }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>
