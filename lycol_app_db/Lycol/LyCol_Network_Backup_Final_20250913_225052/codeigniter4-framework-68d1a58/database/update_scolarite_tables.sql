-- =====================================================
-- MISE À JOUR DES TABLES SCOLARITÉ - KISSAI SCHOOL
-- =====================================================

-- Ajout des colonnes manquantes à la table students
ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique' AFTER `current_class_id`,
ADD COLUMN IF NOT EXISTS `emergency_contact` varchar(200) DEFAULT NULL COMMENT 'Contact d\'urgence' AFTER `parent_email`,
ADD COLUMN IF NOT EXISTS `blood_group` varchar(5) DEFAULT NULL COMMENT 'Groupe sanguin' AFTER `emergency_contact`,
ADD COLUMN IF NOT EXISTS `medical_info` text DEFAULT NULL COMMENT 'Informations médicales' AFTER `blood_group`,
ADD COLUMN IF NOT EXISTS `SUSPENDED` enum('ACTIVE','INACTIVE','GRADUATED','TRANSFERRED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE' COMMENT 'Statut de l\'élève';

-- Mise à jour du statut pour inclure SUSPENDED
ALTER TABLE `students` MODIFY COLUMN `status` enum('ACTIVE','INACTIVE','GRADUATED','TRANSFERRED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE' COMMENT 'Statut de l\'élève';

-- Ajout des index manquants
CREATE INDEX IF NOT EXISTS `idx_students_academic_year` ON `students` (`academic_year`);
CREATE INDEX IF NOT EXISTS `idx_students_parent_phone` ON `students` (`parent_phone`);
CREATE INDEX IF NOT EXISTS `idx_students_parent_email` ON `students` (`parent_email`);

-- Table des absences (création si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `absences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT 'ID de l\'élève',
  `absence_date` date NOT NULL COMMENT 'Date de l\'absence',
  `reason` varchar(500) NOT NULL COMMENT 'Motif de l\'absence',
  `duration` enum('HALF_DAY','FULL_DAY','MULTIPLE_DAYS') NOT NULL DEFAULT 'FULL_DAY' COMMENT 'Durée de l\'absence',
  `justified` tinyint(1) DEFAULT 0 COMMENT 'Absence justifiée (0/1)',
  `justification_document` varchar(255) DEFAULT NULL COMMENT 'Document de justification',
  `recorded_by` int(11) NOT NULL COMMENT 'ID de l\'utilisateur qui a enregistré',
  `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_absence_date` (`absence_date`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_recorded_by` (`recorded_by`),
  CONSTRAINT `fk_absences_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des absences des élèves';

-- Table des incidents disciplinaires (création si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `discipline_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT 'ID de l\'élève',
  `incident_date` date NOT NULL COMMENT 'Date de l\'incident',
  `incident_time` time DEFAULT NULL COMMENT 'Heure de l\'incident',
  `incident_type` enum('MINOR','MAJOR','CRITICAL') NOT NULL DEFAULT 'MINOR' COMMENT 'Type d\'incident',
  `description` text NOT NULL COMMENT 'Description détaillée de l\'incident',
  `location` varchar(100) DEFAULT NULL COMMENT 'Lieu de l\'incident',
  `witnesses` text DEFAULT NULL COMMENT 'Témoins de l\'incident',
  `sanction` varchar(500) NOT NULL COMMENT 'Sanction appliquée',
  `sanction_duration` varchar(50) DEFAULT NULL COMMENT 'Durée de la sanction',
  `parent_notified` tinyint(1) DEFAULT 0 COMMENT 'Parent notifié (0/1)',
  `notification_sent` tinyint(1) DEFAULT 0 COMMENT 'Notification envoyée (0/1)',
  `recorded_by` int(11) NOT NULL COMMENT 'ID de l\'utilisateur qui a enregistré',
  `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_incident_date` (`incident_date`),
  KEY `idx_incident_type` (`incident_type`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_recorded_by` (`recorded_by`),
  CONSTRAINT `fk_discipline_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des incidents disciplinaires';

-- Table des notifications disciplinaires (création si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `discipline_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` int(11) NOT NULL COMMENT 'ID de l\'incident disciplinaire',
  `student_id` int(11) NOT NULL COMMENT 'ID de l\'élève',
  `parent_phone` varchar(20) NOT NULL COMMENT 'Téléphone du parent',
  `parent_email` varchar(100) DEFAULT NULL COMMENT 'Email du parent',
  `message` text NOT NULL COMMENT 'Message envoyé',
  `sms_sent` tinyint(1) DEFAULT 0 COMMENT 'SMS envoyé (0/1)',
  `email_sent` tinyint(1) DEFAULT 0 COMMENT 'Email envoyé (0/1)',
  `whatsapp_sent` tinyint(1) DEFAULT 0 COMMENT 'WhatsApp envoyé (0/1)',
  `sent_at` datetime NOT NULL COMMENT 'Date et heure d\'envoi',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_incident_id` (`incident_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `fk_discipline_notifications_incident_id` FOREIGN KEY (`incident_id`) REFERENCES `discipline_incidents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_discipline_notifications_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des notifications disciplinaires envoyées aux parents';

-- Table des rapports de scolarité (création si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `scolarite_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT 'ID de l\'élève',
  `report_type` enum('ABSENCE','DISCIPLINE','GENERAL','PERIODIC') NOT NULL COMMENT 'Type de rapport',
  `report_date` date NOT NULL COMMENT 'Date du rapport',
  `period` varchar(20) DEFAULT NULL COMMENT 'Période (trimestre, semestre, etc.)',
  `content` text NOT NULL COMMENT 'Contenu du rapport',
  `absences_count` int(11) DEFAULT 0 COMMENT 'Nombre d\'absences',
  `discipline_incidents_count` int(11) DEFAULT 0 COMMENT 'Nombre d\'incidents disciplinaires',
  `generated_by` int(11) NOT NULL COMMENT 'ID de l\'utilisateur qui a généré le rapport',
  `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_report_type` (`report_type`),
  KEY `idx_report_date` (`report_date`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_generated_by` (`generated_by`),
  CONSTRAINT `fk_scolarite_reports_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des rapports de scolarité';

-- Mise à jour des données existantes pour ajouter l'année académique
UPDATE `students` SET `academic_year` = '2024-2025' WHERE `academic_year` IS NULL OR `academic_year` = '';

-- Insertion de données de test pour les absences (seulement si la table est vide)
INSERT INTO `absences` (`student_id`, `absence_date`, `reason`, `duration`, `justified`, `recorded_by`, `academic_year`) 
SELECT 1, '2024-12-15', 'Maladie', 'FULL_DAY', 1, 1, '2024-2025'
WHERE NOT EXISTS (SELECT 1 FROM `absences` LIMIT 1);

INSERT INTO `absences` (`student_id`, `absence_date`, `reason`, `duration`, `justified`, `recorded_by`, `academic_year`) 
SELECT 2, '2024-12-16', 'Rendez-vous médical', 'HALF_DAY', 1, 1, '2024-2025'
WHERE NOT EXISTS (SELECT 1 FROM `absences` WHERE student_id = 2 AND absence_date = '2024-12-16');

-- Insertion de données de test pour les incidents disciplinaires (seulement si la table est vide)
INSERT INTO `discipline_incidents` (`student_id`, `incident_date`, `incident_type`, `description`, `location`, `sanction`, `parent_notified`, `notification_sent`, `recorded_by`, `academic_year`) 
SELECT 2, '2024-12-15', 'MINOR', 'Bavardage excessif en classe', 'Salle de classe CP1', 'Avertissement oral', 0, 0, 1, '2024-2025'
WHERE NOT EXISTS (SELECT 1 FROM `discipline_incidents` LIMIT 1);

INSERT INTO `discipline_incidents` (`student_id`, `incident_date`, `incident_type`, `description`, `location`, `sanction`, `parent_notified`, `notification_sent`, `recorded_by`, `academic_year`) 
SELECT 4, '2024-12-16', 'MAJOR', 'Bagarre avec un autre élève', 'Cour de récréation', 'Retenue de 2 heures', 1, 0, 1, '2024-2025'
WHERE NOT EXISTS (SELECT 1 FROM `discipline_incidents` WHERE student_id = 4 AND incident_date = '2024-12-16');

-- =====================================================
-- INDEX ET OPTIMISATIONS
-- =====================================================

-- Index pour améliorer les performances des requêtes
CREATE INDEX IF NOT EXISTS `idx_students_class_year` ON `students` (`current_class_id`, `academic_year`);
CREATE INDEX IF NOT EXISTS `idx_absences_student_date` ON `absences` (`student_id`, `absence_date`);
CREATE INDEX IF NOT EXISTS `idx_discipline_student_date` ON `discipline_incidents` (`student_id`, `incident_date`);
CREATE INDEX IF NOT EXISTS `idx_notifications_incident` ON `discipline_notifications` (`incident_id`, `sent_at`);

-- =====================================================
-- VUES POUR LES RAPPORTS
-- =====================================================

-- Vue pour les statistiques des absences par classe
CREATE OR REPLACE VIEW `v_absence_stats_by_class` AS
SELECT 
    c.id as class_id,
    c.name as class_name,
    COUNT(a.id) as total_absences,
    COUNT(CASE WHEN a.justified = 1 THEN 1 END) as justified_absences,
    COUNT(CASE WHEN a.justified = 0 THEN 1 END) as unjustified_absences,
    COUNT(CASE WHEN a.duration = 'FULL_DAY' THEN 1 END) as full_day_absences,
    COUNT(CASE WHEN a.duration = 'HALF_DAY' THEN 1 END) as half_day_absences,
    COUNT(CASE WHEN a.duration = 'MULTIPLE_DAYS' THEN 1 END) as multiple_days_absences
FROM classes c
LEFT JOIN students s ON c.id = s.current_class_id
LEFT JOIN absences a ON s.id = a.student_id AND a.academic_year = c.academic_year
WHERE c.is_active = 1
GROUP BY c.id, c.name;

-- Vue pour les statistiques disciplinaires par classe
CREATE OR REPLACE VIEW `v_discipline_stats_by_class` AS
SELECT 
    c.id as class_id,
    c.name as class_name,
    COUNT(d.id) as total_incidents,
    COUNT(CASE WHEN d.incident_type = 'MINOR' THEN 1 END) as minor_incidents,
    COUNT(CASE WHEN d.incident_type = 'MAJOR' THEN 1 END) as major_incidents,
    COUNT(CASE WHEN d.incident_type = 'CRITICAL' THEN 1 END) as critical_incidents,
    COUNT(CASE WHEN d.parent_notified = 1 THEN 1 END) as parent_notifications
FROM classes c
LEFT JOIN students s ON c.id = s.current_class_id
LEFT JOIN discipline_incidents d ON s.id = d.student_id AND d.academic_year = c.academic_year
WHERE c.is_active = 1
GROUP BY c.id, c.name;

-- Vue pour les élèves avec leurs statistiques
CREATE OR REPLACE VIEW `v_student_stats` AS
SELECT 
    s.id,
    s.matricule,
    s.first_name,
    s.last_name,
    s.current_class_id,
    c.name as class_name,
    s.academic_year,
    s.status,
    COUNT(a.id) as total_absences,
    COUNT(CASE WHEN a.justified = 1 THEN 1 END) as justified_absences,
    COUNT(CASE WHEN a.justified = 0 THEN 1 END) as unjustified_absences,
    COUNT(d.id) as total_discipline_incidents,
    COUNT(CASE WHEN d.incident_type = 'MINOR' THEN 1 END) as minor_incidents,
    COUNT(CASE WHEN d.incident_type = 'MAJOR' THEN 1 END) as major_incidents,
    COUNT(CASE WHEN d.incident_type = 'CRITICAL' THEN 1 END) as critical_incidents
FROM students s
LEFT JOIN classes c ON s.current_class_id = c.id
LEFT JOIN absences a ON s.id = a.student_id AND a.academic_year = s.academic_year
LEFT JOIN discipline_incidents d ON s.id = d.student_id AND d.academic_year = s.academic_year
GROUP BY s.id, s.matricule, s.first_name, s.last_name, s.current_class_id, c.name, s.academic_year, s.status;

