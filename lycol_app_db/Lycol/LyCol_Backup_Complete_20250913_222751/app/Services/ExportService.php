<?php

namespace App\Services;

class ExportService
{
    public function exportStatisticsPDF($stats, $chartData, $period)
    {
        $html = view('admin/examens/exports/statistics_pdf', [
            'stats' => $stats,
            'chartData' => $chartData,
            'period' => $period
        ]);

        $filename = "statistiques_examens_{$period}_" . date('Y-m-d') . ".pdf";
        
        $pdfService = new PDFService();
        return $pdfService->generatePDF($html, $filename);
    }

    public function exportStatisticsExcel($stats, $chartData, $period)
    {
        $filename = "statistiques_examens_{$period}_" . date('Y-m-d') . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Créer le contenu Excel
        $content = $this->generateExcelContent($stats, $chartData, $period);
        
        echo $content;
        exit;
    }

    public function exportStatisticsCSV($stats, $chartData, $period)
    {
        $filename = "statistiques_examens_{$period}_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // En-têtes CSV
        fputcsv($output, ['Statistiques des Examens - ' . $period]);
        fputcsv($output, ['']);
        
        // Statistiques générales
        fputcsv($output, ['Statistiques Générales']);
        fputcsv($output, ['Métrique', 'Valeur']);
        
        if (isset($stats['averageScores'])) {
            fputcsv($output, ['Moyenne Générale', number_format($stats['averageScores']['overall'] ?? 0, 2) . '/20']);
        }
        
        if (isset($stats['passRates'])) {
            fputcsv($output, ['Taux de Réussite', number_format($stats['passRates']['overall'] ?? 0, 1) . '%']);
        }
        
        fputcsv($output, ['Total Examens', $stats['totalExams'] ?? 0]);
        fputcsv($output, ['Examens Terminés', $stats['completedExams'] ?? 0]);
        
        fputcsv($output, ['']);
        
        // Meilleure classe
        if (isset($stats['bestClass']) && $stats['bestClass']) {
            fputcsv($output, ['Meilleure Classe']);
            fputcsv($output, ['Classe', 'Moyenne', 'Taux de Réussite', 'Total Notes']);
            fputcsv($output, [
                $stats['bestClass']['class_name'],
                number_format($stats['bestClass']['average_score'], 2) . '/20',
                number_format($stats['bestClass']['pass_rate'], 1) . '%',
                $stats['bestClass']['total']
            ]);
        }
        
        fputcsv($output, ['']);
        
        // Performance par genre
        if (isset($stats['performanceByGender'])) {
            fputcsv($output, ['Performance par Genre']);
            fputcsv($output, ['Genre', 'Moyenne', 'Taux de Réussite', 'Total Notes']);
            
            foreach ($stats['performanceByGender'] as $gender) {
                $genderName = $gender['gender'] === 'M' ? 'Garçons' : 'Filles';
                $passRate = $gender['total'] > 0 ? round(($gender['passed'] / $gender['total']) * 100, 1) : 0;
                fputcsv($output, [
                    $genderName,
                    number_format($gender['average_score'], 2) . '/20',
                    $passRate . '%',
                    $gender['total']
                ]);
            }
        }
        
        fputcsv($output, ['']);
        
        // Performance par classe
        if (isset($stats['performanceByClass'])) {
            fputcsv($output, ['Performance par Classe']);
            fputcsv($output, ['Classe', 'Moyenne', 'Taux de Réussite', 'Total Notes']);
            
            foreach ($stats['performanceByClass'] as $class) {
                $passRate = $class['total'] > 0 ? round(($class['passed'] / $class['total']) * 100, 1) : 0;
                fputcsv($output, [
                    $class['class_name'],
                    number_format($class['average_score'], 2) . '/20',
                    $passRate . '%',
                    $class['total']
                ]);
            }
        }
        
        fputcsv($output, ['']);
        
        // Top 5 des classes
        if (isset($stats['topClasses'])) {
            fputcsv($output, ['Top 5 des Classes']);
            fputcsv($output, ['Rang', 'Classe', 'Moyenne', 'Taux de Réussite', 'Total Notes']);
            
            foreach ($stats['topClasses'] as $index => $class) {
                fputcsv($output, [
                    $index + 1,
                    $class['class_name'],
                    number_format($class['average_score'], 2) . '/20',
                    number_format($class['pass_rate'], 1) . '%',
                    $class['total']
                ]);
            }
        }
        
        fputcsv($output, ['']);
        
        // Meilleurs élèves
        if (isset($stats['topStudents'])) {
            fputcsv($output, ['Meilleurs Élèves']);
            fputcsv($output, ['Rang', 'Élève', 'Classe', 'Moyenne', 'Nombre d\'Examens']);
            
            foreach ($stats['topStudents'] as $index => $student) {
                fputcsv($output, [
                    $index + 1,
                    $student['first_name'] . ' ' . $student['last_name'],
                    $student['class_name'],
                    number_format($student['average_score'], 2) . '/20',
                    $student['exam_count']
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    private function generateExcelContent($stats, $chartData, $period)
    {
        // Contenu Excel simplifié (format CSV avec séparateurs tab)
        $content = "Statistiques des Examens - {$period}\n\n";
        $content .= "Statistiques Générales\n";
        $content .= "Métrique\tValeur\n";
        
        if (isset($stats['averageScores'])) {
            $content .= "Moyenne Générale\t" . number_format($stats['averageScores']['overall'] ?? 0, 2) . "/20\n";
        }
        
        if (isset($stats['passRates'])) {
            $content .= "Taux de Réussite\t" . number_format($stats['passRates']['overall'] ?? 0, 1) . "%\n";
        }
        
        $content .= "Total Examens\t" . ($stats['totalExams'] ?? 0) . "\n";
        $content .= "Examens Terminés\t" . ($stats['completedExams'] ?? 0) . "\n";
        
        // Meilleure classe
        if (isset($stats['bestClass']) && $stats['bestClass']) {
            $content .= "\nMeilleure Classe\n";
            $content .= "Classe\tMoyenne\tTaux de Réussite\tTotal Notes\n";
            $content .= $stats['bestClass']['class_name'] . "\t" . 
                       number_format($stats['bestClass']['average_score'], 2) . "/20\t" . 
                       number_format($stats['bestClass']['pass_rate'], 1) . "%\t" . 
                       $stats['bestClass']['total'] . "\n";
        }
        
        // Performance par genre
        if (isset($stats['performanceByGender'])) {
            $content .= "\nPerformance par Genre\n";
            $content .= "Genre\tMoyenne\tTaux de Réussite\tTotal Notes\n";
            
            foreach ($stats['performanceByGender'] as $gender) {
                $genderName = $gender['gender'] === 'M' ? 'Garçons' : 'Filles';
                $passRate = $gender['total'] > 0 ? round(($gender['passed'] / $gender['total']) * 100, 1) : 0;
                $content .= $genderName . "\t" . 
                           number_format($gender['average_score'], 2) . "/20\t" . 
                           $passRate . "%\t" . 
                           $gender['total'] . "\n";
            }
        }
        
        $content .= "\nPerformance par Classe\n";
        $content .= "Classe\tMoyenne\tTaux de Réussite\tTotal Notes\n";
        
        if (isset($stats['performanceByClass'])) {
            foreach ($stats['performanceByClass'] as $class) {
                $passRate = $class['total'] > 0 ? round(($class['passed'] / $class['total']) * 100, 1) : 0;
                $content .= $class['class_name'] . "\t" . 
                           number_format($class['average_score'], 2) . "/20\t" . 
                           $passRate . "%\t" . 
                           $class['total'] . "\n";
            }
        }
        
        // Top 5 des classes
        if (isset($stats['topClasses'])) {
            $content .= "\nTop 5 des Classes\n";
            $content .= "Rang\tClasse\tMoyenne\tTaux de Réussite\tTotal Notes\n";
            
            foreach ($stats['topClasses'] as $index => $class) {
                $content .= ($index + 1) . "\t" . 
                           $class['class_name'] . "\t" . 
                           number_format($class['average_score'], 2) . "/20\t" . 
                           number_format($class['pass_rate'], 1) . "%\t" . 
                           $class['total'] . "\n";
            }
        }
        
        // Meilleurs élèves
        if (isset($stats['topStudents'])) {
            $content .= "\nMeilleurs Élèves\n";
            $content .= "Rang\tÉlève\tClasse\tMoyenne\tNombre d'Examens\n";
            
            foreach ($stats['topStudents'] as $index => $student) {
                $content .= ($index + 1) . "\t" . 
                           $student['first_name'] . ' ' . $student['last_name'] . "\t" . 
                           $student['class_name'] . "\t" . 
                           number_format($student['average_score'], 2) . "/20\t" . 
                           $student['exam_count'] . "\n";
            }
        }
        
        return $content;
    }
}
