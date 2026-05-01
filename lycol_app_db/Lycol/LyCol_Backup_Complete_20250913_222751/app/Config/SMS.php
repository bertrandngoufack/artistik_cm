<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SMS Configuration
 */
class SMS extends BaseConfig
{
    /**
     * Twilio Configuration
     */
    public string $twilioAccountSid = '';
    public string $twilioAuthToken = '';
    public string $twilioPhoneNumber = '+1234567890'; // Votre numéro Twilio
    
    /**
     * Alternative: TextLocal (Gratuit pour les tests)
     */
    public string $textlocalApiKey = 'your_textlocal_api_key';
    public string $textlocalSender = 'KISSAI';
    
    /**
     * Alternative: MSG91 (Gratuit pour les tests)
     */
    public string $msg91ApiKey = 'your_msg91_api_key';
    public string $msg91Sender = 'KISSAI';
    
    /**
     * Configuration par défaut
     */
    public string $defaultProvider = 'textlocal'; // textlocal, twilio, msg91
}
