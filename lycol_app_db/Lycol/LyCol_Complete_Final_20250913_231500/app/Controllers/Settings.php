<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\SettingModel;

/**
 * Class Settings
 * Gestion des paramètres système
 * Expert Senior PHP/CodeIgniter
 */
class Settings extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Page d'accueil des paramètres
     */
    public function index()
    {
        try {
            $data = [
                'title' => 'Paramètres Système - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'settings',
                'settings' => $this->settingModel->findAll()
            ];

            return view('admin/configuration/settings', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des paramètres');
        }
    }

    /**
     * Créer un nouveau paramètre
     */
    public function create()
    {
        try {
            $data = [
                'title' => 'Nouveau Paramètre - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'settings'
            ];

            return view('admin/configuration/settings_create', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire');
        }
    }

    /**
     * Stocker un nouveau paramètre
     */
    public function store()
    {
        try {
            $rules = [
                'setting_key' => 'required|min_length[3]|max_length[100]|is_unique[settings.setting_key]',
                'setting_value' => 'required|max_length[500]',
                'description' => 'required|max_length[255]',
                'category' => 'required|in_list[system,academic,financial,communication,security]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'setting_key' => $this->request->getPost('setting_key'),
                'setting_value' => $this->request->getPost('setting_value'),
                'description' => $this->request->getPost('description'),
                'category' => $this->request->getPost('category'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->settingModel->insert($data);

            return redirect()->to('/admin/configuration/settings')->with('success', 'Paramètre créé avec succès');
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création du paramètre');
        }
    }

    /**
     * Éditer un paramètre
     */
    public function edit($id = null)
    {
        try {
            if (!$id) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'ID du paramètre manquant');
            }

            $setting = $this->settingModel->find($id);
            if (!$setting) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'Paramètre non trouvé');
            }

            $data = [
                'title' => 'Éditer Paramètre - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'settings',
                'setting' => $setting
            ];

            return view('admin/configuration/settings_edit', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du paramètre');
        }
    }

    /**
     * Mettre à jour un paramètre
     */
    public function update($id = null)
    {
        try {
            if (!$id) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'ID du paramètre manquant');
            }

            $rules = [
                'setting_value' => 'required|max_length[500]',
                'description' => 'required|max_length[255]',
                'category' => 'required|in_list[system,academic,financial,communication,security]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'setting_value' => $this->request->getPost('setting_value'),
                'description' => $this->request->getPost('description'),
                'category' => $this->request->getPost('category'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->settingModel->update($id, $data);

            return redirect()->to('/admin/configuration/settings')->with('success', 'Paramètre mis à jour avec succès');
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du paramètre');
        }
    }

    /**
     * Supprimer un paramètre
     */
    public function delete($id = null)
    {
        try {
            if (!$id) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'ID du paramètre manquant');
            }

            $setting = $this->settingModel->find($id);
            if (!$setting) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'Paramètre non trouvé');
            }

            $this->settingModel->delete($id);

            return redirect()->to('/admin/configuration/settings')->with('success', 'Paramètre supprimé avec succès');
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::delete: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression du paramètre');
        }
    }

    /**
     * Afficher un paramètre
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'ID du paramètre manquant');
            }

            $setting = $this->settingModel->find($id);
            if (!$setting) {
                return redirect()->to('/admin/configuration/settings')->with('error', 'Paramètre non trouvé');
            }

            $data = [
                'title' => 'Détails Paramètre - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'settings',
                'setting' => $setting
            ];

            return view('admin/configuration/settings_show', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Settings::show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du paramètre');
        }
    }
}



