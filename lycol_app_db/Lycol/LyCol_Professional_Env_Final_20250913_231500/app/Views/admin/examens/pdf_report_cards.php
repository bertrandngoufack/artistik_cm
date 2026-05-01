<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletins de Notes - KISSAI SCHOOL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .period-info {
            font-size: 14px;
            color: #666;
        }
        .student-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .student-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .student-details {
            font-size: 12px;
            color: #666;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grades-table th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        .grades-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }
        .grades-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            background-color: #e8f5e8 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
        }
        .grade-excellent { color: #4CAF50; font-weight: bold; }
        .grade-good { color: #2196F3; font-weight: bold; }
        .grade-average { color: #FF9800; font-weight: bold; }
        .grade-poor { color: #F44336; font-weight: bold; }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <?php foreach ($students as $index => $student): ?>
    <div class="container <?= $index > 0 ? 'page-break' : '' ?>">
        <div class="header">
            <div class="school-name">KISSAI SCHOOL</div>
            <div class="document-title">BULLETIN DE NOTES</div>
            <div class="period-info">
                <?= $period ?? 'Période non spécifiée' ?> - 
                Année Académique <?= date('Y') ?>-<?= date('Y')+1 ?>
            </div>
        </div>

        <div class="student-info">
            <div class="student-name">
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
            </div>
            <div class="student-details">
                Matricule: <?= htmlspecialchars($student['matricule'] ?? 'N/A') ?> | 
                Classe: <?= htmlspecialchars($class['name'] ?? 'N/A') ?> | 
                Date de naissance: <?= date('d/m/Y', strtotime($student['birth_date'] ?? 'now')) ?>
            </div>
        </div>

        <table class="grades-table">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Note</th>
                    <th>Coefficient</th>
                    <th>Total</th>
                    <th>Appréciation</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalPoints = 0;
                $totalCoefficient = 0;
                $studentGrades = array_filter($grades, function($grade) use ($student) {
                    return $grade['student_id'] == $student['id'];
                });
                ?>
                
                <?php if (!empty($studentGrades)): ?>
                    <?php foreach ($studentGrades as $grade): ?>
                    <tr>
                        <td><?= htmlspecialchars($grade['subject_name'] ?? 'Matière inconnue') ?></td>
                        <td class="<?= $grade['marks_obtained'] >= 16 ? 'grade-excellent' : 
                                   ($grade['marks_obtained'] >= 14 ? 'grade-good' : 
                                   ($grade['marks_obtained'] >= 10 ? 'grade-average' : 'grade-poor')) ?>">
                            <?= number_format($grade['marks_obtained'], 2) ?>/20
                        </td>
                        <td><?= $grade['coefficient'] ?? 1 ?></td>
                        <td><?= number_format(($grade['marks_obtained'] ?? 0) * ($grade['coefficient'] ?? 1), 2) ?></td>
                        <td>
                            <?php
                            $mark = $grade['marks_obtained'] ?? 0;
                            if ($mark >= 16) echo 'Excellent';
                            elseif ($mark >= 14) echo 'Très Bien';
                            elseif ($mark >= 12) echo 'Bien';
                            elseif ($mark >= 10) echo 'Assez Bien';
                            elseif ($mark >= 8) echo 'Passable';
                            else echo 'Insuffisant';
                            ?>
                        </td>
                    </tr>
                    <?php 
                    $totalPoints += ($grade['marks_obtained'] ?? 0) * ($grade['coefficient'] ?? 1);
                    $totalCoefficient += ($grade['coefficient'] ?? 1);
                    ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">
                            Aucune note disponible pour cet élève
                        </td>
                    </tr>
                <?php endif; ?>
                
                <?php if ($totalCoefficient > 0): ?>
                <tr class="total-row">
                    <td><strong>MOYENNE GÉNÉRALE</strong></td>
                    <td colspan="2">
                        <strong><?= number_format($totalPoints / $totalCoefficient, 2) ?>/20</strong>
                    </td>
                    <td><strong><?= number_format($totalPoints, 2) ?></strong></td>
                    <td>
                        <strong>
                            <?php
                            $average = $totalPoints / $totalCoefficient;
                            if ($average >= 16) echo 'Excellent';
                            elseif ($average >= 14) echo 'Très Bien';
                            elseif ($average >= 12) echo 'Bien';
                            elseif ($average >= 10) echo 'Assez Bien';
                            elseif ($average >= 8) echo 'Passable';
                            else echo 'Insuffisant';
                            ?>
                        </strong>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Observations :</strong></p>
            <p style="min-height: 60px; border: 1px solid #ddd; padding: 10px; margin: 10px 0;">
                <!-- Espace pour les observations -->
            </p>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">Signature du Professeur Principal</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Signature du Directeur</div>
                </div>
            </div>
            
            <p style="text-align: center; margin-top: 20px; font-size: 10px; color: #999;">
                Document généré automatiquement le <?= date('d/m/Y à H:i') ?>
            </p>
        </div>
    </div>
    <?php endforeach; ?>
</body>
</html>


