<?php

namespace App\Controllers;

use DateTime;
use App\Models\ClassModel;
use App\Models\SubjectModel;
use App\Models\CycleModel;
use App\Models\TimetableModel;
use App\Models\TeacherAssignmentModel;
use App\Models\TeacherModel;
use App\Models\StudentModel;
use App\Services\AcademicYearService;

class Etudes extends BaseController
{
    protected $classModel;
    protected $subjectModel;
    protected $cycleModel;
    protected $timetableModel;
    protected $teacherAssignmentModel;
    protected $teacherModel;
    protected $studentModel;
    protected $academicYearService;

    public function __construct()
    {
        $this->classModel = new ClassModel();
        $this->subjectModel = new SubjectModel();
        $this->cycleModel = new CycleModel();
        $this->timetableModel = new TimetableModel();
        $this->teacherAssignmentModel = new TeacherAssignmentModel();
        $this->teacherModel = new TeacherModel();
        $this->studentModel = new StudentModel();
        $this->academicYearService = new AcademicYearService();
    }

    public function index()
    {
        $data = [
            'title' => 'Module Études',
            'stats' => $this->getEtudesStats(),
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'cycles' => $this->cycleModel->getActiveCycles(),
            'recent_assignments' => $this->teacherAssignmentModel->getRecentAssignments(5)
        ];

        return view('admin/etudes/dashboard', $data);
    }

    // ==================== GESTION DES CYCLES ====================
    public function cycles()
    {
        $data = [
            'title' => 'Gestion des Cycles',
            'cycles' => $this->cycleModel->getCycleStats()
        ];

        return view('admin/etudes/cycles', $data);
    }

    public function createCycle()
    {
        $data = [
            'title' => 'Nouveau Cycle'
        ];

        return view('admin/etudes/create_cycle', $data);
    }

