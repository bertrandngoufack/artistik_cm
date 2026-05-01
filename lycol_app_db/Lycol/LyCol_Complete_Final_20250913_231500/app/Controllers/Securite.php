<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class Securite extends BaseController
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Module Sécurité',
            'active_users' => $this->userModel->where('is_active', 1)->countAllResults(),
            'active_sessions' => 0, // À implémenter avec les sessions
            'failed_attempts' => 0, // À implémenter avec les tentatives de connexion
            'total_roles' => $this->roleModel->countAllResults(),
            'recent_users' => $this->userModel->getRecentUsers(),
            'roles' => $this->roleModel->getRolesWithUserCount(),
            'recent_activities' => $this->getRecentActivities()
        ];

        return view('admin/securite/index', $data);
    }

    public function users()
    {
        $page = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search');
        $role_id = $this->request->getGet('role_id');
        $status = $this->request->getGet('status');
        
        $data = [
            'title' => 'Gestion des Utilisateurs',
            'users' => $this->userModel->getUsersPaginated($page, 20, $search, $role_id, $status),
            'pager' => $this->userModel->getUsersPager(),
            'roles' => $this->roleModel->getActiveRoles(),
            'total_users' => $this->userModel->countAllResults(),
            'active_users' => $this->userModel->where('is_active', 1)->countAllResults(),
            'inactive_users' => $this->userModel->where('is_active', 0)->countAllResults(),
            'today_logins' => $this->getTodayLogins(),
            'search' => $search,
            'role_id' => $role_id,
            'status' => $status
        ];

        return view('admin/securite/users', $data);
    }

    public function createUser()
    {
        $data = [
            'title' => 'Nouvel Utilisateur',
            'roles' => $this->roleModel->getActiveRoles()
        ];

        return view('admin/securite/create_user', $data);
    }

    public function storeUser()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'role_id' => 'required|integer',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'role_id' => $this->request->getPost('role_id'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active' => 1
        ];

        if ($this->userModel->insert($userData)) {
            return redirect()->to('admin/securite/users')->with('success', 'Utilisateur créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editUser($id)
    {
        $user = $this->userModel->getUserWithRole($id);
        
        if (!$user) {
            return redirect()->to('admin/securite/users')->with('error', 'Utilisateur non trouvé');
        }

        $data = [
            'title' => 'Modifier l\'Utilisateur',
            'user' => $user,
            'roles' => $this->roleModel->getActiveRoles()
        ];

        return view('admin/securite/edit_user', $data);
    }

    public function updateUser($id)
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,' . $id . ']',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'role_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'role_id' => $this->request->getPost('role_id')
        ];

        // Si un nouveau mot de passe est fourni
        if ($this->request->getPost('password')) {
            $userData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $userData)) {
            return redirect()->to('admin/securite/users')->with('success', 'Utilisateur mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteUser($id)
    {
        if ($this->userModel->delete($id)) {
            return redirect()->to('admin/securite/users')->with('success', 'Utilisateur supprimé avec succès');
        } else {
            return redirect()->to('admin/securite/users')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function roles()
    {
        $page = $this->request->getGet('page') ?? 1;
        
        $data = [
            'title' => 'Gestion des Rôles',
            'roles' => $this->roleModel->getRolesPaginated($page, 20),
            'pager' => $this->roleModel->getRolesPager(),
            'total_roles' => $this->roleModel->countAllResults(),
            'active_roles' => $this->roleModel->where('is_active', 1)->countAllResults(),
            'inactive_roles' => $this->roleModel->where('is_active', 0)->countAllResults(),
            'assigned_users' => $this->getAssignedUsersCount()
        ];

        return view('admin/securite/roles', $data);
    }

    public function createRole()
    {
        $data = [
            'title' => 'Nouveau Rôle'
        ];

        return view('admin/securite/create_role', $data);
    }

    public function storeRole()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]|is_unique[roles.name]',
            'description' => 'required|max_length[200]',
            'permissions' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'permissions' => json_encode($this->request->getPost('permissions')),
            'is_active' => 1
        ];

        if ($this->roleModel->insert($roleData)) {
            return redirect()->to('admin/securite/roles')->with('success', 'Rôle créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function editRole($id)
    {
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('admin/securite/roles')->with('error', 'Rôle non trouvé');
        }

        $data = [
            'title' => 'Modifier le Rôle',
            'role' => $role
        ];

        return view('admin/securite/edit_role', $data);
    }

    public function updateRole($id)
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]|is_unique[roles.name,id,' . $id . ']',
            'description' => 'required|max_length[200]',
            'permissions' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'permissions' => json_encode($this->request->getPost('permissions'))
        ];

        if ($this->roleModel->update($id, $roleData)) {
            return redirect()->to('admin/securite/roles')->with('success', 'Rôle mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteRole($id)
    {
        if ($this->roleModel->delete($id)) {
            return redirect()->to('admin/securite/roles')->with('success', 'Rôle supprimé avec succès');
        } else {
            return redirect()->to('admin/securite/roles')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function logs()
    {
        $data = [
            'title' => 'Journaux d\'Audit',
            'logs' => $this->getAuditLogs()
        ];

        return view('admin/securite/logs', $data);
    }

    private function getSecurityStats()
    {
        return [
            'totalUsers' => $this->userModel->countAllResults(),
            'activeUsers' => $this->userModel->where('is_active', 1)->countAllResults(),
            'totalRoles' => $this->roleModel->countAllResults(),
            'recentLogins' => $this->getRecentLogins()
        ];
    }

    private function getAuditLogs()
    {
        // Simulation des journaux d'audit
        return [
            ['action' => 'Connexion', 'user' => 'admin', 'timestamp' => date('Y-m-d H:i:s')],
            ['action' => 'Modification utilisateur', 'user' => 'admin', 'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
            ['action' => 'Création rôle', 'user' => 'admin', 'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))]
        ];
    }

    private function getRecentLogins()
    {
        // Simulation des connexions récentes
        return [
            ['user' => 'admin', 'timestamp' => date('Y-m-d H:i:s')],
            ['user' => 'directeur', 'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))],
            ['user' => 'secretaire', 'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))]
        ];
    }
    
    private function getRecentActivities()
    {
        // Retourner des données simulées pour les activités récentes
        return [
            [
                'username' => 'admin',
                'action' => 'Connexion',
                'module' => 'securite',
                'created_at' => date('Y-m-d H:i:s'),
                'ip_address' => '127.0.0.1'
            ],
            [
                'username' => 'directeur',
                'action' => 'Modification utilisateur',
                'module' => 'securite',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'ip_address' => '127.0.0.1'
            ],
            [
                'username' => 'secretaire',
                'action' => 'Création d\'étudiant',
                'module' => 'scolarite',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'ip_address' => '127.0.0.1'
            ],
            [
                'username' => 'enseignant',
                'action' => 'Saisie de notes',
                'module' => 'examens',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'ip_address' => '127.0.0.1'
            ],
            [
                'username' => 'comptable',
                'action' => 'Enregistrement paiement',
                'module' => 'economat',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'ip_address' => '127.0.0.1'
            ]
        ];
    }
    
    private function getTodayLogins()
    {
        // Compter les connexions d'aujourd'hui
        try {
            return $this->userModel->where('DATE(last_login)', date('Y-m-d'))->countAllResults();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getAssignedUsersCount()
    {
        // Compter le total d'utilisateurs assignés à des rôles
        try {
            return $this->userModel->where('role_id IS NOT NULL')->countAllResults();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function permissions()
    {
        $data = [
            'title' => 'Gestion des Permissions',
            'permissions' => $this->getAvailablePermissions(),
            'roles' => $this->roleModel->getActiveRoles()
        ];
        
        return view('admin/securite/permissions', $data);
    }
    
    public function audit()
    {
        $page = $this->request->getGet('page') ?? 1;
        $module = $this->request->getGet('module');
        $action = $this->request->getGet('action');
        $user_id = $this->request->getGet('user_id');
        
        $data = [
            'title' => 'Audit de Sécurité',
            'logs' => $this->getAuditLogsPaginated($page, 50, $module, $action, $user_id),
            'modules' => $this->getAvailableModules(),
            'actions' => $this->getAvailableActions(),
            'users' => $this->userModel->getAllUsersWithRoles(),
            'getActionClass' => [$this, 'getActionClass']
        ];
        
        return view('admin/securite/audit', $data);
    }
    
    private function getAvailablePermissions()
    {
        return [
            'economat' => [
                'economat.view' => 'Voir le module économat',
                'economat.create' => 'Créer des éléments économat',
                'economat.edit' => 'Modifier des éléments économat',
                'economat.delete' => 'Supprimer des éléments économat',
                'economat.export' => 'Exporter les données économat'
            ],
            'scolarite' => [
                'scolarite.view' => 'Voir le module scolarité',
                'scolarite.create' => 'Créer des éléments scolarité',
                'scolarite.edit' => 'Modifier des éléments scolarité',
                'scolarite.delete' => 'Supprimer des éléments scolarité',
                'scolarite.export' => 'Exporter les données scolarité'
            ],
            'etudes' => [
                'etudes.view' => 'Voir le module études',
                'etudes.create' => 'Créer des éléments études',
                'etudes.edit' => 'Modifier des éléments études',
                'etudes.delete' => 'Supprimer des éléments études',
                'etudes.export' => 'Exporter les données études'
            ],
            'examens' => [
                'examens.view' => 'Voir le module examens',
                'examens.create' => 'Créer des éléments examens',
                'examens.edit' => 'Modifier des éléments examens',
                'examens.delete' => 'Supprimer des éléments examens',
                'examens.export' => 'Exporter les données examens'
            ],
            'enseignants' => [
                'enseignants.view' => 'Voir le module enseignants',
                'enseignants.create' => 'Créer des éléments enseignants',
                'enseignants.edit' => 'Modifier des éléments enseignants',
                'enseignants.delete' => 'Supprimer des éléments enseignants',
                'enseignants.export' => 'Exporter les données enseignants'
            ],
            'statistiques' => [
                'statistiques.view' => 'Voir les statistiques',
                'statistiques.export' => 'Exporter les statistiques',
                'statistiques.admin' => 'Accès administrateur aux statistiques'
            ],
            'messagerie' => [
                'messagerie.view' => 'Voir le module messagerie',
                'messagerie.send' => 'Envoyer des messages',
                'messagerie.templates' => 'Gérer les templates',
                'messagerie.settings' => 'Configurer la messagerie'
            ],
            'securite' => [
                'securite.view' => 'Voir le module sécurité',
                'securite.users' => 'Gérer les utilisateurs',
                'securite.roles' => 'Gérer les rôles',
                'securite.permissions' => 'Gérer les permissions',
                'securite.audit' => 'Voir les logs d\'audit'
            ]
        ];
    }
    
    private function getAvailableModules()
    {
        return [
            'economat' => 'Économat',
            'scolarite' => 'Scolarité',
            'etudes' => 'Études',
            'examens' => 'Examens',
            'enseignants' => 'Enseignants',
            'statistiques' => 'Statistiques',
            'messagerie' => 'Messagerie',
            'securite' => 'Sécurité'
        ];
    }
    
    private function getAvailableActions()
    {
        return [
            'CREATE' => 'Création',
            'READ' => 'Lecture',
            'UPDATE' => 'Modification',
            'DELETE' => 'Suppression',
            'LOGIN' => 'Connexion',
            'LOGOUT' => 'Déconnexion',
            'EXPORT' => 'Export',
            'IMPORT' => 'Import'
        ];
    }
    
    private function getAuditLogsPaginated($page = 1, $perPage = 50, $module = null, $action = null, $user_id = null)
    {
        try {
            $auditLogModel = new \App\Models\AuditLogModel();
            return $auditLogModel->getLogsPaginated($page, $perPage, $module, $action, $user_id);
        } catch (Exception $e) {
            return [];
        }
    }

    // Méthodes pour les actions spécifiques d'utilisateur
    public function viewUser($id)
    {
        $user = $this->userModel->getUserWithRole($id);
        
        if (!$user) {
            return redirect()->to('admin/securite/users')->with('error', 'Utilisateur non trouvé');
        }

        $data = [
            'title' => 'Détails de l\'Utilisateur',
            'user' => $user
        ];

        return view('admin/securite/view_user', $data);
    }

    public function userPermissions($id)
    {
        $user = $this->userModel->getUserWithRole($id);
        
        if (!$user) {
            return redirect()->to('admin/securite/users')->with('error', 'Utilisateur non trouvé');
        }

        $data = [
            'title' => 'Permissions de l\'Utilisateur',
            'user' => $user,
            'availablePermissions' => $this->getAvailablePermissions()
        ];

        return view('admin/securite/user_permissions', $data);
    }

    public function updateUserPermissions($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('admin/securite/users')->with('error', 'Utilisateur non trouvé');
        }

        $permissions = $this->request->getPost('permissions') ?? [];
        
        // Mettre à jour les permissions de l'utilisateur
        $userData = [
            'permissions' => json_encode($permissions),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->userModel->update($id, $userData);
            return redirect()->to('admin/securite/users')->with('success', 'Permissions mises à jour avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour des permissions: ' . $e->getMessage());
        }
    }

    // Méthodes pour les actions spécifiques de rôle
    public function viewRole($id)
    {
        $role = $this->roleModel->getRoleWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('admin/securite/roles')->with('error', 'Rôle non trouvé');
        }

        $data = [
            'title' => 'Détails du Rôle',
            'role' => $role
        ];

        return view('admin/securite/view_role', $data);
    }

    public function rolePermissions($id)
    {
        $role = $this->roleModel->getRoleWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('admin/securite/roles')->with('error', 'Rôle non trouvé');
        }

        $data = [
            'title' => 'Permissions du Rôle',
            'role' => $role,
            'availablePermissions' => $this->getAvailablePermissions()
        ];

        return view('admin/securite/role_permissions', $data);
    }

    public function updateRolePermissions($id)
    {
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('admin/securite/roles')->with('error', 'Rôle non trouvé');
        }

        $permissions = $this->request->getPost('permissions') ?? [];
        
        // Mettre à jour les permissions du rôle
        $roleData = [
            'permissions' => json_encode($permissions)
        ];

        if ($this->roleModel->update($id, $roleData)) {
            return redirect()->back()->with('success', 'Permissions mises à jour avec succès');
        } else {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour des permissions');
        }
    }
    
    /**
     * Obtenir la classe CSS pour l'affichage des actions
     */
    public function getActionClass($action)
    {
        $actionClasses = [
            'CREATE' => 'is-success',
            'READ' => 'is-info',
            'UPDATE' => 'is-warning',
            'DELETE' => 'is-danger',
            'LOGIN' => 'is-primary',
            'LOGOUT' => 'is-light',
            'EXPORT' => 'is-link',
            'IMPORT' => 'is-info',
            'CREATE' => 'is-success',
            'READ' => 'is-info',
            'UPDATE' => 'is-warning',
            'DELETE' => 'is-danger'
        ];
        
        return $actionClasses[strtoupper($action)] ?? 'is-light';
    }

}




