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
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 32px;
            color: rgba(0, 0, 0, 0.08);
            z-index: -1;
            pointer-events: none;
        }
        
        .receipt-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            position: relative;
            z-index: 1;
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
        
        .receipt-subtitle {
            font-size: 8px;
            color: #666;
            margin-bottom: 1px;
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
        
        .receipt-body {
            margin-bottom: 12px;
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
        
        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 80px;
            display: inline-block;
        }
        
        .info-value {
            color: #333;
        }
        
        .amount-section {
            margin: 10px 0;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .amount-title {
            font-size: 12px;
            font-weight: bold;
            color: #3273dc;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .amount-grid {
            display: table;
            width: 100%;
        }
        
        .amount-row {
            display: table-row;
        }
        
        .amount-item {
            display: table-cell;
            text-align: center;
            padding: 6px;
            border: 1px solid #ddd;
            background: white;
        }
        
        .amount-label {
            font-size: 8px;
            font-weight: bold;
            color: #555;
            margin-bottom: 2px;
        }
        
        .amount-value {
            font-size: 12px;
            font-weight: bold;
            color: #3273dc;
        }
        
        .total-amount .amount-value {
            color: #f14668;
        }
        
        .paid-amount .amount-value {
            color: #48c774;
        }
        
        .remaining-amount .amount-value {
            color: #ffdd57;
        }
        
        .notes-section {
            margin: 8px 0;
            padding: 6px;
            background: #f0f8ff;
            border-left: 3px solid #3273dc;
        }
        
        .notes-title {
            font-size: 10px;
            font-weight: bold;
            color: #3273dc;
            margin-bottom: 2px;
        }
        
        .footer-section {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
        }
        
        .signature-section {
            margin-bottom: 10px;
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
        
        .school-info p {
            margin: 1px 0;
        }
        
        .generation-info {
            margin-top: 6px;
            font-style: italic;
            color: #999;
        }
        
        .payment-method {
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .payment-method.cash {
            background: #48c774;
            color: white;
        }
        
        .payment-method.card {
            background: #3273dc;
            color: white;
        }
        
        .payment-method.bank_transfer {
            background: #ffdd57;
            color: #333;
        }
        
        .payment-method.mobile_money {
            background: #f14668;
            color: white;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        /* Tableau d'historique ultra-compact */
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
        
        .history-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .history-table tr.highlight {
            background: #e8f4fd;
        }
        
        .history-table .tag {
            padding: 1px 2px;
            border-radius: 1px;
            font-size: 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">KISSAI SCHOOL</div>

    <div class="receipt-container no-break">
        <!-- En-tête du reçu -->
        <div class="receipt-header">
            <div class="school-logo">🎓</div>
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
                <div class="info-title">📚 Informations de l'Élève</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Nom :</span>
                            <span class="info-value"><?= $student['first_name'] ?? 'Lucas' ?> <?= $student['last_name'] ?? 'Dubois' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Matricule :</span>
                            <span class="info-value"><?= $student['matricule'] ?? '2024CP001' ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Classe :</span>
                            <span class="info-value"><?= $student['class'] ?? 'CP A' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Né(e) le :</span>
                            <span class="info-value"><?= $student['birth_date'] ?? '15/03/2018' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du paiement -->
            <div class="info-section">
                <div class="info-title">💳 Détails du Paiement</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Type :</span>
                            <span class="info-value"><?= $feeType['name'] ?? 'Frais de scolarité' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date :</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Méthode :</span>
                            <span class="info-value">
                                <span class="payment-method <?= strtolower($payment['payment_method']) ?>">
                                    <?= $payment['payment_method'] === 'CASH' ? 'Espèces' : 
                                        ($payment['payment_method'] === 'CARD' ? 'Carte' : 
                                        ($payment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile')) ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Réf :</span>
                            <span class="info-value"><?= $payment['reference'] ?? 'REF-' . $payment['id'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section des montants -->
            <div class="amount-section">
                <div class="amount-title">💰 Récapitulatif Financier</div>
                <div class="amount-grid">
                    <div class="amount-row">
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
                    <tbody>
                        <?php if (isset($paymentHistory) && !empty($paymentHistory)): ?>
                            <?php foreach ($paymentHistory as $histPayment): ?>
                                <?php 
                                $histFeeType = isset($feeTypes[$histPayment['fee_type_id']]) ? $feeTypes[$histPayment['fee_type_id']] : null;
                                $isCurrentPayment = ($histPayment['id'] == $payment['id']);
                                ?>
                                <tr class="<?= $isCurrentPayment ? 'highlight' : '' ?>">
                                    <td><?= date('d/m/Y', strtotime($histPayment['payment_date'])) ?></td>
                                    <td><?= $histFeeType['name'] ?? 'Frais de scolarité' ?></td>
                                    <td style="text-align: right; <?= $isCurrentPayment ? 'font-weight: bold;' : '' ?>"><?= number_format($histPayment['amount_paid'], 0, ',', ' ') ?> FCFA</td>
                                    <td style="text-align: center;">
                                        <span class="tag" style="background: <?= $histPayment['payment_method'] === 'CASH' ? '#48c774' : ($histPayment['payment_method'] === 'CARD' ? '#3273dc' : '#f14668') ?>; color: white;">
                                            <?= $histPayment['payment_method'] === 'CASH' ? 'Espèces' : 
                                                ($histPayment['payment_method'] === 'CARD' ? 'Carte' : 
                                                ($histPayment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile')) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="tag" style="background: #48c774; color: white;">Payé</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="highlight">
                                <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                <td><?= $feeType['name'] ?? 'Frais de scolarité' ?></td>
                                <td style="text-align: right; font-weight: bold;"><?= number_format($paidAmount, 0, ',', ' ') ?> FCFA</td>
                                <td style="text-align: center;">
                                    <span class="tag" style="background: #48c774; color: white;">
                                        <?= $payment['payment_method'] === 'CASH' ? 'Espèces' : 
                                            ($payment['payment_method'] === 'CARD' ? 'Carte' : 
                                            ($payment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 'Mobile')) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="tag" style="background: #48c774; color: white;">Payé</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Notes -->
            <?php if (!empty($payment['notes'])): ?>
            <div class="notes-section">
                <div class="notes-title">📝 Notes</div>
                <div><?= $payment['notes'] ?></div>
            </div>
            <?php endif; ?>
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
                <p><strong>KISSAI SCHOOL</strong> - Établissement d'enseignement privé</p>
                <p>Yaoundé, Cameroun | Tél : +237 XXX XXX XXX</p>
                <p>Email : contact@kissai-school.cm</p>
                <div class="generation-info">
                    Généré le <?= date('d/m/Y à H:i') ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
