<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\CacheService;

/**
 * Class Configuration
 * Gestion des paramètres système et fournisseurs
 * Expert Senior PHP/CodeIgniter
 */
class Configuration extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected $cacheService;

    public function __construct()
    {
        $this->cacheService = new CacheService();
    }

    /**
     * Page d'accueil de la configuration
     */
    public function index()
    {
        try {
            // Récupérer les informations de licence
            $licenseModel = new \App\Models\LicenseModel();
            $license = $licenseModel->where('status', 'ACTIVE')->first();
            
            // Récupérer les statistiques système avec cache
            $systemStats = $this->cacheService->remember('system_stats', function() {
                return $this->getSystemStats();
            }, 300); // Cache 5 minutes
            
            $data = [
                'title' => 'Configuration - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'license' => $license,
                'system_stats' => $systemStats
            ];

            return view('admin/configuration/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration');
        }
    }

    /**
     * Paramètres généraux
     */
    public function general()
    {
        try {
            $data = [
                'title' => 'Paramètres Généraux - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'general'
            ];

            return view('admin/configuration/general', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::general: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des paramètres généraux');
        }
    }

    /**
     * Configuration Email
     */
    public function email()
    {
        try {
            $data = [
                'title' => 'Configuration Email - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'email',
                'providers' => $this->getEmailProviders()
            ];

            return view('admin/configuration/email', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration email');
        }
    }

    /**
     * Configuration SMS
     */
    public function sms()
    {
        try {
            $data = [
                'title' => 'Configuration SMS - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'sms',
                'providers' => $this->getSMSProviders()
            ];

            return view('admin/configuration/sms', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::sms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration SMS');
        }
    }

    /**
     * Configuration WhatsApp
     */
    public function whatsapp()
    {
        try {
            $data = [
                'title' => 'Configuration WhatsApp - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'whatsapp',
                'providers' => $this->getWhatsAppProviders()
            ];

            return view('admin/configuration/whatsapp', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::whatsapp: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration WhatsApp');
        }
    }

    /**
     * Gestion de la licence
     */
    public function license()
    {
        try {
            $licenseModel = new \App\Models\LicenseModel();
            $license = $licenseModel->where('status', 'ACTIVE')->first();
            
            $data = [
                'title' => 'Gestion de Licence - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'license',
                'license' => $license
            ];

            return view('admin/configuration/license', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::license: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la gestion de licence');
        }
    }

    /**
     * Apparence de l'application
     */
    public function appearance()
    {
        try {
            // Charger le helper
            helper('app');
            
            // Récupérer les paramètres d'apparence actuels
            $appearanceSettings = $this->getAppearanceSettings();
            
            $data = [
                'title' => 'Apparence - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'appearance',
                'settings' => $appearanceSettings
            ];

            return view('admin/configuration/appearance', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::appearance: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration d\'apparence');
        }
    }

    /**
     * Diagnostics système
     */
    public function diagnostics()
    {
        try {
            $diagnostics = $this->runSystemDiagnostics();
            
            $data = [
                'title' => 'Diagnostics Système - KISSAI SCHOOL',
                'active_menu' => 'configuration',
                'submenu' => 'diagnostics',
                'diagnostics' => $diagnostics
            ];

            return view('admin/configuration/diagnostics', $data);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::diagnostics: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'exécution des diagnostics');
        }
    }

    /**
     * Vérification de licence via API
     */
    public function checkLicense()
    {
        try {
            $licenseModel = new \App\Models\LicenseModel();
            $license = $licenseModel->where('status', 'ACTIVE')->first();
            
            if (!$license) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => 'Aucune licence active trouvée'
                ]);
            }

            $licenseGenerator = new \App\Libraries\LicenseGenerator();
            $validation = $licenseGenerator->validateLicenseKey($license['license_key'], $license['client_id'], $license['license_type'], $license['expiry_date']);
            
            return $this->response->setJSON([
                'valid' => $validation['valid'],
                'license' => $license,
                'message' => $validation['valid'] ? 'Licence valide' : 'Licence invalide'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::checkLicense: ' . $e->getMessage());
            return $this->response->setJSON([
                'valid' => false, 
                'message' => 'Erreur de vérification : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API pour obtenir les statistiques système
     */
    public function getSystemStatsApi()
    {
        try {
            $stats = $this->cacheService->remember('system_stats_api', function() {
                return $this->getSystemStats();
            }, 60); // Cache 1 minute
            
            return $this->response->setJSON($stats);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::getSystemStatsApi: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => $e->getMessage(),
                'students' => 0,
                'teachers' => 0,
                'classes' => 0,
                'users' => 0
            ]);
        }
    }

    /**
     * Vider le cache
     */
    public function clearCache()
    {
        try {
            $this->cacheService->flush();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cache vidé avec succès'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::clearCache: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors du vidage du cache : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir les statistiques système
     */
    private function getSystemStats()
    {
        $stats = [];
        
        try {
            $db = \Config\Database::connect();
            
            // Statistiques de base de données
            $result = $db->query("SELECT COUNT(*) as count FROM students");
            $stats['students'] = $result->getRow()->count;
            
            $result = $db->query("SELECT COUNT(*) as count FROM teachers");
            $stats['teachers'] = $result->getRow()->count;
            
            $result = $db->query("SELECT COUNT(*) as count FROM classes");
            $stats['classes'] = $result->getRow()->count;
            
            $result = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $stats['users'] = $result->getRow()->count;
            
            // Informations système
            $stats['disk_usage'] = $this->getDiskUsage();
            $stats['memory_usage'] = $this->getMemoryUsage();
            $stats['php_version'] = PHP_VERSION;
            $stats['ci_version'] = \CodeIgniter\CodeIgniter::CI_VERSION;
            
        } catch (\Exception $e) {
            log_message('error', 'Erreur dans getSystemStats: ' . $e->getMessage());
            $stats['error'] = $e->getMessage();
        }
        
        return $stats;
    }

    /**
     * Obtenir l'utilisation du disque
     */
    private function getDiskUsage()
    {
        try {
            $path = ROOTPATH;
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percentage = round(($used / $total) * 100, 2);
            
            return [
                'total' => $this->formatBytes($total),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'percentage' => $percentage
            ];
        } catch (\Exception $e) {
            return [
                'total' => 'N/A',
                'used' => 'N/A',
                'free' => 'N/A',
                'percentage' => 0
            ];
        }
    }

    /**
     * Obtenir l'utilisation de la mémoire
     */
    private function getMemoryUsage()
    {
        try {
            $memoryLimit = ini_get('memory_limit');
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            
            return [
                'limit' => $memoryLimit,
                'usage' => $this->formatBytes($memoryUsage),
                'peak' => $this->formatBytes($memoryPeak)
            ];
        } catch (\Exception $e) {
            return [
                'limit' => 'N/A',
                'usage' => 'N/A',
                'peak' => 'N/A'
            ];
        }
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Exécuter les diagnostics système
     */
    private function runSystemDiagnostics()
    {
        $diagnostics = [];
        
        try {
            // Vérification de la base de données
            $db = \Config\Database::connect();
            $diagnostics['database'] = [
                'status' => 'OK',
                'message' => 'Connexion à la base de données réussie'
            ];
            
            // Vérification des permissions
            $writableDirs = ['writable/cache', 'writable/logs', 'writable/session'];
            foreach ($writableDirs as $dir) {
                $diagnostics['permissions'][$dir] = [
                    'status' => is_writable($dir) ? 'OK' : 'ERREUR',
                    'message' => is_writable($dir) ? 'Dossier accessible en écriture' : 'Dossier non accessible en écriture'
                ];
            }
            
            // Vérification de l'espace disque
            $diskUsage = $this->getDiskUsage();
            $diagnostics['disk'] = [
                'status' => $diskUsage['percentage'] > 90 ? 'ATTENTION' : 'OK',
                'message' => "Espace disque utilisé : {$diskUsage['percentage']}%"
            ];
            
            // Vérification de la mémoire
            $memoryUsage = $this->getMemoryUsage();
            $diagnostics['memory'] = [
                'status' => 'OK',
                'message' => "Mémoire utilisée : {$memoryUsage['usage']}"
            ];
            
        } catch (\Exception $e) {
            $diagnostics['error'] = $e->getMessage();
        }
        
        return $diagnostics;
    }

    /**
     * Obtenir les fournisseurs email
     */
    private function getEmailProviders()
    {
        return [
            'smtp' => 'SMTP',
            'sendgrid' => 'SendGrid',
            'mailgun' => 'Mailgun',
            'ses' => 'Amazon SES'
        ];
    }

    /**
     * Obtenir les fournisseurs SMS
     */
    private function getSMSProviders()
    {
        return [
            'twilio' => 'Twilio',
            'africastalking' => 'Africa\'s Talking',
            'nexmo' => 'Vonage (Nexmo)',
            'orange' => 'Orange SMS API'
        ];
    }

    /**
     * Obtenir les fournisseurs WhatsApp
     */
    private function getWhatsAppProviders()
    {
        return [
            'whatsapp_business' => 'WhatsApp Business API',
            'twilio_whatsapp' => 'Twilio WhatsApp',
            'messagebird' => 'MessageBird'
        ];
    }

    /**
     * Sauvegarder les paramètres d'apparence
     */
    public function saveAppearance()
    {
        try {
            // Validation des données
            $validation = \Config\Services::validation();
            $validation->setRules([
                'app_name' => 'required|min_length[3]|max_length[100]',
                'primary_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'secondary_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
                'app_description' => 'max_length[500]',
                'app_keywords' => 'max_length[200]'
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erreur de validation: ' . implode(', ', $validation->getErrors()));
            }

            // Récupération des données validées
            $data = [
                'app_name' => $this->request->getPost('app_name'),
                'primary_color' => $this->request->getPost('primary_color'),
                'secondary_color' => $this->request->getPost('secondary_color'),
                'app_description' => $this->request->getPost('app_description'),
                'app_keywords' => $this->request->getPost('app_keywords')
            ];

            // Gestion du logo
            $logoFile = $this->request->getFile('app_logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                $newName = $logoFile->getRandomName();
                $logoFile->move(ROOTPATH . 'public/assets/images/', $newName);
                $data['app_logo'] = 'assets/images/' . $newName;
                
                // Log de l'upload
                log_message('info', 'Logo uploadé: ' . $newName);
            }

            // Gestion du favicon
            $faviconFile = $this->request->getFile('app_favicon');
            if ($faviconFile && $faviconFile->isValid() && !$faviconFile->hasMoved()) {
                $newName = $faviconFile->getRandomName();
                $faviconFile->move(ROOTPATH . 'public/assets/images/', $newName);
                $data['app_favicon'] = 'assets/images/' . $newName;
                
                // Log de l'upload
                log_message('info', 'Favicon uploadé: ' . $newName);
            }

            // Sauvegarde en base de données
            $this->saveAppearanceSettings($data);

            // Mise à jour du cache
            $this->cacheService->delete('appearance_settings');
            
            // Vider le cache des paramètres d'application
            if (function_exists('clear_app_settings_cache')) {
                clear_app_settings_cache();
            }

            // Log de la sauvegarde
            log_message('info', 'Paramètres d\'apparence sauvegardés par l\'utilisateur: ' . session()->get('user_id'));

            return redirect()->back()->with('success', 'Paramètres d\'apparence sauvegardés avec succès');

        } catch (\Exception $e) {
            log_message('error', 'Erreur dans Configuration::saveAppearance: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres d'apparence en base de données
     */
    private function saveAppearanceSettings($data)
    {
        try {
            $db = \Config\Database::connect();
            
            // Vérifier si la table settings existe
            $tableExists = $db->tableExists('settings');
            
            if (!$tableExists) {
                // Créer la table settings si elle n'existe pas
                $this->createSettingsTable($db);
            }

            // Sauvegarder chaque paramètre
            foreach ($data as $key => $value) {
                if ($value !== null && $value !== '') {
                    $existing = $db->table('settings')
                        ->where('setting_key', $key)
                        ->get()
                        ->getRow();

                    if ($existing) {
                        // Mise à jour
                        $db->table('settings')
                            ->where('setting_key', $key)
                            ->update(['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
                    } else {
                        // Insertion
                        $db->table('settings')->insert([
                            'setting_key' => $key,
                            'setting_value' => $value,
                            'setting_type' => 'STRING',
                            'module' => 'appearance',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Erreur dans saveAppearanceSettings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer la table settings si elle n'existe pas
     */
    private function createSettingsTable($db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `setting_type` enum('STRING','INTEGER','BOOLEAN','JSON') DEFAULT 'STRING',
            `description` text,
            `module` varchar(50) DEFAULT NULL,
            `is_public` tinyint(1) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->query($sql);
        log_message('info', 'Table settings créée');
    }

    /**
     * Récupérer les paramètres d'apparence
     */
    private function getAppearanceSettings()
    {
        return $this->cacheService->remember('appearance_settings', function() {
            try {
                $db = \Config\Database::connect();
                
                if (!$db->tableExists('settings')) {
                    return $this->getDefaultAppearanceSettings();
                }

                $settings = $db->table('settings')
                    ->where('module', 'appearance')
                    ->get()
                    ->getResultArray();

                $result = $this->getDefaultAppearanceSettings();
                
                foreach ($settings as $setting) {
                    $result[$setting['setting_key']] = $setting['setting_value'];
                }

                return $result;

            } catch (\Exception $e) {
                log_message('error', 'Erreur dans getAppearanceSettings: ' . $e->getMessage());
                return $this->getDefaultAppearanceSettings();
            }
        }, 300); // Cache 5 minutes
    }

    /**
     * Paramètres d'apparence par défaut
     */
    private function getDefaultAppearanceSettings()
    {
        return [
            'app_name' => 'KISSAI SCHOOL',
            'app_logo' => 'assets/images/logo.png',
            'app_favicon' => 'assets/images/favicon.ico',
            'primary_color' => '#3273dc',
            'secondary_color' => '#00d1b2',
            'app_description' => 'Système de gestion scolaire KISSAI SCHOOL',
            'app_keywords' => 'école, gestion, scolaire, KISSAI'
        ];
    }
}
