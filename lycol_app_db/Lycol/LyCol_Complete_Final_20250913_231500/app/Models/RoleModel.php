<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'description', 'permissions', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[50]|is_unique[roles.name,id,{id}]',
        'description' => 'required|max_length[200]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getRolesPaginated($page = 1, $perPage = 20)
    {
        return $this->orderBy('name', 'ASC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getRolesPager()
    {
        return $this->pager;
    }

    public function getActiveRoles()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getRoleWithPermissions($id)
    {
        $role = $this->find($id);
        if ($role) {
            $role['permissions'] = json_decode($role['permissions'], true);
        }
        return $role;
    }

    public function getRoleStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('is_active', 1)->countAllResults()
        ];
    }
    
    /**
     * Obtenir les rôles avec le nombre d'utilisateurs
     */
    public function getRolesWithUserCount()
    {
        $roles = $this->select('roles.*, COUNT(users.id) as user_count')
                     ->join('users', 'users.role_id = roles.id', 'left')
                     ->groupBy('roles.id')
                     ->orderBy('roles.name', 'ASC')
                     ->findAll();
        
        // Décoder les permissions JSON pour chaque rôle
        foreach ($roles as &$role) {
            if (isset($role['permissions']) && $role['permissions']) {
                $role['permissions'] = json_decode($role['permissions'], true) ?: [];
            } else {
                $role['permissions'] = [];
            }
        }
        
        return $roles;
    }
}




