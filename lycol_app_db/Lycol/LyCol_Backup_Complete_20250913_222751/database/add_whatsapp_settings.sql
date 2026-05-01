-- Script pour ajouter les colonnes WhatsApp Business dans la table settings
-- Exécuté le: 25/08/2025

-- Ajout des colonnes WhatsApp Business
ALTER TABLE settings ADD COLUMN whatsapp_provider VARCHAR(50) DEFAULT 'twilio' COMMENT 'Fournisseur WhatsApp (twilio, meta, africastalking, messagebird)';
ALTER TABLE settings ADD COLUMN whatsapp_account_sid VARCHAR(255) DEFAULT NULL COMMENT 'Account SID WhatsApp Business';
ALTER TABLE settings ADD COLUMN whatsapp_auth_token VARCHAR(255) DEFAULT NULL COMMENT 'Auth Token WhatsApp Business';
ALTER TABLE settings ADD COLUMN whatsapp_phone_number VARCHAR(20) DEFAULT NULL COMMENT 'Numéro WhatsApp Business vérifié';
ALTER TABLE settings ADD COLUMN whatsapp_webhook_url VARCHAR(500) DEFAULT NULL COMMENT 'URL du webhook WhatsApp';
ALTER TABLE settings ADD COLUMN whatsapp_default_template TEXT DEFAULT NULL COMMENT 'Template par défaut pour WhatsApp';
ALTER TABLE settings ADD COLUMN whatsapp_media_enabled TINYINT(1) DEFAULT 0 COMMENT 'Activer l\'envoi de médias WhatsApp';
ALTER TABLE settings ADD COLUMN whatsapp_buttons_enabled TINYINT(1) DEFAULT 0 COMMENT 'Activer les boutons interactifs WhatsApp';

-- Ajout d'index pour optimiser les requêtes
CREATE INDEX idx_whatsapp_provider ON settings(whatsapp_provider);
CREATE INDEX idx_whatsapp_phone ON settings(whatsapp_phone_number);

-- Insertion des valeurs par défaut pour WhatsApp
INSERT INTO settings (setting_key, setting_value, setting_type, setting_description, setting_group, is_public) VALUES
('whatsapp_provider', 'twilio', 'string', 'Fournisseur WhatsApp Business', 'messaging', 0),
('whatsapp_account_sid', '', 'string', 'Account SID WhatsApp Business', 'messaging', 0),
('whatsapp_auth_token', '', 'string', 'Auth Token WhatsApp Business', 'messaging', 0),
('whatsapp_phone_number', '', 'string', 'Numéro WhatsApp Business vérifié', 'messaging', 0),
('whatsapp_webhook_url', '', 'string', 'URL du webhook WhatsApp', 'messaging', 0),
('whatsapp_default_template', 'Bonjour {name}, {message}', 'text', 'Template par défaut pour WhatsApp', 'messaging', 0),
('whatsapp_media_enabled', '0', 'boolean', 'Activer l\'envoi de médias WhatsApp', 'messaging', 0),
('whatsapp_buttons_enabled', '0', 'boolean', 'Activer les boutons interactifs WhatsApp', 'messaging', 0)
ON DUPLICATE KEY UPDATE
setting_value = VALUES(setting_value),
setting_description = VALUES(setting_description);

-- Vérification de l'ajout des colonnes
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'lycol_db' 
AND TABLE_NAME = 'settings' 
AND COLUMN_NAME LIKE 'whatsapp%'
ORDER BY COLUMN_NAME;

-- Affichage des paramètres WhatsApp configurés
SELECT 
    setting_key,
    setting_value,
    setting_description
FROM settings 
WHERE setting_key LIKE 'whatsapp%'
ORDER BY setting_key;