    public function storeCycle()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[20]',
            'description' => 'max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cycleData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1
        ];

        if ($this->cycleModel->insert($cycleData)) {
            return redirect()->to('admin/etudes/cycles')->with('success', 'Cycle créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editCycle($id)
    {
        $cycle = $this->cycleModel->find($id);
        
        if (!$cycle) {
            return redirect()->to('admin/etudes/cycles')->with('error', 'Cycle non trouvé');
        }

        $data = [
            'title' => 'Modifier le Cycle',
            'cycle' => $cycle
        ];

        return view('admin/etudes/edit_cycle', $data);
    }

    public function updateCycle($id)
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[20]',
            'description' => 'max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cycleData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'description' => $this->request->getPost('description')
        ];

        if ($this->cycleModel->update($id, $cycleData)) {
            return redirect()->to('admin/etudes/cycles')->with('success', 'Cycle mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteCycle($id)
    {
        if ($this->cycleModel->delete($id)) {
            return redirect()->to('admin/etudes/cycles')->with('success', 'Cycle supprimé avec succès');
        } else {
            return redirect()->to('admin/etudes/cycles')->with('error', 'Erreur lors de la suppression');
        }
    }

    // ==================== GESTION DES CLASSES ====================
    public function classes()
    {
        $data = [
            'title' => 'Gestion des Classes',
            'classes' => $this->classModel->getAllClassesWithCycles(),
            'cycles' => $this->cycleModel->getActiveCycles(),
            'total_classes' => count($this->classModel->getActiveClasses()),
            'active_classes' => count($this->classModel->getActiveClasses()),
            'total_students' => $this->studentModel->getStudentStats()['total'] ?? 0,
            'total_cycles' => count($this->cycleModel->getActiveCycles())
        ];

        return view('admin/etudes/classes', $data);
    }

    public function createClass()
    {
        $data = [
            'title' => 'Nouvelle Classe',
            'cycles' => $this->cycleModel->getActiveCycles()
        ];

        return view('admin/etudes/create_class', $data);
    }

    public function storeClass()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[20]',
            'cycle_id' => 'required|integer',
            'level' => 'required|integer|greater_than[0]',
            'capacity' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $classData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'cycle_id' => $this->request->getPost('cycle_id'),
            'level' => $this->request->getPost('level'),
            'capacity' => $this->request->getPost('capacity'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1
        ];

        if ($this->classModel->createClass($classData)) {
            return redirect()->to('admin/etudes/classes')->with('success', 'Classe créée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editClass($id)
    {
        $class = $this->classModel->getClassWithCycle($id);
        
        if (!$class) {
            return redirect()->to('admin/etudes/classes')->with('error', 'Classe non trouvée');
        }

        $data = [
            'title' => 'Modifier la Classe',
            'class' => $class,
            'cycles' => $this->cycleModel->getActiveCycles()
        ];

        return view('admin/etudes/edit_class', $data);
    }

    public function updateClass($id)
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[20]',
            'cycle_id' => 'required|integer',
            'level' => 'required|integer|greater_than[0]',
            'capacity' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $classData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'cycle_id' => $this->request->getPost('cycle_id'),
            'level' => $this->request->getPost('level'),
            'capacity' => $this->request->getPost('capacity'),
            'description' => $this->request->getPost('description')
        ];

        if ($this->classModel->updateClass($id, $classData)) {
            return redirect()->to('admin/etudes/classes')->with('success', 'Classe mise à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteClass($id)
    {
        if ($this->classModel->delete($id)) {
            return redirect()->to('admin/etudes/classes')->with('success', 'Classe supprimée avec succès');
        } else {
            return redirect()->to('admin/etudes/classes')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function viewClass($id)
    {
        $class = $this->classModel->getClassWithCycle($id);
        
        if (!$class) {
            return redirect()->to('admin/etudes/classes')->with('error', 'Classe non trouvée');
        }

        $data = [
            'title' => 'Détails de la Classe',
            'class' => $class,
            'students' => $this->studentModel->getStudentsByClass($id),
            'assignments' => $this->teacherAssignmentModel->getClassAssignments($id),
            'timetable' => $this->timetableModel->getClassTimetable($id)
        ];

        return view('admin/etudes/view_class', $data);
    }

    // ==================== GESTION DES MATIÈRES ====================
    public function subjects()
    {
        try {
            // Récupération sécurisée des paramètres GET
            $search = $this->request->getGet('search') ?? '';
            $status = $this->request->getGet('status') ?? '';
            $sort = $this->request->getGet('sort') ?? 'name';
            
            // Récupérer les matières avec statistiques
            if (!empty($search)) {
                $subjects = $this->subjectModel->searchSubjects($search);
            } else {
                $subjects = $this->subjectModel->getSubjectsWithStats();
            }
            
            // Filtrer par statut si spécifié
            if (!empty($status) && $status !== '') {
                $subjects = array_filter($subjects, function($subject) use ($status) {
                    return $subject['is_active'] == $status;
                });
            }
            
            // Trier les résultats
            usort($subjects, function($a, $b) use ($sort) {
                switch($sort) {
                    case 'code':
                        return strcmp($a['code'], $b['code']);
                    case 'coefficient':
                        return $a['coefficient'] <=> $b['coefficient'];
                    case 'created_at':
                        return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                    default:
                        return strcmp($a['name'], $b['name']);
                }
            });
            
            // Calculer les statistiques de manière sécurisée
            $total_subjects = count($subjects);
            $active_subjects = count(array_filter($subjects, function($s) { 
                return $s['is_active']; 
            }));
            
            // Statistiques des assignations
            try {
                $assignmentStats = $this->teacherAssignmentModel->getAssignmentStats();
                $total_assignments = $assignmentStats['total_assignments'] ?? 0;
            } catch (Exception $e) {
                $total_assignments = 0;
            }
            
            // Statistiques des emplois du temps
            try {
                $timetableStats = $this->timetableModel->getTimetableStats();
                $total_timetables = $timetableStats['total_timetables'] ?? 0;
            } catch (Exception $e) {
                $total_timetables = 0;
            }
            
            $data = [
                'title' => 'Gestion des Matières',
                'subjects' => $subjects,
                'total_subjects' => $total_subjects,
                'active_subjects' => $active_subjects,
                'total_assignments' => $total_assignments,
                'total_timetables' => $total_timetables,
                'search' => $search,
                'status' => $status,
                'sort' => $sort
            ];

            return view('admin/etudes/subjects', $data);
        } catch (Exception $e) {
            // En cas d'erreur, afficher une page d'erreur ou rediriger
            log_message('error', 'Erreur dans la méthode subjects: ' . $e->getMessage());
            return redirect()->to('admin/etudes')->with('error', 'Erreur lors du chargement des matières');
        }
    }

    public function createSubject()
    {
        $data = [
            'title' => 'Nouvelle Matière'
        ];

        return view('admin/etudes/create_subject', $data);
    }

    public function storeSubject()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|min_length[2]|max_length[20]',
            'coefficient' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $subjectData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'coefficient' => $this->request->getPost('coefficient'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1
        ];

        if ($this->subjectModel->insert($subjectData)) {
            return redirect()->to('admin/etudes/subjects')->with('success', 'Matière créée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editSubject($id)
    {
        $subject = $this->subjectModel->find($id);
        
        if (!$subject) {
            return redirect()->to('admin/etudes/subjects')->with('error', 'Matière non trouvée');
        }

        $data = [
            'title' => 'Modifier la Matière',
            'subject' => $subject
        ];

        return view('admin/etudes/edit_subject', $data);
    }

    public function viewSubject($id)
    {
        $subject = $this->subjectModel->find($id);
        
        if (!$subject) {
            return redirect()->to('admin/etudes/subjects')->with('error', 'Matière non trouvée');
        }

        $data = [
            'title' => 'Détails de la Matière',
            'subject' => $subject,
            'assignments' => [],
            'timetables' => [],
            'classes' => []
        ];

        return view('admin/etudes/view_subject', $data);
    }

    public function updateSubject($id)
    {
        // Log de début de méthode
        log_message('info', 'Début updateSubject pour ID: ' . $id);
        
        try {
            // Récupérer la matière existante
            $existingSubject = $this->subjectModel->find($id);
            if (!$existingSubject) {
                log_message('error', 'Matière non trouvée pour ID: ' . $id);
                return redirect()->to('/admin/etudes/subjects')->with('error', 'Matière non trouvée');
            }
            
            log_message('info', 'Matière trouvée: ' . json_encode($existingSubject));

            // Règles de validation avec gestion de l'unicité du code
            $rules = [
                'name' => 'required|min_length[2]|max_length[100]',
                'code' => 'required|min_length[2]|max_length[20]|is_unique[subjects.code,id,' . $id . ']',
                'coefficient' => 'required|numeric|greater_than[0]'
            ];

            log_message('info', 'Règles de validation: ' . json_encode($rules));

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                log_message('error', 'Erreurs de validation: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('errors', $errors);
            }

            // Préparer les données de mise à jour
            $subjectData = [
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code'),
                'coefficient' => $this->request->getPost('coefficient'),
                'description' => $this->request->getPost('description'),
                'hours_per_week' => $this->request->getPost('hours_per_week') ?? null,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0
            ];
            
            log_message('info', 'Données à mettre à jour: ' . json_encode($subjectData));

            log_message('info', 'Tentative de mise à jour...');
            
            // Tentative de mise à jour avec gestion d'erreur robuste
            $updateResult = $this->subjectModel->update($id, $subjectData);
            
            if ($updateResult !== false) {
                log_message('info', 'Mise à jour réussie pour ID: ' . $id);
                return redirect()->to('/admin/etudes/subjects')->with('success', 'Matière mise à jour avec succès');
            } else {
                log_message('error', 'Échec de la mise à jour pour ID: ' . $id . ' - Retour false');
                return redirect()->to('/admin/etudes/subjects')->with('error', 'Erreur lors de la mise à jour de la base de données');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception lors de la mise à jour de la matière: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // Redirection vers la liste des matières en cas d'erreur
            return redirect()->to('/admin/etudes/subjects')->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Error lors de la mise à jour de la matière: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // Redirection vers la liste des matières en cas d'erreur
            return redirect()->to('/admin/etudes/subjects')->with('error', 'Erreur système lors de la mise à jour');
        }
    }

    public function deleteSubject($id)
    {
        // Vérifier si la matière existe
        $subject = $this->subjectModel->find($id);
        if (!$subject) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Matière non trouvée'
            ]);
        }

        // Vérifier si la matière peut être supprimée (pas d'assignations, etc.)
        // Pour l'instant, on supprime directement
        
        if ($this->subjectModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Matière supprimée avec succès'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ]);
        }
    }

    // ==================== GESTION DES EMPLOIS DU TEMPS ====================
    public function timetable()
    {
        $timetables = $this->timetableModel->getActiveTimetables();
        $stats = $this->timetableModel->getTimetableStats();
        
        $data = [
            'title' => 'Emploi du Temps',
            'timetables' => $timetables,
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers(),
            'total_timetables' => count($timetables),
            'classes_covered' => count(array_unique(array_column($timetables, 'class_id'))),
            'teachers_involved' => count(array_unique(array_filter(array_column($timetables, 'teacher_id')))),
            'subjects_covered' => count(array_unique(array_column($timetables, 'subject_id')))
        ];

        return view('admin/etudes/timetable', $data);
    }

    public function createTimetable()
    {
        $data = [
            'title' => 'Nouveau Cours',
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers()
        ];

        return view('admin/etudes/create_timetable', $data);
    }

    public function storeTimetable()
    {
        $rules = [
            'class_id' => 'required|integer',
            'day_of_week' => 'required|integer|greater_than[0]|less_than[7]',
            'start_time' => 'required',
            'end_time' => 'required',
            'subject_id' => 'required|integer',
            'teacher_id' => 'integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $timetableData = [
            'class_id' => $this->request->getPost('class_id'),
            'day_of_week' => $this->request->getPost('day_of_week'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'subject_id' => $this->request->getPost('subject_id'),
            'teacher_id' => $this->request->getPost('teacher_id') ?: null,
            'room' => $this->request->getPost('room'),
            'is_active' => 1
        ];

        // Vérifier les conflits
        if ($this->timetableModel->checkConflicts(
            $timetableData['class_id'], 
            $timetableData['day_of_week'], 
            $timetableData['start_time'], 
            $timetableData['end_time']
        )) {
            return redirect()->back()->withInput()->with('error', 'Conflit d\'emploi du temps détecté');
        }

        if ($timetableData['teacher_id'] && $this->timetableModel->checkTeacherConflicts(
            $timetableData['teacher_id'], 
            $timetableData['day_of_week'], 
            $timetableData['start_time'], 
            $timetableData['end_time']
        )) {
            return redirect()->back()->withInput()->with('error', 'L\'enseignant a déjà un cours à cette heure');
        }

        if ($this->timetableModel->insert($timetableData)) {
            return redirect()->to('admin/etudes/timetable')->with('success', 'Cours ajouté avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'ajout');
        }
    }

    public function editTimetable($id)
    {
        $timetable = $this->timetableModel->find($id);
        
        if (!$timetable) {
            return redirect()->to('admin/etudes/timetable')->with('error', 'Cours non trouvé');
        }

        $data = [
            'title' => 'Modifier le Cours',
            'timetable' => $timetable,
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers()
        ];

        return view('admin/etudes/edit_timetable', $data);
    }

    public function updateTimetable($id)
    {
        $rules = [
            'class_id' => 'required|integer',
            'day_of_week' => 'required|integer|greater_than[0]|less_than[7]',
            'start_time' => 'required',
            'end_time' => 'required',
            'subject_id' => 'required|integer',
            'teacher_id' => 'integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $timetableData = [
            'class_id' => $this->request->getPost('class_id'),
            'day_of_week' => $this->request->getPost('day_of_week'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'subject_id' => $this->request->getPost('subject_id'),
            'teacher_id' => $this->request->getPost('teacher_id') ?: null,
            'room' => $this->request->getPost('room')
        ];

        // Vérifier les conflits
        if ($this->timetableModel->checkConflicts(
            $timetableData['class_id'], 
            $timetableData['day_of_week'], 
            $timetableData['start_time'], 
            $timetableData['end_time'],
            $id
        )) {
            return redirect()->back()->withInput()->with('error', 'Conflit d\'emploi du temps détecté');
        }

        if ($timetableData['teacher_id'] && $this->timetableModel->checkTeacherConflicts(
            $timetableData['teacher_id'], 
            $timetableData['day_of_week'], 
            $timetableData['start_time'], 
            $timetableData['end_time'],
            $id
        )) {
            return redirect()->back()->withInput()->with('error', 'L\'enseignant a déjà un cours à cette heure');
        }

        if ($this->timetableModel->update($id, $timetableData)) {
            return redirect()->to('admin/etudes/timetable')->with('success', 'Cours mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteTimetable($id)
    {
        if ($this->timetableModel->delete($id)) {
            return redirect()->to('admin/etudes/timetable')->with('success', 'Cours supprimé avec succès');
        } else {
            return redirect()->to('admin/etudes/timetable')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function viewClassTimetable($classId)
    {
        $class = $this->classModel->getClassWithCycle($classId);
        
        if (!$class) {
            return redirect()->to('admin/etudes/timetable')->with('error', 'Classe non trouvée');
        }

        $data = [
            'title' => 'Emploi du Temps - ' . $class['name'],
            'class' => $class,
            'timetable' => $this->timetableModel->getClassTimetable($classId)
        ];

        return view('admin/etudes/view_class_timetable', $data);
    }

    /**
     * Page d'impression d'emploi du temps
     */
    public function printTimetable()
    {
        $data = [
            'title' => 'Impression Emploi du Temps',
            'classes' => $this->classModel->getActiveClasses(),
            'teachers' => $this->teacherModel->getActiveTeachers(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'current_academic_year' => $this->getCurrentAcademicYear()
        ];

        return view('admin/etudes/print_timetable', $data);
    }

    /**
     * Génération de l'emploi du temps pour impression
     */
    public function generatePrintTimetable()
    {
        $filters = [
            'class_id' => $this->request->getPost('class_id'),
            'teacher_id' => $this->request->getPost('teacher_id'),
            'subject_id' => $this->request->getPost('subject_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'academic_year' => $this->request->getPost('academic_year'),
            'print_format' => $this->request->getPost('print_format') ?: 'pdf'
        ];

        // Validation des filtres
        $rules = [
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'academic_year' => 'required|min_length[9]|max_length[9]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Récupération des données filtrées
        $timetableData = $this->timetableModel->getFilteredTimetables($filters);
        
        if (empty($timetableData)) {
            return redirect()->back()->withInput()->with('error', 'Aucun emploi du temps trouvé avec les critères sélectionnés');
        }

        $data = [
            'title' => 'Emploi du Temps - Impression',
            'timetables' => $timetableData,
            'filters' => $filters,
            'summary' => $this->generateTimetableSummary($timetableData)
        ];

        // Génération selon le format demandé
        if ($filters['print_format'] === 'pdf') {
            return $this->generatePDFTimetable($data);
        } else {
            return view('admin/etudes/print_timetable_result', $data);
        }
    }

    /**
     * Génération du PDF
     */
    private function generatePDFTimetable($data)
    {
        // Pour l'instant, on retourne la vue HTML
        // TODO: Implémenter la génération PDF avec TCPDF ou Dompdf
        return view('admin/etudes/print_timetable_pdf', $data);
    }

    /**
     * Génération du résumé des emplois du temps
     */
    private function generateTimetableSummary($timetables)
    {
        $summary = [
            'total_sessions' => count($timetables),
            'classes_count' => count(array_unique(array_column($timetables, 'class_id'))),
            'teachers_count' => count(array_unique(array_filter(array_column($timetables, 'teacher_id')))),
            'subjects_count' => count(array_unique(array_column($timetables, 'subject_id'))),
            'days_covered' => count(array_unique(array_column($timetables, 'day_of_week'))),
            'total_hours' => 0
        ];

        // Calcul du total des heures
        foreach ($timetables as $timetable) {
            $start = new DateTime($timetable['start_time']);
            $end = new DateTime($timetable['end_time']);
            $duration = $start->diff($end);
            $summary['total_hours'] += $duration->h + ($duration->i / 60);
        }

        return $summary;
    }

    /**
     * Obtenir l'année académique actuelle
     */
    private function getCurrentAcademicYear()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Si on est entre septembre et juin, année académique = année courante - année suivante
        if ($currentMonth >= 9) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    // ==================== GESTION DES ASSIGNATIONS ====================
    public function assignments()
    {
        // Récupérer les paramètres de filtrage depuis l'URL
        $teacherId = $this->request->getGet('teacher_id');
        $classId = $this->request->getGet('class_id');
        $subjectId = $this->request->getGet('subject_id');
        $cycleId = $this->request->getGet('cycle_id');
        $academicYear = $this->request->getGet('academic_year');
        
        // Récupérer les assignations avec filtrage optionnel
        $assignments = $this->teacherAssignmentModel->getActiveAssignments();
        
        // Appliquer les filtres si spécifiés
        if ($teacherId || $classId || $subjectId || $cycleId || $academicYear) {
            $assignments = array_filter($assignments, function($assignment) use ($teacherId, $classId, $subjectId, $cycleId, $academicYear) {
                $matches = true;
                
                if ($teacherId && $assignment['teacher_id'] != $teacherId) {
                    $matches = false;
                }
                
                if ($classId && $assignment['class_id'] != $classId) {
                    $matches = false;
                }
                
                if ($subjectId && $assignment['subject_id'] != $subjectId) {
                    $matches = false;
                }
                
                if ($cycleId) {
                    // Récupérer le cycle de la classe
                    $class = $this->classModel->find($assignment['class_id']);
                    if (!$class || $class['cycle_id'] != $cycleId) {
                        $matches = false;
                    }
                }
                
                if ($academicYear && $assignment['academic_year'] != $academicYear) {
                    $matches = false;
                }
                
                return $matches;
            });
        }
        
        $stats = $this->teacherAssignmentModel->getAssignmentStats();
        
        $data = [
            'title' => 'Assignations Enseignants',
            'assignments' => $assignments,
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers(),
            'cycles' => $this->cycleModel->getActiveCycles(),
            'total_assignments' => count($assignments),
            'teachers_assigned' => count(array_unique(array_column($assignments, 'teacher_id'))),
            'classes_covered' => count(array_unique(array_column($assignments, 'class_id'))),
            'subjects_covered' => count(array_unique(array_column($assignments, 'subject_id'))),
            // Paramètres de filtrage pour pré-sélectionner les filtres
            'filter_teacher_id' => $teacherId,
            'filter_class_id' => $classId,
            'filter_subject_id' => $subjectId,
            'filter_cycle_id' => $cycleId,
            'filter_academic_year' => $academicYear,
            // Années académiques disponibles
            'available_academic_years' => $this->academicYearService->getAvailableAcademicYears()
        ];

        return view('admin/etudes/assignments', $data);
    }

    public function createAssignment()
    {
        $data = [
            'title' => 'Nouvelle Assignation',
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers(),
            'current_academic_year' => $this->academicYearService->getCurrentAcademicYear()
        ];

        return view('admin/etudes/create_assignment', $data);
    }

    public function storeAssignment()
    {
        $rules = [
            'teacher_id' => 'required|integer',
            'class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'academic_year' => 'required|min_length[9]|max_length[9]',
            'is_principal' => 'integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assignmentData = [
            'teacher_id' => $this->request->getPost('teacher_id'),
            'class_id' => $this->request->getPost('class_id'),
            'subject_id' => $this->request->getPost('subject_id'),
            'academic_year' => $this->request->getPost('academic_year'),
            'is_principal' => $this->request->getPost('is_principal') ? 1 : 0,
            'is_active' => 1
        ];

        // Vérifier si l'assignation existe déjà
        if ($this->teacherAssignmentModel->isTeacherAssigned(
            $assignmentData['teacher_id'],
            $assignmentData['class_id'],
            $assignmentData['subject_id'],
            $assignmentData['academic_year']
        )) {
            return redirect()->back()->withInput()->with('error', 'Cette assignation existe déjà');
        }

        if ($this->teacherAssignmentModel->insert($assignmentData)) {
            return redirect()->to('admin/etudes/assignments')->with('success', 'Assignation créée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function viewAssignment($id)
    {
        $assignment = $this->teacherAssignmentModel->getAssignmentWithDetails($id);
        
        if (!$assignment) {
            return redirect()->to('admin/etudes/assignments')->with('error', 'Assignation non trouvée');
        }

        $data = [
            'title' => 'Détails de l\'Assignation',
            'assignment' => $assignment
        ];

        return view('admin/etudes/view_assignment', $data);
    }

    public function editAssignment($id)
    {
        $assignment = $this->teacherAssignmentModel->find($id);
        
        if (!$assignment) {
            return redirect()->to('admin/etudes/assignments')->with('error', 'Assignation non trouvée');
        }

        $data = [
            'title' => 'Modifier l\'Assignation',
            'assignment' => $assignment,
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers()
        ];

        return view('admin/etudes/edit_assignment', $data);
    }

    public function updateAssignment($id)
    {
        $rules = [
            'teacher_id' => 'required|integer',
            'class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'academic_year' => 'required|min_length[9]|max_length[9]',
            'is_principal' => 'integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assignmentData = [
            'teacher_id' => $this->request->getPost('teacher_id'),
            'class_id' => $this->request->getPost('class_id'),
            'subject_id' => $this->request->getPost('subject_id'),
            'academic_year' => $this->request->getPost('academic_year'),
            'is_principal' => $this->request->getPost('is_principal') ? 1 : 0
        ];

        // Vérifier si l'assignation existe déjà
        if ($this->teacherAssignmentModel->isTeacherAssigned(
            $assignmentData['teacher_id'],
            $assignmentData['class_id'],
            $assignmentData['subject_id'],
            $assignmentData['academic_year'],
            $id
        )) {
            return redirect()->back()->withInput()->with('error', 'Cette assignation existe déjà');
        }

        if ($this->teacherAssignmentModel->update($id, $assignmentData)) {
            return redirect()->to('admin/etudes/assignments')->with('success', 'Assignation mise à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteAssignment($id)
    {
        if ($this->teacherAssignmentModel->delete($id)) {
            return redirect()->to('admin/etudes/assignments')->with('success', 'Assignation supprimée avec succès');
        } else {
            return redirect()->to('admin/etudes/assignments')->with('error', 'Erreur lors de la suppression');
        }
    }

    // ==================== MÉTHODES PRIVÉES ====================
    private function getEtudesStats()
    {
        return [
            'totalClasses' => count($this->classModel->getActiveClasses()),
            'totalSubjects' => count($this->subjectModel->getActiveSubjects()),
            'totalCycles' => count($this->cycleModel->getActiveCycles()),
            'totalTeachers' => count($this->teacherModel->getActiveTeachers()),
            'classesByLevel' => [],
            'cyclesStats' => $this->cycleModel->getActiveCycles()
        ];
    }

    // ==================== RAPPORTS ====================
    public function reports()
    {
        $data = [
            'title' => 'Rapports Études',
            'cycles' => $this->cycleModel->getActiveCycles(),
            'classes' => $this->classModel->getActiveClasses(),
            'subjects' => $this->subjectModel->getActiveSubjects(),
            'teachers' => $this->teacherModel->getActiveTeachers(),
            'stats' => $this->getEtudesStats()
        ];

        return view('admin/etudes/reports', $data);
    }

    public function generateReport()
    {
        $reportType = $this->request->getPost('report_type') ?: $this->request->getGet('report_type');
        $cycleId = $this->request->getPost('cycle_id') ?: $this->request->getGet('cycle_id');
        $classId = $this->request->getPost('class_id') ?: $this->request->getGet('class_id');
        $subjectId = $this->request->getPost('subject_id') ?: $this->request->getGet('subject_id');
        $teacherId = $this->request->getPost('teacher_id') ?: $this->request->getGet('teacher_id');
        $academicYear = $this->academicYearService->getAcademicYearFromRequest($this->request);
        $format = $this->request->getPost('format') ?: $this->request->getGet('format') ?: 'html';

        $data = [
            'title' => 'Rapport Études - ' . ucfirst($reportType),
            'reportType' => $reportType,
            'cycleId' => $cycleId,
            'classId' => $classId,
            'subjectId' => $subjectId,
            'teacherId' => $teacherId,
            'academicYear' => $academicYear,
            'format' => $format,
            'reportData' => $this->getReportData($reportType, $cycleId, $classId, $subjectId, $teacherId, $academicYear),
            'filters' => [
                'cycle' => $cycleId ? $this->cycleModel->find($cycleId) : null,
                'class' => $classId ? $this->classModel->find($classId) : null,
                'subject' => $subjectId ? $this->subjectModel->find($subjectId) : null,
                'teacher' => $teacherId ? $this->teacherModel->find($teacherId) : null
            ]
        ];

        if ($format === 'pdf') {
            return view('admin/etudes/report_pdf', $data);
        } else {
            return view('admin/etudes/report_result', $data);
        }
    }

    public function exportReport($format)
    {
        $reportType = $this->request->getGet('report_type');
        $cycleId = $this->request->getGet('cycle_id');
        $classId = $this->request->getGet('class_id');
        $subjectId = $this->request->getGet('subject_id');
        $teacherId = $this->request->getGet('teacher_id');
        $academicYear = $this->academicYearService->getAcademicYearFromRequest($this->request);

        $reportData = $this->getReportData($reportType, $cycleId, $classId, $subjectId, $teacherId, $academicYear);

        if ($format === 'csv') {
            return $this->exportToCSV($reportData, $reportType);
        } elseif ($format === 'excel') {
            return $this->exportToExcel($reportData, $reportType);
        } else {
            return redirect()->back()->with('error', 'Format d\'export non supporté');
        }
    }

    private function getReportData($reportType, $cycleId = null, $classId = null, $subjectId = null, $teacherId = null, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        switch ($reportType) {
            case 'cycles':
                return $this->getCyclesReport($academicYear);
            case 'classes':
                return $this->getClassesReport($cycleId, $academicYear);
            case 'subjects':
                return $this->getSubjectsReport($academicYear);
            case 'assignments':
                return $this->getAssignmentsReport($classId, $subjectId, $teacherId, $academicYear);
            case 'timetable':
                return $this->getTimetableReport($classId, $teacherId, $academicYear);
            case 'summary':
            default:
                return $this->getSummaryReport($academicYear);
        }
    }

    private function getCyclesReport($academicYear)
    {
        $cycles = $this->cycleModel->getActiveCycles();
        $reportData = [];

        foreach ($cycles as $cycle) {
            $classes = $this->classModel->where('cycle_id', $cycle['id'])->findAll();
            $totalStudents = 0;
            $totalTeachers = 0;

            foreach ($classes as $class) {
                $students = $this->studentModel->where('current_class_id', $class['id'])->where('academic_year', $academicYear)->findAll();
                $totalStudents += count($students);
                
                $assignments = $this->teacherAssignmentModel->getClassAssignments($class['id'], $academicYear);
                $totalTeachers += count(array_unique(array_column($assignments, 'teacher_id')));
            }

            $reportData[] = [
                'cycle' => $cycle,
                'classes_count' => count($classes),
                'students_count' => $totalStudents,
                'teachers_count' => $totalTeachers
            ];
        }

        return $reportData;
    }

    private function getClassesReport($cycleId = null, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        $builder = $this->classModel->select('classes.*, cycles.name as cycle_name')
                                   ->join('cycles', 'cycles.id = classes.cycle_id', 'left');

        if ($cycleId) {
            $builder->where('classes.cycle_id', $cycleId);
        }

        $classes = $builder->where('classes.is_active', 1)->findAll();
        $reportData = [];

        foreach ($classes as $class) {
            $students = $this->studentModel->where('current_class_id', $class['id'])->where('academic_year', $academicYear)->findAll();
            $assignments = $this->teacherAssignmentModel->getClassAssignments($class['id'], $academicYear);
            $timetables = $this->timetableModel->getClassTimetable($class['id']);

            $reportData[] = [
                'class' => $class,
                'students_count' => count($students),
                'teachers_count' => count(array_unique(array_column($assignments, 'teacher_id'))),
                'subjects_count' => count(array_unique(array_column($assignments, 'subject_id'))),
                'timetable_hours' => count($timetables)
            ];
        }

        return $reportData;
    }

    private function getSubjectsReport($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        $subjects = $this->subjectModel->getActiveSubjects();
        $reportData = [];

        foreach ($subjects as $subject) {
            $assignments = $this->teacherAssignmentModel->getAssignmentsBySubject($subject['id'], $academicYear);
            $classes = array_unique(array_column($assignments, 'class_id'));
            $teachers = array_unique(array_column($assignments, 'teacher_id'));

            $reportData[] = [
                'subject' => $subject,
                'classes_count' => count($classes),
                'teachers_count' => count($teachers),
                'assignments_count' => count($assignments)
            ];
        }

        return $reportData;
    }

    private function getAssignmentsReport($classId = null, $subjectId = null, $teacherId = null, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        $builder = $this->teacherAssignmentModel->select('teacher_assignments.*, teachers.first_name, teachers.last_name, classes.name as class_name, subjects.name as subject_name')
                                               ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                                               ->join('classes', 'classes.id = teacher_assignments.class_id')
                                               ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                                               ->where('teacher_assignments.is_active', 1);

        if ($classId) {
            $builder->where('teacher_assignments.class_id', $classId);
        }
        if ($subjectId) {
            $builder->where('teacher_assignments.subject_id', $subjectId);
        }
        if ($teacherId) {
            $builder->where('teacher_assignments.teacher_id', $teacherId);
        }
        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->orderBy('classes.name', 'ASC')
                      ->orderBy('subjects.name', 'ASC')
                      ->findAll();
    }

    private function getTimetableReport($classId = null, $teacherId = null, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        $builder = $this->timetableModel->select('timetables.*, classes.name as class_name, subjects.name as subject_name, teachers.first_name, teachers.last_name')
                                       ->join('classes', 'classes.id = timetables.class_id')
                                       ->join('subjects', 'subjects.id = timetables.subject_id')
                                       ->join('teachers', 'teachers.id = timetables.teacher_id', 'left')
                                       ->where('timetables.is_active', 1);

        if ($classId) {
            $builder->where('timetables.class_id', $classId);
        }
        if ($teacherId) {
            $builder->where('timetables.teacher_id', $teacherId);
        }

        return $builder->orderBy('timetables.day_of_week', 'ASC')
                      ->orderBy('timetables.start_time', 'ASC')
                      ->findAll();
    }

    private function getSummaryReport($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear();
        }
        
        $stats = $this->getEtudesStats();
        $cycles = $this->getCyclesReport($academicYear);
        $classes = $this->getClassesReport(null, $academicYear);
        $subjects = $this->getSubjectsReport($academicYear);
        $assignments = $this->getAssignmentsReport(null, null, null, $academicYear);

        return [
            'stats' => $stats,
            'cycles' => $cycles,
            'classes' => $classes,
            'subjects' => $subjects,
            'assignments' => $assignments
        ];
    }

    private function exportToCSV($data, $reportType)
    {
        $filename = 'rapport_etudes_' . $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // En-têtes selon le type de rapport
        switch ($reportType) {
            case 'summary':
                // Export des statistiques générales
                fputcsv($output, ['Statistique', 'Valeur']);
                fputcsv($output, ['Total Cycles', $data['stats']['totalCycles']]);
                fputcsv($output, ['Total Classes', $data['stats']['totalClasses']]);
                fputcsv($output, ['Total Matières', $data['stats']['totalSubjects']]);
                fputcsv($output, ['Total Enseignants', $data['stats']['totalTeachers']]);
                fputcsv($output, ['Total Assignations', count($data['assignments'])]);
                fputcsv($output, ['']);
                
                // Export des cycles
                fputcsv($output, ['CYCLES']);
                fputcsv($output, ['Cycle', 'Classes', 'Élèves', 'Enseignants']);
                foreach ($data['cycles'] as $row) {
                    fputcsv($output, [
                        $row['cycle']['name'],
                        $row['classes_count'],
                        $row['students_count'],
                        $row['teachers_count']
                    ]);
                }
                fputcsv($output, ['']);
                
                // Export des classes
                fputcsv($output, ['CLASSES']);
                fputcsv($output, ['Classe', 'Cycle', 'Élèves', 'Enseignants', 'Matières', 'Heures EDT']);
                foreach ($data['classes'] as $row) {
                    fputcsv($output, [
                        $row['class']['name'],
                        $row['class']['cycle_name'] ?? 'N/A',
                        $row['students_count'],
                        $row['teachers_count'],
                        $row['subjects_count'],
                        $row['timetable_hours']
                    ]);
                }
                break;
            case 'cycles':
                fputcsv($output, ['Cycle', 'Classes', 'Élèves', 'Enseignants']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['cycle']['name'],
                        $row['classes_count'],
                        $row['students_count'],
                        $row['teachers_count']
                    ]);
                }
                break;
            case 'classes':
                fputcsv($output, ['Classe', 'Cycle', 'Élèves', 'Enseignants', 'Matières', 'Heures EDT']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['class']['name'],
                        $row['class']['cycle_name'],
                        $row['students_count'],
                        $row['teachers_count'],
                        $row['subjects_count'],
                        $row['timetable_hours']
                    ]);
                }
                break;
            case 'assignments':
                fputcsv($output, ['Enseignant', 'Classe', 'Matière', 'Principal', 'Année']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['first_name'] . ' ' . $row['last_name'],
                        $row['class_name'],
                        $row['subject_name'],
                        $row['is_principal'] ? 'Oui' : 'Non',
                        $row['academic_year']
                    ]);
                }
                break;
        }
        
        fclose($output);
        exit;
    }

    private function exportToExcel($data, $reportType)
    {
        // Pour l'instant, on utilise CSV comme Excel
        return $this->exportToCSV($data, $reportType);
    }
}




