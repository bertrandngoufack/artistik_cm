-- =====================================================
-- TABLES DU MODULE SCOLARITÉ - KISSAI SCHOOL
-- =====================================================

-- Table des élèves (mise à jour)
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) NOT NULL COMMENT 'Numéro matricule unique',
  `first_name` varchar(100) NOT NULL COMMENT 'Prénom',
  `last_name` varchar(100) NOT NULL COMMENT 'Nom de famille',
  `birth_date` date NOT NULL COMMENT 'Date de naissance',
  `birth_place` varchar(100) DEFAULT NULL COMMENT 'Lieu de naissance',
  `gender` enum('M','F') NOT NULL COMMENT 'Genre (M/F)',
  `nationality` varchar(50) NOT NULL DEFAULT 'Camerounaise' COMMENT 'Nationalité',
  `address` text DEFAULT NULL COMMENT 'Adresse complète',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Téléphone de l\'élève',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email de l\'élève',
  `photo` varchar(255) DEFAULT NULL COMMENT 'Chemin vers la photo',
  `parent_name` varchar(200) NOT NULL COMMENT 'Nom du parent/tuteur',
  `parent_phone` varchar(20) NOT NULL COMMENT 'Téléphone du parent',
  `parent_email` varchar(100) DEFAULT NULL COMMENT 'Email du parent',
  `emergency_contact` varchar(200) DEFAULT NULL COMMENT 'Contact d\'urgence',
  `blood_group` varchar(5) DEFAULT NULL COMMENT 'Groupe sanguin',
  `medical_info` text DEFAULT NULL COMMENT 'Informations médicales',
  `admission_date` date NOT NULL COMMENT 'Date d\'admission',
  `current_class_id` int(11) NOT NULL COMMENT 'ID de la classe actuelle',
  `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique',
  `status` enum('ACTIVE','INACTIVE','GRADUATED','TRANSFERRED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE' COMMENT 'Statut de l\'élève',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_matricule` (`matricule`),
  KEY `idx_class_id` (`current_class_id`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_status` (`status`),
  KEY `idx_parent_phone` (`parent_phone`),
  KEY `idx_parent_email` (`parent_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des élèves de KISSAI SCHOOL';

-- Table des classes (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nom de la classe',
  `level` varchar(20) NOT NULL COMMENT 'Niveau (Maternelle, Primaire, Secondaire)',
  `academic_year` varchar(9) NOT NULL DEFAULT '2024-2025' COMMENT 'Année académique',
  `capacity` int(11) DEFAULT 40 COMMENT 'Capacité maximale',
  `teacher_id` int(11) DEFAULT NULL COMMENT 'ID du professeur principal',
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE' COMMENT 'Statut de la classe',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_level` (`level`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des classes de KISSAI SCHOOL';

-- Table des absences (mise à jour)
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

-- Table des incidents disciplinaires (mise à jour)
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

-- Table des notifications disciplinaires
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

-- Table des rapports de scolarité
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

-- Insertion de données de test pour les classes
INSERT INTO `classes` (`name`, `level`, `academic_year`, `capacity`, `status`) VALUES
('CP1', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('CP2', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('CE1', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('CE2', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('CM1', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('CM2', 'Primaire', '2024-2025', 35, 'ACTIVE'),
('6ème', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('5ème', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('4ème', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('3ème', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('2nde', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('1ère', 'Secondaire', '2024-2025', 40, 'ACTIVE'),
('Terminale', 'Secondaire', '2024-2025', 40, 'ACTIVE')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insertion de données de test pour les élèves
INSERT INTO `students` (`matricule`, `first_name`, `last_name`, `birth_date`, `gender`, `nationality`, `parent_name`, `parent_phone`, `parent_email`, `admission_date`, `current_class_id`, `academic_year`, `status`) VALUES
('STU2024001', 'Marie', 'Dubois', '2010-03-15', 'F', 'Camerounaise', 'Jean Dubois', '+237 690123456', 'jean.dubois@email.com', '2024-09-01', 1, '2024-2025', 'ACTIVE'),
('STU2024002', 'Pierre', 'Martin', '2009-07-22', 'M', 'Camerounaise', 'Sophie Martin', '+237 691234567', 'sophie.martin@email.com', '2024-09-01', 1, '2024-2025', 'ACTIVE'),
('STU2024003', 'Emma', 'Bernard', '2011-11-08', 'F', 'Camerounaise', 'Paul Bernard', '+237 692345678', 'paul.bernard@email.com', '2024-09-01', 2, '2024-2025', 'ACTIVE'),
('STU2024004', 'Lucas', 'Petit', '2010-05-12', 'M', 'Camerounaise', 'Marie Petit', '+237 693456789', 'marie.petit@email.com', '2024-09-01', 2, '2024-2025', 'ACTIVE'),
('STU2024005', 'Léa', 'Robert', '2009-12-03', 'F', 'Camerounaise', 'François Robert', '+237 694567890', 'francois.robert@email.com', '2024-09-01', 3, '2024-2025', 'ACTIVE'),
('STU2024006', 'Hugo', 'Richard', '2010-08-19', 'M', 'Camerounaise', 'Catherine Richard', '+237 695678901', 'catherine.richard@email.com', '2024-09-01', 3, '2024-2025', 'ACTIVE'),
('STU2024007', 'Chloé', 'Durand', '2011-01-25', 'F', 'Camerounaise', 'Michel Durand', '+237 696789012', 'michel.durand@email.com', '2024-09-01', 4, '2024-2025', 'ACTIVE'),
('STU2024008', 'Jules', 'Moreau', '2010-09-14', 'M', 'Camerounaise', 'Isabelle Moreau', '+237 697890123', 'isabelle.moreau@email.com', '2024-09-01', 4, '2024-2025', 'ACTIVE'),
('STU2024009', 'Alice', 'Simon', '2009-04-30', 'F', 'Camerounaise', 'Philippe Simon', '+237 698901234', 'philippe.simon@email.com', '2024-09-01', 5, '2024-2025', 'ACTIVE'),
('STU2024010', 'Louis', 'Michel', '2010-06-17', 'M', 'Camerounaise', 'Anne Michel', '+237 699012345', 'anne.michel@email.com', '2024-09-01', 5, '2024-2025', 'ACTIVE')
ON DUPLICATE KEY UPDATE `first_name` = VALUES(`first_name`);

-- Insertion de données de test pour les absences
INSERT INTO `absences` (`student_id`, `absence_date`, `reason`, `duration`, `justified`, `recorded_by`, `academic_year`) VALUES
(1, '2024-12-15', 'Maladie', 'FULL_DAY', 1, 1, '2024-2025'),
(2, '2024-12-16', 'Rendez-vous médical', 'HALF_DAY', 1, 1, '2024-2025'),
(3, '2024-12-17', 'Absence non justifiée', 'FULL_DAY', 0, 1, '2024-2025'),
(4, '2024-12-18', 'Voyage familial', 'MULTIPLE_DAYS', 1, 1, '2024-2025'),
(5, '2024-12-19', 'Maladie', 'FULL_DAY', 1, 1, '2024-2025')
ON DUPLICATE KEY UPDATE `reason` = VALUES(`reason`);

-- Insertion de données de test pour les incidents disciplinaires
INSERT INTO `discipline_incidents` (`student_id`, `incident_date`, `incident_type`, `description`, `location`, `sanction`, `parent_notified`, `notification_sent`, `recorded_by`, `academic_year`) VALUES
(2, '2024-12-15', 'MINOR', 'Bavardage excessif en classe', 'Salle de classe CP1', 'Avertissement oral', 0, 0, 1, '2024-2025'),
(4, '2024-12-16', 'MAJOR', 'Bagarre avec un autre élève', 'Cour de récréation', 'Retenue de 2 heures', 1, 0, 1, '2024-2025'),
(6, '2024-12-17', 'MINOR', 'Retard répété', 'Entrée de l\'école', 'Avertissement écrit', 0, 0, 1, '2024-2025'),
(8, '2024-12-18', 'CRITICAL', 'Vandalisme sur le matériel scolaire', 'Salle informatique', 'Suspension de 3 jours', 1, 0, 1, '2024-2025'),
(10, '2024-12-19', 'MAJOR', 'Non-respect des consignes de sécurité', 'Laboratoire', 'Retenue de 4 heures', 1, 0, 1, '2024-2025')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

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
WHERE c.status = 'ACTIVE'
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
WHERE c.status = 'ACTIVE'
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

