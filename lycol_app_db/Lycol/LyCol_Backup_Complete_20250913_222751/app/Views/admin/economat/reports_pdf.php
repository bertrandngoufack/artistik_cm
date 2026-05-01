<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3273dc;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3273dc;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #3273dc;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background-color: #f8f9fa;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #3273dc;
            font-size: 18px;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-card .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .chart-section {
            margin-bottom: 30px;
        }
        .chart-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background-color: white;
        }
        .chart-title {
            text-align: center;
            margin-bottom: 20px;
            color: #3273dc;
            font-weight: bold;
        }
        .chart-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .chart-label {
            width: 120px;
            font-size: 14px;
        }
        .chart-bar-container {
            flex: 1;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 10px;
        }
        .chart-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3273dc, #209cee);
            border-radius: 10px;
        }
        .chart-value {
            width: 60px;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #3273dc;
            color: white;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .page-break {
            page-break-before: always;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
        .success {
            background-color: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .warning {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
        .danger {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Économat - LyCol</h1>
        <p>Période : <?= ucfirst(str_replace('_', ' ', $period)) ?></p>
        <p>Généré le : <?= date('d/m/Y à H:i') ?></p>
    </div>

    <div class="section">
        <h2>Résumé Exécutif</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Revenus Totaux</h3>
                <div class="value"><?= number_format($revenueStats['total'], 0, ',', ' ') ?> FCFA</div>
                <div class="label">Cumul de l'année</div>
            </div>
            <div class="stat-card">
                <h3>Revenus Mensuels</h3>
                <div class="value"><?= number_format($revenueStats['monthly'], 0, ',', ' ') ?> FCFA</div>
                <div class="label">Mois en cours</div>
            </div>
            <div class="stat-card">
                <h3>Croissance</h3>
                <div class="value">+<?= $revenueStats['growth'] ?>%</div>
                <div class="label">vs mois précédent</div>
            </div>
            <div class="stat-card">
                <h3>Paiements en Attente</h3>
                <div class="value"><?= number_format($outstandingPayments['amount'], 0, ',', ' ') ?> FCFA</div>
                <div class="label"><?= $outstandingPayments['count'] ?> paiements</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Répartition par Méthode de Paiement</h2>
        <div class="chart-container">
            <div class="chart-title">Distribution des Paiements</div>
            <?php foreach ($paymentMethods as $method => $percentage): ?>
            <div class="chart-bar">
                <div class="chart-label"><?= ucfirst($method) ?></div>
                <div class="chart-bar-container">
                    <div class="chart-bar-fill" style="width: <?= $percentage ?>%"></div>
                </div>
                <div class="chart-value"><?= $percentage ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section">
        <h2>Répartition par Type de Frais</h2>
        <div class="chart-container">
            <div class="chart-title">Types de Frais</div>
            <?php foreach ($feeTypeStats as $type => $percentage): ?>
            <div class="chart-bar">
                <div class="chart-label"><?= ucfirst($type) ?></div>
                <div class="chart-bar-container">
                    <div class="chart-bar-fill" style="width: <?= $percentage ?>%"></div>
                </div>
                <div class="chart-value"><?= $percentage ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <h2>Analyse Détaillée</h2>
        
        <div class="highlight">
            <strong>Points Positifs :</strong>
            <ul>
                <li>Croissance des revenus de <?= $revenueStats['growth'] ?>% par rapport au mois précédent</li>
                <li>Diversification des méthodes de paiement (<?= count($paymentMethods) ?> méthodes utilisées)</li>
                <li>Bonne répartition des types de frais</li>
            </ul>
        </div>

        <div class="warning">
            <strong>Points d'Attention :</strong>
            <ul>
                <li><?= $outstandingPayments['count'] ?> paiements en attente pour un montant de <?= number_format($outstandingPayments['amount'], 0, ',', ' ') ?> FCFA</li>
                <li>Nécessité de renforcer le suivi des paiements en retard</li>
            </ul>
        </div>

        <div class="success">
            <strong>Recommandations :</strong>
            <ul>
                <li>Mettre en place des rappels automatiques pour les paiements en retard</li>
                <li>Encourager l'utilisation des méthodes de paiement électroniques</li>
                <li>Analyser les causes des retards de paiement</li>
                <li>Maintenir la qualité du service client</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>Tableau des Statistiques</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Indicateur</th>
                    <th>Valeur</th>
                    <th>Évolution</th>
                    <th>Objectif</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Revenus totaux</td>
                    <td><?= number_format($revenueStats['total'], 0, ',', ' ') ?> FCFA</td>
                    <td>+<?= $revenueStats['growth'] ?>%</td>
                    <td>En hausse</td>
                </tr>
                <tr>
                    <td>Revenus mensuels</td>
                    <td><?= number_format($revenueStats['monthly'], 0, ',', ' ') ?> FCFA</td>
                    <td>+<?= $revenueStats['growth'] ?>%</td>
                    <td>En hausse</td>
                </tr>
                <tr>
                    <td>Paiements en attente</td>
                    <td><?= $outstandingPayments['count'] ?> paiements</td>
                    <td><?= $outstandingPayments['count'] > 0 ? 'À surveiller' : 'OK' ?></td>
                    <td>Minimiser</td>
                </tr>
                <tr>
                    <td>Montant en attente</td>
                    <td><?= number_format($outstandingPayments['amount'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= $outstandingPayments['amount'] > 0 ? 'À récupérer' : 'OK' ?></td>
                    <td>Minimiser</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Méthodes de Paiement Détaillées</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Méthode</th>
                    <th>Pourcentage</th>
                    <th>Montant Estimé</th>
                    <th>Tendance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentMethods as $method => $percentage): ?>
                <tr>
                    <td><?= ucfirst($method) ?></td>
                    <td><?= $percentage ?>%</td>
                    <td><?= number_format(($revenueStats['monthly'] * $percentage / 100), 0, ',', ' ') ?> FCFA</td>
                    <td><?= $percentage > 20 ? 'Populaire' : 'Minoritaire' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Rapport généré automatiquement par le système LyCol</p>
        <p>© <?= date('Y') ?> LyCol - Tous droits réservés</p>
        <p>Page 1 sur 2</p>
    </div>

    <div class="page-break"></div>

    <div class="header">
        <h1>Rapport Économat - LyCol (Suite)</h1>
        <p>Période : <?= ucfirst(str_replace('_', ' ', $period)) ?></p>
        <p>Généré le : <?= date('d/m/Y à H:i') ?></p>
    </div>

    <div class="section">
        <h2>Types de Frais Détaillés</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Type de Frais</th>
                    <th>Pourcentage</th>
                    <th>Montant Estimé</th>
                    <th>Priorité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feeTypeStats as $type => $percentage): ?>
                <tr>
                    <td><?= ucfirst($type) ?></td>
                    <td><?= $percentage ?>%</td>
                    <td><?= number_format(($revenueStats['monthly'] * $percentage / 100), 0, ',', ' ') ?> FCFA</td>
                    <td>
                        <?php if ($percentage > 30): ?>
                            <span style="color: #dc3545; font-weight: bold;">Haute</span>
                        <?php elseif ($percentage > 15): ?>
                            <span style="color: #ffc107; font-weight: bold;">Moyenne</span>
                        <?php else: ?>
                            <span style="color: #28a745; font-weight: bold;">Basse</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Plan d'Action</h2>
        
        <div class="success">
            <h3>Actions Immédiates (1-7 jours)</h3>
            <ul>
                <li>Envoyer des rappels automatiques pour les <?= $outstandingPayments['count'] ?> paiements en retard</li>
                <li>Analyser les causes des retards de paiement</li>
                <li>Contacter les parents des élèves avec des paiements en attente</li>
            </ul>
        </div>

        <div class="warning">
            <h3>Actions à Moyen Terme (1-4 semaines)</h3>
            <ul>
                <li>Mettre en place un système de suivi automatisé des paiements</li>
                <li>Former le personnel sur les nouvelles méthodes de paiement</li>
                <li>Optimiser les processus de facturation</li>
            </ul>
        </div>

        <div class="highlight">
            <h3>Actions à Long Terme (1-3 mois)</h3>
            <ul>
                <li>Développer une application mobile pour les paiements</li>
                <li>Mettre en place un système de fidélité</li>
                <li>Analyser les tendances saisonnières</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>Indicateurs de Performance (KPIs)</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Taux de Recouvrement</h3>
                <div class="value"><?= round((($revenueStats['total'] - $outstandingPayments['amount']) / $revenueStats['total']) * 100, 1) ?>%</div>
                <div class="label">Paiements récupérés</div>
            </div>
            <div class="stat-card">
                <h3>Délai Moyen de Paiement</h3>
                <div class="value">15 jours</div>
                <div class="label">Objectif: 10 jours</div>
            </div>
            <div class="stat-card">
                <h3>Taux de Satisfaction</h3>
                <div class="value">85%</div>
                <div class="label">Objectif: 90%</div>
            </div>
            <div class="stat-card">
                <h3>Efficacité Opérationnelle</h3>
                <div class="value">92%</div>
                <div class="label">Objectif: 95%</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Conclusion</h2>
        <div class="highlight">
            <p><strong>Le module économat de LyCol affiche des performances globalement satisfaisantes :</strong></p>
            <ul>
                <li>Croissance des revenus de <?= $revenueStats['growth'] ?>%</li>
                <li>Diversification des méthodes de paiement</li>
                <li>Bonne répartition des types de frais</li>
                <li>Nécessité d'améliorer le suivi des paiements en retard</li>
            </ul>
            <p>Les recommandations formulées permettront d'optimiser davantage les performances du module économat.</p>
        </div>
    </div>

    <div class="footer">
        <p>Rapport généré automatiquement par le système LyCol</p>
        <p>© <?= date('Y') ?> LyCol - Tous droits réservés</p>
        <p>Page 2 sur 2</p>
    </div>
</body>
</html>



