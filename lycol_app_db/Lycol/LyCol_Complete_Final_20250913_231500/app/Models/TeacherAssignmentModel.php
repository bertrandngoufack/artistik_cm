<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherAssignmentModel extends Model
{
    protected $table = 'teacher_assignments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'teacher_id', 'class_id', 'subject_id', 'is_principal', 
        'academic_year', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'teacher_id' => 'required|integer',
        'class_id' => 'required|integer',
        'subject_id' => 'required|integer',
        'academic_year' => 'required|min_length[9]|max_length[9]',
    ];

    protected $validationMessages = [
        'teacher_id' => [
            'required' => 'L\'enseignant est requis',
            'integer' => 'L\'enseignant doit être un nombre entier'
        ],
        'class_id' => [
            'required' => 'La classe est requise',
            'integer' => 'La classe doit être un nombre entier'
        ],
        'subject_id' => [
            'required' => 'La matière est requise',
            'integer' => 'La matière doit être un nombre entier'
        ],
                    'academic_year' => [
                'required' => 'L\'année académique est requise',
                'min_length' => 'L\'année académique doit être au format YYYY-YYYY',
                'max_length' => 'L\'année académique doit être au format YYYY-YYYY'
            ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir les assignations d'un enseignant
     */
    public function getTeacherAssignments($teacherId, $academicYear = null)
    {
        $builder = $this->select('teacher_assignments.*, classes.name as class_name, classes.code as class_code, subjects.name as subject_name, subjects.code as subject_code')
                       ->join('classes', 'classes.id = teacher_assignments.class_id')
                       ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                       ->where('teacher_assignments.teacher_id', $teacherId)
                       ->where('teacher_assignments.is_active', 1);

        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->orderBy('classes.level', 'ASC')
                      ->orderBy('classes.name', 'ASC')
                      ->findAll();
    }

    /**
     * Obtenir les assignations d'une classe
     */
    public function getClassAssignments($classId, $academicYear = null)
    {
        $builder = $this->select('teacher_assignments.*, teachers.first_name, teachers.last_name, teachers.email, subjects.name as subject_name, subjects.code as subject_code')
                       ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                       ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                       ->where('teacher_assignments.class_id', $classId)
                       ->where('teacher_assignments.is_active', 1);

        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->orderBy('subjects.name', 'ASC')
                      ->findAll();
    }

    /**
     * Obtenir l'enseignant principal d'une classe
     */
    public function getClassPrincipalTeacher($classId, $academicYear = null)
    {
        $builder = $this->select('teacher_assignments.*, teachers.first_name, teachers.last_name, teachers.email')
                       ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                       ->where('teacher_assignments.class_id', $classId)
                       ->where('teacher_assignments.is_principal', 1)
                       ->where('teacher_assignments.is_active', 1);

        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->first();
    }

    /**
     * Obtenir les matières d'un enseignant pour une classe
     */
    public function getTeacherSubjectsForClass($teacherId, $classId, $academicYear = null)
    {
        $builder = $this->select('teacher_assignments.*, subjects.name as subject_name, subjects.code as subject_code')
                       ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                       ->where('teacher_assignments.teacher_id', $teacherId)
                       ->where('teacher_assignments.class_id', $classId)
                       ->where('teacher_assignments.is_active', 1);

        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->orderBy('subjects.name', 'ASC')
                      ->findAll();
    }

    /**
     * Vérifier si un enseignant est déjà assigné à une classe-matière
     */
    public function isTeacherAssigned($teacherId, $classId, $subjectId, $academicYear, $excludeId = null)
    {
        $builder = $this->where('teacher_id', $teacherId)
                       ->where('class_id', $classId)
                       ->where('subject_id', $subjectId)
                       ->where('academic_year', $academicYear)
                       ->where('is_active', 1);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les statistiques des assignations
     */
    public function getAssignmentStats($academicYear = null)
    {
        $builder = $this->select('COUNT(*) as total_assignments, COUNT(DISTINCT teacher_id) as total_teachers, COUNT(DISTINCT class_id) as total_classes')
                       ->where('is_active', 1);

        if ($academicYear) {
            $builder->where('academic_year', $academicYear);
        }

        return $builder->first();
    }

    /**
     * Obtenir les assignations par matière
     */
    public function getAssignmentsBySubject($subjectId, $academicYear = null)
    {
        $builder = $this->select('teacher_assignments.*, teachers.first_name, teachers.last_name, classes.name as class_name')
                       ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                       ->join('classes', 'classes.id = teacher_assignments.class_id')
                       ->where('teacher_assignments.subject_id', $subjectId)
                       ->where('teacher_assignments.is_active', 1);

        if ($academicYear) {
            $builder->where('teacher_assignments.academic_year', $academicYear);
        }

        return $builder->orderBy('classes.level', 'ASC')
                      ->orderBy('classes.name', 'ASC')
                      ->findAll();
    }

    /**
     * Obtenir toutes les assignations actives
     */
    public function getActiveAssignments()
    {
        return $this->select('teacher_assignments.*, teachers.first_name, teachers.last_name, classes.name as class_name, subjects.name as subject_name, cycles.name as cycle_name')
                   ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                   ->join('classes', 'classes.id = teacher_assignments.class_id')
                   ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                   ->join('cycles', 'cycles.id = classes.cycle_id', 'left')
                   ->where('teacher_assignments.is_active', 1)
                   ->orderBy('classes.level', 'ASC')
                   ->orderBy('classes.name', 'ASC')
                   ->orderBy('subjects.name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les assignations récentes (les 5 plus récentes)
     */
    public function getRecentAssignments($limit = 5)
    {
        return $this->select('teacher_assignments.*, teachers.first_name, teachers.last_name, classes.name as class_name, subjects.name as subject_name')
                   ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                   ->join('classes', 'classes.id = teacher_assignments.class_id')
                   ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                   ->where('teacher_assignments.is_active', 1)
                   ->orderBy('teacher_assignments.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtenir une assignation avec tous les détails pour la vue
     */
    public function getAssignmentWithDetails($id)
    {
        $assignment = $this->select('teacher_assignments.*, 
                                   teachers.first_name, teachers.last_name, teachers.email as teacher_email,
                                   classes.name as class_name, classes.level as class_level,
                                   subjects.name as subject_name, subjects.code as subject_code,
                                   cycles.name as cycle_name')
                   ->join('teachers', 'teachers.id = teacher_assignments.teacher_id')
                   ->join('classes', 'classes.id = teacher_assignments.class_id')
                   ->join('subjects', 'subjects.id = teacher_assignments.subject_id')
                   ->join('cycles', 'cycles.id = classes.cycle_id', 'left')
                   ->where('teacher_assignments.id', $id)
                   ->where('teacher_assignments.is_active', 1)
                   ->first();

        if ($assignment) {
            // Ajouter des statistiques supplémentaires
            $assignment['total_teacher_assignments'] = $this->where('teacher_id', $assignment['teacher_id'])
                                                          ->where('is_active', 1)
                                                          ->countAllResults();
            
            $assignment['total_class_assignments'] = $this->where('class_id', $assignment['class_id'])
                                                        ->where('is_active', 1)
                                                        ->countAllResults();
            
            $assignment['total_subject_assignments'] = $this->where('subject_id', $assignment['subject_id'])
                                                          ->where('is_active', 1)
                                                          ->countAllResults();
        }

        return $assignment;
    }
}
