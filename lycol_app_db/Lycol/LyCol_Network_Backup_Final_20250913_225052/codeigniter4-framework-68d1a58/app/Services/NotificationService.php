<?php

namespace App\Services;

use App\Services\ConfigurationService;

class NotificationService
{
    protected $configService;

    public function __construct()
    {
        $this->configService = new ConfigurationService();
    }

    public function sendEmail($to, $subject, $message)
    {
        $smtpConfig = $this->configService->getSMTPConfig();
        
        // Configuration email avec Office 365
        $email = \Config\Services::email();
        
        $email->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($message);
        
        $email->setSMTPHost($smtpConfig['host']);
        $email->setSMTPPort($smtpConfig['port']);
        $email->setSMTPUser($smtpConfig['username']);
        $email->setSMTPPass($smtpConfig['password']);
        $email->setSMTPCrypto($smtpConfig['encryption']);
        
        return $email->send();
    }

    public function sendSMS($phone, $message)
    {
        $smsConfig = $this->configService->getSMSConfig();
        
        // Configuration SMS avec TextLocal
        $url = 'https://api.textlocal.in/send/';
        $data = [
            'apikey' => $smsConfig['api_key'],
            'numbers' => $phone,
            'sender' => $smsConfig['sender_id'],
            'message' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function sendWhatsApp($phone, $message)
    {
        $whatsappConfig = $this->configService->getWhatsAppConfig();
        
        // Configuration WhatsApp avec Twilio
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$whatsappConfig['account_sid']}/Messages.json";
        
        $data = [
            'From' => "whatsapp:{$whatsappConfig['from_number']}",
            'To' => "whatsapp:{$phone}",
            'Body' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$whatsappConfig['account_sid']}:{$whatsappConfig['auth_token']}");
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function sendMultiChannelNotification($email, $phone, $subject, $message)
    {
        $results = [];
        
        // Email
        if (!empty($email)) {
            $results['email'] = $this->sendEmail($email, $subject, $message);
        }
        
        // SMS
        if (!empty($phone)) {
            $results['sms'] = $this->sendSMS($phone, $message);
        }
        
        // WhatsApp
        if (!empty($phone)) {
            $results['whatsapp'] = $this->sendWhatsApp($phone, $message);
        }
        
        return $results;
    }
}



















