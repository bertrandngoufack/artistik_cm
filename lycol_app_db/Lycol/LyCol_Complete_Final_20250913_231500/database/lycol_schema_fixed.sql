-- =====================================================
-- SCRIPT SQL CORRIGÉ POUR LYCOL (KISSAI SCHOOL)
-- Base de données : lycol_db
-- =====================================================

-- Configuration de la base de données
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. CRÉATION DES TABLES PRINCIPALES
-- =====================================================

-- Table des rôles utilisateurs
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Table des écoles
CREATE TABLE IF NOT EXISTS schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(100),
    logo_url VARCHAR(255),
    academic_year VARCHAR(9) DEFAULT '2024-2025',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des cycles éducatifs
CREATE TABLE IF NOT EXISTS cycles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des niveaux
CREATE TABLE IF NOT EXISTS levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL,
    cycle_id INT NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cycle_id) REFERENCES cycles(id)
);

-- Table des séries
CREATE TABLE IF NOT EXISTS series (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL,
    level_id INT NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (level_id) REFERENCES levels(id)
);

-- Table des classes
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    level_id INT NOT NULL,
    series_id INT NULL,
    academic_year VARCHAR(9) NOT NULL,
    capacity INT DEFAULT 40,
    current_students INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (level_id) REFERENCES levels(id),
    FOREIGN KEY (series_id) REFERENCES series(id)
);

-- Table des matières
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    coefficient DECIMAL(3,2) DEFAULT 1.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des étudiants
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(100),
    nationality VARCHAR(50) DEFAULT 'Camerounaise',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    parent_name VARCHAR(100),
    parent_phone VARCHAR(20),
    parent_email VARCHAR(100),
    current_class_id INT,
    admission_date DATE,
    status ENUM('ACTIVE', 'INACTIVE', 'GRADUATED', 'TRANSFERRED') DEFAULT 'ACTIVE',
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_class_id) REFERENCES classes(id)
);

-- Table des types de frais
CREATE TABLE IF NOT EXISTS fee_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    frequency ENUM('ONCE', 'MONTHLY', 'QUARTERLY', 'YEARLY') DEFAULT 'YEARLY',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    fee_type_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('CASH', 'CHECK', 'BANK_TRANSFER', 'MOBILE_MONEY') DEFAULT 'CASH',
    reference_number VARCHAR(50),
    academic_year VARCHAR(9) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id)
);

