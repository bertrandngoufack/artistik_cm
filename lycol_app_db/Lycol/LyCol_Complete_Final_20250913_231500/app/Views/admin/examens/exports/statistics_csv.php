<?php
/**
 * Vue d'export CSV pour les statistiques d'examens
 * Cette vue génère un fichier CSV avec les statistiques des examens
 */

// Headers pour CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="statistiques_examens_' . date('Y-m-d') . '.csv"');
header('Cache-Control: max-age=0');

// Données des statistiques
$stats = $stats ?? [];
$chartData = $chartData ?? [];
$period = $period ?? 'current';

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// BOM pour UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-tête principal
fputcsv($output, ['STATISTIQUES DES EXAMENS - KISSAI SCHOOL']);
fputcsv($output, ['Période : ' . ($period === 'current' ? 'Année en cours' : $period)]);
fputcsv($output, ['Généré le : ' . date('d/m/Y à H:i')]);
fputcsv($output, []);

// Statistiques générales
fputcsv($output, ['STATISTIQUES GÉNÉRALES']);
fputcsv($output, ['Métrique', 'Valeur', 'Détails', 'Pourcentage']);

fputcsv($output, [
    'Total Examens',
    $stats['total_exams'] ?? 0,
    'Nombre total d\'examens programmés',
    '100%'
]);

fputcsv($output, [
    'Examens Complétés',
    $stats['completed_exams'] ?? 0,
    'Examens avec notes saisies',
    $stats['total_exams'] > 0 ? round(($stats['completed_exams'] / $stats['total_exams']) * 100, 1) . '%' : '0%'
]);

fputcsv($output, [
    'Total Notes',
    $stats['total_grades'] ?? 0,
    'Nombre total de notes saisies',
    '-'
]);

fputcsv($output, [
    'Moyenne Générale',
    number_format($stats['average_score'] ?? 0, 2) . '/20',
    'Moyenne de toutes les notes',
    $stats['average_score'] > 0 ? round(($stats['average_score'] / 20) * 100, 1) . '%' : '0%'
]);

fputcsv($output, [
    'Taux de Réussite',
    number_format($stats['pass_rate'] ?? 0, 1) . '%',
    'Élèves avec moyenne ≥ 10/20',
    number_format($stats['pass_rate'] ?? 0, 1) . '%'
]);

fputcsv($output, []);

// Statistiques par type d'examen
fputcsv($output, ['STATISTIQUES PAR TYPE D\'EXAMEN']);
fputcsv($output, ['Type d\'Examen', 'Nombre', 'Moyenne', 'Taux de Réussite']);

if (isset($stats['by_type']) && is_array($stats['by_type'])) {
    foreach ($stats['by_type'] as $type => $data) {
        fputcsv($output, [
            $this->translateExamType($type),
            $data['count'] ?? 0,
            number_format($data['average'] ?? 0, 2) . '/20',
            number_format($data['pass_rate'] ?? 0, 1) . '%'
        ]);
    }
}

fputcsv($output, []);

// Statistiques par classe
fputcsv($output, ['STATISTIQUES PAR CLASSE']);
fputcsv($output, ['Classe', 'Nombre d\'Examens', 'Moyenne', 'Taux de Réussite']);

if (isset($stats['by_class']) && is_array($stats['by_class'])) {
    foreach ($stats['by_class'] as $class) {
        fputcsv($output, [
            $class['class_name'] ?? 'Classe inconnue',
            $class['exam_count'] ?? 0,
            number_format($class['average'] ?? 0, 2) . '/20',
            number_format($class['pass_rate'] ?? 0, 1) . '%'
        ]);
    }
}

fputcsv($output, []);

// Distribution des notes
fputcsv($output, ['DISTRIBUTION DES NOTES']);
fputcsv($output, ['Tranche de Notes', 'Nombre d\'Élèves', 'Pourcentage', 'Statut']);

if (isset($stats['grade_distribution']) && is_array($stats['grade_distribution'])) {
    foreach ($stats['grade_distribution'] as $range => $count) {
        $percentage = $stats['total_grades'] > 0 ? round(($count / $stats['total_grades']) * 100, 1) : 0;
        
        $rangeNum = (int)substr($range, 0, 2);
        $status = '';
        if ($rangeNum >= 16) $status = 'Excellent';
        elseif ($rangeNum >= 14) $status = 'Très Bien';
        elseif ($rangeNum >= 12) $status = 'Bien';
        elseif ($rangeNum >= 10) $status = 'Assez Bien';
        elseif ($rangeNum >= 8) $status = 'Passable';
        else $status = 'Insuffisant';
        
        fputcsv($output, [
            $range,
            $count,
            $percentage . '%',
            $status
        ]);
    }
}

fputcsv($output, []);

// Examens récents
fputcsv($output, ['EXAMENS RÉCENTS']);
fputcsv($output, ['Examen', 'Classe', 'Date', 'Statut']);

if (isset($stats['recent_exams']) && is_array($stats['recent_exams'])) {
    foreach ($stats['recent_exams'] as $exam) {
        fputcsv($output, [
            $exam['name'] ?? 'Examen inconnu',
            $exam['class_name'] ?? 'Classe inconnue',
            date('d/m/Y', strtotime($exam['exam_date'] ?? 'now')),
            $this->translateStatus($exam['status'] ?? 'UNKNOWN')
        ]);
    }
}

fputcsv($output, []);

// Résumé et recommandations
fputcsv($output, ['RÉSUMÉ ET RECOMMANDATIONS']);
fputcsv($output, ['Points forts :']);
fputcsv($output, ['• ' . ($stats['total_exams'] ?? 0) . ' examens programmés']);
fputcsv($output, ['• ' . ($stats['total_grades'] ?? 0) . ' notes saisies']);
fputcsv($output, ['• Taux de réussite de ' . number_format($stats['pass_rate'] ?? 0, 1) . '%']);

fputcsv($output, []);
fputcsv($output, ['Recommandations :']);

if (($stats['pass_rate'] ?? 0) < 70) {
    fputcsv($output, ['• Renforcer le soutien pédagogique pour améliorer les résultats']);
}

if (($stats['average_score'] ?? 0) < 12) {
    fputcsv($output, ['• Revoir les méthodes d\'évaluation']);
}

fputcsv($output, ['• Continuer le suivi régulier des performances']);

fclose($output);
?>


