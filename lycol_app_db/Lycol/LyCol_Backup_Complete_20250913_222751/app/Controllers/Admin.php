<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\ClassModel;
use App\Models\SubjectModel;
use App\Models\ExamModel;
use App\Models\GradeModel;
use App\Models\PaymentModel;
use App\Models\AbsenceModel;
use App\Models\BookModel;
use App\Models\MessageModel;

/**
 * Contrôleur principal d'administration pour LyCol
 */
class Admin extends BaseController
{
    protected $studentModel;
    protected $classModel;
    protected $subjectModel;
    protected $examModel;
    protected $gradeModel;
    protected $paymentModel;
    protected $absenceModel;
    protected $bookModel;
    protected $messageModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->classModel = new ClassModel();
        $this->subjectModel = new SubjectModel();
        $this->examModel = new ExamModel();
        $this->gradeModel = new GradeModel();
        $this->paymentModel = new PaymentModel();
        $this->absenceModel = new AbsenceModel();
        $this->bookModel = new BookModel();
        $this->messageModel = new MessageModel();
    }

    /**
     * Tableau de bord principal
     */
    public function dashboard()
    {
        // Vérifier l'authentification
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        // Récupérer les données du module études
        $teacherAssignmentModel = new \App\Models\TeacherAssignmentModel();
        $cycleModel = new \App\Models\CycleModel();

        $data = [
            'title' => 'Tableau de bord - KISSAI SCHOOL',
            'total_students' => $this->studentModel->where('status', 'ACTIVE')->countAllResults(),
            'total_classes' => $this->classModel->where('is_active', 1)->countAllResults(),
            'total_subjects' => $this->subjectModel->where('is_active', 1)->countAllResults(),
            'total_exams' => $this->examModel->where('status', 'COMPLETED')->countAllResults(),
            'total_assignments' => $teacherAssignmentModel->where('is_active', 1)->countAllResults(),
            'total_cycles' => $cycleModel->where('is_active', 1)->countAllResults(),
            'recent_students' => $this->studentModel->where('status', 'ACTIVE')
                                                   ->orderBy('created_at', 'DESC')
                                                   ->limit(5)
                                                   ->find(),
            'recent_payments' => $this->paymentModel->orderBy('created_at', 'DESC')
                                                   ->limit(5)
                                                   ->find(),
            'recent_absences' => $this->absenceModel->select('absences.*, students.first_name, students.last_name')
                                                   ->join('students', 'students.id = absences.student_id')
                                                   ->orderBy('absences.created_at', 'DESC')
                                                   ->limit(5)
                                                   ->find()
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Module Économat
     */
    public function economat()
    {
        $data = [
            'title' => 'Économat - LyCol',
            'total_payments' => $this->paymentModel->countAllResults(),
            'total_amount' => $this->paymentModel->selectSum('amount_paid')->get()->getRow()->amount_paid ?? 0,
            'recent_payments' => $this->paymentModel->orderBy('created_at', 'DESC')
                                                   ->limit(10)
                                                   ->find()
        ];

        return view('admin/economat/index', $data);
    }

    /**
     * Module Scolarité
     */
    public function scolarite()
    {
        $data = [
            'title' => 'Scolarité - LyCol',
            'total_students' => $this->studentModel->where('status', 'ACTIVE')->countAllResults(),
            'total_absences' => $this->absenceModel->countAllResults(),
            'students_by_class' => $this->studentModel->select('classes.name as class_name, COUNT(*) as count')
                                                     ->join('classes', 'classes.id = students.current_class_id', 'left')
                                                     ->where('students.status', 'ACTIVE')
                                                     ->groupBy('classes.id')
                                                     ->find()
        ];

        return view('admin/scolarite/index', $data);
    }

    /**
     * Module Études
     */
    public function etudes()
    {
        $data = [
            'title' => 'Études - LyCol',
            'total_classes' => $this->classModel->where('is_active', 1)->countAllResults(),
            'total_subjects' => $this->subjectModel->where('is_active', 1)->countAllResults(),
            'classes' => $this->classModel->where('is_active', 1)->find(),
            'subjects' => $this->subjectModel->where('is_active', 1)->find()
        ];

        return view('admin/etudes/index', $data);
    }

    /**
     * Module Examens
     */
    public function examens()
    {
        $data = [
            'title' => 'Examens - LyCol',
            'total_exams' => $this->examModel->countAllResults(),
            'total_grades' => $this->gradeModel->countAllResults(),
            'recent_exams' => $this->examModel->orderBy('created_at', 'DESC')
                                             ->limit(5)
                                             ->find(),
            'exam_stats' => $this->gradeModel->select('AVG(marks_obtained) as average, COUNT(*) as total')
                                           ->get()
                                           ->getRow()
        ];

        return view('admin/examens/index', $data);
    }

    /**
     * Module Statistiques
     */
    public function statistiques()
    {
        $data = [
            'title' => 'Statistiques - LyCol',
            'student_stats' => $this->getStudentStats(),
            'grade_stats' => $this->getGradeStats(),
            'payment_stats' => $this->getPaymentStats(),
            'absence_stats' => $this->getAbsenceStats()
        ];

        return view('admin/statistiques/index', $data);
    }

    /**
     * Module Bibliothèque
     */
    public function bibliotheque()
    {
        $data = [
            'title' => 'Bibliothèque - LyCol',
            'total_books' => $this->bookModel->countAllResults(),
            'available_books' => $this->bookModel->where('available_copies >', 0)->countAllResults(),
            'recent_books' => $this->bookModel->orderBy('created_at', 'DESC')
                                             ->limit(10)
                                             ->find()
        ];

        return view('admin/bibliotheque/index', $data);
    }

    /**
     * Module Messagerie
     */
    public function messagerie()
    {
        $data = [
            'title' => 'Messagerie - LyCol',
            'total_messages' => $this->messageModel->countAllResults(),
            'sent_messages' => $this->messageModel->where('status', 'SENT')->countAllResults(),
            'recent_messages' => $this->messageModel->orderBy('created_at', 'DESC')
                                                   ->limit(10)
                                                   ->find()
        ];

        return view('admin/messagerie/index', $data);
    }

    /**
     * Module Sécurité
     */
    public function securite()
    {
        $data = [
            'title' => 'Sécurité - LyCol',
            'total_users' => (new \App\Models\UserModel())->countAllResults(),
            'active_users' => (new \App\Models\UserModel())->where('is_active', 1)->countAllResults(),
            'recent_logins' => (new \App\Models\UserModel())->where('last_login IS NOT NULL')
                                                           ->orderBy('last_login', 'DESC')
                                                           ->limit(10)
                                                           ->find()
        ];

        return view('admin/securite/index', $data);
    }

    /**
     * Configuration générale
     */
    public function configuration()
    {
        $data = [
            'title' => 'Configuration - LyCol',
            'settings' => (new \App\Models\SettingModel())->findAll()
        ];

        return view('admin/configuration/index', $data);
    }

    /**
     * Gestion des licences
     */
    public function licenses()
    {
        $data = [
            'title' => 'Gestion des Licences - LyCol',
            'licenses' => (new \App\Models\LicenseModel())->findAll()
        ];

        return view('admin/securite/licenses', $data);
    }

    /**
     * Générer une nouvelle licence
     */
    public function generateLicense()
    {
        $clientId = $this->request->getPost('client_id');
        $licenseType = $this->request->getPost('license_type');
        $duration = $this->request->getPost('duration', '1'); // années

        try {
            $licenseKey = LicenseGenerator::generateLicenseKey(
                $clientId,
                $licenseType,
                date('Y-m-d', strtotime("+{$duration} year"))
            );

            $licenseData = [
                'license_key' => $licenseKey,
                'client_id' => $clientId,
                'license_type' => $licenseType,
                'start_date' => date('Y-m-d'),
                'expiry_date' => date('Y-m-d', strtotime("+{$duration} year")),
                'is_active' => 1
            ];

            (new \App\Models\LicenseModel())->insert($licenseData);

            return redirect()->back()->with('success', 'Licence générée avec succès: ' . $licenseKey);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques des élèves
     */
    private function getStudentStats()
    {
        return [
            'total' => $this->studentModel->where('status', 'ACTIVE')->countAllResults(),
            'male' => $this->studentModel->where('status', 'ACTIVE')->where('gender', 'M')->countAllResults(),
            'female' => $this->studentModel->where('status', 'ACTIVE')->where('gender', 'F')->countAllResults(),
            'by_class' => $this->studentModel->select('classes.name, COUNT(*) as count')
                                           ->join('classes', 'classes.id = students.current_class_id')
                                           ->where('students.status', 'ACTIVE')
                                           ->groupBy('classes.id')
                                           ->find()
        ];
    }

    /**
     * Statistiques des notes
     */
    private function getGradeStats()
    {
        $grades = $this->gradeModel->select('AVG(marks_obtained) as average, MIN(marks_obtained) as min, MAX(marks_obtained) as max, COUNT(*) as total')
                                  ->get()
                                  ->getRow();

        return [
            'average' => round($grades->average ?? 0, 2),
            'min' => $grades->min ?? 0,
            'max' => $grades->max ?? 0,
            'total' => $grades->total ?? 0
        ];
    }

    /**
     * Statistiques des paiements
     */
    private function getPaymentStats()
    {
        $payments = $this->paymentModel->select('SUM(amount_paid) as total, COUNT(*) as count')
                                     ->get()
                                     ->getRow();

        return [
            'total' => $payments->total ?? 0,
            'count' => $payments->count ?? 0,
            'average' => $payments->count > 0 ? round($payments->total / $payments->count, 2) : 0
        ];
    }

    /**
     * Statistiques des absences
     */
    private function getAbsenceStats()
    {
        return [
            'total' => $this->absenceModel->countAllResults(),
            'justified' => $this->absenceModel->where('justified', 1)->countAllResults(),
            'unjustified' => $this->absenceModel->where('justified', 0)->countAllResults()
        ];
    }

    /**
     * Export des données
     */
    public function export($type = 'students')
    {
        switch ($type) {
            case 'students':
                $data = $this->studentModel->where('status', 'ACTIVE')->findAll();
                $filename = 'eleves_' . date('Y-m-d') . '.csv';
                break;
            case 'grades':
                $data = $this->gradeModel->findAll();
                $filename = 'notes_' . date('Y-m-d') . '.csv';
                break;
            case 'payments':
                $data = $this->paymentModel->findAll();
                $filename = 'paiements_' . date('Y-m-d') . '.csv';
                break;
            default:
                return redirect()->back()->with('error', 'Type d\'export invalide');
        }

        return $this->response->download($filename, $this->generateCSV($data));
    }

    /**
     * Générer un fichier CSV
     */
    private function generateCSV($data)
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // En-têtes
        fputcsv($output, array_keys($data[0]));
        
        // Données
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
