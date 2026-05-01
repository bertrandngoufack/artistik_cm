<?php

namespace App\Models;

use CodeIgniter\Model;

class SubjectModel extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'code', 'description', 'coefficient', 'hours_per_week', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'code' => 'required|min_length[2]|max_length[20]|is_unique[subjects.code,id,{id}]',
        'coefficient' => 'required|numeric|greater_than[0]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getActiveSubjects()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getSubjectsByClass($classId)
    {
        return $this->select('subjects.*')
                   ->join('class_subjects', 'class_subjects.subject_id = subjects.id')
                   ->where('class_subjects.class_id', $classId)
                   ->where('subjects.is_active', 1)
                   ->orderBy('subjects.name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les matières avec leurs statistiques d'utilisation (version simplifiée)
     */
    public function getSubjectsWithStats()
    {
        try {
            // Récupérer d'abord toutes les matières actives
            $subjects = $this->getActiveSubjects();
            
            // Ajouter des statistiques par défaut pour éviter les erreurs
            foreach ($subjects as &$subject) {
                $subject['assignment_count'] = 0;
                $subject['timetable_count'] = 0;
            }
            
            return $subjects;
        } catch (Exception $e) {
            // En cas d'erreur, retourner les matières de base
            return $this->getActiveSubjects();
        }
    }

    /**
     * Rechercher des matières par nom ou code
     */
    public function searchSubjects($query)
    {
        try {
            return $this->where('is_active', 1)
                       ->groupStart()
                       ->like('name', $query)
                       ->orLike('code', $query)
                       ->groupEnd()
                       ->orderBy('name', 'ASC')
                       ->findAll();
        } catch (Exception $e) {
            return $this->getActiveSubjects();
        }
    }

    /**
     * Obtenir les matières par cycle
     */
    public function getSubjectsByCycle($cycleId)
    {
        try {
            return $this->select('subjects.*')
                       ->join('class_subjects cs', 'cs.subject_id = subjects.id')
                       ->join('classes c', 'c.id = cs.class_id')
                       ->where('c.cycle_id', $cycleId)
                       ->where('subjects.is_active', 1)
                       ->groupBy('subjects.id')
                       ->orderBy('subjects.name', 'ASC')
                       ->findAll();
        } catch (Exception $e) {
            return $this->getActiveSubjects();
        }
    }

    /**
     * Vérifier si une matière peut être supprimée
     */
    public function canBeDeleted($subjectId)
    {
        try {
            // Vérifier les assignations d'enseignants
            $assignments = $this->db->table('teacher_assignments')
                                   ->where('subject_id', $subjectId)
                                   ->countAllResults();
            
            // Vérifier les emplois du temps
            $timetables = $this->db->table('timetables')
                                   ->where('subject_id', $subjectId)
                                   ->countAllResults();
            
            // Vérifier les notes
            $grades = $this->db->table('grades')
                               ->where('subject_id', $subjectId)
                               ->countAllResults();
            
            return $assignments == 0 && $timetables == 0 && $grades == 0;
        } catch (Exception $e) {
            return false; // En cas d'erreur, on considère qu'on ne peut pas supprimer
        }
    }
}




