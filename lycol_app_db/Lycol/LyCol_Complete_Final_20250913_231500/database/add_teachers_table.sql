-- =====================================================
-- AJOUT DE LA TABLE TEACHERS MANQUANTE
-- Script pour corriger le module enseignants
-- =====================================================

-- Table des enseignants
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL DEFAULT 1,
    user_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
    specialization VARCHAR(200),
    qualification VARCHAR(200),
    hire_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des assignations de matières aux enseignants
CREATE TABLE IF NOT EXISTS class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NULL,
    academic_year VARCHAR(9) NOT NULL DEFAULT '2024-2025',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_class_subject (class_id, subject_id, academic_year)
);

-- Ajout de la colonne teacher_id à la table classes pour le responsable principal
ALTER TABLE classes ADD COLUMN IF NOT EXISTS teacher_id INT NULL;

-- Supprimer la contrainte existante si elle existe
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'lycol_db' 
    AND TABLE_NAME = 'classes' 
    AND COLUMN_NAME = 'teacher_id' 
    AND REFERENCED_TABLE_NAME = 'teachers'
    LIMIT 1
);

SET @sql = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE classes DROP FOREIGN KEY ', @constraint_name), 
    'SELECT "No constraint to drop"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ajouter la nouvelle contrainte
ALTER TABLE classes ADD CONSTRAINT fk_classes_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL;

-- Insertion de données de test pour les enseignants
INSERT INTO teachers (school_id, first_name, last_name, email, phone, specialization, qualification, hire_date, is_active) VALUES
(1, 'Jean', 'Dupont', 'jean.dupont@kissai.cm', '+237679481111', 'Mathématiques', 'Master', '2020-09-01', 1),
(1, 'Marie', 'Martin', 'marie.martin@kissai.cm', '+237679481112', 'Français', 'Licence', '2021-09-01', 1),
(1, 'Pierre', 'Bernard', 'pierre.bernard@kissai.cm', '+237679481113', 'Histoire-Géographie', 'Master', '2019-09-01', 1),
(1, 'Sophie', 'Petit', 'sophie.petit@kissai.cm', '+237679481114', 'Sciences de la Vie et de la Terre', 'Doctorat', '2018-09-01', 1),
(1, 'Michel', 'Robert', 'michel.robert@kissai.cm', '+237679481115', 'Physique-Chimie', 'Agrégation', '2022-09-01', 1);

-- Mise à jour de la table users pour ajouter des enseignants
UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'enseignant') WHERE email LIKE '%@kissai.cm';

-- Association des comptes utilisateurs aux enseignants
UPDATE teachers SET user_id = (SELECT id FROM users WHERE email = teachers.email) WHERE email IN (SELECT email FROM users);
