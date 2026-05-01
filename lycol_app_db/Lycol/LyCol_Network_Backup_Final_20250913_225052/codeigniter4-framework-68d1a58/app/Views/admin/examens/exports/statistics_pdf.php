<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Examens - <?= $period ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            font-size: 16px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .stat-box {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            padding: 10px;
            border-radius: 5px;
            min-width: 150px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #2c3e50;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #bdc3c7;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
        }
        .highlight {
            background: #f39c12;
            color: white;
            font-weight: bold;
        }
        .success {
            color: #27ae60;
        }
        .warning {
            color: #f39c12;
        }
        .danger {
            color: #e74c3c;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Statistiques des Examens</h1>
        <p>Période : <?= $period ?></p>
        <p>Généré le : <?= date('d/m/Y à H:i') ?></p>
    </div>

    <!-- Statistiques générales -->
    <div class="section">
        <h2>Statistiques Générales</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <h3>Moyenne Générale</h3>
                <div class="value"><?= number_format($stats['averageScores']['overall'] ?? 0, 2) ?>/20</div>
            </div>
            <div class="stat-box">
                <h3>Taux de Réussite</h3>
                <div class="value"><?= number_format($stats['passRates']['overall'] ?? 0, 1) ?>%</div>
            </div>
            <div class="stat-box">
                <h3>Total Examens</h3>
                <div class="value"><?= number_format($stats['totalExams'] ?? 0) ?></div>
            </div>
            <div class="stat-box">
                <h3>Examens Terminés</h3>
                <div class="value"><?= number_format($stats['completedExams'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Meilleure classe -->
    <?php if (isset($stats['bestClass']) && $stats['bestClass']): ?>
    <div class="section">
        <h2>Meilleure Classe</h2>
        <div class="stats-grid">
            <div class="stat-box highlight">
                <h3><?= $stats['bestClass']['class_name'] ?></h3>
                <div class="value"><?= number_format($stats['bestClass']['average_score'], 2) ?>/20</div>
                <div>Taux de réussite: <?= number_format($stats['bestClass']['pass_rate'], 1) ?>%</div>
                <div>Total notes: <?= $stats['bestClass']['total'] ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Performance par genre -->
    <?php if (isset($stats['performanceByGender']) && !empty($stats['performanceByGender'])): ?>
    <div class="section">
        <h2>Performance par Genre</h2>
        <table>
            <thead>
                <tr>
                    <th>Genre</th>
                    <th>Moyenne</th>
                    <th>Taux de Réussite</th>
                    <th>Total Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['performanceByGender'] as $gender): ?>
                <tr>
                    <td><strong><?= $gender['gender'] === 'M' ? 'Garçons' : 'Filles' ?></strong></td>
                    <td class="success"><?= number_format($gender['average_score'], 2) ?>/20</td>
                    <td class="warning"><?= $gender['total'] > 0 ? round(($gender['passed'] / $gender['total']) * 100, 1) : 0 ?>%</td>
                    <td><?= $gender['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Performance par classe -->
    <?php if (isset($stats['performanceByClass']) && !empty($stats['performanceByClass'])): ?>
    <div class="section">
        <h2>Performance par Classe</h2>
        <table>
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Moyenne</th>
                    <th>Taux de Réussite</th>
                    <th>Total Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['performanceByClass'] as $class): ?>
                <tr>
                    <td><strong><?= $class['class_name'] ?></strong></td>
                    <td class="success"><?= number_format($class['average_score'], 2) ?>/20</td>
                    <td class="warning"><?= $class['total'] > 0 ? round(($class['passed'] / $class['total']) * 100, 1) : 0 ?>%</td>
                    <td><?= $class['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Top 5 des classes -->
    <?php if (isset($stats['topClasses']) && !empty($stats['topClasses'])): ?>
    <div class="section">
        <h2>Top 5 des Classes</h2>
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Classe</th>
                    <th>Moyenne</th>
                    <th>Taux de Réussite</th>
                    <th>Total Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['topClasses'] as $index => $class): ?>
                <tr>
                    <td><strong><?= $index + 1 ?></strong></td>
                    <td><?= $class['class_name'] ?></td>
                    <td class="success"><?= number_format($class['average_score'], 2) ?>/20</td>
                    <td class="warning"><?= number_format($class['pass_rate'], 1) ?>%</td>
                    <td><?= $class['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Meilleurs élèves -->
    <?php if (isset($stats['topStudents']) && !empty($stats['topStudents'])): ?>
    <div class="section">
        <h2>Meilleurs Élèves</h2>
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Moyenne</th>
                    <th>Nombre d'Examens</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['topStudents'] as $index => $student): ?>
                <tr>
                    <td><strong><?= $index + 1 ?></strong></td>
                    <td><?= $student['first_name'] . ' ' . $student['last_name'] ?></td>
                    <td><?= $student['class_name'] ?></td>
                    <td class="success"><?= number_format($student['average_score'], 2) ?>/20</td>
                    <td><?= $student['exam_count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Rapport généré automatiquement par le système de gestion scolaire</p>
        <p>KISSAI SCHOOL - Module Examens</p>
    </div>
</body>
</html>









