-- Table pour enregistrer les rappels de paiement
CREATE TABLE IF NOT EXISTS `payment_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `sent_to_phone` varchar(20) DEFAULT NULL,
  `sent_to_email` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `email_sent` tinyint(1) DEFAULT 0,
  `whatsapp_sent` tinyint(1) DEFAULT 0,
  `sent_at` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajout de contraintes de clé étrangère
ALTER TABLE `payment_reminders` 
ADD CONSTRAINT `fk_payment_reminders_payment_id` 
FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;


