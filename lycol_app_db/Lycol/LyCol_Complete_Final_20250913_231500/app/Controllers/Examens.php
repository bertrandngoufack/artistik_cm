<?php

namespace App\Controllers;

use App\Models\ExamModel;
use App\Models\GradeModel;
use App\Models\StudentModel;
use App\Services\PDFService;
use App\Services\ExportService;
use App\Services\NotificationService;
use App\Traits\AcademicYearTrait;

class Examens extends BaseController
{
    use AcademicYearTrait;
    
    protected $examModel;
    protected $gradeModel;
    protected $studentModel;
    protected $classModel;
    protected $pdfService;
    protected $exportService;
    protected $notificationService;

    public function __construct()
    {
        $this->examModel = new ExamModel();
        $this->gradeModel = new GradeModel();
        $this->studentModel = new StudentModel();
        $this->classModel = new \App\Models\ClassModel();
        $this->pdfService = new PDFService();
        $this->exportService = new ExportService();
        $this->notificationService = new NotificationService();
        
        // Initialiser l'année scolaire
        $this->initAcademicYear();
    }

    public function index()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $recentExams = $this->examModel->getRecentExamsByAcademicYear(10, $academicYear);
        
        // Traduire les types et statuts des examens
        foreach ($recentExams as &$exam) {
            $exam['exam_type_translated'] = $this->translateExamType($exam['exam_type']);
            $exam['status_translated'] = $this->translateStatus($exam['status']);
        }
        
        $data = $this->prepareViewData([
            'title' => 'Module Examens',
            'stats' => $this->examModel->getExamStatsByAcademicYear($academicYear),
            'recentExams' => $recentExams,
            'academicPeriods' => $this->getAcademicPeriods()
        ]);

