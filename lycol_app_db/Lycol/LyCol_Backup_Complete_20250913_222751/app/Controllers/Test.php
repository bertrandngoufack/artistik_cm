<?php

namespace App\Controllers;

use App\Libraries\LicenseGenerator;

class Test extends BaseController
{
    public function license()
    {
        $generator = new LicenseGenerator();
        
        $clientId = "LYCOL_TEST_001";
        $licenseType = "TRIAL";
        $expiryDate = "2025-12-31";
        $secretSeed = "LYCOL_SECRET_KEY_2025";

        try {
            $licenseKey = $generator->generateLicenseKey($clientId, $licenseType, $expiryDate, $secretSeed);
            $validation = $generator->validateLicenseKey($licenseKey, $clientId, $licenseType, $expiryDate, $secretSeed);
            
            $data = [
                'title' => 'Test de Génération de Licence',
                'client_id' => $clientId,
                'license_type' => $licenseType,
                'expiry_date' => $expiryDate,
                'license_key' => $licenseKey,
                'validation' => $validation
            ];

            return view('test/license', $data);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function database()
    {
        try {
            $db = \Config\Database::connect();
            $result = $db->query('SELECT 1 as test')->getRow();
            
            $data = [
                'title' => 'Test de Base de Données',
                'status' => 'success',
                'message' => 'Connexion à la base de données réussie',
                'test_result' => $result
            ];

            return view('test/database', $data);
        } catch (\Exception $e) {
            $data = [
                'title' => 'Test de Base de Données',
                'status' => 'error',
                'message' => 'Erreur de connexion à la base de données',
                'error' => $e->getMessage()
            ];

            return view('test/database', $data);
        }
    }

    public function email()
    {
        $data = [
            'title' => 'Test d\'Email',
            'status' => 'info',
            'message' => 'Configuration email à tester'
        ];

        return view('test/email', $data);
    }
}




