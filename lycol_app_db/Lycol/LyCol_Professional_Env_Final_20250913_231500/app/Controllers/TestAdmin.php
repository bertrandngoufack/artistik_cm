<?php

namespace App\Controllers;

/**
 * Contrôleur de test pour l'administration
 */
class TestAdmin extends BaseController
{
    /**
     * Tableau de bord de test
     */
    public function dashboard()
    {
        // Vérifier l'authentification
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Tableau de bord - KISSAI SCHOOL (TEST)',
            'message' => 'Test du dashboard sans base de données'
        ];

        return view('admin/dashboard', $data);
    }
}
