-- =====================================================
-- LYCOL - Solution de Gestion Scolaire
-- Script de création de la base de données
-- Version: 1.0
-- Date: 2025-08-22
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS lycol_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lycol_db;

-- =====================================================
-- 1. MODULE SÉCURITÉ ET AUTHENTIFICATION
-- =====================================================

-- Table des rôles utilisateurs
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Table des sessions utilisateurs
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des licences
CREATE TABLE licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(50) NOT NULL UNIQUE,
    client_id VARCHAR(100) NOT NULL,
    license_type ENUM('TRIAL', 'BASIC', 'PRO', 'ENTERPRISE') NOT NULL,
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    max_users INT DEFAULT 100,
    features JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. MODULE CONFIGURATION GÉNÉRALE
-- =====================================================

-- Table des établissements
CREATE TABLE schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) UNIQUE,
    type ENUM('MATERNELLE', 'PRIMAIRE', 'SECONDAIRE', 'UNIVERSITAIRE', 'FORMATION') NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    logo VARCHAR(255),
    director_name VARCHAR(200),
    director_phone VARCHAR(20),
    academic_year VARCHAR(9) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des configurations
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('STRING', 'INTEGER', 'BOOLEAN', 'JSON') DEFAULT 'STRING',
    description TEXT,
    module VARCHAR(50),
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 3. MODULE ÉTUDES
-- =====================================================

-- Table des cycles d'études
CREATE TABLE cycles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    duration_years INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des séries
CREATE TABLE series (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    cycle_id INT NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cycle_id) REFERENCES cycles(id)
);

-- Table des classes
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    cycle_id INT NOT NULL,
    series_id INT,
    capacity INT DEFAULT 50,
    room_number VARCHAR(20),
    academic_year VARCHAR(9) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cycle_id) REFERENCES cycles(id),
    FOREIGN KEY (series_id) REFERENCES series(id)
);

-- Table des matières
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    coefficient DECIMAL(3,2) DEFAULT 1.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table de répartition des matières par classe
CREATE TABLE class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    hours_per_week INT DEFAULT 1,
    academic_year VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    UNIQUE KEY unique_class_subject (class_id, subject_id, academic_year)
);

-- =====================================================
-- 4. MODULE SCOLARITÉ
-- =====================================================

-- Table des élèves
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    birth_place VARCHAR(100),
    gender ENUM('M', 'F') NOT NULL,
    nationality VARCHAR(50) DEFAULT 'Camerounaise',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    photo VARCHAR(255),
    parent_name VARCHAR(200),
    parent_phone VARCHAR(20),
    parent_email VARCHAR(100),
    emergency_contact VARCHAR(20),
    blood_group VARCHAR(5),
    medical_info TEXT,
    admission_date DATE NOT NULL,
    current_class_id INT,
    academic_year VARCHAR(9) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'GRADUATED', 'TRANSFERRED', 'SUSPENDED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_class_id) REFERENCES classes(id)
);

-- Table des inscriptions
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('ENROLLED', 'WITHDRAWN', 'GRADUATED') DEFAULT 'ENROLLED',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    UNIQUE KEY unique_enrollment (student_id, class_id, academic_year)
);

-- Table des absences
CREATE TABLE absences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT,
    date DATE NOT NULL,
    period ENUM('MORNING', 'AFTERNOON', 'FULL_DAY') DEFAULT 'FULL_DAY',
    reason TEXT,
    is_justified BOOLEAN DEFAULT FALSE,
    justification_document VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des sanctions disciplinaires
CREATE TABLE disciplinary_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    action_type ENUM('WARNING', 'REPRIMAND', 'SUSPENSION', 'EXPULSION') NOT NULL,
    description TEXT NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- =====================================================
-- 5. MODULE EXAMENS
-- =====================================================

-- Table des types d'examens
CREATE TABLE exam_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    weight DECIMAL(3,2) DEFAULT 1.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des examens
CREATE TABLE exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    exam_type_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    start_date DATE,
    end_date DATE,
    total_marks DECIMAL(5,2) DEFAULT 20.00,
    passing_marks DECIMAL(5,2) DEFAULT 10.00,
    status ENUM('PLANNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PLANNED',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_type_id) REFERENCES exam_types(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des notes
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    total_marks DECIMAL(5,2) DEFAULT 20.00,
    percentage DECIMAL(5,2),
    grade VARCHAR(2),
    remarks TEXT,
    entered_by INT NOT NULL,
    entered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (exam_id) REFERENCES exams(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (entered_by) REFERENCES users(id),
    UNIQUE KEY unique_grade (student_id, exam_id, subject_id)
);

-- Table des bulletins
CREATE TABLE report_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    term VARCHAR(20) NOT NULL,
    total_marks DECIMAL(6,2),
    average DECIMAL(5,2),
    rank INT,
    total_students INT,
    class_average DECIMAL(5,2),
    remarks TEXT,
    generated_by INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (generated_by) REFERENCES users(id),
    UNIQUE KEY unique_report_card (student_id, class_id, academic_year, term)
);

