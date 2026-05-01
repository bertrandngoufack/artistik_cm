<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle Class pour LyCol
 */
class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'code', 'cycle_id', 'level', 'capacity', 'is_active', 'description'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'code' => 'required|min_length[2]|max_length[20]|is_unique[classes.code,id,{id}]',
        'cycle_id' => 'required|integer',
        'level' => 'required|integer|greater_than[0]',
        'capacity' => 'required|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Le nom de la classe est requis',
            'min_length' => 'Le nom doit contenir au moins 2 caractères',
            'max_length' => 'Le nom ne peut pas dépasser 100 caractères'
        ],
        'code' => [
            'required' => 'Le code de la classe est requis',
            'min_length' => 'Le code doit contenir au moins 2 caractères',
            'max_length' => 'Le code ne peut pas dépasser 20 caractères',
            'is_unique' => 'Ce code de classe existe déjà'
        ],
        'cycle_id' => [
            'required' => 'Le cycle est requis',
            'integer' => 'Le cycle doit être un nombre entier'
        ],
        'level' => [
            'required' => 'Le niveau est requis',
            'integer' => 'Le niveau doit être un nombre entier',
            'greater_than' => 'Le niveau doit être supérieur à 0'
        ],
        'capacity' => [
            'required' => 'La capacité est requise',
            'integer' => 'La capacité doit être un nombre entier',
            'greater_than' => 'La capacité doit être supérieure à 0'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir une classe avec son cycle
     */
    public function getClassWithCycle($id)
    {
        return $this->select('classes.*, cycles.name as cycle_name')
                   ->join('cycles', 'cycles.id = classes.cycle_id')
                   ->where('classes.id', $id)
                   ->first();
    }

    /**
     * Obtenir toutes les classes avec leurs cycles
     */
    public function getAllClassesWithCycles()
    {
        return $this->select('classes.*, cycles.name as cycle_name')
                   ->join('cycles', 'cycles.id = classes.cycle_id')
                   ->where('classes.is_active', 1)
                   ->orderBy('classes.level', 'ASC')
                   ->orderBy('classes.name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les classes actives
     */
    public function getActiveClasses()
    {
        return $this->where('is_active', 1)
                   ->orderBy('level', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les classes par cycle
     */
    public function getClassesByCycle($cycleId)
    {
        return $this->where('cycle_id', $cycleId)
                   ->where('is_active', 1)
                   ->orderBy('level', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les classes par niveau
     */
    public function getClassesByLevel($level)
    {
        return $this->where('level', $level)
                   ->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Rechercher des classes
     */
    public function searchClasses($query)
    {
        return $this->like('name', $query)
                   ->orLike('code', $query)
                   ->where('is_active', 1)
                   ->orderBy('level', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les statistiques des classes
     */
    public function getClassStats()
    {
        return [
            'total' => $this->where('is_active', 1)->countAllResults(),
            'by_cycle' => $this->select('cycles.name as cycle_name, COUNT(*) as count')
                              ->join('cycles', 'cycles.id = classes.cycle_id')
                              ->where('classes.is_active', 1)
                              ->groupBy('cycles.id')
                              ->findAll(),
            'by_level' => $this->select('level, COUNT(*) as count')
                              ->where('is_active', 1)
                              ->groupBy('level')
                              ->orderBy('level', 'ASC')
                              ->findAll()
        ];
    }

    /**
     * Vérifier si un code de classe existe
     */
    public function codeExists($code, $excludeId = null)
    {
        $builder = $this->where('code', $code);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les classes par page
     */
    public function getClassesPaginated($page = 1, $perPage = 20)
    {
        return $this->select('classes.*, cycles.name as cycle_name')
                   ->join('cycles', 'cycles.id = classes.cycle_id')
                   ->where('classes.is_active', 1)
                   ->orderBy('classes.level', 'ASC')
                   ->orderBy('classes.name', 'ASC')
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * Obtenir le pager pour la pagination
     */
    public function getClassesPager()
    {
        return $this->pager;
    }

    /**
     * Obtenir les classes avec le nombre d'élèves
     */
    public function getClassesWithStudentCount()
    {
        return $this->select('classes.*, cycles.name as cycle_name, COUNT(students.id) as student_count')
                   ->join('cycles', 'cycles.id = classes.cycle_id')
                   ->join('students', 'students.current_class_id = classes.id', 'left')
                   ->where('classes.is_active', 1)
                   ->where('students.status', 'ACTIVE')
                   ->groupBy('classes.id')
                   ->orderBy('classes.level', 'ASC')
                   ->orderBy('classes.name', 'ASC')
                   ->findAll();
    }

    /**
     * Créer une nouvelle classe
     */
    public function createClass($data)
    {
        return $this->insert($data);
    }

    /**
     * Mettre à jour une classe
     */
    public function updateClass($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Désactiver une classe
     */
    public function deactivateClass($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Activer une classe
     */
    public function activateClass($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Obtenir les statistiques des classes
     */
    public function getClassStatistics()
    {
        return [
            'total' => $this->where('is_active', 1)->countAllResults(),
            'by_cycle' => $this->select('cycles.name as cycle_name, COUNT(*) as count')
                              ->join('cycles', 'cycles.id = classes.cycle_id')
                              ->where('classes.is_active', 1)
                              ->groupBy('cycles.id')
                              ->findAll(),
            'by_level' => $this->select('level, COUNT(*) as count')
                              ->where('is_active', 1)
                              ->groupBy('level')
                              ->orderBy('level', 'ASC')
                              ->findAll(),
            'with_students' => $this->select('classes.name, classes.code, COUNT(students.id) as student_count')
                                  ->join('students', 'students.current_class_id = classes.id', 'left')
                                  ->where('classes.is_active', 1)
                                  ->where('students.status', 'ACTIVE')
                                  ->groupBy('classes.id')
                                  ->orderBy('student_count', 'DESC')
                                  ->findAll()
        ];
    }
}




