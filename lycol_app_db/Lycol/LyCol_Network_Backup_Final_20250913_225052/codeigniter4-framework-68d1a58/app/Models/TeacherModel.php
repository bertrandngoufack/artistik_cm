<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherModel extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'school_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'specialization',
        'qualification',
        'hire_date',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name' => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email',
        'phone' => 'permit_empty|min_length[8]|max_length[20]',
        'specialization' => 'permit_empty|max_length[200]',
        'qualification' => 'permit_empty|max_length[200]',
        'hire_date' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'Le prénom est requis',
            'min_length' => 'Le prénom doit contenir au moins 2 caractères',
            'max_length' => 'Le prénom ne peut pas dépasser 100 caractères'
        ],
        'last_name' => [
            'required' => 'Le nom est requis',
            'min_length' => 'Le nom doit contenir au moins 2 caractères',
            'max_length' => 'Le nom ne peut pas dépasser 100 caractères'
        ],
        'email' => [
            'required' => 'L\'email est requis',
            'valid_email' => 'L\'email doit être valide',
            'is_unique' => 'Cet email est déjà utilisé'
        ],
        'phone' => [
            'min_length' => 'Le téléphone doit contenir au moins 8 caractères',
            'max_length' => 'Le téléphone ne peut pas dépasser 20 caractères'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    protected function beforeInsert(array $data)
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        $data['data']['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function beforeUpdate(array $data)
    {
        $data['data']['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * Récupère un enseignant avec ses informations utilisateur
     */
    public function getTeacherWithUser($id)
    {
        return $this->select('teachers.*, users.username, users.role_id, roles.name as role_name')
                    ->join('users', 'users.id = teachers.user_id', 'left')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('teachers.id', $id)
                    ->first();
    }

    /**
     * Récupère tous les enseignants actifs avec leurs informations utilisateur
     */
    public function getActiveTeachers()
    {
        return $this->select('teachers.*, users.username, users.role_id, roles.name as role_name')
                    ->join('users', 'users.id = teachers.user_id', 'left')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('teachers.is_active', 1)
                    ->findAll();
    }

    /**
     * Récupère les matières enseignées par un enseignant
     */
    public function getTeacherSubjects($teacherId)
    {
        $db = \Config\Database::connect();
        return $db->table('class_subjects')
                  ->select('class_subjects.*, subjects.name as subject_name, subjects.code as subject_code, classes.name as class_name')
                  ->join('subjects', 'subjects.id = class_subjects.subject_id')
                  ->join('classes', 'classes.id = class_subjects.class_id')
                  ->where('class_subjects.teacher_id', $teacherId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Récupère les classes dont un enseignant est responsable principal
     */
    public function getTeacherClasses($teacherId)
    {
        $db = \Config\Database::connect();
        return $db->table('classes')
                  ->select('classes.*, cycles.name as level_name, series.name as series_name')
                  ->join('cycles', 'cycles.id = classes.cycle_id', 'left')
                  ->join('series', 'series.id = classes.series_id', 'left')
                  ->where('classes.teacher_id', $teacherId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Récupère les statistiques d'un enseignant
     */
    public function getTeacherStats($teacherId)
    {
        $db = \Config\Database::connect();
        
        // Nombre de matières enseignées
        $subjectsCount = $db->table('class_subjects')
                           ->where('teacher_id', $teacherId)
                           ->countAllResults();

        // Nombre de classes enseignées
        $classesCount = $db->table('class_subjects')
                          ->distinct()
                          ->select('class_id')
                          ->where('teacher_id', $teacherId)
                          ->countAllResults();

        // Nombre d'élèves enseignés
        $studentsCount = $db->table('class_subjects')
                           ->select('students.id')
                           ->join('students', 'students.current_class_id = class_subjects.class_id')
                           ->where('class_subjects.teacher_id', $teacherId)
                           ->where('students.status', 'ACTIVE')
                           ->distinct()
                           ->countAllResults();

        // Nombre de classes dont il est responsable principal
        $principalClassesCount = $db->table('classes')
                                   ->where('teacher_id', $teacherId)
                                   ->countAllResults();

        return [
            'subjects_count' => $subjectsCount,
            'classes_count' => $classesCount,
            'students_count' => $studentsCount,
            'principal_classes_count' => $principalClassesCount
        ];
    }

    /**
     * Recherche d'enseignants
     */
    public function searchTeachers($search)
    {
        return $this->select('teachers.*, users.username, roles.name as role_name')
                    ->join('users', 'users.id = teachers.user_id', 'left')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->like('teachers.first_name', $search)
                    ->orLike('teachers.last_name', $search)
                    ->orLike('teachers.email', $search)
                    ->orLike('teachers.specialization', $search)
                    ->findAll();
    }

    /**
     * Récupère les enseignants par spécialisation
     */
    public function getTeachersBySpecialization($specialization)
    {
        return $this->select('teachers.*, users.username, roles.name as role_name')
                    ->join('users', 'users.id = teachers.user_id', 'left')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('teachers.specialization', $specialization)
                    ->where('teachers.is_active', 1)
                    ->findAll();
    }

    /**
     * Récupère les enseignants disponibles pour une matière
     */
    public function getAvailableTeachersForSubject($subjectId)
    {
        $db = \Config\Database::connect();
        
        // Enseignants qui enseignent déjà cette matière
        $assignedTeachers = $db->table('class_subjects')
                              ->select('teacher_id')
                              ->where('subject_id', $subjectId)
                              ->where('teacher_id IS NOT NULL')
                              ->get()
                              ->getResultArray();

        $assignedIds = array_column($assignedTeachers, 'teacher_id');

        // Enseignants actifs qui ne sont pas encore assignés à cette matière
        $query = $this->select('teachers.*, users.username')
                     ->join('users', 'users.id = teachers.user_id', 'left')
                     ->where('teachers.is_active', 1);

        if (!empty($assignedIds)) {
            $query->whereNotIn('teachers.id', $assignedIds);
        }

        return $query->findAll();
    }

    /**
     * Assigne une matière à un enseignant
     */
    public function assignSubjectToTeacher($classId, $subjectId, $teacherId)
    {
        $db = \Config\Database::connect();
        
        // Vérifier si l'assignation existe déjà
        $existing = $db->table('class_subjects')
                      ->where('class_id', $classId)
                      ->where('subject_id', $subjectId)
                      ->first();

        if ($existing) {
            // Mettre à jour l'assignation existante
            return $db->table('class_subjects')
                     ->where('class_id', $classId)
                     ->where('subject_id', $subjectId)
                     ->update(['teacher_id' => $teacherId]);
        } else {
            // Créer une nouvelle assignation
            return $db->table('class_subjects')->insert([
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId
            ]);
        }
    }

    /**
     * Retire l'assignation d'une matière à un enseignant
     */
    public function removeSubjectFromTeacher($classId, $subjectId)
    {
        $db = \Config\Database::connect();
        return $db->table('class_subjects')
                 ->where('class_id', $classId)
                 ->where('subject_id', $subjectId)
                 ->update(['teacher_id' => null]);
    }

    /**
     * Assigne un enseignant comme responsable principal d'une classe
     */
    public function assignTeacherToClass($classId, $teacherId)
    {
        $db = \Config\Database::connect();
        return $db->table('classes')
                 ->where('id', $classId)
                 ->update(['teacher_id' => $teacherId]);
    }

    /**
     * Retire un enseignant comme responsable principal d'une classe
     */
    public function removeTeacherFromClass($classId)
    {
        $db = \Config\Database::connect();
        return $db->table('classes')
                 ->where('id', $classId)
                 ->update(['teacher_id' => null]);
    }
}




