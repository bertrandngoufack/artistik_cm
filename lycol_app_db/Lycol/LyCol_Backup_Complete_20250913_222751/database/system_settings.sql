-- Table pour stocker les paramètres système
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_type` varchar(50) NOT NULL COMMENT 'Type de paramètre (general, email, sms, whatsapp)',
  `setting_value` longtext NOT NULL COMMENT 'Valeur JSON des paramètres',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_type` (`setting_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paramètres système de KISSAI SCHOOL';

-- Insertion des paramètres par défaut
INSERT INTO `system_settings` (`setting_type`, `setting_value`) VALUES
('general', '{"school_name":"KISSAI SCHOOL","school_address":"Douala, Cameroun","school_phone":"+237 XXX XXX XXX","school_email":"contact@kissai-school.cm","school_website":"https://www.kissai-school.cm","academic_year":"2024-2025","currency":"FCFA","timezone":"Africa/Douala"}'),
('email', '{"provider":"gmail","from_email":"kissai.school@gmail.com","from_name":"KISSAI SCHOOL","smtp_host":"smtp.gmail.com","smtp_port":587,"smtp_crypto":"tls","smtp_user":"","smtp_pass":""}'),
('sms', '{"provider":"textlocal","sender_name":"KISSAI","api_key":"","account_sid":"","auth_token":"","phone_number":""}'),
('whatsapp', '{"provider":"twilio","account_sid":"","auth_token":"","phone_number":"","api_key":""}')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Configuration Email (Office 365)
INSERT INTO system_settings (setting_type, setting_value, created_at, updated_at) VALUES
('email', '{"fromEmail":"notifications@cca-bank.com","fromName":"KISSAI SCHOOL","protocol":"smtp","SMTPHost":"smtp.office365.com","SMTPPort":587,"SMTPUser":"notifications@cca-bank.com","SMTPPass":"P@ssW0rd2022","SMTPCrypto":"tls","SMTPAuth":true,"mailType":"html","charset":"utf-8"}', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();
