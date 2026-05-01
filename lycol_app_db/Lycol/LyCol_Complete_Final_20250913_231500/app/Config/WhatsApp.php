<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * WhatsApp Configuration
 */
class WhatsApp extends BaseConfig
{
    /**
     * WhatsApp Business API Configuration
     */
    public string $whatsappApiUrl = 'https://graph.facebook.com/v17.0';
    public string $whatsappPhoneNumberId = 'your_phone_number_id';
    public string $whatsappAccessToken = 'your_access_token';
    
    /**
     * Alternative: Twilio WhatsApp
     */
    public string $twilioWhatsappAccountSid = '';
    public string $twilioWhatsappAuthToken = '';
    public string $twilioWhatsappFrom = '';
    
    /**
     * Alternative: 360dialog (Gratuit pour les tests)
     */
    public string $dialog360ApiKey = 'your_dialog360_api_key';
    public string $dialog360WhatsappNumber = 'your_whatsapp_number';
    
    /**
     * Configuration par défaut
     */
    public string $defaultProvider = 'twilio'; // twilio, dialog360
}