-- Table des examens
CREATE TABLE IF NOT EXISTS exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    exam_type ENUM('CONTINUOUS', 'MIDTERM', 'FINAL', 'COMPETITIVE') DEFAULT 'CONTINUOUS',
    exam_date DATE NOT NULL,
    total_marks DECIMAL(5,2) DEFAULT 20.00,
    coefficient DECIMAL(3,2) DEFAULT 1.00,
    status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Table des notes
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (exam_id) REFERENCES exams(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Table des absences
CREATE TABLE IF NOT EXISTS absences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    reason TEXT,
    justified BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des sanctions disciplinaires
CREATE TABLE IF NOT EXISTS discipline (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    incident_date DATE NOT NULL,
    incident_description TEXT NOT NULL,
    sanction_type ENUM('WARNING', 'SUSPENSION', 'EXPULSION', 'OTHER') DEFAULT 'WARNING',
    sanction_details TEXT,
    duration_days INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des livres
CREATE TABLE IF NOT EXISTS books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100),
    isbn VARCHAR(20),
    category VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des emprunts de livres
CREATE TABLE IF NOT EXISTS book_loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    member_type ENUM('STUDENT', 'STAFF') DEFAULT 'STUDENT',
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('BORROWED', 'RETURNED', 'OVERDUE', 'LOST') DEFAULT 'BORROWED',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Table des messages
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    recipient_type ENUM('ALL', 'STUDENTS', 'PARENTS', 'STAFF', 'SPECIFIC') DEFAULT 'ALL',
    recipient_ids JSON,
    sender_id INT NOT NULL,
    status ENUM('DRAFT', 'SENT', 'DELIVERED', 'FAILED') DEFAULT 'DRAFT',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

-- Table des templates de messages
CREATE TABLE IF NOT EXISTS message_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des licences
CREATE TABLE IF NOT EXISTS licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    client_id VARCHAR(50) NOT NULL,
    license_type ENUM('TRIAL', 'ANNUAL', 'BIENNIAL') DEFAULT 'TRIAL',
    issued_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('ACTIVE', 'EXPIRED', 'REVOKED') DEFAULT 'ACTIVE',
    features JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des sessions utilisateur
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- 2. DONNÉES INITIALES
-- =====================================================

-- Insertion des rôles
INSERT INTO roles (name, description) VALUES
('admin', 'Administrateur système'),
('directeur', 'Directeur de l\'établissement'),
('secretaire', 'Secrétaire administratif'),
('enseignant', 'Enseignant'),
('parent', 'Parent d\'élève');

-- Insertion des utilisateurs par défaut
INSERT INTO users (username, email, password, first_name, last_name, role_id) VALUES
('admin', 'admin@kissai.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Système', 1),
('directeur', 'directeur@kissai.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Directeur', 'École', 2),
('secretaire', 'secretaire@kissai.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Secrétaire', 'Admin', 3),
('enseignant', 'enseignant@kissai.cm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Enseignant', 'Test', 4);

-- Insertion de l'école par défaut
INSERT INTO schools (name, code, address, phone, email) VALUES
('KISSAI SCHOOL', 'KISSAI001', 'Douala, Cameroun', '+237 123456789', 'contact@kissai.cm');

-- Insertion des cycles
INSERT INTO cycles (name, code, description) VALUES
('Maternelle', 'MAT', 'Cycle maternel'),
('Primaire', 'PRI', 'Cycle primaire'),
('Secondaire', 'SEC', 'Cycle secondaire'),
('Supérieur', 'SUP', 'Cycle supérieur');

-- Insertion des niveaux
INSERT INTO levels (name, code, cycle_id, description) VALUES
('Petite Section', 'PS', 1, 'Petite section maternelle'),
('Moyenne Section', 'MS', 1, 'Moyenne section maternelle'),
('Grande Section', 'GS', 1, 'Grande section maternelle'),
('CP', 'CP', 2, 'Cours préparatoire'),
('CE1', 'CE1', 2, 'Cours élémentaire 1'),
('CE2', 'CE2', 2, 'Cours élémentaire 2'),
('CM1', 'CM1', 2, 'Cours moyen 1'),
('CM2', 'CM2', 2, 'Cours moyen 2'),
('6ème', '6E', 3, 'Sixième'),
('5ème', '5E', 3, 'Cinquième'),
('4ème', '4E', 3, 'Quatrième'),
('3ème', '3E', 3, 'Troisième'),
('2nde', '2ND', 3, 'Seconde'),
('1ère', '1ERE', 3, 'Première'),
('Tle', 'TLE', 3, 'Terminale');

-- Insertion des matières
INSERT INTO subjects (name, code, description, coefficient) VALUES
('Mathématiques', 'MATH', 'Mathématiques', 4.00),
('Français', 'FR', 'Français', 4.00),
('Anglais', 'EN', 'Anglais', 3.00),
('Histoire-Géographie', 'HG', 'Histoire et Géographie', 2.00),
('Sciences', 'SC', 'Sciences', 3.00),
('Éducation physique', 'EPS', 'Éducation physique et sportive', 1.00);

-- Insertion des types de frais
INSERT INTO fee_types (name, description, amount, frequency) VALUES
('Frais de scolarité', 'Frais de scolarité annuels', 150000.00, 'YEARLY'),
('Frais d\'inscription', 'Frais d\'inscription', 25000.00, 'ONCE'),
('Frais de cantine', 'Frais de cantine mensuels', 15000.00, 'MONTHLY'),
('Frais de transport', 'Frais de transport scolaire', 20000.00, 'MONTHLY');

-- Insertion d'une licence d'essai
INSERT INTO licenses (license_key, client_id, license_type, issued_date, expiry_date, status) VALUES
('TRIAL-KISSAI-2025-001', 'KISSAI_SCHOOL', 'TRIAL', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'ACTIVE');

-- =====================================================
-- 3. INDEX POUR LES PERFORMANCES
-- =====================================================

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_students_matricule ON students(matricule);
CREATE INDEX idx_students_class ON students(current_class_id);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_grades_student ON grades(student_id);
CREATE INDEX idx_grades_exam ON grades(exam_id);
CREATE INDEX idx_absences_student ON absences(student_id);
CREATE INDEX idx_absences_date ON absences(date);
CREATE INDEX idx_licenses_key ON licenses(license_key);
CREATE INDEX idx_licenses_expiry ON licenses(expiry_date);

SET FOREIGN_KEY_CHECKS = 1;




