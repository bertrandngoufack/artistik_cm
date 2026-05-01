<?php

namespace App\Services;

use PDO;
use App\Services\DatabaseService;

/**
 * Service de configuration pour récupérer les paramètres depuis la base de données
 */
class ConfigurationService
{
    private $pdo;
    
    public function __construct()
    {
        $this->pdo = DatabaseService::getInstance()->getConnection();
    }
    
    /**
     * Récupérer la configuration email depuis la base de données
     */
    public function getEmailConfig()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_type = 'email'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['setting_value'], true);
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération de la configuration email: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer la configuration SMS depuis la base de données
     */
    public function getSMSConfig()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_type = 'sms'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['setting_value'], true);
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération de la configuration SMS: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer la configuration WhatsApp depuis la base de données
     */
    public function getWhatsAppConfig()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_type = 'whatsapp'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['setting_value'], true);
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération de la configuration WhatsApp: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer la configuration générale depuis la base de données
     */
    public function getGeneralConfig()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_type = 'general'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['setting_value'], true);
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération de la configuration générale: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir la configuration email pour CodeIgniter
     */
    public function getEmailConfigForCodeIgniter()
    {
        $config = $this->getEmailConfig();
        
        return [
            'fromEmail' => $config['fromEmail'] ?? 'notifications@cca-bank.com',
            'fromName' => $config['fromName'] ?? 'KISSAI SCHOOL',
            'protocol' => $config['protocol'] ?? 'smtp',
            'SMTPHost' => $config['SMTPHost'] ?? 'smtp.office365.com',
            'SMTPPort' => $config['SMTPPort'] ?? 587,
            'SMTPUser' => $config['SMTPUser'] ?? 'notifications@cca-bank.com',
            'SMTPPass' => $config['SMTPPass'] ?? 'P@ssW0rd2022',
            'SMTPCrypto' => $config['SMTPCrypto'] ?? 'tls',
            'SMTPAuth' => $config['SMTPAuth'] ?? true,
            'mailType' => $config['mailType'] ?? 'html',
            'charset' => $config['charset'] ?? 'utf-8'
        ];
    }
    
    /**
     * Obtenir la configuration SMS pour l'envoi
     */
    public function getSMSConfigForSending()
    {
        $config = $this->getSMSConfig();
        
        if (!$config) {
            return null;
        }
        
        return $config;
    }
    
    /**
     * Obtenir la configuration WhatsApp pour l'envoi
     */
    public function getWhatsAppConfigForSending()
    {
        $config = $this->getWhatsAppConfig();
        
        if (!$config) {
            return null;
        }
        
        return $config;
    }
}
