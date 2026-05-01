<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class SettingModel
 * Modèle pour la gestion des paramètres système
 * Expert Senior PHP/CodeIgniter
 */
class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'description',
        'category',
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
        'setting_key' => 'required|min_length[3]|max_length[100]|is_unique[settings.setting_key,id,{id}]',
        'setting_value' => 'required|max_length[500]',
        'description' => 'required|max_length[255]',
        'category' => 'required|in_list[system,academic,financial,communication,security]'
    ];

    protected $validationMessages = [
        'setting_key' => [
            'required' => 'La clé du paramètre est requise',
            'min_length' => 'La clé du paramètre doit contenir au moins 3 caractères',
            'max_length' => 'La clé du paramètre ne peut pas dépasser 100 caractères',
            'is_unique' => 'Cette clé de paramètre existe déjà'
        ],
        'setting_value' => [
            'required' => 'La valeur du paramètre est requise',
            'max_length' => 'La valeur du paramètre ne peut pas dépasser 500 caractères'
        ],
        'description' => [
            'required' => 'La description du paramètre est requise',
            'max_length' => 'La description ne peut pas dépasser 255 caractères'
        ],
        'category' => [
            'required' => 'La catégorie du paramètre est requise',
            'in_list' => 'La catégorie doit être l\'une des suivantes : system, academic, financial, communication, security'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Récupérer un paramètre par sa clé
     */
    public function getByKey($key)
    {
        return $this->where('setting_key', $key)
                   ->where('is_active', 1)
                   ->first();
    }

    /**
     * Récupérer la valeur d'un paramètre par sa clé
     */
    public function getValue($key, $default = null)
    {
        $setting = $this->getByKey($key);
        return $setting ? $setting['setting_value'] : $default;
    }

    /**
     * Récupérer tous les paramètres d'une catégorie
     */
    public function getByCategory($category)
    {
        return $this->where('category', $category)
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Mettre à jour un paramètre par sa clé
     */
    public function updateByKey($key, $value, $description = null)
    {
        $setting = $this->getByKey($key);
        if ($setting) {
            $data = ['setting_value' => $value];
            if ($description) {
                $data['description'] = $description;
            }
            return $this->update($setting['id'], $data);
        }
        return false;
    }

    /**
     * Créer ou mettre à jour un paramètre
     */
    public function setValue($key, $value, $description = null, $category = 'system')
    {
        $setting = $this->getByKey($key);
        if ($setting) {
            // Mettre à jour
            $data = ['setting_value' => $value];
            if ($description) {
                $data['description'] = $description;
            }
            return $this->update($setting['id'], $data);
        } else {
            // Créer
            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description ?: 'Paramètre système',
                'category' => $category,
                'is_active' => 1
            ];
            return $this->insert($data);
        }
    }

    /**
     * Récupérer tous les paramètres actifs
     */
    public function getActiveSettings()
    {
        return $this->where('is_active', 1)
                   ->orderBy('category', 'ASC')
                   ->orderBy('setting_key', 'ASC')
                   ->findAll();
    }

    /**
     * Rechercher des paramètres
     */
    public function searchSettings($search)
    {
        return $this->like('setting_key', $search)
                   ->orLike('description', $search)
                   ->orLike('category', $search)
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Récupérer les statistiques des paramètres
     */
    public function getSettingsStats()
    {
        $stats = [];
        
        // Total des paramètres
        $stats['total'] = $this->countAll();
        
        // Paramètres actifs
        $stats['active'] = $this->where('is_active', 1)->countAllResults();
        
        // Paramètres par catégorie
        $categories = $this->select('category, COUNT(*) as count')
                          ->where('is_active', 1)
                          ->groupBy('category')
                          ->findAll();
        
        $stats['by_category'] = [];
        foreach ($categories as $cat) {
            $stats['by_category'][$cat['category']] = $cat['count'];
        }
        
        return $stats;
    }
}




