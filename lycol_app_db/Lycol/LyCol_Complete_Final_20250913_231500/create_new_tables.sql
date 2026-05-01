-- Script de création des nouvelles tables pour le module Études
-- Exécutez ce script dans votre base de données MySQL

-- Table des cycles éducatifs (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `cycles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `code` varchar(20) NOT NULL,
    `description` text,
    `is_active` tinyint(1) DEFAULT '1',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des matières (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `code` varchar(20) NOT NULL,
    `description` text,
    `coefficient` decimal(3,2) DEFAULT '1.00',
    `is_active` tinyint(1) DEFAULT '1',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison classes-matières
CREATE TABLE IF NOT EXISTS `class_subjects` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `hours_per_week` int(11) DEFAULT '0',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `class_subject_unique` (`class_id`,`subject_id`),
    KEY `subject_id` (`subject_id`),
    CONSTRAINT `class_subjects_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `class_subjects_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des emplois du temps
CREATE TABLE IF NOT EXISTS `timetables` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `class_id` int(11) NOT NULL,
    `day_of_week` tinyint(1) NOT NULL COMMENT '1=Lundi, 2=Mardi, 3=Mercredi, 4=Jeudi, 5=Vendredi, 6=Samedi',
    `start_time` time NOT NULL,
    `end_time` time NOT NULL,
    `subject_id` int(11) NOT NULL,
    `teacher_id` int(11) DEFAULT NULL,
    `room` varchar(50) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT '1',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `class_id` (`class_id`),
    KEY `subject_id` (`subject_id`),
    KEY `teacher_id` (`teacher_id`),
    CONSTRAINT `timetables_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `timetables_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `timetables_teacher_id_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des assignations enseignants-classes
CREATE TABLE IF NOT EXISTS `teacher_assignments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `teacher_id` int(11) NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `is_principal` tinyint(1) DEFAULT '0' COMMENT '1=Enseignant principal de la classe',
    `academic_year` varchar(9) NOT NULL COMMENT 'Format: 2024-2025',
    `is_active` tinyint(1) DEFAULT '1',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `teacher_assignment_unique` (`teacher_id`,`class_id`,`subject_id`,`academic_year`),
    KEY `class_id` (`class_id`),
    KEY `subject_id` (`subject_id`),
    CONSTRAINT `teacher_assignments_teacher_id_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `teacher_assignments_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `teacher_assignments_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données de test pour les cycles
INSERT IGNORE INTO `cycles` (`name`, `code`, `description`) VALUES
('Primaire', 'PRIM', 'Cycle primaire'),
('Secondaire', 'SEC', 'Cycle secondaire'),
('Supérieur', 'SUP', 'Cycle supérieur');

-- Insertion des données de test pour les matières
INSERT IGNORE INTO `subjects` (`name`, `code`, `coefficient`) VALUES
('Mathématiques', 'MATH', 4.00),
('Français', 'FRAN', 3.00),
('Anglais', 'ANGL', 2.00),
('Histoire', 'HIST', 2.00),
('Géographie', 'GEO', 2.00),
('Sciences', 'SCI', 3.00),
('Physique', 'PHY', 3.00),
('Chimie', 'CHIM', 2.00),
('Biologie', 'BIO', 2.00),
('Informatique', 'INFO', 2.00);
