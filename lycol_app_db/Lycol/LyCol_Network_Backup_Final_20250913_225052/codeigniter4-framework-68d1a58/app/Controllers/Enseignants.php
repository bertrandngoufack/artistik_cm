<?php

namespace App\Controllers;

use App\Models\TeacherModel;
use App\Models\UserModel;
use App\Models\ClassModel;
use App\Models\SubjectModel;
use App\Models\AuditLogModel;

class Enseignants extends BaseController
{
    protected $teacherModel;
    protected $userModel;
    protected $classModel;
    protected $subjectModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->teacherModel = new TeacherModel();
        $this->userModel = new UserModel();
        $this->classModel = new ClassModel();
        $this->subjectModel = new SubjectModel();
        $this->auditLogModel = new AuditLogModel();
    }

    /**
     * Page d'accueil du module enseignants
     */
    public function index()
    {
        $data = [
            'title' => 'Gestion des Enseignants - KISSAI SCHOOL',
            'total_teachers' => $this->teacherModel->where('is_active', 1)->countAllResults(),
            'active_teachers' => $this->teacherModel->getActiveTeachers(),
            'recent_teachers' => $this->teacherModel->orderBy('created_at', 'DESC')
                                                   ->limit(5)
                                                   ->find(),
            'specializations' => $this->getSpecializations()
        ];

        return view('admin/enseignants/index', $data);
    }

    /**
     * Liste des enseignants avec pagination
     */
    public function list()
    {
        $search = $this->request->getGet('search');
        $specialization = $this->request->getGet('specialization');
        $status = $this->request->getGet('status');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10; // Nombre d'enseignants par page

        $query = $this->teacherModel->select('teachers.*, users.username, roles.name as role_name')
                                   ->join('users', 'users.id = teachers.user_id', 'left')
                                   ->join('roles', 'roles.id = users.role_id', 'left');

        if ($search) {
            $query->like('teachers.first_name', $search)
                  ->orLike('teachers.last_name', $search)
                  ->orLike('teachers.email', $search);
        }

        if ($specialization) {
            $query->where('teachers.specialization', $specialization);
        }

        if ($status !== null) {
            $query->where('teachers.is_active', $status);
        }

        // Compter le total pour la pagination
        $total = $query->countAllResults(false);
        
        // Récupérer les données paginées
        $offset = ($page - 1) * $perPage;
        $teachers = $query->limit($perPage, $offset)->findAll();

        // Calculer les informations de pagination
        $totalPages = ceil($total / $perPage);
        $pager = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null
        ];

        $data = [
            'title' => 'Liste des Enseignants - KISSAI SCHOOL',
            'teachers' => $teachers,
            'specializations' => $this->getSpecializations(),
            'pager' => $pager,
            'filters' => [
                'search' => $search,
                'specialization' => $specialization,
                'status' => $status
            ]
        ];

        return view('admin/enseignants/list', $data);
    }

    /**
     * Créer un nouvel enseignant
     */
    public function create()
    {
        $data = [
            'title' => 'Nouvel Enseignant - KISSAI SCHOOL',
            'specializations' => $this->getSpecializations(),
            'qualifications' => $this->getQualifications(),
            'users' => $this->userModel->where('is_active', 1)->findAll()
        ];

        return view('admin/enseignants/create', $data);
    }

    /**
     * Enregistrer un nouvel enseignant
     */
    public function store()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty|min_length[8]|max_length[20]',
            'specialization' => 'permit_empty|max_length[200]',
            'qualification' => 'permit_empty|max_length[200]',
            'hire_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Vérification manuelle de l'unicité de l'email
        $email = $this->request->getPost('email');
        $existingTeacher = $this->teacherModel->where('email', $email)->first();
        if ($existingTeacher) {
            return redirect()->back()->withInput()->with('error', 'Cet email est déjà utilisé par un autre enseignant');
        }

        $teacherData = [
            'school_id' => 1, // ID de l'école par défaut
            'user_id' => $this->request->getPost('user_id') ?: null,
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $email,
            'specialization' => $this->request->getPost('specialization'),
            'qualification' => $this->request->getPost('qualification'),
            'hire_date' => $this->request->getPost('hire_date'),
            'is_active' => 1
        ];

        $teacherId = $this->teacherModel->insert($teacherData);

        if ($teacherId) {
            // Log d'audit pour la création
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'CREATE',
                'teachers',
                $teacherId,
                null,
                $teacherData
            );
            
            return redirect()->to('/admin/enseignants')->with('success', 'Enseignant créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de l\'enseignant');
        }
    }

    /**
     * Afficher un enseignant
     */
    public function show($id)
    {
        $teacher = $this->teacherModel->getTeacherWithUser($id);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        $data = [
            'title' => 'Profil Enseignant - KISSAI SCHOOL',
            'teacher' => $teacher,
            'teacher_stats' => $this->teacherModel->getTeacherStats($id),
            'teacher_subjects' => $this->teacherModel->getTeacherSubjects($id),
            'teacher_classes' => $this->teacherModel->getTeacherClasses($id)
        ];

        return view('admin/enseignants/show', $data);
    }

    /**
     * Modifier un enseignant
     */
    public function edit($id)
    {
        $teacher = $this->teacherModel->find($id);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        $data = [
            'title' => 'Modifier Enseignant - KISSAI SCHOOL',
            'teacher' => $teacher,
            'specializations' => $this->getSpecializations(),
            'qualifications' => $this->getQualifications(),
            'users' => $this->userModel->where('is_active', 1)->findAll()
        ];

        return view('admin/enseignants/edit', $data);
    }

    /**
     * Mettre à jour un enseignant
     */
    public function update($id)
    {
        $teacher = $this->teacherModel->find($id);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty|min_length[8]|max_length[20]',
            'specialization' => 'permit_empty|max_length[200]',
            'qualification' => 'permit_empty|max_length[200]',
            'hire_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Vérification manuelle de l'unicité de l'email
        $newEmail = $this->request->getPost('email');
        if ($newEmail !== $teacher['email']) {
            $existingTeacher = $this->teacherModel->where('email', $newEmail)->first();
            if ($existingTeacher) {
                return redirect()->back()->withInput()->with('error', 'Cet email est déjà utilisé par un autre enseignant');
            }
        }

        $teacherData = [
            'user_id' => $this->request->getPost('user_id') ?: null,
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $newEmail,
            'specialization' => $this->request->getPost('specialization'),
            'qualification' => $this->request->getPost('qualification'),
            'hire_date' => $this->request->getPost('hire_date'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->teacherModel->update($id, $teacherData)) {
            // Log d'audit pour la mise à jour
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'UPDATE',
                'teachers',
                $id,
                $teacher,
                $teacherData
            );
            
            return redirect()->to('/admin/enseignants')->with('success', 'Enseignant mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    /**
     * Supprimer un enseignant
     */
    public function delete($id)
    {
        $teacher = $this->teacherModel->find($id);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        // Vérifier si l'enseignant a des assignations
        $hasAssignments = $this->teacherModel->getTeacherSubjects($id) || $this->teacherModel->getTeacherClasses($id);
        
        if ($hasAssignments) {
            return redirect()->to('/admin/enseignants')->with('error', 'Impossible de supprimer cet enseignant car il a des assignations actives');
        }

        if ($this->teacherModel->delete($id)) {
            // Log d'audit pour la suppression
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'DELETE',
                'teachers',
                $id,
                $teacher,
                null
            );
            
            return redirect()->to('/admin/enseignants')->with('success', 'Enseignant supprimé avec succès');
        } else {
            return redirect()->to('/admin/enseignants')->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Gestion des matières d'un enseignant
     */
    public function subjects($teacherId)
    {
        $teacher = $this->teacherModel->find($teacherId);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        $data = [
            'title' => 'Matières de l\'Enseignant - KISSAI SCHOOL',
            'teacher' => $teacher,
            'teacher_subjects' => $this->teacherModel->getTeacherSubjects($teacherId),
            'available_subjects' => $this->subjectModel->where('is_active', 1)->findAll(),
            'classes' => $this->classModel->where('is_active', 1)->findAll()
        ];

        return view('admin/enseignants/subjects', $data);
    }

    /**
     * Assigner une matière à un enseignant
     */
    public function assignSubject()
    {
        $teacherId = $this->request->getPost('teacher_id');
        $classId = $this->request->getPost('class_id');
        $subjectId = $this->request->getPost('subject_id');

        if ($this->teacherModel->assignSubjectToTeacher($classId, $subjectId, $teacherId)) {
            // Log d'audit pour l'assignation
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'ASSIGN',
                'class_subjects',
                null,
                null,
                ['class_id' => $classId, 'subject_id' => $subjectId, 'teacher_id' => $teacherId]
            );
            
            return redirect()->back()->with('success', 'Matière assignée avec succès');
        } else {
            return redirect()->back()->with('error', 'Erreur lors de l\'assignation');
        }
    }

    /**
     * Retirer une matière d'un enseignant
     */
    public function removeSubject()
    {
        $classId = $this->request->getPost('class_id');
        $subjectId = $this->request->getPost('subject_id');

        if ($this->teacherModel->removeSubjectFromTeacher($classId, $subjectId)) {
            // Log d'audit pour le retrait
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'REMOVE',
                'class_subjects',
                null,
                ['class_id' => $classId, 'subject_id' => $subjectId],
                null
            );
            
            return redirect()->back()->with('success', 'Matière retirée avec succès');
        } else {
            return redirect()->back()->with('error', 'Erreur lors du retrait');
        }
    }

    /**
     * Gestion des classes d'un enseignant
     */
    public function classes($teacherId)
    {
        $teacher = $this->teacherModel->find($teacherId);
        
        if (!$teacher) {
            return redirect()->to('/admin/enseignants')->with('error', 'Enseignant non trouvé');
        }

        $data = [
            'title' => 'Classes de l\'Enseignant - KISSAI SCHOOL',
            'teacher' => $teacher,
            'teacher_classes' => $this->teacherModel->getTeacherClasses($teacherId),
            'available_classes' => $this->classModel->where('is_active', 1)->findAll()
        ];

        return view('admin/enseignants/classes', $data);
    }

    /**
     * Assigner un enseignant comme responsable principal d'une classe
     */
    public function assignClass()
    {
        $teacherId = $this->request->getPost('teacher_id');
        $classId = $this->request->getPost('class_id');

        if ($this->teacherModel->assignTeacherToClass($classId, $teacherId)) {
            return redirect()->back()->with('success', 'Enseignant assigné comme responsable principal');
        } else {
            return redirect()->back()->with('error', 'Erreur lors de l\'assignation');
        }
    }

    /**
     * Retirer un enseignant comme responsable principal d'une classe
     */
    public function removeClass()
    {
        $classId = $this->request->getPost('class_id');

        if ($this->teacherModel->removeTeacherFromClass($classId)) {
            return redirect()->back()->with('success', 'Responsabilité retirée avec succès');
        } else {
            return redirect()->back()->with('error', 'Erreur lors du retrait');
        }
    }

    /**
     * Statistiques des enseignants
     */
    public function statistics()
    {
        $data = [
            'title' => 'Statistiques des Enseignants - KISSAI SCHOOL',
            'total_teachers' => $this->teacherModel->countAllResults(),
            'active_teachers' => $this->teacherModel->where('is_active', 1)->countAllResults(),
            'teachers_by_specialization' => $this->getTeachersBySpecialization(),
            'recent_hires' => $this->teacherModel->where('is_active', 1)
                                                ->orderBy('hire_date', 'DESC')
                                                ->limit(10)
                                                ->find()
        ];

        return view('admin/enseignants/statistics', $data);
    }

    /**
     * Export des données enseignants
     */
    public function export($format = 'csv')
    {
        $teachers = $this->teacherModel->getActiveTeachers();

        if ($format === 'csv') {
            $filename = 'enseignants_' . date('Y-m-d') . '.csv';
            return $this->response->download($filename, $this->generateCSV($teachers));
        }

        return redirect()->back()->with('error', 'Format d\'export non supporté');
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
            fputcsv($output, $row, ',', '"', '\\');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Récupérer les spécialisations disponibles
     */
    private function getSpecializations()
    {
        return [
            'Mathématiques',
            'Physique-Chimie',
            'Sciences de la Vie et de la Terre',
            'Histoire-Géographie',
            'Français',
            'Anglais',
            'Espagnol',
            'Allemand',
            'Philosophie',
            'Économie',
            'Sciences Économiques et Sociales',
            'Sciences de l\'Ingénieur',
            'Informatique',
            'Éducation Physique et Sportive',
            'Arts Plastiques',
            'Musique',
            'Technologie',
            'Latin',
            'Grec',
            'Autre'
        ];
    }

    /**
     * Récupérer les qualifications disponibles
     */
    private function getQualifications()
    {
        return [
            'Licence',
            'Master',
            'Doctorat',
            'CAPES',
            'Agrégation',
            'Certificat d\'Aptitude',
            'Diplôme d\'État',
            'Autre'
        ];
    }

    /**
     * Récupérer les enseignants par spécialisation
     */
    private function getTeachersBySpecialization()
    {
        $specializations = $this->getSpecializations();
        $stats = [];

        foreach ($specializations as $spec) {
            $count = $this->teacherModel->where('specialization', $spec)
                                       ->where('is_active', 1)
                                       ->countAllResults();
            if ($count > 0) {
                $stats[$spec] = $count;
            }
        }

        return $stats;
    }
}




