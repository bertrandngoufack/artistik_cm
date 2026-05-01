<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\AcademicYearService;

class MemberModel extends Model
{
    protected $table = 'students'; // Table par défaut
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'matricule', 'first_name', 'last_name', 'gender', 'date_of_birth',
        'place_of_birth', 'nationality', 'address', 'phone', 'email',
        'parent_name', 'parent_phone', 'parent_email', 'emergency_contact',
        'blood_group', 'medical_info', 'current_class_id', 'academic_year',
        'admission_date', 'status', 'photo_url'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'matricule' => 'required|min_length[3]|max_length[20]|is_unique[students.matricule,id,{id}]',
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name' => 'required|min_length[2]|max_length[50]',
        'gender' => 'required|in_list[M,F]',
        'date_of_birth' => 'required|valid_date',
        'academic_year' => 'required|min_length[9]|max_length[9]'
    ];

    protected $validationMessages = [
        'matricule' => [
            'required' => 'Le matricule est requis',
            'min_length' => 'Le matricule doit contenir au moins 3 caractères',
            'max_length' => 'Le matricule ne peut pas dépasser 20 caractères',
            'is_unique' => 'Ce matricule existe déjà'
        ],
        'first_name' => [
            'required' => 'Le prénom est requis',
            'min_length' => 'Le prénom doit contenir au moins 2 caractères',
            'max_length' => 'Le prénom ne peut pas dépasser 50 caractères'
        ],
        'last_name' => [
            'required' => 'Le nom de famille est requis',
            'min_length' => 'Le nom de famille doit contenir au moins 2 caractères',
            'max_length' => 'Le nom de famille ne peut pas dépasser 50 caractères'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $academicYearService;

    public function __construct()
    {
        parent::__construct();
        $this->academicYearService = new AcademicYearService();
    }

    /**
     * Récupérer tous les membres (étudiants et enseignants)
     */
    public function getAllMembers()
    {
        $db = \Config\Database::connect();
        
        // Récupérer les étudiants
        $students = $db->table('students')
                      ->select('id, matricule, first_name, last_name, email, phone, status, created_at, "STUDENT" as member_type')
                      ->where('status', 'ACTIVE')
                      ->get()
                      ->getResultArray();
        
        // Récupérer les enseignants
        $teachers = $db->table('teachers')
                      ->select('id, first_name, last_name, email, phone, is_active, created_at, "TEACHER" as member_type')
                      ->where('is_active', 1)
                      ->get()
                      ->getResultArray();
        
        // Combiner et trier par nom
        $members = array_merge($students, $teachers);
        usort($members, function($a, $b) {
            return strcmp($a['last_name'], $b['last_name']);
        });
        
        return $members;
    }

    /**
     * Récupérer un membre par ID et type
     */
    public function getMemberById($id, $type = 'STUDENT')
    {
        $table = ($type === 'TEACHER') ? 'teachers' : 'students';
        
        if ($type === 'TEACHER') {
            return $this->db->table($table)
                           ->select('id, first_name, last_name, email, phone, is_active, created_at, "TEACHER" as member_type')
                           ->where('id', $id)
                           ->get()
                           ->getRowArray();
        } else {
            return $this->db->table($table)
                           ->select('id, matricule, first_name, last_name, email, phone, status, created_at, "STUDENT" as member_type')
                           ->where('id', $id)
                           ->get()
                           ->getRowArray();
        }
    }

    /**
     * Rechercher des membres
     */
    public function searchMembers($query)
    {
        $db = \Config\Database::connect();
        
        // Rechercher dans les étudiants
        $students = $db->table('students')
                      ->select('id, matricule, first_name, last_name, email, phone, status, created_at, "STUDENT" as member_type')
                      ->where('status', 'ACTIVE')
                      ->groupStart()
                      ->like('first_name', $query)
                      ->orLike('last_name', $query)
                      ->orLike('matricule', $query)
                      ->orLike('email', $query)
                      ->groupEnd()
                      ->get()
                      ->getResultArray();
        
        // Rechercher dans les enseignants
        $teachers = $db->table('teachers')
                      ->select('id, first_name, last_name, email, phone, is_active, created_at, "TEACHER" as member_type')
                      ->where('is_active', 1)
                      ->groupStart()
                      ->like('first_name', $query)
                      ->orLike('last_name', $query)
                      ->orLike('email', $query)
                      ->groupEnd()
                      ->get()
                      ->getResultArray();
        
        return array_merge($students, $teachers);
    }

    /**
     * Récupérer les statistiques des membres
     */
    public function getMemberStats()
    {
        $db = \Config\Database::connect();
        
        $totalStudents = $db->table('students')->where('status', 'ACTIVE')->countAllResults();
        $totalTeachers = $db->table('teachers')->where('is_active', 1)->countAllResults();
        $totalMembers = $totalStudents + $totalTeachers;
        
        // Compter les emprunts actifs
        $activeLoans = $db->table('loans')->where('status', 'ACTIVE')->countAllResults();
        
        // Compter les emprunts en retard
        $overdueLoans = $db->table('loans')
                          ->where('status', 'ACTIVE')
                          ->where('due_date <', date('Y-m-d'))
                          ->countAllResults();
        
        return [
            'totalMembers' => $totalMembers,
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'averageLoans' => $totalMembers > 0 ? round($activeLoans / $totalMembers, 1) : 0
        ];
    }

    /**
     * Récupérer l'historique des emprunts d'un membre
     */
    public function getMemberLoanHistory($memberId, $memberType = 'STUDENT')
    {
        $db = \Config\Database::connect();
        
        $memberField = ($memberType === 'TEACHER') ? 'teacher_id' : 'student_id';
        
        return $db->table('loans')
                 ->select('loans.*, books.title as book_title, books.author as book_author')
                 ->join('books', 'books.id = loans.book_id')
                 ->where($memberField, $memberId)
                 ->orderBy('loan_date', 'DESC')
                 ->get()
                 ->getResultArray();
    }

    /**
     * Créer un nouveau membre (étudiant)
     */
    public function createStudent($data)
    {
        $db = \Config\Database::connect();
        
        $studentData = [
            'matricule' => $data['matricule'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'place_of_birth' => $data['place_of_birth'] ?? null,
            'nationality' => $data['nationality'] ?? 'Camerounaise',
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'parent_name' => $data['parent_name'] ?? null,
            'parent_phone' => $data['parent_phone'] ?? null,
            'parent_email' => $data['parent_email'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'medical_info' => $data['medical_info'] ?? null,
            'current_class_id' => $data['current_class_id'] ?? null,
            'academic_year' => $data['academic_year'] ?? $this->getCurrentAcademicYear(),
            'admission_date' => $data['admission_date'] ?? date('Y-m-d'),
            'status' => 'ACTIVE'
        ];
        
        return $db->table('students')->insert($studentData);
    }

    /**
     * Mettre à jour un membre
     */
    public function updateMember($id, $data, $type = 'STUDENT')
    {
        $table = ($type === 'TEACHER') ? 'teachers' : 'students';
        
        return $this->db->table($table)->where('id', $id)->update($data);
    }

    /**
     * Supprimer un membre (désactiver)
     */
    public function deleteMember($id, $type = 'STUDENT')
    {
        $table = ($type === 'TEACHER') ? 'teachers' : 'students';
        
        return $this->db->table($table)->where('id', $id)->update(['status' => 'INACTIVE']);
    }

    /**
     * Rechercher des membres avec filtres avancés
     */
    public function searchMembersWithFilters($search = '', $status = '', $type = '', $registrationDate = '')
    {
        $db = \Config\Database::connect();
        
        // Construire la requête pour les étudiants
        $studentBuilder = $db->table('students')
                            ->select('id, matricule, first_name, last_name, email, phone, status, created_at, "STUDENT" as member_type')
                            ->where('status', 'ACTIVE');
        
        // Construire la requête pour les enseignants
        $teacherBuilder = $db->table('teachers')
                            ->select('id, null as matricule, first_name, last_name, email, phone, is_active, created_at, "TEACHER" as member_type')
                            ->where('is_active', 1);
        
        // Appliquer les filtres de recherche
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $studentBuilder->groupStart()
                          ->like('first_name', $searchTerm)
                          ->orLike('last_name', $searchTerm)
                          ->orLike('matricule', $searchTerm)
                          ->orLike('email', $searchTerm)
                          ->orLike('phone', $searchTerm)
                          ->groupEnd();
            
            $teacherBuilder->groupStart()
                          ->like('first_name', $searchTerm)
                          ->orLike('last_name', $searchTerm)
                          ->orLike('email', $searchTerm)
                          ->orLike('phone', $searchTerm)
                          ->groupEnd();
        }
        
        // Appliquer le filtre de statut
        if ($status) {
            if ($status === 'active') {
                $studentBuilder->where('status', 'ACTIVE');
                $teacherBuilder->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $studentBuilder->where('status', 'INACTIVE');
                $teacherBuilder->where('is_active', 0);
            }
        }
        
        // Appliquer le filtre de date d'inscription
        if ($registrationDate) {
            $studentBuilder->where('DATE(created_at)', $registrationDate);
            $teacherBuilder->where('DATE(created_at)', $registrationDate);
        }
        
        // Appliquer le filtre de type (APRÈS tous les autres filtres)
        if ($type) {
            if ($type === 'student') {
                // Pour les étudiants, on ne récupère que les étudiants
                $teacherBuilder = null;
            } elseif ($type === 'teacher') {
                // Pour les enseignants, on ne récupère que les enseignants
                $studentBuilder = null;
            }
        }
        
        // Exécuter les requêtes
        $students = [];
        $teachers = [];
        
        if ($studentBuilder) {
            $students = $studentBuilder->get()->getResultArray();
        }
        
        if ($teacherBuilder) {
            $teachers = $teacherBuilder->get()->getResultArray();
        }
        
        // Combiner et trier les résultats
        $members = array_merge($students, $teachers);
        usort($members, function($a, $b) {
            return strcmp($a['last_name'], $b['last_name']);
        });
        
        return $members;
    }

    /**
     * Obtenir l'année académique actuelle
     */
    protected function getCurrentAcademicYear(): string
    {
        return $this->academicYearService->getCurrentAcademicYear();
    }
}
