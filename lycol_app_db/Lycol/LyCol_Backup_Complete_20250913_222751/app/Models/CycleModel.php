<?php

namespace App\Models;

use CodeIgniter\Model;

class CycleModel extends Model
{
    protected $table = 'cycles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'code', 'description', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'code' => 'required|min_length[2]|max_length[20]|is_unique[cycles.code,id,{id}]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Le nom du cycle est requis',
            'min_length' => 'Le nom doit contenir au moins 2 caractères',
            'max_length' => 'Le nom ne peut pas dépasser 100 caractères'
        ],
        'code' => [
            'required' => 'Le code du cycle est requis',
            'min_length' => 'Le code doit contenir au moins 2 caractères',
            'max_length' => 'Le code ne peut pas dépasser 20 caractères',
            'is_unique' => 'Ce code de cycle existe déjà'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir tous les cycles actifs
     */
    public function getActiveCycles()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir un cycle avec ses classes
     */
    public function getCycleWithClasses($id)
    {
        return $this->select('cycles.*, COUNT(classes.id) as class_count')
                   ->join('classes', 'classes.cycle_id = cycles.id', 'left')
                   ->where('cycles.id', $id)
                   ->groupBy('cycles.id')
                   ->first();
    }

    /**
     * Obtenir les statistiques des cycles
     */
    public function getCycleStats()
    {
        $builder = $this->db->table('cycles');
        $builder->select('cycles.*, COUNT(classes.id) as class_count, SUM(classes.capacity) as total_capacity')
                ->join('classes', 'classes.cycle_id = cycles.id', 'left')
                ->where('cycles.is_active', 1)
                ->groupBy('cycles.id')
                ->orderBy('cycles.name', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Vérifier si un code de cycle existe
     */
    public function codeExists($code, $excludeId = null)
    {
        $builder = $this->where('code', $code);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}
