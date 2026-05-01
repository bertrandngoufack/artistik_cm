<?php

namespace App\Models;

use CodeIgniter\Model;

class TimetableModel extends Model
{
    protected $table = 'timetables';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'class_id', 'day_of_week', 'start_time', 'end_time', 
        'subject_id', 'teacher_id', 'room', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'class_id' => 'required|integer',
        'day_of_week' => 'required|integer|greater_than[0]|less_than[7]',
        'start_time' => 'required',
        'end_time' => 'required',
        'subject_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'class_id' => [
            'required' => 'La classe est requise',
            'integer' => 'La classe doit être un nombre entier'
        ],
        'day_of_week' => [
            'required' => 'Le jour de la semaine est requis',
            'integer' => 'Le jour doit être un nombre entier',
            'greater_than' => 'Le jour doit être entre 1 et 6',
            'less_than' => 'Le jour doit être entre 1 et 6'
        ],
        'start_time' => [
            'required' => 'L\'heure de début est requise'
        ],
        'end_time' => [
            'required' => 'L\'heure de fin est requise'
        ],
        'subject_id' => [
            'required' => 'La matière est requise',
            'integer' => 'La matière doit être un nombre entier'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir l'emploi du temps d'une classe
     */
    public function getClassTimetable($classId)
    {
        return $this->select('timetables.*, subjects.name as subject_name, subjects.code as subject_code, teachers.first_name, teachers.last_name')
                   ->join('subjects', 'subjects.id = timetables.subject_id')
                   ->join('teachers', 'teachers.id = timetables.teacher_id', 'left')
                   ->where('timetables.class_id', $classId)
                   ->where('timetables.is_active', 1)
                   ->orderBy('timetables.day_of_week', 'ASC')
                   ->orderBy('timetables.start_time', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir l'emploi du temps d'un enseignant
     */
    public function getTeacherTimetable($teacherId)
    {
        return $this->select('timetables.*, subjects.name as subject_name, classes.name as class_name, classes.code as class_code')
                   ->join('subjects', 'subjects.id = timetables.subject_id')
                   ->join('classes', 'classes.id = timetables.class_id')
                   ->where('timetables.teacher_id', $teacherId)
                   ->where('timetables.is_active', 1)
                   ->orderBy('timetables.day_of_week', 'ASC')
                   ->orderBy('timetables.start_time', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir l'emploi du temps par jour
     */
    public function getTimetableByDay($classId, $dayOfWeek)
    {
        return $this->select('timetables.*, subjects.name as subject_name, teachers.first_name, teachers.last_name')
                   ->join('subjects', 'subjects.id = timetables.subject_id')
                   ->join('teachers', 'teachers.id = timetables.teacher_id', 'left')
                   ->where('timetables.class_id', $classId)
                   ->where('timetables.day_of_week', $dayOfWeek)
                   ->where('timetables.is_active', 1)
                   ->orderBy('timetables.start_time', 'ASC')
                   ->findAll();
    }

    /**
     * Vérifier les conflits d'emploi du temps
     */
    public function checkConflicts($classId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $builder = $this->where('class_id', $classId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('is_active', 1)
                       ->groupStart()
                       ->where('start_time <', $endTime)
                       ->where('end_time >', $startTime)
                       ->groupEnd();

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Vérifier les conflits pour un enseignant
     */
    public function checkTeacherConflicts($teacherId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $builder = $this->where('teacher_id', $teacherId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('is_active', 1)
                       ->groupStart()
                       ->where('start_time <', $endTime)
                       ->where('end_time >', $startTime)
                       ->groupEnd();

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les statistiques de l'emploi du temps
     */
    public function getTimetableStats($classId = null)
    {
        $builder = $this->select('COUNT(*) as total_sessions, COUNT(DISTINCT day_of_week) as days_covered');
        
        if ($classId) {
            $builder->where('class_id', $classId);
        }
        
        $builder->where('is_active', 1);
        
        return $builder->first();
    }

    /**
     * Obtenir tous les emplois du temps actifs
     */
    public function getActiveTimetables()
    {
        return $this->select('timetables.*, classes.name as class_name, subjects.name as subject_name, 
                             CONCAT(teachers.first_name, " ", teachers.last_name) as teacher_name')
                   ->join('classes', 'classes.id = timetables.class_id')
                   ->join('subjects', 'subjects.id = timetables.subject_id')
                   ->join('teachers', 'teachers.id = timetables.teacher_id', 'left')
                   ->where('timetables.is_active', 1)
                   ->orderBy('timetables.day_of_week', 'ASC')
                   ->orderBy('timetables.start_time', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les emplois du temps filtrés pour impression
     */
    public function getFilteredTimetables($filters)
    {
        $builder = $this->select('timetables.*, classes.name as class_name, subjects.name as subject_name, 
                                 CONCAT(teachers.first_name, " ", teachers.last_name) as teacher_name,
                                 classes.code as class_code, subjects.code as subject_code')
                       ->join('classes', 'classes.id = timetables.class_id')
                       ->join('subjects', 'subjects.id = timetables.subject_id')
                       ->join('teachers', 'teachers.id = timetables.teacher_id', 'left')
                       ->where('timetables.is_active', 1);

        // Filtres optionnels
        if (!empty($filters['class_id'])) {
            $builder->where('timetables.class_id', $filters['class_id']);
        }

        if (!empty($filters['teacher_id'])) {
            $builder->where('timetables.teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['subject_id'])) {
            $builder->where('timetables.subject_id', $filters['subject_id']);
        }

        // Filtre par année académique (si applicable)
        if (!empty($filters['academic_year'])) {
            // Note: Si la table timetables a une colonne academic_year, l'ajouter ici
            // $builder->where('timetables.academic_year', $filters['academic_year']);
        }

        // Filtre par période (si applicable)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            // Note: Si la table timetables a des colonnes de dates, les ajouter ici
            // $builder->where('timetables.date >=', $filters['start_date'])
            //         ->where('timetables.date <=', $filters['end_date']);
        }

        return $builder->orderBy('timetables.day_of_week', 'ASC')
                      ->orderBy('timetables.start_time', 'ASC')
                      ->orderBy('classes.name', 'ASC')
                      ->findAll();
    }
}