        return view('admin/examens/dashboard', $data);
    }

    public function exams()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $exams = $this->examModel->getExamsPaginatedByAcademicYear($academicYear);
        
        // Traduire les types et statuts des examens
        foreach ($exams as &$exam) {
            $exam['exam_type_translated'] = $this->translateExamType($exam['exam_type']);
            $exam['status_translated'] = $this->translateStatus($exam['status']);
        }
        
        $data = $this->prepareViewData([
            'title' => 'Gestion des Examens',
            'exams' => $exams,
            'pager' => $this->examModel->getExamsPager(),
            'academicPeriods' => $this->getAcademicPeriods()
        ]);

        return view('admin/examens/exams', $data);
    }

    public function createExam()
    {
        $data = $this->prepareViewData([
            'title' => 'Nouvel Examen',
            'classes' => $this->getClasses(),
            'academicPeriods' => $this->getAcademicPeriods(),
            'subjects' => $this->getSubjects()
        ]);

        return view('admin/examens/create_exam', $data);
    }

    public function storeExam()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'exam_type' => 'required|in_list[CONTINUOUS,MIDTERM,FINAL,COMPETITIVE]',
            'class_id' => 'required|integer',
            'exam_date' => 'required|valid_date',
            'total_marks' => 'required|numeric|greater_than[0]',
            'coefficient' => 'permit_empty|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $examData = [
            'name' => $this->request->getPost('name'),
            'exam_type' => $this->request->getPost('exam_type'),
            'class_id' => $this->request->getPost('class_id'),
            'exam_date' => $this->request->getPost('exam_date'),
            'total_marks' => $this->request->getPost('total_marks'),
            'coefficient' => $this->request->getPost('coefficient') ?: 1.0,
            'academic_year' => $this->getCurrentAcademicYear(),
            'status' => 'SCHEDULED'
        ];

        if ($this->examModel->insert($examData)) {
            // Envoyer des notifications aux parents
            $this->sendExamNotifications($examData);
            
            return redirect()->to('admin/examens/exams')->with('success', 'Examen créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editExam($id)
    {
        $exam = $this->examModel->find($id);
        
        if (!$exam) {
            return redirect()->to('admin/examens/exams')->with('error', 'Examen non trouvé');
        }

        $data = [
            'title' => 'Modifier l\'Examen',
            'exam' => $exam,
            'classes' => $this->getClasses(),
            'academicPeriods' => $this->getAcademicPeriods(),
            'subjects' => $this->getSubjects()
        ];

        return view('admin/examens/edit_exam', $data);
    }

    public function updateExam($id)
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'exam_type' => 'required|in_list[CONTINUOUS,MIDTERM,FINAL,COMPETITIVE]',
            'class_id' => 'required|integer',
            'exam_date' => 'required|valid_date',
            'total_marks' => 'required|numeric|greater_than[0]',
            'coefficient' => 'permit_empty|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $examData = [
            'name' => $this->request->getPost('name'),
            'exam_type' => $this->request->getPost('exam_type'),
            'class_id' => $this->request->getPost('class_id'),
            'exam_date' => $this->request->getPost('exam_date'),
            'total_marks' => $this->request->getPost('total_marks'),
            'coefficient' => $this->request->getPost('coefficient') ?: 1.0
        ];

        if ($this->examModel->update($id, $examData)) {
            return redirect()->to('admin/examens/exams')->with('success', 'Examen modifié avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la modification');
        }
    }

    public function deleteExam($id)
    {
        if ($this->examModel->delete($id)) {
            return redirect()->to('admin/examens/exams')->with('success', 'Examen supprimé avec succès');
        } else {
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }

    public function grades()
    {
        $exams = $this->examModel->getRecentExams(50);
        
        // Traduire les types et statuts des examens
        foreach ($exams as &$exam) {
            $exam['exam_type_translated'] = $this->translateExamType($exam['exam_type']);
            $exam['status_translated'] = $this->translateStatus($exam['status']);
        }
        
        $data = [
            'title' => 'Gestion des Notes',
            'exams' => $exams,
            'stats' => $this->examModel->getExamStats()
        ];

        return view('admin/examens/grades', $data);
    }

    public function enterGrades($examId)
    {
        $exam = $this->examModel->getExamWithDetails($examId);
        
        if (!$exam) {
            return redirect()->to('admin/examens/grades')->with('error', 'Examen non trouvé');
        }

        $students = $this->studentModel->getStudentsByClass($exam['class_id']);
        $existingGrades = $this->gradeModel->getGradesByExam($examId);

        $data = [
            'title' => 'Saisie des Notes',
            'exam' => $exam,
            'students' => $students,
            'grades' => $existingGrades
        ];

        return view('admin/examens/enter_grades', $data);
    }

    public function storeGrades()
    {
        $examId = $this->request->getPost('exam_id');
        $grades = $this->request->getPost('grades');

        if (!$grades || !is_array($grades)) {
            return redirect()->back()->with('error', 'Aucune note à enregistrer');
        }

        $successCount = 0;
        $errors = [];

        foreach ($grades as $studentId => $gradeData) {
            $marks = $gradeData['marks'] ?? 0;
            $comments = $gradeData['comments'] ?? '';

            // Validation stricte des notes (0-20)
            if ($marks < 0 || $marks > 20) {
                $errors[] = "Note invalide pour l'élève ID {$studentId}: {$marks}/20";
                continue;
            }

            $existingGrade = $this->gradeModel->where('exam_id', $examId)
                                             ->where('student_id', $studentId)
                                             ->first();

            $gradeData = [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'subject_id' => 1, // Valeur par défaut, à adapter selon la logique métier
                'marks_obtained' => $marks,
                'remarks' => $comments
            ];

            if ($existingGrade) {
                // Mettre à jour la note existante
                if ($this->gradeModel->update($existingGrade['id'], $gradeData)) {
                    $successCount++;
                }
            } else {
                // Créer une nouvelle note
                if ($this->gradeModel->insert($gradeData)) {
                    $successCount++;
                }
            }
        }

        // Mettre à jour le statut de l'examen si des notes ont été saisies
        if ($successCount > 0) {
            $this->examModel->update($examId, ['status' => 'TERMINÉ']);
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        return redirect()->to('admin/examens/grades')->with('success', "{$successCount} note(s) enregistrée(s) avec succès");
    }

    public function reportCards()
    {
        $data = [
            'title' => 'Bulletins de Notes',
            'classes' => $this->classModel->getActiveClasses(),
            'academicPeriods' => $this->getAcademicPeriods()
        ];

        return view('admin/examens/report_cards', $data);
    }

    public function generateReportCards()
    {
        // Récupérer les paramètres depuis GET ou POST
        $classId = $this->request->getGet('class_id') ?? $this->request->getPost('class_id');
        $examId = $this->request->getGet('exam_id') ?? $this->request->getPost('exam_id');
        $period = $this->request->getGet('period') ?? $this->request->getPost('period');
        $format = $this->request->getGet('format') ?? $this->request->getPost('format');

        // Si on a un examId depuis le lien, récupérer les informations de l'examen
        if ($examId && !$classId) {
            $exam = $this->examModel->find($examId);
            if ($exam) {
                $classId = $exam['class_id'];
            }
        }

        $data = [
            'title' => 'Bulletins Générés',
            'class_id' => $classId,
            'exam_id' => $examId,
            'period' => $period,
            'format' => $format,
            'class' => $classId ? $this->classModel->find($classId) : null,
            'students' => $classId ? $this->studentModel->getStudentsByClass($classId) : [],
            'grades' => $examId ? $this->gradeModel->getGradesByExam($examId) : [],
            'classes' => $this->classModel->getActiveClasses(),
            'academicPeriods' => $this->getAcademicPeriods()
        ];

        return view('admin/examens/generated_report_cards', $data);
    }

    // Génération effective des PDF pour les bulletins
    public function generatePDFReportCards()
    {
        $classId = $this->request->getPost('class_id');
        $examId = $this->request->getPost('exam_id');
        $period = $this->request->getPost('period');

        $students = $this->studentModel->getStudentsByClass($classId);
        $grades = $examId ? $this->gradeModel->getGradesByExam($examId) : [];
        $class = $this->getClassById($classId);

        $html = view('admin/examens/pdf_report_cards', [
            'students' => $students,
            'grades' => $grades,
            'class' => $class,
            'period' => $period
        ]);

        $filename = "bulletins_{$class['name']}_{$period}_" . date('Y-m-d') . ".pdf";
        
        return $this->pdfService->generatePDF($html, $filename);
    }

    public function statistics()
    {
        try {
            $data = [
                'title' => 'Statistiques des Examens',
                'stats' => $this->getExamStatistics(),
                'chartData' => $this->getChartData()
            ];

            return view('admin/examens/statistics', $data);
        } catch (Exception $e) {
            // Log l'erreur et retourner une page d'erreur simple
            log_message('error', 'Erreur dans statistics: ' . $e->getMessage());
            
            return view('admin/examens/statistics_simple', [
                'title' => 'Statistiques des Examens',
                'error' => $e->getMessage()
            ]);
        }
    }

    // Exports de statistiques (PDF, Excel, CSV)
    public function exportStatistics()
    {
        $format = $this->request->getGet('format') ?? 'pdf';
        $period = $this->request->getGet('period') ?? 'current';

        $stats = $this->getExamStatistics();
        $chartData = $this->getChartData();

        switch ($format) {
            case 'pdf':
                return $this->exportService->exportStatisticsPDF($stats, $chartData, $period);
            case 'excel':
                return $this->exportService->exportStatisticsExcel($stats, $chartData, $period);
            case 'csv':
                return $this->exportService->exportStatisticsCSV($stats, $chartData, $period);
            default:
                return redirect()->back()->with('error', 'Format d\'export non supporté');
        }
    }

    // Gestion des périodes académiques
    public function academicPeriods()
    {
        $academicPeriodModel = new \App\Models\AcademicPeriodModel();
        $academicYear = $this->request->getGet('academic_year') ?: $academicPeriodModel->getCurrentAcademicYear();
        
        $data = [
            'title' => 'Gestion des Périodes Académiques',
            'periods' => $academicPeriodModel->getActivePeriods($academicYear),
            'currentPeriod' => $academicPeriodModel->getCurrentPeriod($academicYear),
            'periodStats' => $academicPeriodModel->getPeriodStats($academicYear),
            'academicYear' => $academicYear,
            'availableYears' => $academicPeriodModel->getAvailableAcademicYears()
        ];

        return view('admin/examens/academic_periods', $data);
    }

    public function updateAcademicPeriod()
    {
        $periodId = $this->request->getPost('period_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');

        $rules = [
            'period_id' => 'required|integer',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Mettre à jour la période académique
        $periodModel = new \App\Models\AcademicPeriodModel();
        $periodData = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($periodModel->updatePeriod($periodId, $periodData)) {
            return redirect()->back()->with('success', 'Période académique mise à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function createAcademicYear()
    {
        $academicYear = $this->request->getPost('academic_year');
        
        $rules = [
            'academic_year' => 'required|regex_match[/^\d{4}-\d{4}$/]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodModel = new \App\Models\AcademicPeriodModel();
        
        // Vérifier si l'année académique existe déjà
        if ($periodModel->academicYearExists($academicYear)) {
            return redirect()->back()->withInput()->with('error', 'Cette année académique existe déjà');
        }

        // Créer les périodes par défaut
        if ($periodModel->createDefaultPeriods($academicYear)) {
            return redirect()->back()->with('success', 'Nouvelle année académique créée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de l\'année académique');
        }
    }

    // Notifications pour les examens
    public function sendExamNotifications($examData)
    {
        $students = $this->studentModel->getStudentsByClass($examData['class_id']);
        
        foreach ($students as $student) {
            $message = "Nouvel examen programmé : {$examData['name']} le " . date('d/m/Y', strtotime($examData['exam_date']));
            
            // Notification par email
            $this->notificationService->sendEmail(
                $student['parent_email'],
                'Nouvel Examen Programmé',
                $message
            );
            
            // Notification par SMS
            if (!empty($student['parent_phone'])) {
                $this->notificationService->sendSMS(
                    $student['parent_phone'],
                    $message
                );
            }
            
            // Notification WhatsApp
            if (!empty($student['parent_phone'])) {
                $this->notificationService->sendWhatsApp(
                    $student['parent_phone'],
                    $message
                );
            }
        }
    }

    private function getExamStats()
    {
        return [
            'totalExams' => $this->examModel->countAllResults(),
            'completedExams' => $this->examModel->getCompletedExams()->count(),
            'pendingExams' => $this->examModel->where('status', 'PROGRAMMÉ')->countAllResults()
        ];
    }

    private function getExamStatistics()
    {
        $averageScores = $this->gradeModel->getAverageScores();
        $passRates = $this->gradeModel->getPassRates();
        $performanceByClass = $this->gradeModel->getPerformanceByClass();
        
        // Calculer les moyennes globales
        $overallAverage = 0;
        $overallPassRate = 0;
        $totalGrades = 0;
        $totalPassed = 0;
        
        if (!empty($averageScores)) {
            foreach ($averageScores as $score) {
                $overallAverage += $score['average_score'] * $score['total'];
                $totalGrades += $score['total'];
                $totalPassed += $score['passed'];
            }
            $overallAverage = $totalGrades > 0 ? $overallAverage / $totalGrades : 0;
            $overallPassRate = $totalGrades > 0 ? ($totalPassed / $totalGrades) * 100 : 0;
        }
        
        return [
            'averageScores' => [
                'overall' => round($overallAverage, 2),
                'bySubject' => $averageScores
            ],
            'passRates' => [
                'overall' => round($overallPassRate, 1),
                'bySubject' => $passRates
            ],
            'topStudents' => $this->gradeModel->getTopStudents(),
            'performanceByClass' => $performanceByClass,
            'performanceBySubject' => $this->gradeModel->getPerformanceBySubject(),
            'performanceByGender' => $this->gradeModel->getPerformanceByGender(),
            'bestClass' => $this->gradeModel->getBestClass(),
            'topClasses' => $this->gradeModel->getTopClasses(),
            'totalExams' => $this->examModel->countAllResults(),
            'completedExams' => $this->examModel->where('status', 'COMPLETED')->countAllResults()
        ];
    }

    private function getChartData()
    {
        $averageScoresData = $this->gradeModel->getAverageScoresForChart();
        $passRatesData = $this->gradeModel->getPassRatesForChart();
        $performanceTrendData = $this->gradeModel->getPerformanceTrendForChart();
        
        // Formater les données pour Chart.js
        $averageScoresChart = [
            'labels' => array_column($averageScoresData, 'name'),
            'data' => array_column($averageScoresData, 'average_score')
        ];
        
        $passRatesChart = [
            'labels' => array_column($passRatesData, 'name'),
            'data' => array_column($passRatesData, 'pass_rate')
        ];
        
        $performanceTrendChart = [
            'labels' => array_column($performanceTrendData, 'exam_date'),
            'averages' => array_column($performanceTrendData, 'average_score'),
            'passRates' => [] // À calculer si nécessaire
        ];

        // Nouvelles données pour les graphiques par genre et par classe
        $genderData = $this->gradeModel->getPerformanceByGenderForChart();
        $topClassesData = $this->gradeModel->getTopClassesForChart();

        $genderChart = [
            'labels' => array_column($genderData, 'gender'),
            'data' => array_column($genderData, 'average_score')
        ];

        $topClassesChart = [
            'labels' => array_column($topClassesData, 'class_name'),
            'data' => array_column($topClassesData, 'average_score')
        ];
        
        return [
            'averageScoresChart' => $averageScoresChart,
            'passRatesChart' => $passRatesChart,
            'performanceTrendChart' => $performanceTrendChart,
            'genderChart' => $genderChart,
            'topClassesChart' => $topClassesChart
        ];
    }

    private function getClasses()
    {
        $classModel = new \App\Models\ClassModel();
        return $classModel->where('is_active', 1)->findAll();
    }

    private function getClassById($id)
    {
        $classModel = new \App\Models\ClassModel();
        return $classModel->find($id);
    }

    public function viewExam($id)
    {
        $exam = $this->examModel->getExamWithDetails($id);
        
        if (!$exam) {
            return redirect()->to('admin/examens/exams')->with('error', 'Examen non trouvé')->setStatusCode(404);
        }

        // Récupérer les vraies données des élèves de cette classe
        $students = $this->studentModel->getStudentsByClass($exam['class_id']);
        
        // Récupérer toutes les notes pour les statistiques
        $allGrades = $this->gradeModel->getGradesByExam($id);
        
        // Pagination simple
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('limit') ?? 20;
        $offset = ($page - 1) * $perPage;
        
        // Pagination manuelle des notes
        $grades = array_slice($allGrades, $offset, $perPage);
        $totalGrades = count($allGrades);
        
        // Calculer les statistiques
        $totalMarks = array_sum(array_column($allGrades, 'marks_obtained'));
        $average = count($allGrades) > 0 ? round($totalMarks / count($allGrades), 2) : 0;
        $passed = count(array_filter($allGrades, function($grade) {
            return $grade['marks_obtained'] >= 10;
        }));
        $passRate = count($allGrades) > 0 ? round(($passed / count($allGrades)) * 100, 1) : 0;

        // Traduire le statut de l'examen
        $exam['status_translated'] = $this->translateStatus($exam['status']);
        $exam['exam_type_translated'] = $this->translateExamType($exam['exam_type']);

        $data = [
            'title' => 'Détails de l\'Examen',
            'exam' => $exam,
            'students' => $students,
            'grades' => $grades,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalGrades,
                'total_pages' => ceil($totalGrades / $perPage)
            ],
            'stats' => [
                'total_grades' => count($allGrades),
                'average' => $average,
                'passed' => $passed,
                'pass_rate' => $passRate
            ]
        ];

        return view('admin/examens/view_exam', $data);
    }

    private function getSubjects()
    {
        $subjectModel = new \App\Models\SubjectModel();
        return $subjectModel->where('is_active', 1)->findAll();
    }

    private function getAcademicPeriods()
    {
        return [
            '1ER_TRIMESTRE' => '1er Trimestre',
            '2EME_TRIMESTRE' => '2ème Trimestre', 
            '3EME_TRIMESTRE' => '3ème Trimestre'
        ];
    }

    private function getCurrentAcademicPeriod()
    {
        $currentDate = date('Y-m-d');
        $periods = [
            '1ER_TRIMESTRE' => ['start' => '2024-09-01', 'end' => '2024-12-20'],
            '2EME_TRIMESTRE' => ['start' => '2025-01-06', 'end' => '2025-03-28'],
            '3EME_TRIMESTRE' => ['start' => '2025-04-07', 'end' => '2025-06-30']
        ];

        foreach ($periods as $period => $dates) {
            if ($currentDate >= $dates['start'] && $currentDate <= $dates['end']) {
                return $period;
            }
        }

        return '3EME_TRIMESTRE'; // Par défaut
    }

    // Méthodes de traduction pour l'affichage en français
    public function translateExamType($type)
    {
        $translations = [
            'CONTINUOUS' => 'Continu',
            'MIDTERM' => 'Mi-parcours',
            'FINAL' => 'Final',
            'COMPETITIVE' => 'Compétitif'
        ];
        
        return $translations[$type] ?? $type;
    }

    public function translateStatus($status)
    {
        $translations = [
            'SCHEDULED' => 'Programmé',
            'IN_PROGRESS' => 'En cours',
            'COMPLETED' => 'Terminé',
            'CANCELLED' => 'Annulé',
            'PROGRAMMÉ' => 'Programmé',
            'EN_COURS' => 'En cours',
            'TERMINÉ' => 'Terminé',
            'ANNULE' => 'Annulé'
        ];
        
        return $translations[$status] ?? $status;
    }
}




