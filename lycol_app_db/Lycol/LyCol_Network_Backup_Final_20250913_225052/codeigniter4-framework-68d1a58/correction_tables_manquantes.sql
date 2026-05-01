-- ========================================
-- CORRECTION DES TABLES MANQUANTES
-- KISSAI SCHOOL - Expert Senior
-- ========================================

-- 1. CRÉATION DE LA TABLE LOANS (EMPRUNTS BIBLIOTHÈQUE)
-- ====================================================

CREATE TABLE IF NOT EXISTS `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('ACTIVE','RETURNED','OVERDUE','LOST') NOT NULL DEFAULT 'ACTIVE',
  `notes` text DEFAULT NULL,
  `academic_year` varchar(9) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  KEY `idx_loan_date` (`loan_date`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_status` (`status`),
  KEY `idx_academic_year` (`academic_year`),
  CONSTRAINT `fk_loans_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CRÉATION DE LA TABLE TEMPLATES (MODÈLES DE MESSAGES)
-- =======================================================

CREATE TABLE IF NOT EXISTS `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('SMS','EMAIL','WHATSAPP','NOTIFICATION') NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_templates_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. INSERTION DE DONNÉES DE TEST POUR LES TEMPLATES
-- ==================================================

INSERT INTO `templates` (`name`, `type`, `subject`, `content`, `variables`, `is_active`, `created_by`) VALUES
('Rappel Paiement', 'SMS', NULL, 'Bonjour {parent_name}, rappel: paiement de {amount} FCFA pour {student_name} échéance {due_date}. KISSAI SCHOOL', '["parent_name", "amount", "student_name", "due_date"]', 1, 1),
('Absence Étudiant', 'EMAIL', 'Absence de {student_name}', 'Bonjour {parent_name},\n\nNous vous informons que {student_name} a été absent(e) le {date}.\n\nMotif: {reason}\n\nCordialement,\nKISSAI SCHOOL', '["student_name", "parent_name", "date", "reason"]', 1, 1),
('Rappel Emprunt', 'WHATSAPP', NULL, 'Bonjour {student_name}, rappel: livre "{book_title}" à retourner avant {due_date}. KISSAI SCHOOL', '["student_name", "book_title", "due_date"]', 1, 1),
('Notification Générale', 'NOTIFICATION', 'Information importante', 'Information importante: {message}\n\nKISSAI SCHOOL', '["message"]', 1, 1);

-- 4. INSERTION DE DONNÉES DE TEST POUR LES LOANS
-- ==============================================

INSERT INTO `loans` (`book_id`, `student_id`, `loan_date`, `due_date`, `status`, `academic_year`) VALUES
(1, 1, '2024-09-15', '2024-10-15', 'RETURNED', '2024-2025'),
(2, 3, '2024-09-20', '2024-10-20', 'ACTIVE', '2024-2025'),
(3, 5, '2024-09-25', '2024-10-25', 'OVERDUE', '2024-2025'),
(4, 7, '2024-09-30', '2024-10-30', 'ACTIVE', '2024-2025'),
(5, 9, '2024-10-05', '2024-11-05', 'RETURNED', '2024-2025');

-- 5. VÉRIFICATION DES TABLES CRÉÉES
-- =================================

SELECT 'Tables créées avec succès' as status;
SELECT COUNT(*) as loans_count FROM loans;
SELECT COUNT(*) as templates_count FROM templates;





