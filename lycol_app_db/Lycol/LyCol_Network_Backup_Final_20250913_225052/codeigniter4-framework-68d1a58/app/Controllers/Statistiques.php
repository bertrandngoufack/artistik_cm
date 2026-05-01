<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\GradeModel;
use App\Models\PaymentModel;
use App\Models\AbsenceModel;
use App\Models\TeacherModel;
use App\Models\ClassModel;
use App\Models\SubjectModel;
use App\Models\ExamModel;
use App\Models\AuditLogModel;

class Statistiques extends BaseController
{
    protected $studentModel;
    protected $gradeModel;
    protected $paymentModel;
    protected $absenceModel;
    protected $teacherModel;
    protected $classModel;
    protected $subjectModel;
    protected $examModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->gradeModel = new GradeModel();
        $this->paymentModel = new PaymentModel();
        $this->absenceModel = new AbsenceModel();
        $this->teacherModel = new TeacherModel();
        $this->classModel = new ClassModel();
        $this->subjectModel = new SubjectModel();
        $this->examModel = new ExamModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Module Statistiques',
            'stats' => $this->getGlobalStats()
        ];

        return view('admin/statistiques/index', $data);
    }

    public function students()
    {
        $data = [
            'title' => 'Statistiques des Élèves',
            'stats' => $this->getStudentStatistics()
        ];

        return view('admin/statistiques/students', $data);
    }

    public function grades()
    {
        $data = [
            'title' => 'Statistiques des Notes',
            'stats' => $this->getGradeStatistics()
        ];

        return view('admin/statistiques/grades', $data);
    }

    public function payments()
    {
        $data = [
            'title' => 'Statistiques des Paiements',
            'stats' => $this->getPaymentStatistics()
        ];

        return view('admin/statistiques/payments', $data);
    }

    public function absences()
    {
        $data = [
            'title' => 'Statistiques des Absences',
            'stats' => $this->getAbsenceStatistics()
        ];

        return view('admin/statistiques/absences', $data);
    }

    public function reports()
    {
        $data = [
            'title' => 'Rapports Statistiques',
            'reports' => $this->getAvailableReports()
        ];

        return view('admin/statistiques/reports', $data);
    }

    public function export($type = 'students')
    {
        switch ($type) {
            case 'students':
                $data = $this->studentModel->getAllStudentsWithClasses();
                $filename = 'eleves_' . date('Y-m-d') . '.csv';
                break;
            case 'grades':
                $data = $this->gradeModel->getAllGrades();
                $filename = 'notes_' . date('Y-m-d') . '.csv';
                break;
            case 'payments':
                $data = $this->paymentModel->getAllPayments();
                $filename = 'paiements_' . date('Y-m-d') . '.csv';
                break;
            case 'absences':
                $data = $this->absenceModel->getAllAbsences();
                $filename = 'absences_' . date('Y-m-d') . '.csv';
                break;
            default:
                return redirect()->back()->with('error', 'Type d\'export non valide');
        }

        return $this->response->download($filename, $this->generateCSV($data));
    }

    private function getGlobalStats()
    {
        // Log d'audit pour l'accès aux statistiques (temporairement désactivé)
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'VIEW_STATS',
                'statistiques',
                null,
                null,
                ['page' => 'dashboard']
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit pour l'instant
        }

        $studentStats = $this->studentModel->getStudentStats();
        $gradeStats = $this->gradeModel->getGradeStats();

        return [
            'totalStudents' => $studentStats['total'],
            'totalClasses' => $this->classModel->where('is_active', 1)->countAllResults(),
            'totalTeachers' => $this->teacherModel->where('is_active', 1)->countAllResults(),
            'totalSubjects' => $this->subjectModel->where('is_active', 1)->countAllResults(),
            'totalExams' => $this->examModel->countAllResults(),
            'totalPayments' => $this->paymentModel->getTotalRevenue(),
            'totalAbsences' => $this->absenceModel->getAbsenceStats()['total'],
            'successRate' => $this->calculateSuccessRate(),
            'byGender' => $studentStats['by_gender'],
            'byClass' => $studentStats['by_class'],
            'performanceByClass' => $this->getPerformanceByClass(),
            'recentActivity' => $this->getRecentActivity()
        ];
    }

    private function getStudentStatistics()
    {
        return [
            'byGender' => $this->studentModel->getStudentStats()['by_gender'],
            'byClass' => $this->studentModel->getStudentStats()['by_class'],
            'enrollmentTrend' => $this->studentModel->getEnrollmentTrend()
        ];
    }

    private function getGradeStatistics()
    {
        return [
            'averageScores' => $this->gradeModel->getAverageScores(),
            'passRates' => $this->gradeModel->getPassRates(),
            'topStudents' => $this->gradeModel->getTopStudents()
        ];
    }

    private function getPaymentStatistics()
    {
        return [
            'totalRevenue' => $this->paymentModel->getTotalRevenue(),
            'monthlyRevenue' => $this->paymentModel->getMonthlyRevenue(),
            'paymentMethods' => $this->paymentModel->getPaymentMethodDistribution()
        ];
    }

    private function getAbsenceStatistics()
    {
        return [
            'totalAbsences' => $this->absenceModel->getAbsenceStats()['total'],
            'byDuration' => $this->absenceModel->getAbsenceStats()['by_duration'],
            'monthlyTrend' => $this->absenceModel->getMonthlyTrend()
        ];
    }

    private function getAvailableReports()
    {
        return [
            'student_report' => 'Rapport des Élèves',
            'grade_report' => 'Rapport des Notes',
            'payment_report' => 'Rapport des Paiements',
            'absence_report' => 'Rapport des Absences'
        ];
    }

    private function generateCSV($data)
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Headers
        fputcsv($output, array_keys($data[0]));
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Obtenir la performance par classe
     */
    private function getPerformanceByClass()
    {
        try {
            $rawData = $this->gradeModel->getTopClasses(10);
            $mappedData = [];
            
            foreach ($rawData as $class) {
                $level = $this->mapNumericLevelToFrench($class['class_level']);
                if ($level) {
                    $mappedData[] = [
                        'class_name' => $class['class_name'],
                        'class_level' => $level,
                        'average_score' => $class['average_score'],
                        'passed' => $class['passed'],
                        'total' => $class['total'],
                        'pass_rate' => $class['pass_rate']
                    ];
                }
            }
            
            // Trier par niveau français
            usort($mappedData, function($a, $b) {
                $frenchLevels = ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'];
                $aIndex = array_search($a['class_level'], $frenchLevels);
                $bIndex = array_search($b['class_level'], $frenchLevels);
                return $aIndex - $bIndex;
            });
            
            return $mappedData;
        } catch (Exception $e) {
            log_message('error', 'Erreur lors de la récupération des performances par classe: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mapper les niveaux numériques vers les niveaux français
     */
    private function mapNumericLevelToFrench($numericLevel)
    {
        $levelMapping = [
            9 => '6ème',
            10 => '5ème',
            11 => '4ème',
            12 => '3ème',
            13 => '2nde',
            14 => '1ère',
            15 => 'Tle'
        ];
        
        return $levelMapping[$numericLevel] ?? null;
    }

    /**
     * Obtenir l'activité récente
     */
    private function getRecentActivity()
    {
        return [
            'recentPayments' => $this->paymentModel->getRecentPayments(5),
            'recentAbsences' => $this->absenceModel->getRecentAbsences(5),
            'recentGrades' => $this->gradeModel->getRecentGrades(5)
        ];
    }

    /**
     * Calculer le taux de réussite global
     */
    private function calculateSuccessRate()
    {
        $stats = $this->gradeModel->getGradeStats();
        if (isset($stats['total']) && $stats['total'] > 0) {
            $passingGrades = $stats['passing'] ?? 0;
            return round(($passingGrades / $stats['total']) * 100, 1);
        }
        return 0;
    }

    /**
     * Générer un rapport personnalisé
     */
    public function generateCustomReport()
    {
        $reportType = $this->request->getPost('report_type');
        $exportFormat = $this->request->getPost('export_format');
        $period = $this->request->getPost('period');

        try {
            switch ($reportType) {
                case 'students':
                    $data = $this->studentModel->getAllStudentsWithClasses();
                    $filename = 'rapport_eleves_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'grades':
                    $data = $this->gradeModel->getAllGrades();
                    $filename = 'rapport_notes_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'payments':
                    $data = $this->paymentModel->getAllPayments();
                    $filename = 'rapport_paiements_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'absences':
                    $data = $this->absenceModel->getAllAbsences();
                    $filename = 'rapport_absences_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'teachers':
                    $data = $this->teacherModel->getAllTeachers();
                    $filename = 'rapport_enseignants_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'academic':
                    $data = $this->classModel->getAllClasses();
                    $filename = 'rapport_academique_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'financial':
                    $data = $this->paymentModel->getAllPayments();
                    $filename = 'rapport_financier_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                case 'attendance':
                    $data = $this->absenceModel->getAllAbsences();
                    $filename = 'rapport_presence_' . date('Y-m-d') . '.' . $exportFormat;
                    break;
                default:
                    return redirect()->back()->with('error', 'Type de rapport non valide');
            }

            // Log de l'action
            try {
                $this->auditLogModel->logAction(
                    session()->get('user_id') ?? 1,
                    'GENERATE_REPORT',
                    'statistiques',
                    null,
                    null,
                    ['report_type' => $reportType, 'format' => $exportFormat, 'period' => $period]
                );
            } catch (Exception $e) {
                // Ignorer les erreurs de logs d'audit
            }

            if ($exportFormat === 'csv') {
                return $this->response->download($filename, $this->generateCSV($data));
            } else {
                // Pour l'instant, on ne supporte que CSV
                return redirect()->back()->with('error', 'Format d\'export non supporté pour le moment');
            }

        } catch (Exception $e) {
            log_message('error', 'Erreur lors de la génération du rapport: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du rapport');
        }
    }

    /**
     * Obtenir les statistiques des enseignants
     */
    public function teachers()
    {
        $data = [
            'title' => 'Statistiques des Enseignants',
            'stats' => $this->getTeacherStatistics()
        ];

        return view('admin/statistiques/teachers', $data);
    }

    /**
     * Obtenir les statistiques académiques
     */
    public function academic()
    {
        $data = [
            'title' => 'Statistiques Académiques',
            'stats' => $this->getAcademicStatistics()
        ];

        return view('admin/statistiques/academic', $data);
    }

    /**
     * Obtenir les statistiques financières
     */
    public function financial()
    {
        $data = [
            'title' => 'Statistiques Financières',
            'stats' => $this->getFinancialStatistics()
        ];

        return view('admin/statistiques/financial', $data);
    }

    /**
     * Obtenir les statistiques de présence
     */
    public function attendance()
    {
        $data = [
            'title' => 'Statistiques de Présence',
            'stats' => $this->getAttendanceStatistics()
        ];

        return view('admin/statistiques/attendance', $data);
    }

    private function getTeacherStatistics()
    {
        try {
            return [
                'totalTeachers' => $this->teacherModel->where('is_active', 1)->countAllResults(),
                'bySpecialization' => $this->teacherModel->getActiveTeachers() ?? [],
                'byQualification' => $this->teacherModel->getActiveTeachers() ?? [],
                'assignments' => []
            ];
        } catch (Exception $e) {
            return [
                'totalTeachers' => 0,
                'bySpecialization' => [],
                'byQualification' => [],
                'assignments' => []
            ];
        }
    }

    private function getAcademicStatistics()
    {
        try {
            return [
                'classStats' => $this->classModel->getClassStatistics() ?? [],
                'subjectStats' => $this->subjectModel->getSubjectStatistics() ?? [],
                'examStats' => $this->examModel->getExamStatistics() ?? [],
                'performanceStats' => $this->gradeModel->getPerformanceStatistics() ?? []
            ];
        } catch (Exception $e) {
            return [
                'classStats' => [],
                'subjectStats' => [],
                'examStats' => [],
                'performanceStats' => []
            ];
        }
    }

    private function getFinancialStatistics()
    {
        return [
            'totalRevenue' => $this->paymentModel->getTotalRevenue(),
            'monthlyRevenue' => $this->paymentModel->getMonthlyRevenue(),
            'paymentMethods' => $this->paymentModel->getPaymentMethodDistribution(),
            'feeTypeDistribution' => $this->paymentModel->getFeeTypeDistribution(),
            'outstandingPayments' => $this->paymentModel->getOutstandingPayments()
        ];
    }

    private function getAttendanceStatistics()
    {
        return [
            'totalAbsences' => $this->absenceModel->getAbsenceStats()['total'],
            'byDuration' => $this->absenceModel->getAbsenceStats()['by_duration'],
            'monthlyTrend' => $this->absenceModel->getMonthlyTrend(),
            'byClass' => $this->absenceModel->getAbsencesByClass(),
            'justifiedRate' => $this->absenceModel->getJustifiedAbsenceRate()
        ];
    }
}




