<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle Student pour LyCol
 */
class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'matricule', 'first_name', 'last_name', 'birth_date', 'birth_place',
        'gender', 'nationality', 'address', 'phone', 'email', 'photo',
        'parent_name', 'parent_phone', 'parent_email', 'emergency_contact',
        'blood_group', 'medical_info', 'admission_date', 'current_class_id',
        'academic_year', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'matricule' => 'required|min_length[5]|max_length[20]|is_unique[students.matricule,id,{id}]',
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name' => 'required|min_length[2]|max_length[100]',
        'birth_date' => 'required|valid_date',
        'gender' => 'required|in_list[M,F]',
        'nationality' => 'required|max_length[50]',
        'parent_name' => 'required|max_length[200]',
        'parent_phone' => 'required|max_length[20]',
        'admission_date' => 'required|valid_date',
        'current_class_id' => 'required|integer',
        'academic_year' => 'required|max_length[9]',
        'status' => 'required|in_list[ACTIVE,INACTIVE,GRADUATED,TRANSFERRED,SUSPENDED]'
    ];

    protected $validationMessages = [
        'matricule' => [
            'required' => 'Le matricule est requis',
            'min_length' => 'Le matricule doit contenir au moins 5 caractères',
            'max_length' => 'Le matricule ne peut pas dépasser 20 caractères',
            'is_unique' => 'Ce matricule existe déjà'
        ],
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
        'birth_date' => [
            'required' => 'La date de naissance est requise',
            'valid_date' => 'Veuillez saisir une date valide'
        ],
        'gender' => [
            'required' => 'Le genre est requis',
            'in_list' => 'Le genre doit être M ou F'
        ],
        'nationality' => [
            'required' => 'La nationalité est requise',
            'max_length' => 'La nationalité ne peut pas dépasser 50 caractères'
        ],
        'parent_name' => [
            'required' => 'Le nom du parent est requis',
            'max_length' => 'Le nom du parent ne peut pas dépasser 200 caractères'
        ],
        'parent_phone' => [
            'required' => 'Le téléphone du parent est requis',
            'max_length' => 'Le téléphone ne peut pas dépasser 20 caractères'
        ],
        'admission_date' => [
            'required' => 'La date d\'admission est requise',
            'valid_date' => 'Veuillez saisir une date valide'
        ],
        'current_class_id' => [
            'required' => 'La classe est requise',
            'integer' => 'La classe doit être un nombre entier'
        ],
        'academic_year' => [
            'required' => 'L\'année académique est requise',
            'max_length' => 'L\'année académique ne peut pas dépasser 9 caractères'
        ],
        'status' => [
            'required' => 'Le statut est requis',
            'in_list' => 'Le statut doit être ACTIVE, INACTIVE, GRADUATED, TRANSFERRED ou SUSPENDED'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir un élève avec sa classe
     */
    public function getStudentWithClass($id)
    {
        return $this->select('students.*, classes.name as class_name, classes.code as class_code')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->where('students.id', $id)
                   ->first();
    }

    /**
     * Obtenir tous les élèves avec leurs classes
     */
    public function getAllStudentsWithClasses()
    {
        return $this->select('students.*, classes.name as class_name, classes.code as class_code')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->orderBy('students.last_name', 'ASC')
                   ->orderBy('students.first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les élèves actifs
     */
    public function getActiveStudents()
    {
        return $this->where('status', 'ACTIVE')
                   ->orderBy('last_name', 'ASC')
                   ->orderBy('first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les élèves par classe
     */
    public function getStudentsByClass($classId)
    {
        return $this->where('current_class_id', $classId)
                   ->where('status', 'ACTIVE')
                   ->orderBy('last_name', 'ASC')
                   ->orderBy('first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les élèves par genre
     */
    public function getStudentsByGender($gender)
    {
        return $this->where('gender', $gender)
                   ->where('status', 'ACTIVE')
                   ->orderBy('last_name', 'ASC')
                   ->orderBy('first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Rechercher des élèves
     */
    public function searchStudents($query)
    {
        return $this->like('matricule', $query)
                   ->orLike('first_name', $query)
                   ->orLike('last_name', $query)
                   ->orLike('parent_name', $query)
                   ->where('status', 'ACTIVE')
                   ->orderBy('last_name', 'ASC')
                   ->orderBy('first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les statistiques des élèves
     */
    public function getStudentStats()
    {
        return [
            'total' => $this->where('status', 'ACTIVE')->countAllResults(),
            'male' => $this->where('status', 'ACTIVE')->where('gender', 'M')->countAllResults(),
            'female' => $this->where('status', 'ACTIVE')->where('gender', 'F')->countAllResults(),
            'by_gender' => [
                'M' => $this->where('status', 'ACTIVE')->where('gender', 'M')->countAllResults(),
                'F' => $this->where('status', 'ACTIVE')->where('gender', 'F')->countAllResults()
            ],
            'by_class' => $this->select('classes.name as class_name, COUNT(*) as count')
                              ->join('classes', 'classes.id = students.current_class_id')
                              ->where('students.status', 'ACTIVE')
                              ->groupBy('classes.id')
                              ->findAll(),
            'by_status' => $this->select('status, COUNT(*) as count')
                              ->groupBy('status')
                              ->findAll()
        ];
    }

    /**
     * Obtenir les élèves récemment inscrits
     */
    public function getRecentlyEnrolled($limit = 10)
    {
        return $this->where('status', 'ACTIVE')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Vérifier si un matricule existe
     */
    public function matriculeExists($matricule, $excludeId = null)
    {
        $builder = $this->where('matricule', $matricule);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les élèves par page
     */
    public function getStudentsPaginated($page = 1, $perPage = 20)
    {
        return $this->select('students.*, classes.name as class_name, classes.code as class_code')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->orderBy('students.last_name', 'ASC')
                   ->orderBy('students.first_name', 'ASC')
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * Obtenir le pager pour la pagination
     */
    public function getStudentsPager()
    {
        return $this->pager;
    }

    /**
     * Obtenir les élèves avec leurs notes
     */
    public function getStudentsWithGrades($examId)
    {
        return $this->select('students.*, grades.marks_obtained, grades.total_marks, subjects.name as subject_name')
                   ->join('grades', 'grades.student_id = students.id', 'left')
                   ->join('subjects', 'subjects.id = grades.subject_id', 'left')
                   ->where('grades.exam_id', $examId)
                   ->where('students.status', 'ACTIVE')
                   ->orderBy('students.last_name', 'ASC')
                   ->orderBy('students.first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les élèves avec leurs absences
     */
    public function getStudentsWithAbsences($date = null)
    {
        $builder = $this->select('students.*, absences.date, absences.period, absences.is_justified')
                       ->join('absences', 'absences.student_id = students.id', 'left');
        
        if ($date) {
            $builder->where('absences.date', $date);
        }
        
        return $builder->where('students.status', 'ACTIVE')
                      ->orderBy('students.last_name', 'ASC')
                      ->orderBy('students.first_name', 'ASC')
                      ->findAll();
    }

    /**
     * Obtenir les élèves avec leurs paiements
     */
    public function getStudentsWithPayments($academicYear = null)
    {
        $builder = $this->select('students.*, payments.amount_paid, payments.payment_date, fee_types.name as fee_type')
                       ->join('payments', 'payments.student_id = students.id', 'left')
                       ->join('fee_types', 'fee_types.id = payments.fee_type_id', 'left');
        
        if ($academicYear) {
            $builder->where('payments.academic_year', $academicYear);
        }
        
        return $builder->where('students.status', 'ACTIVE')
                      ->orderBy('students.last_name', 'ASC')
                      ->orderBy('students.first_name', 'ASC')
                      ->findAll();
    }

    /**
     * Créer un nouvel élève
     */
    public function createStudent($data)
    {
        // Générer un matricule automatique si non fourni
        if (empty($data['matricule'])) {
            $data['matricule'] = $this->generateMatricule();
        }
        
        return $this->insert($data);
    }

    /**
     * Mettre à jour un élève
     */
    public function updateStudent($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Changer le statut d'un élève
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Générer un matricule automatique
     */
    private function generateMatricule()
    {
        $year = date('Y');
        $count = $this->where('YEAR(created_at)', $year)->countAllResults() + 1;
        return $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir les élèves par année académique
     */
    public function getStudentsByAcademicYear($academicYear)
    {
        return $this->where('academic_year', $academicYear)
                   ->where('status', 'ACTIVE')
                   ->orderBy('last_name', 'ASC')
                   ->orderBy('first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les élèves avec leurs bulletins
     */
    public function getStudentsWithReportCards($academicYear, $term)
    {
        return $this->select('students.*, report_cards.average, report_cards.rank, report_cards.total_students')
                   ->join('report_cards', 'report_cards.student_id = students.id', 'left')
                   ->where('report_cards.academic_year', $academicYear)
                   ->where('report_cards.term', $term)
                   ->where('students.status', 'ACTIVE')
                   ->orderBy('students.last_name', 'ASC')
                   ->orderBy('students.first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir la tendance des inscriptions
     */
    public function getEnrollmentTrend()
    {
        return $this->select('YEAR(admission_date) as year, MONTH(admission_date) as month, COUNT(*) as count')
                   ->where('status', 'ACTIVE')
                   ->groupBy('YEAR(admission_date), MONTH(admission_date)')
                   ->orderBy('year', 'DESC')
                   ->orderBy('month', 'DESC')
                   ->limit(12)
                   ->findAll();
    }
}




