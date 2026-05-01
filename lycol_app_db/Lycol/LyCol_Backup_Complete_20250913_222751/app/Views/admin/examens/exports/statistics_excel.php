<?php
/**
 * Vue d'export Excel pour les statistiques d'examens
 * Cette vue génère un fichier Excel avec les statistiques des examens
 */

// Headers pour Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="statistiques_examens_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Données des statistiques
$stats = $stats ?? [];
$chartData = $chartData ?? [];
$period = $period ?? 'current';
?>

<table border="1">
    <tr>
        <th colspan="4" style="background-color: #4CAF50; color: white; text-align: center; font-size: 16px;">
            STATISTIQUES DES EXAMENS - KISSAI SCHOOL
        </th>
    </tr>
    <tr>
        <th colspan="4" style="background-color: #f0f0f0; text-align: center;">
            Période : <?= $period === 'current' ? 'Année en cours' : $period ?>
        </th>
    </tr>
    <tr>
        <th colspan="4" style="background-color: #f0f0f0; text-align: center;">
            Généré le : <?= date('d/m/Y à H:i') ?>
    </tr>
    
    <!-- Statistiques générales -->
    <tr>
        <th colspan="4" style="background-color: #2196F3; color: white; text-align: center;">
            STATISTIQUES GÉNÉRALES
        </th>
    </tr>
    <tr>
        <th style="background-color: #f0f0f0;">Métrique</th>
        <th style="background-color: #f0f0f0;">Valeur</th>
        <th style="background-color: #f0f0f0;">Détails</th>
        <th style="background-color: #f0f0f0;">Pourcentage</th>
    </tr>
    
    <tr>
        <td><strong>Total Examens</strong></td>
        <td><?= $stats['total_exams'] ?? 0 ?></td>
        <td>Nombre total d'examens programmés</td>
        <td>100%</td>
    </tr>
    
    <tr>
        <td><strong>Examens Complétés</strong></td>
        <td><?= $stats['completed_exams'] ?? 0 ?></td>
        <td>Examens avec notes saisies</td>
        <td><?= $stats['total_exams'] > 0 ? round(($stats['completed_exams'] / $stats['total_exams']) * 100, 1) : 0 ?>%</td>
    </tr>
    
    <tr>
        <td><strong>Total Notes</strong></td>
        <td><?= $stats['total_grades'] ?? 0 ?></td>
        <td>Nombre total de notes saisies</td>
        <td>-</td>
    </tr>
    
    <tr>
        <td><strong>Moyenne Générale</strong></td>
        <td><?= number_format($stats['average_score'] ?? 0, 2) ?>/20</td>
        <td>Moyenne de toutes les notes</td>
        <td><?= $stats['average_score'] > 0 ? round(($stats['average_score'] / 20) * 100, 1) : 0 ?>%</td>
    </tr>
    
    <tr>
        <td><strong>Taux de Réussite</strong></td>
        <td><?= number_format($stats['pass_rate'] ?? 0, 1) ?>%</td>
        <td>Élèves avec moyenne ≥ 10/20</td>
        <td><?= number_format($stats['pass_rate'] ?? 0, 1) ?>%</td>
    </tr>
    
    <!-- Statistiques par type d'examen -->
    <tr>
        <th colspan="4" style="background-color: #FF9800; color: white; text-align: center;">
            STATISTIQUES PAR TYPE D'EXAMEN
        </th>
    </tr>
    <tr>
        <th style="background-color: #f0f0f0;">Type d'Examen</th>
        <th style="background-color: #f0f0f0;">Nombre</th>
        <th style="background-color: #f0f0f0;">Moyenne</th>
        <th style="background-color: #f0f0f0;">Taux de Réussite</th>
    </tr>
    
    <?php if (isset($stats['by_type']) && is_array($stats['by_type'])): ?>
        <?php foreach ($stats['by_type'] as $type => $data): ?>
        <tr>
            <td><?= $this->translateExamType($type) ?></td>
            <td><?= $data['count'] ?? 0 ?></td>
            <td><?= number_format($data['average'] ?? 0, 2) ?>/20</td>
            <td><?= number_format($data['pass_rate'] ?? 0, 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Statistiques par classe -->
    <tr>
        <th colspan="4" style="background-color: #9C27B0; color: white; text-align: center;">
            STATISTIQUES PAR CLASSE
        </th>
    </tr>
    <tr>
        <th style="background-color: #f0f0f0;">Classe</th>
        <th style="background-color: #f0f0f0;">Nombre d'Examens</th>
        <th style="background-color: #f0f0f0;">Moyenne</th>
        <th style="background-color: #f0f0f0;">Taux de Réussite</th>
    </tr>
    
    <?php if (isset($stats['by_class']) && is_array($stats['by_class'])): ?>
        <?php foreach ($stats['by_class'] as $class): ?>
        <tr>
            <td><?= $class['class_name'] ?? 'Classe inconnue' ?></td>
            <td><?= $class['exam_count'] ?? 0 ?></td>
            <td><?= number_format($class['average'] ?? 0, 2) ?>/20</td>
            <td><?= number_format($class['pass_rate'] ?? 0, 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Distribution des notes -->
    <tr>
        <th colspan="4" style="background-color: #607D8B; color: white; text-align: center;">
            DISTRIBUTION DES NOTES
        </th>
    </tr>
    <tr>
        <th style="background-color: #f0f0f0;">Tranche de Notes</th>
        <th style="background-color: #f0f0f0;">Nombre d'Élèves</th>
        <th style="background-color: #f0f0f0;">Pourcentage</th>
        <th style="background-color: #f0f0f0;">Statut</th>
    </tr>
    
    <?php if (isset($stats['grade_distribution']) && is_array($stats['grade_distribution'])): ?>
        <?php foreach ($stats['grade_distribution'] as $range => $count): ?>
        <tr>
            <td><?= $range ?></td>
            <td><?= $count ?></td>
            <td><?= $stats['total_grades'] > 0 ? round(($count / $stats['total_grades']) * 100, 1) : 0 ?>%</td>
            <td>
                <?php
                $rangeNum = (int)substr($range, 0, 2);
                if ($rangeNum >= 16) echo 'Excellent';
                elseif ($rangeNum >= 14) echo 'Très Bien';
                elseif ($rangeNum >= 12) echo 'Bien';
                elseif ($rangeNum >= 10) echo 'Assez Bien';
                elseif ($rangeNum >= 8) echo 'Passable';
                else echo 'Insuffisant';
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Notes récentes -->
    <tr>
        <th colspan="4" style="background-color: #795548; color: white; text-align: center;">
            EXAMENS RÉCENTS
        </th>
    </tr>
    <tr>
        <th style="background-color: #f0f0f0;">Examen</th>
        <th style="background-color: #f0f0f0;">Classe</th>
        <th style="background-color: #f0f0f0;">Date</th>
        <th style="background-color: #f0f0f0;">Statut</th>
    </tr>
    
    <?php if (isset($stats['recent_exams']) && is_array($stats['recent_exams'])): ?>
        <?php foreach ($stats['recent_exams'] as $exam): ?>
        <tr>
            <td><?= $exam['name'] ?? 'Examen inconnu' ?></td>
            <td><?= $exam['class_name'] ?? 'Classe inconnue' ?></td>
            <td><?= date('d/m/Y', strtotime($exam['exam_date'] ?? 'now')) ?></td>
            <td><?= $this->translateStatus($exam['status'] ?? 'UNKNOWN') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Résumé -->
    <tr>
        <th colspan="4" style="background-color: #E91E63; color: white; text-align: center;">
            RÉSUMÉ ET RECOMMANDATIONS
        </th>
    </tr>
    <tr>
        <td colspan="4">
            <strong>Points forts :</strong><br>
            • <?= $stats['total_exams'] ?? 0 ?> examens programmés<br>
            • <?= $stats['total_grades'] ?? 0 ?> notes saisies<br>
            • Taux de réussite de <?= number_format($stats['pass_rate'] ?? 0, 1) ?>%
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <strong>Recommandations :</strong><br>
            <?php if (($stats['pass_rate'] ?? 0) < 70): ?>
            • Renforcer le soutien pédagogique pour améliorer les résultats<br>
            <?php endif; ?>
            <?php if (($stats['average_score'] ?? 0) < 12): ?>
            • Revoir les méthodes d'évaluation<br>
            <?php endif; ?>
            • Continuer le suivi régulier des performances
        </td>
    </tr>
</table>


