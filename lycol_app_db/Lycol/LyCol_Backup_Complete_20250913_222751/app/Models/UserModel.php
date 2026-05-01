<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle User pour LyCol
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'username', 'email', 'password', 'first_name', 'last_name',
        'phone', 'avatar', 'role_id', 'is_active', 'last_login'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name' => 'required|min_length[2]|max_length[100]',
        'role_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Le nom d\'utilisateur est requis',
            'min_length' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères',
            'max_length' => 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères',
            'is_unique' => 'Ce nom d\'utilisateur existe déjà'
        ],
        'email' => [
            'required' => 'L\'email est requis',
            'valid_email' => 'Veuillez saisir un email valide',
            'is_unique' => 'Cet email existe déjà'
        ],
        'password' => [
            'required' => 'Le mot de passe est requis',
            'min_length' => 'Le mot de passe doit contenir au moins 6 caractères'
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
        'role_id' => [
            'required' => 'Le rôle est requis',
            'integer' => 'Le rôle doit être un nombre entier'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir un utilisateur avec son rôle
     */
    public function getUserWithRole($id)
    {
        return $this->select('users.*, roles.name as role_name, roles.permissions')
                   ->join('roles', 'roles.id = users.role_id')
                   ->where('users.id', $id)
                   ->first();
    }

    /**
     * Obtenir tous les utilisateurs avec leurs rôles
     */
    public function getAllUsersWithRoles()
    {
        return $this->select('users.id, users.username, users.email, users.first_name, users.last_name, users.avatar, users.role_id, users.is_active, users.last_login, users.created_at, roles.name as role_name')
                   ->join('roles', 'roles.id = users.role_id', 'left')
                   ->orderBy('users.first_name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtenir les utilisateurs par rôle
     */
    public function getUsersByRole($roleId)
    {
        return $this->where('role_id', $roleId)
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Obtenir les enseignants actifs
     */
    public function getActiveTeachers()
    {
        return $this->where('role_id', 4) // Rôle ENSEIGNANT
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Vérifier si un utilisateur a une permission
     */
    public function hasPermission($userId, $permission)
    {
        $user = $this->getUserWithRole($userId);
        if (!$user) {
            return false;
        }

        $permissions = json_decode($user['permissions'], true);
        
        // SUPER_ADMIN a tous les droits
        if ($user['role_name'] === 'SUPER_ADMIN') {
            return true;
        }

        // Vérifier la permission spécifique
        if (isset($permissions[$permission])) {
            return $permissions[$permission] === true || 
                   (is_array($permissions[$permission]) && 
                    (isset($permissions[$permission]['read']) || isset($permissions[$permission]['write'])));
        }

        return false;
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUser($data)
    {
        // Hasher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->insert($data);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser($id, $data)
    {
        // Si le mot de passe est fourni, le hasher
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivateUser($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Activer un utilisateur
     */
    public function activateUser($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Obtenir les statistiques des utilisateurs
     */
    public function getUserStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('is_active', 1)->countAllResults(),
            'inactive' => $this->where('is_active', 0)->countAllResults(),
            'by_role' => $this->select('roles.name as role_name, COUNT(*) as count')
                             ->join('roles', 'roles.id = users.role_id')
                             ->groupBy('roles.id')
                             ->findAll()
        ];
    }

    /**
     * Rechercher des utilisateurs
     */
    public function searchUsers($query)
    {
        return $this->like('username', $query)
                   ->orLike('email', $query)
                   ->orLike('first_name', $query)
                   ->orLike('last_name', $query)
                   ->findAll();
    }

    /**
     * Obtenir les utilisateurs récemment connectés
     */
    public function getRecentlyLoggedIn($limit = 10)
    {
        return $this->where('last_login IS NOT NULL')
                   ->orderBy('last_login', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Vérifier si un nom d'utilisateur existe
     */
    public function usernameExists($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Vérifier si un email existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les utilisateurs par page avec filtres
     */
    public function getUsersPaginated($page = 1, $perPage = 20, $search = null, $role_id = null, $status = null)
    {
        $builder = $this->select('users.id, users.username, users.email, users.first_name, users.last_name, users.avatar, users.role_id, users.is_active, users.last_login, users.created_at, roles.name as role_name')
                       ->join('roles', 'roles.id = users.role_id', 'left');
        
        // Filtre de recherche
        if ($search) {
            $builder->groupStart()
                    ->like('users.username', $search)
                    ->orLike('users.email', $search)
                    ->orLike('users.first_name', $search)
                    ->orLike('users.last_name', $search)
                    ->orLike('roles.name', $search)
                    ->groupEnd();
        }
        
        // Filtre par rôle
        if ($role_id) {
            $builder->where('users.role_id', $role_id);
        }
        
        // Filtre par statut
        if ($status !== null) {
            $builder->where('users.is_active', $status);
        }
        
        return $builder->orderBy('users.created_at', 'DESC')
                      ->paginate($perPage, 'default', $page);
    }

    /**
     * Obtenir le pager pour la pagination
     */
    public function getUsersPager()
    {
        return $this->pager;
    }

    /**
     * Obtenir les utilisateurs récents
     */
    public function getRecentUsers($limit = 5)
    {
        return $this->select('users.id, users.username, users.first_name, users.last_name, users.email, users.is_active, users.last_login, users.created_at, roles.name as role_name')
                   ->join('roles', 'roles.id = users.role_id', 'left')
                   ->orderBy('users.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtenir les connexions d'aujourd'hui
     */
    public function getTodayLogins()
    {
        return $this->where('DATE(last_login)', date('Y-m-d'))
                   ->countAllResults();
    }

}