-- =====================================================
-- 6. MODULE ÉCONOMAT
-- =====================================================

-- Table des types de frais
CREATE TABLE fee_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'XAF',
    frequency ENUM('ONCE', 'MONTHLY', 'QUARTERLY', 'ANNUALLY') DEFAULT 'ONCE',
    is_mandatory BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des frais par classe
CREATE TABLE class_fees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    fee_type_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
    UNIQUE KEY unique_class_fee (class_id, fee_type_id, academic_year)
);

-- Table des paiements
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    fee_type_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('CASH', 'CHECK', 'BANK_TRANSFER', 'MOBILE_MONEY') DEFAULT 'CASH',
    reference_number VARCHAR(50),
    receipt_number VARCHAR(50),
    academic_year VARCHAR(9) NOT NULL,
    term VARCHAR(20),
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Table des budgets
CREATE TABLE budgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    academic_year VARCHAR(9) NOT NULL,
    category VARCHAR(100) NOT NULL,
    budgeted_amount DECIMAL(12,2) NOT NULL,
    spent_amount DECIMAL(12,2) DEFAULT 0.00,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des dépenses
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    budget_id INT,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    supplier VARCHAR(200),
    invoice_number VARCHAR(50),
    payment_method ENUM('CASH', 'CHECK', 'BANK_TRANSFER') DEFAULT 'CASH',
    approved_by INT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES budgets(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- =====================================================
-- 7. MODULE BIBLIOTHÈQUE
-- =====================================================

-- Table des livres
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(200),
    publisher VARCHAR(200),
    publication_year INT,
    category VARCHAR(100),
    subject VARCHAR(100),
    description TEXT,
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des abonnés
CREATE TABLE library_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    user_id INT,
    member_code VARCHAR(20) UNIQUE NOT NULL,
    membership_date DATE NOT NULL,
    expiry_date DATE,
    status ENUM('ACTIVE', 'SUSPENDED', 'EXPIRED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table des emprunts
CREATE TABLE book_loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('BORROWED', 'RETURNED', 'OVERDUE', 'LOST') DEFAULT 'BORROWED',
    fine_amount DECIMAL(8,2) DEFAULT 0.00,
    notes TEXT,
    processed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (member_id) REFERENCES library_members(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- =====================================================
-- 8. MODULE MESSAGERIE
-- =====================================================

-- Table des fournisseurs de services
CREATE TABLE service_providers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    service_type ENUM('SMS', 'EMAIL', 'WHATSAPP') NOT NULL,
    api_url VARCHAR(255),
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des messages
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    message_type ENUM('SMS', 'EMAIL', 'WHATSAPP', 'NOTIFICATION') NOT NULL,
    sender_id INT NOT NULL,
    recipient_type ENUM('STUDENT', 'PARENT', 'TEACHER', 'STAFF', 'ALL') NOT NULL,
    recipient_ids JSON,
    status ENUM('DRAFT', 'SENT', 'DELIVERED', 'FAILED') DEFAULT 'DRAFT',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

-- Table des modèles de messages
CREATE TABLE message_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    message_type ENUM('SMS', 'EMAIL', 'WHATSAPP') NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 9. MODULE EMPLOI DU TEMPS
-- =====================================================

-- Table des emplois du temps
CREATE TABLE timetables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    term VARCHAR(20) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des créneaux horaires
CREATE TABLE time_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    timetable_id INT NOT NULL,
    day_of_week ENUM('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    room VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (timetable_id) REFERENCES timetables(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- =====================================================
-- 10. INSERTION DES DONNÉES DE BASE
-- =====================================================

-- Insertion des rôles de base
INSERT INTO roles (name, description, permissions) VALUES
('SUPER_ADMIN', 'Administrateur principal avec tous les droits', '{"all": true}'),
('ADMIN', 'Administrateur de l\'établissement', '{"economat": true, "scolarite": true, "etudes": true, "examens": true, "statistiques": true, "bibliotheque": true, "messagerie": true, "securite": true}'),
('DIRECTEUR', 'Directeur de l\'établissement', '{"economat": {"read": true}, "scolarite": true, "etudes": true, "examens": true, "statistiques": true, "bibliotheque": {"read": true}, "messagerie": true}'),
('ENSEIGNANT', 'Enseignant', '{"scolarite": {"read": true}, "etudes": {"read": true}, "examens": {"read": true, "write": true}, "bibliotheque": {"read": true}}'),
('SECRETAIRE', 'Secrétaire administratif', '{"economat": {"read": true, "write": true}, "scolarite": {"read": true, "write": true}, "bibliotheque": true}'),
('PARENT', 'Parent d\'élève', '{"scolarite": {"read": true}, "examens": {"read": true}}'),
('ELEVE', 'Élève', '{"examens": {"read": true}}');

-- Insertion des types d'examens
INSERT INTO exam_types (name, code, description, weight) VALUES
('Devoir', 'DEV', 'Devoir de classe', 0.30),
('Composition', 'COMP', 'Composition trimestrielle', 0.70),
('Examen', 'EXAM', 'Examen de fin d\'année', 1.00),
('Contrôle', 'CTRL', 'Contrôle continu', 0.20);

-- Insertion des cycles d'études
INSERT INTO cycles (name, code, description, duration_years) VALUES
('Maternelle', 'MAT', 'Cycle maternelle', 3),
('Primaire', 'PRI', 'Cycle primaire', 6),
('Secondaire Général', 'SEC', 'Cycle secondaire général', 7),
('Secondaire Technique', 'TECH', 'Cycle secondaire technique', 7),
('Universitaire', 'UNIV', 'Cycle universitaire', 5),
('Formation Professionnelle', 'FP', 'Formation professionnelle', 3);

-- Insertion des séries
INSERT INTO series (name, code, cycle_id, description) VALUES
('Petite Section', 'PS', 1, 'Petite section maternelle'),
('Moyenne Section', 'MS', 1, 'Moyenne section maternelle'),
('Grande Section', 'GS', 1, 'Grande section maternelle'),
('CP', 'CP', 2, 'Cours préparatoire'),
('CE1', 'CE1', 2, 'Cours élémentaire 1ère année'),
('CE2', 'CE2', 2, 'Cours élémentaire 2ème année'),
('CM1', 'CM1', 2, 'Cours moyen 1ère année'),
('CM2', 'CM2', 2, 'Cours moyen 2ème année'),
('6ème', '6E', 3, 'Sixième'),
('5ème', '5E', 3, 'Cinquième'),
('4ème', '4E', 3, 'Quatrième'),
('3ème', '3E', 3, 'Troisième'),
('2nde', '2ND', 3, 'Seconde'),
('1ère', '1ER', 3, 'Première'),
('Terminale', 'TER', 3, 'Terminale');

-- Insertion des matières de base
INSERT INTO subjects (name, code, description, coefficient) VALUES
('Mathématiques', 'MATH', 'Mathématiques', 4.00),
('Français', 'FRAN', 'Français', 3.00),
('Anglais', 'ANG', 'Anglais', 2.00),
('Histoire-Géographie', 'HIST', 'Histoire et Géographie', 2.00),
('Sciences', 'SCI', 'Sciences', 2.00),
('Education Physique', 'EPS', 'Education Physique et Sportive', 1.00),
('Informatique', 'INFO', 'Informatique', 1.00),
('Philosophie', 'PHILO', 'Philosophie', 2.00),
('Physique', 'PHY', 'Physique', 3.00),
('Chimie', 'CHIM', 'Chimie', 2.00),
('Biologie', 'BIO', 'Biologie', 2.00);

-- Insertion des types de frais
INSERT INTO fee_types (name, code, description, amount, frequency) VALUES
('Frais d\'inscription', 'INS', 'Frais d\'inscription annuels', 50000.00, 'ONCE'),
('Frais de scolarité', 'SCOL', 'Frais de scolarité mensuels', 25000.00, 'MONTHLY'),
('Frais d\'examen', 'EXAM', 'Frais d\'examen', 15000.00, 'ONCE'),
('Frais de laboratoire', 'LAB', 'Frais de laboratoire', 10000.00, 'QUARTERLY'),
('Frais de bibliothèque', 'BIB', 'Frais de bibliothèque', 5000.00, 'ANNUALLY'),
('Frais de transport', 'TRANS', 'Frais de transport scolaire', 15000.00, 'MONTHLY');

-- Insertion des modèles de messages
INSERT INTO message_templates (name, subject, content, message_type, variables) VALUES
('Bulletin disponible', 'Bulletin scolaire disponible', 'Bonjour {parent_name}, le bulletin de {student_name} pour le {term} est disponible. Connectez-vous à votre espace parent pour le consulter.', 'EMAIL', '["parent_name", "student_name", "term"]'),
('Absence élève', 'Absence de votre enfant', 'Bonjour {parent_name}, votre enfant {student_name} est absent aujourd\'hui. Merci de nous contacter pour justifier cette absence.', 'SMS', '["parent_name", "student_name"]'),
('Rappel paiement', 'Rappel de paiement', 'Bonjour {parent_name}, un rappel pour le paiement des frais de scolarité de {student_name}. Montant restant: {amount} FCFA.', 'SMS', '["parent_name", "student_name", "amount"]'),
('Conseil de classe', 'Conseil de classe', 'Bonjour {parent_name}, le conseil de classe de {student_name} aura lieu le {date} à {time}. Votre présence est souhaitée.', 'WHATSAPP', '["parent_name", "student_name", "date", "time"]');

-- Insertion des configurations de base
INSERT INTO settings (setting_key, setting_value, setting_type, description, module) VALUES
('school_name', 'École Modèle', 'STRING', 'Nom de l\'établissement', 'general'),
('school_address', 'Douala, Cameroun', 'STRING', 'Adresse de l\'établissement', 'general'),
('school_phone', '+237 123 456 789', 'STRING', 'Téléphone de l\'établissement', 'general'),
('school_email', 'contact@ecole-modele.cm', 'STRING', 'Email de l\'établissement', 'general'),
('academic_year', '2024-2025', 'STRING', 'Année académique en cours', 'general'),
('currency', 'XAF', 'STRING', 'Devise utilisée', 'economat'),
('smtp_host', 'smtp.gmail.com', 'STRING', 'Serveur SMTP', 'messagerie'),
('smtp_port', '587', 'INTEGER', 'Port SMTP', 'messagerie'),
('smtp_username', '', 'STRING', 'Nom d\'utilisateur SMTP', 'messagerie'),
('smtp_password', '', 'STRING', 'Mot de passe SMTP', 'messagerie'),
('whatsapp_api_key', '', 'STRING', 'Clé API WhatsApp', 'messagerie'),
('sms_api_key', '', 'STRING', 'Clé API SMS', 'messagerie'),
('license_check_interval', '20', 'INTEGER', 'Intervalle de vérification de licence (minutes)', 'security'),
('max_login_attempts', '3', 'INTEGER', 'Nombre maximum de tentatives de connexion', 'security'),
('session_timeout', '30', 'INTEGER', 'Timeout de session (minutes)', 'security');

-- =====================================================
-- 11. CRÉATION DES INDEX POUR LES PERFORMANCES
-- =====================================================

-- Index pour les performances des requêtes
CREATE INDEX idx_students_matricule ON students(matricule);
CREATE INDEX idx_students_class ON students(current_class_id);
CREATE INDEX idx_students_academic_year ON students(academic_year);
CREATE INDEX idx_grades_student_exam ON grades(student_id, exam_id);
CREATE INDEX idx_grades_subject ON grades(subject_id);
CREATE INDEX idx_absences_student_date ON absences(student_id, date);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_book_loans_member ON book_loans(member_id);
CREATE INDEX idx_book_loans_status ON book_loans(status);
CREATE INDEX idx_messages_recipient ON messages(recipient_type, recipient_ids);
CREATE INDEX idx_messages_status ON messages(status);
CREATE INDEX idx_licenses_key ON licenses(license_key);
CREATE INDEX idx_licenses_expiry ON licenses(expiry_date);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_expires ON user_sessions(expires_at);

-- =====================================================
-- 12. CRÉATION DES VUES POUR LES RAPPORTS
-- =====================================================

-- Vue pour les statistiques des élèves par classe
CREATE VIEW v_students_by_class AS
SELECT 
    c.name as class_name,
    c.code as class_code,
    COUNT(s.id) as total_students,
    COUNT(CASE WHEN s.gender = 'M' THEN 1 END) as male_count,
    COUNT(CASE WHEN s.gender = 'F' THEN 1 END) as female_count,
    c.academic_year
FROM classes c
LEFT JOIN students s ON c.id = s.current_class_id AND s.status = 'ACTIVE'
GROUP BY c.id, c.name, c.code, c.academic_year;

-- Vue pour les moyennes par classe
CREATE VIEW v_class_averages AS
SELECT 
    c.name as class_name,
    c.code as class_code,
    e.name as exam_name,
    AVG(g.marks_obtained) as average_marks,
    COUNT(g.id) as total_students,
    c.academic_year
FROM classes c
JOIN exams e ON c.id = e.class_id
JOIN grades g ON e.id = g.exam_id
GROUP BY c.id, c.name, c.code, e.id, e.name, c.academic_year;

-- Vue pour les paiements en retard
CREATE VIEW v_overdue_payments AS
SELECT 
    s.matricule,
    s.first_name,
    s.last_name,
    c.name as class_name,
    ft.name as fee_type,
    ft.amount as expected_amount,
    COALESCE(SUM(p.amount_paid), 0) as paid_amount,
    (ft.amount - COALESCE(SUM(p.amount_paid), 0)) as remaining_amount
FROM students s
JOIN classes c ON s.current_class_id = c.id
JOIN class_fees cf ON c.id = cf.class_id
JOIN fee_types ft ON cf.fee_type_id = ft.id
LEFT JOIN payments p ON s.id = p.student_id AND ft.id = p.fee_type_id AND p.academic_year = c.academic_year
WHERE s.status = 'ACTIVE'
GROUP BY s.id, s.matricule, s.first_name, s.last_name, c.name, ft.name, ft.amount
HAVING remaining_amount > 0;

-- =====================================================
-- 13. CRÉATION DES TRIGGERS POUR LA MAINTENANCE
-- =====================================================

-- Trigger pour mettre à jour les copies disponibles lors d'un emprunt
DELIMITER //
CREATE TRIGGER after_book_loan_insert
AFTER INSERT ON book_loans
FOR EACH ROW
BEGIN
    UPDATE books 
    SET available_copies = available_copies - 1 
    WHERE id = NEW.book_id;
END//

-- Trigger pour mettre à jour les copies disponibles lors d'un retour
CREATE TRIGGER after_book_loan_update
AFTER UPDATE ON book_loans
FOR EACH ROW
BEGIN
    IF NEW.status = 'RETURNED' AND OLD.status != 'RETURNED' THEN
        UPDATE books 
        SET available_copies = available_copies + 1 
        WHERE id = NEW.book_id;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- 14. CRÉATION DES PROCÉDURES STOCKÉES
-- =====================================================

-- Procédure pour calculer les moyennes d'un élève
DELIMITER //
CREATE PROCEDURE CalculateStudentAverage(
    IN p_student_id INT,
    IN p_exam_id INT,
    OUT p_average DECIMAL(5,2)
)
BEGIN
    SELECT AVG(marks_obtained) INTO p_average
    FROM grades
    WHERE student_id = p_student_id AND exam_id = p_exam_id;
END//

-- Procédure pour générer un bulletin
CREATE PROCEDURE GenerateReportCard(
    IN p_student_id INT,
    IN p_academic_year VARCHAR(9),
    IN p_term VARCHAR(20)
)
BEGIN
    DECLARE v_class_id INT;
    DECLARE v_total_marks DECIMAL(6,2);
    DECLARE v_average DECIMAL(5,2);
    
    -- Récupérer la classe de l'élève
    SELECT current_class_id INTO v_class_id
    FROM students
    WHERE id = p_student_id;
    
    -- Calculer le total des notes
    SELECT SUM(g.marks_obtained * cs.hours_per_week) INTO v_total_marks
    FROM grades g
    JOIN exams e ON g.exam_id = e.id
    JOIN class_subjects cs ON g.subject_id = cs.subject_id AND cs.class_id = v_class_id
    WHERE g.student_id = p_student_id 
    AND e.academic_year = p_academic_year
    AND e.exam_type_id IN (SELECT id FROM exam_types WHERE code IN ('COMP', 'EXAM'));
    
    -- Calculer la moyenne
    SELECT AVG(g.marks_obtained) INTO v_average
    FROM grades g
    JOIN exams e ON g.exam_id = e.id
    WHERE g.student_id = p_student_id 
    AND e.academic_year = p_academic_year
    AND e.exam_type_id IN (SELECT id FROM exam_types WHERE code IN ('COMP', 'EXAM'));
    
    -- Insérer ou mettre à jour le bulletin
    INSERT INTO report_cards (student_id, class_id, academic_year, term, total_marks, average, generated_by)
    VALUES (p_student_id, v_class_id, p_academic_year, p_term, v_total_marks, v_average, 1)
    ON DUPLICATE KEY UPDATE
    total_marks = v_total_marks,
    average = v_average,
    generated_at = CURRENT_TIMESTAMP;
END//

DELIMITER ;

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================

-- Affichage des informations de création
SELECT 'Base de données LyCol créée avec succès!' as message;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'lycol_db';
SELECT COUNT(*) as total_roles FROM roles;
SELECT COUNT(*) as total_subjects FROM subjects;
SELECT COUNT(*) as total_cycles FROM cycles;








