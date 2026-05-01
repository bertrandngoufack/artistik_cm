<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\AbsenceModel;
use App\Models\DisciplineModel;
use App\Traits\AcademicYearTrait;
use App\Services\ConfigurationService;
use App\Services\DatabaseService;
use \PDO;
use \PDOException;

class Scolarite extends BaseController
{
    protected $studentModel;
    protected $absenceModel;
    protected $disciplineModel;
    protected $configService;
    protected $pdo;

    use AcademicYearTrait;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->absenceModel = new AbsenceModel();
        $this->disciplineModel = new DisciplineModel();
        $this->configService = new ConfigurationService();
        $this->pdo = DatabaseService::getInstance()->getConnection();
        $this->initAcademicYear();
    }

    public function index()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        
        // Statistiques avec filtrage par année académique
        $stats = $this->getScolariteStats($academicYear);
        
        // Derniers élèves inscrits
        $recentStudents = $this->getRecentStudents($academicYear);
        
        // Dernières absences
        $recentAbsences = $this->getRecentAbsences($academicYear);
        
        // Derniers incidents disciplinaires
        $recentIncidents = $this->getRecentIncidents($academicYear);
        

        
        $data = [
            'title' => 'Module Scolarité',
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'recentAbsences' => $recentAbsences,
            'recentIncidents' => $recentIncidents,
            'current_academic_year' => $academicYear,
            'available_academic_years' => $this->academicYearConfig->getAvailableAcademicYears(),
            'academic_year_dates' => $this->academicYearConfig->getAcademicYearDates($academicYear)
        ];

        return view('admin/scolarite/index', $data);
    }

    public function students()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $classId = $this->request->getGet('class_id');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');
        
        // Récupération des élèves avec filtres
        $students = $this->getStudentsWithFilters($academicYear, $classId, $status, $search);
        
        // Statistiques des élèves
        $studentStats = $this->getStudentStats($academicYear, $classId, $status);
        
        // Classes disponibles
        $classes = $this->getActiveClasses($academicYear);
        
        $data = $this->prepareViewData([
            'title' => 'Gestion des Élèves',
            'students' => $students,
            'studentStats' => $studentStats,
            'classes' => $classes,
            'filters' => [
                'academic_year' => $academicYear,
                'class_id' => $classId,
                'status' => $status,
                'search' => $search
            ]
        ]);

        return view('admin/scolarite/students', $data);
    }

    public function createStudent()
    {
        $academicYear = $this->getCurrentAcademicYear();
        $data = [
            'title' => 'Nouvel Élève',
            'classes' => $this->getActiveClasses($academicYear)
        ];

        return view('admin/scolarite/create_student', $data);
    }

    public function storeStudent()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'birth_date' => 'required|valid_date',
            'gender' => 'required|in_list[MALE,FEMALE]',
            'current_class_id' => 'required|integer',
            'enrollment_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $studentData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'birth_date' => $this->request->getPost('birth_date'),
            'gender' => $this->request->getPost('gender'),
            'current_class_id' => $this->request->getPost('current_class_id'),
            'enrollment_date' => $this->request->getPost('enrollment_date'),
            'status' => 'ACTIVE'
        ];

        if ($this->studentModel->createStudent($studentData)) {
            return redirect()->to('admin/scolarite/students')->with('success', 'Élève enregistré avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement');
        }
    }

    public function editStudent($id)
    {
        $student = $this->studentModel->getStudentWithClass($id);
        
        if (!$student) {
            return redirect()->to('admin/scolarite/students')->with('error', 'Élève non trouvé');
        }

        $academicYear = $this->getCurrentAcademicYear();
        $data = [
            'title' => 'Modifier l\'Élève',
            'student' => $student,
            'classes' => $this->getActiveClasses($academicYear)
        ];

        return view('admin/scolarite/edit_student', $data);
    }

    public function updateStudent($id)
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'birth_date' => 'required|valid_date',
            'gender' => 'required|in_list[MALE,FEMALE]',
            'current_class_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $studentData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'birth_date' => $this->request->getPost('birth_date'),
            'gender' => $this->request->getPost('gender'),
            'current_class_id' => $this->request->getPost('current_class_id')
        ];

        if ($this->studentModel->updateStudent($id, $studentData)) {
            return redirect()->to('admin/scolarite/students')->with('success', 'Élève mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteStudent($id)
    {
        if ($this->studentModel->deleteStudent($id)) {
            return redirect()->to('admin/scolarite/students')->with('success', 'Élève supprimé avec succès');
        } else {
            return redirect()->to('admin/scolarite/students')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function viewStudent($id)
    {
        $student = $this->studentModel->getStudentWithClass($id);
        
        if (!$student) {
            return redirect()->to('admin/scolarite/students')->with('error', 'Élève non trouvé');
        }

        $data = [
            'title' => 'Profil de l\'Élève',
            'student' => $student,
            'absences' => $this->absenceModel->getAbsencesByStudent($id),
            'discipline' => $this->disciplineModel->getDisciplineByStudent($id),
            'current_academic_year' => $this->getCurrentAcademicYear(),
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/view_student', $data);
    }

    public function absences()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $studentId = $this->request->getGet('student_id');
        $classId = $this->request->getGet('class_id');
        $justified = $this->request->getGet('justified');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        
        // Récupération des absences avec filtres
        $absences = $this->getAbsencesWithFilters($academicYear, $studentId, $classId, $justified, $dateFrom, $dateTo);
        
        // Statistiques des absences
        $absenceStats = $this->getAbsenceStats($academicYear, $studentId, $classId, $justified, $dateFrom, $dateTo);
        
        // Élèves et classes disponibles
        $students = $this->getActiveStudents($academicYear);
        $classes = $this->getActiveClasses($academicYear);
        
        $data = [
            'title' => 'Gestion des Absences',
            'absences' => $absences,
            'absenceStats' => $absenceStats,
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'academic_year' => $academicYear,
                'student_id' => $studentId,
                'class_id' => $classId,
                'justified' => $justified,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'current_academic_year' => $academicYear,
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/absences', $data);
    }

    public function createAbsence()
    {
        $data = [
            'title' => 'Nouvelle Absence',
            'students' => $this->studentModel->getActiveStudents()
        ];

        return view('admin/scolarite/create_absence', $data);
    }

    public function storeAbsence()
    {
        $rules = [
            'student_id' => 'required|integer',
            'date' => 'required|valid_date',
            'reason' => 'required|max_length[500]',
            'duration' => 'required|in_list[HALF_DAY,FULL_DAY,MULTIPLE_DAYS]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $absenceData = [
            'student_id' => $this->request->getPost('student_id'),
            'date' => $this->request->getPost('date'),
            'reason' => $this->request->getPost('reason'),
            'duration' => $this->request->getPost('duration'),
            'recorded_by' => session()->get('user_id')
        ];

        if ($this->absenceModel->createAbsence($absenceData)) {
            return redirect()->to('admin/scolarite/absences')->with('success', 'Absence enregistrée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement');
        }
    }

    public function viewAbsence($id)
    {
        // Récupérer l'absence avec les informations de l'élève
        $stmt = $this->pdo->prepare("
            SELECT a.*, s.first_name, s.last_name, s.matricule, c.name as class_name
            FROM absences a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN classes c ON s.current_class_id = c.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $absence = $stmt->fetch(\PDO::FETCH_OBJ);
        
        if (!$absence) {
            return redirect()->to('admin/scolarite/absences')->with('error', 'Absence non trouvée');
        }

        $data = [
            'title' => 'Détails de l\'Absence',
            'absence' => $absence,
            'current_academic_year' => $this->getCurrentAcademicYear(),
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/view_absence', $data);
    }

    public function viewIncident($id)
    {
        // Récupérer l'incident avec les informations de l'élève
        $stmt = $this->pdo->prepare("
            SELECT d.*, s.first_name, s.last_name, s.matricule, c.name as class_name
            FROM discipline_incidents d
            JOIN students s ON d.student_id = s.id
            LEFT JOIN classes c ON s.current_class_id = c.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $incident = $stmt->fetch(\PDO::FETCH_OBJ);
        
        if (!$incident) {
            return redirect()->to('admin/scolarite/discipline')->with('error', 'Incident non trouvé');
        }

        $data = [
            'title' => 'Détails de l\'Incident Disciplinaire',
            'incident' => $incident,
            'current_academic_year' => $this->getCurrentAcademicYear(),
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/view_incident', $data);
    }

    // =====================================================
    // MÉTHODES CRUD POUR LES INCIDENTS DISCIPLINAIRES
    // =====================================================

    public function createIncident()
    {
        $academicYear = $this->getCurrentAcademicYear();
        $students = $this->getActiveStudents($academicYear);
        
        $data = [
            'title' => 'Nouvel Incident Disciplinaire',
            'students' => $students,
            'current_academic_year' => $academicYear,
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/create_incident', $data);
    }

    public function storeIncident()
    {
        $rules = [
            'student_id' => 'required|integer',
            'incident_type' => 'required|in_list[MINOR,MAJOR,CRITICAL]',
            'description' => 'required|max_length[1000]',
            'sanction' => 'required|max_length[500]',
            'incident_date' => 'required|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'witnesses' => 'permit_empty|max_length[500]',
            'sanction_duration' => 'permit_empty|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $incidentData = [
            'student_id' => $this->request->getPost('student_id'),
            'incident_type' => $this->request->getPost('incident_type'),
            'description' => $this->request->getPost('description'),
            'sanction' => $this->request->getPost('sanction'),
            'incident_date' => $this->request->getPost('incident_date'),
            'incident_time' => $this->request->getPost('incident_time') ?: null,
            'location' => $this->request->getPost('location'),
            'witnesses' => $this->request->getPost('witnesses'),
            'sanction_duration' => $this->request->getPost('sanction_duration'),
            'parent_notified' => $this->request->getPost('parent_notified') ? 1 : 0,
            'notification_sent' => 0,
            'recorded_by' => session()->get('user_id'),
            'academic_year' => $this->getCurrentAcademicYear()
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO discipline_incidents (
                student_id, incident_type, description, sanction, incident_date, incident_time,
                location, witnesses, sanction_duration, parent_notified, notification_sent,
                recorded_by, academic_year
            ) VALUES (
                :student_id, :incident_type, :description, :sanction, :incident_date, :incident_time,
                :location, :witnesses, :sanction_duration, :parent_notified, :notification_sent,
                :recorded_by, :academic_year
            )
        ");

        if ($stmt->execute($incidentData)) {
            return redirect()->to('admin/scolarite/discipline')->with('success', 'Incident disciplinaire enregistré avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement');
        }
    }

    public function editIncident($id)
    {
        // Récupérer l'incident avec les informations de l'élève
        $stmt = $this->pdo->prepare("
            SELECT d.*, s.first_name, s.last_name, s.matricule
            FROM discipline_incidents d
            JOIN students s ON d.student_id = s.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $incident = $stmt->fetch(\PDO::FETCH_OBJ);
        
        if (!$incident) {
            return redirect()->to('admin/scolarite/discipline')->with('error', 'Incident non trouvé');
        }

        $academicYear = $this->getCurrentAcademicYear();
        $students = $this->getActiveStudents($academicYear);
        
        $data = [
            'title' => 'Modifier l\'Incident Disciplinaire',
            'incident' => $incident,
            'students' => $students,
            'current_academic_year' => $academicYear,
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/edit_incident', $data);
    }

    public function updateIncident($id)
    {
        $rules = [
            'student_id' => 'required|integer',
            'incident_type' => 'required|in_list[MINOR,MAJOR,CRITICAL]',
            'description' => 'required|max_length[1000]',
            'sanction' => 'required|max_length[500]',
            'incident_date' => 'required|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'witnesses' => 'permit_empty|max_length[500]',
            'sanction_duration' => 'permit_empty|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $incidentData = [
            'student_id' => $this->request->getPost('student_id'),
            'incident_type' => $this->request->getPost('incident_type'),
            'description' => $this->request->getPost('description'),
            'sanction' => $this->request->getPost('sanction'),
            'incident_date' => $this->request->getPost('incident_date'),
            'incident_time' => $this->request->getPost('incident_time') ?: null,
            'location' => $this->request->getPost('location'),
            'witnesses' => $this->request->getPost('witnesses'),
            'sanction_duration' => $this->request->getPost('sanction_duration'),
            'parent_notified' => $this->request->getPost('parent_notified') ? 1 : 0,
            'id' => $id
        ];

        $stmt = $this->pdo->prepare("
            UPDATE discipline_incidents SET
                student_id = :student_id,
                incident_type = :incident_type,
                description = :description,
                sanction = :sanction,
                incident_date = :incident_date,
                incident_time = :incident_time,
                location = :location,
                witnesses = :witnesses,
                sanction_duration = :sanction_duration,
                parent_notified = :parent_notified,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        if ($stmt->execute($incidentData)) {
            return redirect()->to('admin/scolarite/discipline')->with('success', 'Incident disciplinaire modifié avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la modification');
        }
    }

    public function deleteIncident($id)
    {
        // Vérifier que l'incident existe
        $stmt = $this->pdo->prepare("SELECT id FROM discipline_incidents WHERE id = ?");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            return redirect()->to('admin/scolarite/discipline')->with('error', 'Incident non trouvé');
        }

        // Supprimer l'incident
        $stmt = $this->pdo->prepare("DELETE FROM discipline_incidents WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            return redirect()->to('admin/scolarite/discipline')->with('success', 'Incident disciplinaire supprimé avec succès');
        } else {
            return redirect()->to('admin/scolarite/discipline')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function discipline()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $studentId = $this->request->getGet('student_id');
        $classId = $this->request->getGet('class_id');
        $incidentType = $this->request->getGet('incident_type');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        
        // Récupération des incidents avec filtres
        $incidents = $this->getIncidentsWithFilters($academicYear, $studentId, $classId, $incidentType, $dateFrom, $dateTo);
        
        // Statistiques des incidents
        $incidentStats = $this->getIncidentStats($academicYear, $studentId, $classId, $incidentType, $dateFrom, $dateTo);
        
        // Élèves et classes disponibles
        $students = $this->getActiveStudents($academicYear);
        $classes = $this->getActiveClasses($academicYear);
        
        $data = [
            'title' => 'Gestion de la Discipline',
            'incidents' => $incidents,
            'incidentStats' => $incidentStats,
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'academic_year' => $academicYear,
                'student_id' => $studentId,
                'class_id' => $classId,
                'incident_type' => $incidentType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'current_academic_year' => $academicYear,
            'available_academic_years' => ['2024-2025'],
            'academic_year_dates' => ['start_date' => '2024-09-01', 'end_date' => '2025-06-30']
        ];

        return view('admin/scolarite/discipline', $data);
    }

    public function reports()
    {
        $data = [
            'title' => 'Rapports Scolarité',
            'studentStats' => $this->getStudentStats($this->getCurrentAcademicYear()),
            'absenceStats' => $this->getAbsenceStats($this->getCurrentAcademicYear()),
            'disciplineStats' => $this->getDisciplineStats($this->getCurrentAcademicYear())
        ];

        return view('admin/scolarite/reports', $data);
    }

    // =====================================================
    // MÉTHODES POUR LES NOTIFICATIONS DISCIPLINAIRES
    // =====================================================

    public function sendDisciplineNotification($incident_id = null)
    {
        if ($incident_id) {
            // Notification pour un incident spécifique
            $incident = $this->getIncidentWithStudent($incident_id);
            if (!$incident) {
                return redirect()->back()->with('error', 'Incident non trouvé');
            }
            
            $this->sendMultiChannelDisciplineNotification($incident);
            return redirect()->back()->with('success', 'Notification disciplinaire envoyée avec succès');
        } else {
            // Notifications en masse pour les incidents non notifiés
            $incidents = $this->getUnnotifiedIncidents();
            $sentCount = 0;
            
            foreach ($incidents as $incident) {
                if ($this->sendMultiChannelDisciplineNotification($incident)) {
                    $sentCount++;
                }
            }
            
            return redirect()->back()->with('success', $sentCount . ' notifications disciplinaires envoyées avec succès');
        }
    }

    public function disciplineNotifications()
    {
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $channel = $this->request->getGet('channel');
        
        $notifications = $this->getDisciplineNotifications($academicYear, $dateFrom, $dateTo);
        $notificationStats = $this->getNotificationStats($academicYear, $dateFrom, $dateTo);
        
        $data = $this->prepareViewData([
            'title' => 'Historique des Notifications Disciplinaires',
            'notifications' => $notifications,
            'notificationStats' => $notificationStats,
            'filters' => [
                'academic_year' => $academicYear,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'channel' => $channel
            ]
        ]);

        return view('admin/scolarite/discipline_notifications', $data);
    }

    // =====================================================
    // MÉTHODES PRIVÉES POUR LES DONNÉES
    // =====================================================

    private function getScolariteStats($academicYear = null)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            // Statistiques des élèves
            $studentStats = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_students,
                    COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as active_students,
                    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 END) as new_this_month
                FROM students 
                WHERE academic_year = ?
            ");
            $studentStats->execute([$academicYear]);
            $studentData = $studentStats->fetch(\PDO::FETCH_ASSOC);

            // Statistiques des absences
            $absenceStats = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_absences,
                    COUNT(CASE WHEN justified = 1 THEN 1 END) as justified_absences,
                    COUNT(CASE WHEN justified = 0 THEN 1 END) as unjustified_absences,
                    COUNT(CASE WHEN a.date = CURRENT_DATE() THEN 1 END) as today_absences
                FROM absences a
                JOIN students s ON a.student_id = s.id
                WHERE s.academic_year = ?
            ");
            $absenceStats->execute([$academicYear]);
            $absenceData = $absenceStats->fetch(\PDO::FETCH_ASSOC);

            // Statistiques des incidents disciplinaires
            $incidentStats = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_incidents,
                    COUNT(CASE WHEN incident_type = 'MINOR' THEN 1 END) as minor_incidents,
                    COUNT(CASE WHEN incident_type = 'MAJOR' THEN 1 END) as major_incidents,
                    COUNT(CASE WHEN incident_type = 'CRITICAL' THEN 1 END) as critical_incidents,
                    COUNT(CASE WHEN incident_date = CURRENT_DATE() THEN 1 END) as today_incidents
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                WHERE s.academic_year = ?
            ");
            $incidentStats->execute([$academicYear]);
            $incidentData = $incidentStats->fetch(\PDO::FETCH_ASSOC);

            return [
                'total_students' => $studentData['total_students'] ?? 0,
                'active_students' => $studentData['active_students'] ?? 0,
                'new_this_month' => $studentData['new_this_month'] ?? 0,
                'total_absences' => $absenceData['total_absences'] ?? 0,
                'justified_absences' => $absenceData['justified_absences'] ?? 0,
                'unjustified_absences' => $absenceData['unjustified_absences'] ?? 0,
                'today_absences' => $absenceData['today_absences'] ?? 0,
                'total_incidents' => $incidentData['total_incidents'] ?? 0,
                'minor_incidents' => $incidentData['minor_incidents'] ?? 0,
                'major_incidents' => $incidentData['major_incidents'] ?? 0,
                'critical_incidents' => $incidentData['critical_incidents'] ?? 0,
                'today_incidents' => $incidentData['today_incidents'] ?? 0,
                'attendance_rate' => $this->calculateAttendanceRate($academicYear)
            ];
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des statistiques de scolarité: ' . $e->getMessage());
            return [
                'total_students' => 0,
                'active_students' => 0,
                'new_this_month' => 0,
                'total_absences' => 0,
                'justified_absences' => 0,
                'unjustified_absences' => 0,
                'today_absences' => 0,
                'total_incidents' => 0,
                'minor_incidents' => 0,
                'major_incidents' => 0,
                'critical_incidents' => 0,
                'today_incidents' => 0,
                'attendance_rate' => 0
            ];
        }
    }

    private function getRecentStudents($academicYear = null, $limit = 10)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, c.name as class_name
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
                ORDER BY s.created_at DESC
                LIMIT " . (int)$limit
            );
            $stmt->execute([$academicYear]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des élèves récents: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentAbsences($academicYear = null, $limit = 10)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, s.first_name, s.last_name, s.matricule, c.name as class_name
                FROM absences a
                JOIN students s ON a.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
                ORDER BY a.date DESC, a.created_at DESC
                LIMIT " . (int)$limit
            );
            $stmt->execute([$academicYear]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des absences récentes: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentIncidents($academicYear = null, $limit = 10)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, s.first_name, s.last_name, s.matricule, c.name as class_name
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
                ORDER BY d.incident_date DESC, d.created_at DESC
                LIMIT " . (int)$limit
            );
            $stmt->execute([$academicYear]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des incidents récents: ' . $e->getMessage());
            return [];
        }
    }

    private function getStudentsWithFilters($academicYear, $classId = null, $status = null, $search = null)
    {
        try {
            $sql = "
                SELECT s.*, c.name as class_name
                FROM students s
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($classId) {
                $sql .= " AND s.current_class_id = ?";
                $params[] = $classId;
            }

            if ($status) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }

            if ($search) {
                $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.matricule LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY s.last_name, s.first_name";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des élèves avec filtres: ' . $e->getMessage());
            return [];
        }
    }

    private function getAbsencesWithFilters($academicYear, $studentId = null, $classId = null, $justified = null, $dateFrom = null, $dateTo = null)
    {
        try {
            $sql = "
                SELECT a.*, s.first_name, s.last_name, s.matricule, c.name as class_name
                FROM absences a
                JOIN students s ON a.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($studentId) {
                $sql .= " AND a.student_id = ?";
                $params[] = $studentId;
            }

            if ($classId) {
                $sql .= " AND s.current_class_id = ?";
                $params[] = $classId;
            }

            if ($justified !== null) {
                $sql .= " AND a.justified = ?";
                $params[] = $justified;
            }

            if ($dateFrom) {
                $sql .= " AND a.date >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= " AND a.date <= ?";
                $params[] = $dateTo;
            }

            $sql .= " ORDER BY a.date DESC, a.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des absences avec filtres: ' . $e->getMessage());
            return [];
        }
    }

    private function getIncidentsWithFilters($academicYear, $studentId = null, $classId = null, $incidentType = null, $dateFrom = null, $dateTo = null)
    {
        try {
            $sql = "
                SELECT d.*, s.first_name, s.last_name, s.matricule, c.name as class_name
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($studentId) {
                $sql .= " AND d.student_id = ?";
                $params[] = $studentId;
            }

            if ($classId) {
                $sql .= " AND s.current_class_id = ?";
                $params[] = $classId;
            }

            if ($incidentType) {
                $sql .= " AND d.incident_type = ?";
                $params[] = $incidentType;
            }

            if ($dateFrom) {
                $sql .= " AND d.incident_date >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= " AND d.incident_date <= ?";
                $params[] = $dateTo;
            }

            $sql .= " ORDER BY d.incident_date DESC, d.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des incidents avec filtres: ' . $e->getMessage());
            return [];
        }
    }

    private function getActiveStudents($academicYear = null)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, matricule, first_name, last_name, current_class_id
                FROM students
                WHERE academic_year = ? AND status = 'ACTIVE'
                ORDER BY last_name, first_name
            ");
            $stmt->execute([$academicYear]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des élèves actifs: ' . $e->getMessage());
            return [];
        }
    }

    private function getActiveClasses($academicYear = null)
    {
        $academicYear = $academicYear ?: $this->getCurrentAcademicYear();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, level
                FROM classes
                WHERE academic_year = ? AND is_active = 1
                ORDER BY level, name
            ");
            $stmt->execute([$academicYear]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des classes actives: ' . $e->getMessage());
            return [];
        }
    }

    private function calculateAttendanceRate($academicYear)
    {
        try {
            // Calcul du taux de présence basé sur les absences du mois en cours
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(a.id) as total_absences
                FROM students s
                LEFT JOIN absences a ON s.id = a.student_id 
                    AND MONTH(a.date) = MONTH(CURRENT_DATE())
                    AND YEAR(a.date) = YEAR(CURRENT_DATE())
                WHERE s.academic_year = ? AND s.status = 'ACTIVE'
            ");
            $stmt->execute([$academicYear]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($data['total_students'] > 0) {
                // Calcul basé sur les jours de présence vs absences du mois
                $daysInMonth = date('t'); // Nombre de jours dans le mois
                $totalPossibleDays = $data['total_students'] * $daysInMonth;
                $totalAbsenceDays = $data['total_absences'];
                
                if ($totalPossibleDays > 0) {
                    $attendanceRate = (($totalPossibleDays - $totalAbsenceDays) / $totalPossibleDays) * 100;
                    return round($attendanceRate, 1);
                }
            }
            return 95.0; // Taux par défaut si pas de données
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors du calcul du taux de présence: ' . $e->getMessage());
            return 95.0;
        }
    }

    // =====================================================
    // MÉTHODES POUR LES NOTIFICATIONS DISCIPLINAIRES
    // =====================================================

    private function sendMultiChannelDisciplineNotification($incident)
    {
        $studentName = $incident->first_name . ' ' . $incident->last_name;
        $parentName = $incident->parent_name;
        $parentPhone = $incident->parent_phone;
        $parentEmail = $incident->parent_email;
        
        // Construction du message
        $message = $this->buildDisciplineMessage($incident);
        
        // Envoi via différents canaux
        $smsSent = $this->sendSMS($parentPhone, $message);
        $emailSent = $this->sendEmail($parentEmail, $parentName, $message, $studentName, $incident->incident_type, $incident->sanction);
        $whatsappSent = $this->sendWhatsApp($parentPhone, $message);
        
        // Log de la notification
        $this->logDisciplineNotification($incident->id, $incident->student_id, $parentPhone, $parentEmail, $message, $smsSent, $emailSent, $whatsappSent);
        
        // Mise à jour du statut de notification
        $this->updateIncidentNotificationStatus($incident->id);
        
        return $smsSent || $emailSent || $whatsappSent;
    }

    private function buildDisciplineMessage($incident)
    {
        $studentName = $incident->first_name . ' ' . $incident->last_name;
        $incidentType = $this->getIncidentTypeLabel($incident->incident_type);
        
        $message = "KISSAI SCHOOL - NOTIFICATION DISCIPLINAIRE\n\n";
        $message .= "Cher(e) parent/tuteur,\n\n";
        $message .= "Nous vous informons qu'un incident disciplinaire a été enregistré concernant votre enfant :\n\n";
        $message .= "👤 Élève : " . $studentName . "\n";
        $message .= "📅 Date : " . date('d/m/Y', strtotime($incident->incident_date)) . "\n";
        $message .= "⚠️ Type : " . $incidentType . "\n";
        $message .= "📍 Lieu : " . ($incident->location ?: 'Non spécifié') . "\n";
        $message .= "📝 Description : " . $incident->description . "\n";
        $message .= "⚖️ Sanction : " . $incident->sanction . "\n\n";
        $message .= "Nous vous invitons à prendre contact avec l'établissement pour discuter de cette situation et assurer le bien-être de votre enfant.\n\n";
        $message .= "Merci de votre compréhension.\n\n";
        $message .= "Cordialement,\nL'équipe de KISSAI SCHOOL";
        
        return $message;
    }

    private function getIncidentTypeLabel($type)
    {
        $labels = [
            'MINOR' => 'Mineur',
            'MAJOR' => 'Majeur',
            'CRITICAL' => 'Critique'
        ];
        return $labels[$type] ?? $type;
    }

    private function sendSMS($phone, $message)
    {
        try {
            $smsConfig = $this->configService->getSMSConfigForSending();
            if (!$smsConfig || empty($smsConfig['api_key'])) {
                log_message('warning', 'Configuration SMS non disponible');
                return false;
            }

            // Simulation d'envoi SMS (à remplacer par l'API réelle)
            log_message('info', 'SMS envoyé à ' . $phone . ': ' . substr($message, 0, 100) . '...');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de l\'envoi SMS: ' . $e->getMessage());
            return false;
        }
    }

    private function sendEmail($email, $parentName, $message, $studentName, $incidentType, $sanction)
    {
        try {
            $emailConfig = $this->configService->getEmailConfigForCodeIgniter();
            if (!$emailConfig) {
                log_message('warning', 'Configuration email non disponible');
                return false;
            }

            $emailService = \Config\Services::email();
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($email);
            $emailService->setSubject('KISSAI SCHOOL - Notification Disciplinaire - ' . $studentName);
            $emailService->setMessage($message);
            
            if ($emailService->send()) {
                log_message('info', 'Email disciplinaire envoyé à ' . $email);
                return true;
            } else {
                log_message('error', 'Erreur lors de l\'envoi email: ' . $emailService->printDebugger());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de l\'envoi email: ' . $e->getMessage());
            return false;
        }
    }

    private function sendWhatsApp($phone, $message)
    {
        try {
            $whatsappConfig = $this->configService->getWhatsAppConfigForSending();
            if (!$whatsappConfig || empty($whatsappConfig['api_key'])) {
                log_message('warning', 'Configuration WhatsApp non disponible');
                return false;
            }

            // Simulation d'envoi WhatsApp (à remplacer par l'API réelle)
            log_message('info', 'WhatsApp envoyé à ' . $phone . ': ' . substr($message, 0, 100) . '...');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de l\'envoi WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    private function logDisciplineNotification($incidentId, $studentId, $parentPhone, $parentEmail, $message, $smsSent, $emailSent, $whatsappSent)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO discipline_notifications 
                (incident_id, student_id, parent_phone, parent_email, message, sms_sent, email_sent, whatsapp_sent, sent_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$incidentId, $studentId, $parentPhone, $parentEmail, $message, $smsSent, $emailSent, $whatsappSent]);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de l\'enregistrement de la notification disciplinaire: ' . $e->getMessage());
        }
    }

    private function updateIncidentNotificationStatus($incidentId)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE discipline_incidents 
                SET parent_notified = 1, notification_sent = 1, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$incidentId]);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la mise à jour du statut de notification: ' . $e->getMessage());
        }
    }

    private function getIncidentWithStudent($incidentId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, s.first_name, s.last_name, s.parent_name, s.parent_phone, s.parent_email
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                WHERE d.id = ?
            ");
            $stmt->execute([$incidentId]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération de l\'incident: ' . $e->getMessage());
            return null;
        }
    }

    private function getUnnotifiedIncidents()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, s.first_name, s.last_name, s.parent_name, s.parent_phone, s.parent_email
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                WHERE d.parent_notified = 0 AND d.notification_sent = 0
                ORDER BY d.incident_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des incidents non notifiés: ' . $e->getMessage());
            return [];
        }
    }

    private function getDisciplineNotifications($academicYear, $dateFrom = null, $dateTo = null)
    {
        try {
            // Vérifier d'abord s'il y a des notifications
            $checkSql = "SELECT COUNT(*) as count FROM discipline_notifications dn 
                        JOIN students s ON dn.student_id = s.id 
                        WHERE s.academic_year = ?";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$academicYear]);
            $count = $checkStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($count == 0) {
                return [];
            }
            
            $sql = "
                SELECT dn.*, d.incident_type, d.incident_date, d.description, d.sanction,
                       s.first_name, s.last_name, s.matricule, c.name as class_name
                FROM discipline_notifications dn
                JOIN discipline_incidents d ON dn.incident_id = d.id
                JOIN students s ON dn.student_id = s.id
                LEFT JOIN classes c ON s.current_class_id = c.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($dateFrom) {
                $sql .= " AND dn.sent_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $sql .= " AND dn.sent_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $sql .= " ORDER BY dn.sent_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des notifications disciplinaires: ' . $e->getMessage());
            return [];
        }
    }

    private function getNotificationStats($academicYear, $dateFrom = null, $dateTo = null)
    {
        try {
            // Vérifier d'abord s'il y a des notifications
            $checkSql = "SELECT COUNT(*) as count FROM discipline_notifications dn 
                        JOIN students s ON dn.student_id = s.id 
                        WHERE s.academic_year = ?";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$academicYear]);
            $count = $checkStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($count == 0) {
                return [
                    'total_notifications' => 0,
                    'sms_sent' => 0,
                    'email_sent' => 0,
                    'whatsapp_sent' => 0
                ];
            }
            
            $sql = "
                SELECT 
                    COUNT(*) as total_notifications,
                    COUNT(CASE WHEN sms_sent = 1 THEN 1 END) as sms_sent,
                    COUNT(CASE WHEN email_sent = 1 THEN 1 END) as email_sent,
                    COUNT(CASE WHEN whatsapp_sent = 1 THEN 1 END) as whatsapp_sent
                FROM discipline_notifications dn
                JOIN students s ON dn.student_id = s.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($dateFrom) {
                $sql .= " AND dn.sent_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if ($dateTo) {
                $sql .= " AND dn.sent_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: [
                'total_notifications' => 0,
                'sms_sent' => 0,
                'email_sent' => 0,
                'whatsapp_sent' => 0
            ];
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des statistiques de notifications: ' . $e->getMessage());
            return [
                'total_notifications' => 0,
                'sms_sent' => 0,
                'email_sent' => 0,
                'whatsapp_sent' => 0
            ];
        }
    }

    // Méthodes de statistiques pour les filtres
    private function getStudentStats($academicYear, $classId = null, $status = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM students WHERE academic_year = ?";
            $params = [$academicYear];

            if ($classId) {
                $sql .= " AND current_class_id = ?";
                $params[] = $classId;
            }

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des statistiques élèves: ' . $e->getMessage());
            return 0;
        }
    }

    private function getAbsenceStats($academicYear, $studentId = null, $classId = null, $justified = null, $dateFrom = null, $dateTo = null)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_absences,
                    COUNT(CASE WHEN a.justified = 1 THEN 1 END) as justified_absences,
                    COUNT(CASE WHEN a.justified = 0 THEN 1 END) as unjustified_absences
                FROM absences a
                JOIN students s ON a.student_id = s.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($studentId) {
                $sql .= " AND a.student_id = ?";
                $params[] = $studentId;
            }

            if ($classId) {
                $sql .= " AND s.current_class_id = ?";
                $params[] = $classId;
            }

            if ($justified !== null) {
                $sql .= " AND a.justified = ?";
                $params[] = $justified;
            }

            if ($dateFrom) {
                $sql .= " AND a.date >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= " AND a.date <= ?";
                $params[] = $dateTo;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des statistiques d\'absences: ' . $e->getMessage());
            return [
                'total_absences' => 0,
                'justified_absences' => 0,
                'unjustified_absences' => 0
            ];
        }
    }

    private function getIncidentStats($academicYear, $studentId = null, $classId = null, $incidentType = null, $dateFrom = null, $dateTo = null)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_incidents,
                    COUNT(CASE WHEN d.incident_type = 'MINOR' THEN 1 END) as minor_incidents,
                    COUNT(CASE WHEN d.incident_type = 'MAJOR' THEN 1 END) as major_incidents,
                    COUNT(CASE WHEN d.incident_type = 'CRITICAL' THEN 1 END) as critical_incidents
                FROM discipline_incidents d
                JOIN students s ON d.student_id = s.id
                WHERE s.academic_year = ?
            ";
            $params = [$academicYear];

            if ($studentId) {
                $sql .= " AND d.student_id = ?";
                $params[] = $studentId;
            }

            if ($classId) {
                $sql .= " AND s.current_class_id = ?";
                $params[] = $classId;
            }

            if ($incidentType) {
                $sql .= " AND d.incident_type = ?";
                $params[] = $incidentType;
            }

            if ($dateFrom) {
                $sql .= " AND d.incident_date >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= " AND d.incident_date <= ?";
                $params[] = $dateTo;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            log_message('error', 'Erreur lors de la récupération des statistiques d\'incidents: ' . $e->getMessage());
            return [
                'total_incidents' => 0,
                'minor_incidents' => 0,
                'major_incidents' => 0,
                'critical_incidents' => 0
            ];
        }
    }

    private function getDisciplineStats($academicYear, $studentId = null, $classId = null, $incidentType = null, $dateFrom = null, $dateTo = null)
    {
        return $this->getIncidentStats($academicYear, $studentId, $classId, $incidentType, $dateFrom, $dateTo);
    }

    /**
     * Exporter les données en CSV
     */
    public function exportToCSV()
    {
        $reportType = $this->request->getGet('report_type') ?: 'students';
        $academicYear = $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear();
        
        $filename = 'rapport_scolarite_' . $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        switch ($reportType) {
            case 'students':
                // En-têtes pour les élèves
                fputcsv($output, ['Matricule', 'Nom', 'Prénom', 'Genre', 'Date de Naissance', 'Classe', 'Statut', 'Date d\'Admission']);
                
                // Récupérer les élèves
                $students = $this->pdo->prepare("
                    SELECT s.*, c.name as class_name 
                    FROM students s 
                    LEFT JOIN classes c ON s.current_class_id = c.id 
                    WHERE s.academic_year = ? 
                    ORDER BY s.last_name, s.first_name
                ");
                $students->execute([$academicYear]);
                
                while ($student = $students->fetch(\PDO::FETCH_ASSOC)) {
                    fputcsv($output, [
                        $student['matricule'],
                        $student['last_name'],
                        $student['first_name'],
                        $student['gender'],
                        date('d/m/Y', strtotime($student['date_of_birth'])),
                        $student['class_name'] ?? 'N/A',
                        $student['status'],
                        $student['admission_date'] ? date('d/m/Y', strtotime($student['admission_date'])) : 'N/A'
                    ]);
                }
                break;
                
            case 'absences':
                // En-têtes pour les absences
                fputcsv($output, ['Élève', 'Date', 'Motif', 'Justifiée', 'Durée']);
                
                // Récupérer les absences
                $absences = $this->pdo->prepare("
                    SELECT a.*, s.first_name, s.last_name 
                    FROM absences a 
                    JOIN students s ON a.student_id = s.id 
                    WHERE s.academic_year = ? 
                    ORDER BY a.date DESC
                ");
                $absences->execute([$academicYear]);
                
                while ($absence = $absences->fetch(\PDO::FETCH_ASSOC)) {
                    fputcsv($output, [
                        $absence['first_name'] . ' ' . $absence['last_name'],
                        date('d/m/Y', strtotime($absence['date'])),
                        $absence['reason'] ?? 'N/A',
                        $absence['justified'] ? 'Oui' : 'Non',
                        $absence['duration'] ?? 'N/A'
                    ]);
                }
                break;
                
            case 'discipline':
                // En-têtes pour la discipline
                fputcsv($output, ['Élève', 'Date', 'Type', 'Description', 'Sanction', 'Statut']);
                
                // Récupérer les incidents
                $incidents = $this->pdo->prepare("
                    SELECT d.*, s.first_name, s.last_name 
                    FROM discipline_incidents d 
                    JOIN students s ON d.student_id = s.id 
                    WHERE s.academic_year = ? 
                    ORDER BY d.incident_date DESC
                ");
                $incidents->execute([$academicYear]);
                
                while ($incident = $incidents->fetch(\PDO::FETCH_ASSOC)) {
                    fputcsv($output, [
                        $incident['first_name'] . ' ' . $incident['last_name'],
                        date('d/m/Y', strtotime($incident['incident_date'])),
                        $incident['incident_type'],
                        $incident['description'] ?? 'N/A',
                        $incident['sanction'] ?? 'N/A',
                        $incident['status'] ?? 'N/A'
                    ]);
                }
                break;
        }
        
        fclose($output);
        exit;
    }
}



