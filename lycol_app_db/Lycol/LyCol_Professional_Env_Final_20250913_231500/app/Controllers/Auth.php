<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LicenseModel;
use App\Libraries\LicenseGenerator;

/**
 * Contrôleur d'authentification pour LyCol
 */
class Auth extends BaseController
{
    protected $userModel;
    protected $licenseModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->licenseModel = new LicenseModel();
    }



    /**
     * Page de connexion
     */
    public function login()
    {
        // Vérifier si l'utilisateur est déjà connecté
        if (session()->get('user_id') && session()->get('user_role')) {
            // Vérifier le rôle pour rediriger vers la bonne page
            $userRole = session()->get('user_role');
            $allowedRoles = ['admin', 'directeur', 'secretaire', 'enseignant'];
            
            if (in_array($userRole, $allowedRoles)) {
                return redirect()->to('/admin/dashboard');
            }
        }

        return view('auth/login', [
            'title' => 'Connexion - LyCol',
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success')
        ]);
    }

    /**
     * Traitement de la connexion
     */
    public function authenticate()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validation des données
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez corriger les erreurs');
        }

        // Vérifier l'utilisateur
        $user = $this->userModel->where('username', $username)
                               ->where('is_active', 1)
                               ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Identifiants incorrects');
        }

        // Vérifier la licence (avertissement seulement, pas de blocage)
        $licenseValid = $this->checkLicense();
        $licenseWarning = null;
        if (!$licenseValid) {
            $licenseWarning = 'Licence expirée ou invalide - Accès en mode avertissement';
        }

        // Créer la session
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id'],
            'user_role' => $this->getRoleName($user['role_id']),
            'role_name' => $this->getRoleName($user['role_id']),
            'login_time' => time(),
            'license_warning' => $licenseWarning
        ];

        session()->set($sessionData);

        // Mettre à jour la dernière connexion
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Redirection avec avertissement si nécessaire
        if ($licenseWarning) {
            return redirect()->to('/admin/dashboard')->with('warning', $licenseWarning);
        } else {
            return redirect()->to('/admin/dashboard')->with('success', 'Connexion réussie');
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'Déconnexion réussie');
    }

    /**
     * Page d'accès parents
     */
    public function parents()
    {
        return view('auth/parents', [
            'title' => 'Espace Parents - LyCol',
            'error' => session()->getFlashdata('error')
        ]);
    }

    /**
     * Authentification parents
     */
    public function authenticateParent()
    {
        $matricule = $this->request->getPost('matricule');
        $birthYear = $this->request->getPost('birth_year');

        // Validation
        if (empty($matricule) || empty($birthYear)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez saisir le matricule et l\'année de naissance');
        }

        // Vérifier l'élève
        $studentModel = new \App\Models\StudentModel();
        $student = $studentModel->where('matricule', $matricule)
                               ->where('status', 'ACTIVE')
                               ->first();

        if (!$student) {
            return redirect()->back()->withInput()->with('error', 'Élève non trouvé');
        }

        $studentBirthYear = date('Y', strtotime($student['birth_date']));
        if ($studentBirthYear != $birthYear) {
            return redirect()->back()->withInput()->with('error', 'Année de naissance incorrecte');
        }

        // Créer la session parent
        $sessionData = [
            'parent_mode' => true,
            'student_id' => $student['id'],
            'student_name' => $student['first_name'] . ' ' . $student['last_name'],
            'matricule' => $student['matricule'],
            'class_id' => $student['current_class_id']
        ];

        session()->set($sessionData);

        return redirect()->to('/parents/dashboard')->with('success', 'Accès autorisé');
    }

    /**
     * Interface mobile pour enseignants
     */
    public function mobile()
    {
        return view('auth/mobile', [
            'title' => 'Interface Mobile - LyCol',
            'error' => session()->getFlashdata('error')
        ]);
    }

    /**
     * Authentification mobile
     */
    public function authenticateMobile()
    {
        $teacherCode = $this->request->getPost('teacher_code');

        if (empty($teacherCode)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez saisir votre code enseignant');
        }

        // Vérifier l'enseignant
        $user = $this->userModel->where('username', $teacherCode)
                               ->where('role_id', 4) // Rôle ENSEIGNANT
                               ->where('is_active', 1)
                               ->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Code enseignant invalide');
        }

        // Créer la session mobile
        $sessionData = [
            'mobile_mode' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id']
        ];

        session()->set($sessionData);

        return redirect()->to('/mobile/grades')->with('success', 'Connexion mobile réussie');
    }

    /**
     * Vérifier la licence
     */
    private function checkLicense()
    {
        // Récupérer la licence active
        $license = $this->licenseModel->where('status', 'ACTIVE')->first();
        
        if (!$license) {
            return false;
        }

        // Vérifier la validité
        $validation = LicenseGenerator::validateLicenseKey(
            $license['license_key'],
            $license['client_id'],
            $license['license_type'],
            $license['expiry_date'],
            'KISSAI_SECRET_KEY_2025'
        );

        return $validation['valid'];
    }

    /**
     * Obtenir le nom du rôle
     */
    private function getRoleName($roleId)
    {
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->find($roleId);
        return $role ? $role['name'] : 'UNKNOWN';
    }

    /**
     * Page de changement de mot de passe
     */
    public function changePassword()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        return view('auth/change_password', [
            'title' => 'Changer le mot de passe - LyCol',
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success')
        ]);
    }

    /**
     * Traitement du changement de mot de passe
     */
    public function updatePassword()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validation
        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'Les mots de passe ne correspondent pas');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'Le mot de passe doit contenir au moins 6 caractères');
        }

        // Vérifier l'ancien mot de passe
        $user = $this->userModel->find(session()->get('user_id'));
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect');
        }

        // Mettre à jour le mot de passe
        $this->userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success', 'Mot de passe modifié avec succès');
    }
}
