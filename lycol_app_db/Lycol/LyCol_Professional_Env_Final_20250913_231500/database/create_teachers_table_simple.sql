-- =====================================================
-- CRÉATION SIMPLIFIÉE DE LA TABLE TEACHERS
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des assignations de matières aux enseignants
CREATE TABLE IF NOT EXISTS class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NULL,
    academic_year VARCHAR(9) NOT NULL DEFAULT '2024-2025',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion de données de test pour les enseignants
INSERT INTO teachers (school_id, first_name, last_name, email, phone, specialization, qualification, hire_date, is_active) VALUES
(1, 'Jean', 'Dupont', 'jean.dupont@kissai.cm', '+237679481111', 'Mathématiques', 'Master', '2020-09-01', 1),
(1, 'Marie', 'Martin', 'marie.martin@kissai.cm', '+237679481112', 'Français', 'Licence', '2021-09-01', 1),
(1, 'Pierre', 'Bernard', 'pierre.bernard@kissai.cm', '+237679481113', 'Histoire-Géographie', 'Master', '2019-09-01', 1),
(1, 'Sophie', 'Petit', 'sophie.petit@kissai.cm', '+237679481114', 'Sciences de la Vie et de la Terre', 'Doctorat', '2018-09-01', 1),
(1, 'Michel', 'Robert', 'michel.robert@kissai.cm', '+237679481115', 'Physique-Chimie', 'Agrégation', '2022-09-01', 1);

-- Vérification de la création
SELECT 'Table teachers créée avec succès' as status;
SELECT COUNT(*) as nombre_enseignants FROM teachers;








