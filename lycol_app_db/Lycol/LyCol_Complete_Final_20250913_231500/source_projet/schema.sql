-- Lycol — MariaDB Schema (evolutif)
-- Charset/Engine
SET NAMES utf8mb4;
SET sql_notes = 0;

CREATE DATABASE IF NOT EXISTS `lycol`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `lycol`;

-- Helper: standard timestamp columns
-- CI4 friendly (created_at, updated_at, deleted_at)

-- ==========================
-- Noyau / Sécurité / Config
-- ==========================
CREATE TABLE IF NOT EXISTS schools (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  short_name VARCHAR(50) NULL,
  address TEXT NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(190) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS academic_years (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(20) NOT NULL, -- ex: 2024-2025
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_school_year (school_id, code),
  CONSTRAINT fk_year_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(190) NULL,
  password_hash VARCHAR(255) NULL,
  full_name VARCHAR(190) NULL,
  phone VARCHAR(50) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_user_school_username (school_id, username),
  CONSTRAINT fk_user_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  code VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS modules (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(100) NOT NULL UNIQUE, -- ex: economat, scolarite
  name VARCHAR(150) NOT NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  `key` VARCHAR(150) NOT NULL,
  `value` TEXT NULL,
  UNIQUE KEY uq_setting (school_id, `key`),
  CONSTRAINT fk_settings_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS licenses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  license_key VARCHAR(64) NOT NULL,
  license_type VARCHAR(20) NOT NULL DEFAULT 'ANNUELLE',
  start_date DATE NOT NULL,
  expiry_date DATE NOT NULL,
  status ENUM('ACTIVE','EXPIREE','SUSPENDUE') NOT NULL DEFAULT 'ACTIVE',
  last_validation DATETIME NULL,
  failure_reason VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_school_license (school_id, license_key),
  CONSTRAINT fk_license_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(150) NOT NULL,
  payload JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL,
  KEY idx_audit_school (school_id, created_at),
  CONSTRAINT fk_audit_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================
-- Élèves / Parents / Personnel
-- ==========================
CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  matricule VARCHAR(50) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  gender ENUM('M','F') NULL,
  birth_date DATE NULL,
  birth_place VARCHAR(150) NULL,
  address TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_student_matricule (school_id, matricule),
  KEY idx_student_lookup (school_id, birth_date),
  CONSTRAINT fk_student_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS guardians (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(190) NOT NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(190) NULL,
  address TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_guardian_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_guardians (
  student_id BIGINT UNSIGNED NOT NULL,
  guardian_id BIGINT UNSIGNED NOT NULL,
  relationship VARCHAR(50) NULL,
  PRIMARY KEY (student_id, guardian_id),
  CONSTRAINT fk_sg_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sg_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teachers (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(190) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_teacher_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================
-- Structure pédagogique
-- ==========================
CREATE TABLE IF NOT EXISTS levels (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL, -- ex: 6e, 3e, Terminale
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_level (school_id, name),
  CONSTRAINT fk_level_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS series (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL, -- ex: A, C, D
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_series (school_id, name),
  CONSTRAINT fk_series_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS classes (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  level_id BIGINT UNSIGNED NOT NULL,
  series_id BIGINT UNSIGNED NULL,
  name VARCHAR(100) NOT NULL, -- ex: 3e A
  capacity INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_class (school_id, academic_year_id, name),
  CONSTRAINT fk_class_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_class_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
  CONSTRAINT fk_class_level FOREIGN KEY (level_id) REFERENCES levels(id),
  CONSTRAINT fk_class_series FOREIGN KEY (series_id) REFERENCES series(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(150) NOT NULL,
  coefficient DECIMAL(5,2) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_subject (school_id, code),
  CONSTRAINT fk_subject_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_subjects (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT UNSIGNED NOT NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  teacher_id BIGINT UNSIGNED NULL,
  weekly_hours DECIMAL(5,2) NULL,
  UNIQUE KEY uq_class_subject (class_id, subject_id),
  CONSTRAINT fk_cs_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_cs_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_cs_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enrollments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  enrolled_on DATE NOT NULL,
  status ENUM('ACTIVE','TRANSFER','EXCLUDE','GRADUATED') NOT NULL DEFAULT 'ACTIVE',
  UNIQUE KEY uq_enrollment (student_id, class_id),
  CONSTRAINT fk_enr_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_enr_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS timetables (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT UNSIGNED NOT NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  teacher_id BIGINT UNSIGNED NOT NULL,
  day_of_week TINYINT NOT NULL, -- 1..7
  starts_at TIME NOT NULL,
  ends_at TIME NOT NULL,
  room VARCHAR(50) NULL,
  UNIQUE KEY uq_timetable (class_id, subject_id, teacher_id, day_of_week, starts_at),
  CONSTRAINT fk_tt_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_tt_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_tt_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Examens / Notes / Bulletins
-- ==========================
CREATE TABLE IF NOT EXISTS exam_sessions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL, -- ex: Trimestre 1
  start_date DATE NULL,
  end_date DATE NULL,
  UNIQUE KEY uq_exam_session (academic_year_id, name),
  CONSTRAINT fk_exam_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evaluations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT UNSIGNED NOT NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  exam_session_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(100) NOT NULL, -- DS1, Partiel, Soutenance, etc.
  max_score DECIMAL(6,2) NOT NULL DEFAULT 20,
  weight DECIMAL(6,3) NOT NULL DEFAULT 1,
  exam_date DATE NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_eval (class_id, subject_id, exam_session_id, label),
  CONSTRAINT fk_eval_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_session FOREIGN KEY (exam_session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grades (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  evaluation_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  score DECIMAL(6,2) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_grade (evaluation_id, student_id),
  CONSTRAINT fk_grade_eval FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
  CONSTRAINT fk_grade_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS report_cards (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  enrollment_id BIGINT UNSIGNED NOT NULL,
  exam_session_id BIGINT UNSIGNED NOT NULL,
  average DECIMAL(6,3) NULL,
  rank_in_class INT NULL,
  appreciation VARCHAR(255) NULL,
  UNIQUE KEY uq_report (enrollment_id, exam_session_id),
  CONSTRAINT fk_rc_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  CONSTRAINT fk_rc_session FOREIGN KEY (exam_session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS anonymats (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  evaluation_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(20) NOT NULL,
  UNIQUE KEY uq_anon (evaluation_id, student_id),
  UNIQUE KEY uq_anon_code (evaluation_id, code),
  CONSTRAINT fk_anon_eval FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
  CONSTRAINT fk_anon_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Présences / Discipline
-- ==========================
CREATE TABLE IF NOT EXISTS attendance (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('PRESENT','ABSENT','LATE','EXCUSED') NOT NULL DEFAULT 'PRESENT',
  remarks VARCHAR(255) NULL,
  UNIQUE KEY uq_att (class_id, student_id, attendance_date),
  CONSTRAINT fk_att_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS incidents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT UNSIGNED NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  incident_date DATE NOT NULL,
  description TEXT NOT NULL,
  created_at DATETIME NULL,
  CONSTRAINT fk_incident_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  CONSTRAINT fk_incident_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sanctions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(100) NOT NULL,
  decision_date DATE NULL,
  notes TEXT NULL,
  CONSTRAINT fk_sanction_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Bibliothèque
-- ==========================
CREATE TABLE IF NOT EXISTS books (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  isbn VARCHAR(50) NULL,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NULL,
  category VARCHAR(100) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_books_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS book_copies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  book_id BIGINT UNSIGNED NOT NULL,
  copy_code VARCHAR(50) NOT NULL,
  UNIQUE KEY uq_copy (book_id, copy_code),
  CONSTRAINT fk_copy_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subscribers (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  user_type ENUM('STUDENT','TEACHER','EXTERNAL') NOT NULL,
  student_id BIGINT UNSIGNED NULL,
  teacher_id BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  CONSTRAINT fk_sub_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
  CONSTRAINT fk_sub_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS borrows (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  copy_id BIGINT UNSIGNED NOT NULL,
  subscriber_id BIGINT UNSIGNED NOT NULL,
  borrowed_at DATETIME NOT NULL,
  due_at DATETIME NULL,
  returned_at DATETIME NULL,
  status ENUM('BORROWED','RETURNED','LATE') NOT NULL DEFAULT 'BORROWED',
  CONSTRAINT fk_borrow_copy FOREIGN KEY (copy_id) REFERENCES book_copies(id) ON DELETE CASCADE,
  CONSTRAINT fk_borrow_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blacklist (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  subscriber_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_blacklist_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Economat / Finance
-- ==========================
CREATE TABLE IF NOT EXISTS fees (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL, -- Pension, Cantine, Bus, etc.
  description VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_fee_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fee_tranches (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  fee_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(100) NOT NULL, -- Tranche 1, 2, 3
  amount DECIMAL(12,2) NOT NULL,
  due_date DATE NULL,
  UNIQUE KEY uq_tranche (fee_id, academic_year_id, label),
  CONSTRAINT fk_tranche_fee FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
  CONSTRAINT fk_tranche_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_fees (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT UNSIGNED NOT NULL,
  fee_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  amount_due DECIMAL(12,2) NOT NULL,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_student_fee (student_id, fee_id, academic_year_id),
  CONSTRAINT fk_sf_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sf_fee FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
  CONSTRAINT fk_sf_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  student_fee_id BIGINT UNSIGNED NOT NULL,
  tranche_id BIGINT UNSIGNED NULL,
  paid_amount DECIMAL(12,2) NOT NULL,
  paid_on DATETIME NOT NULL,
  method ENUM('CASH','MOBILE','BANK') NOT NULL DEFAULT 'CASH',
  reference VARCHAR(100) NULL,
  notes VARCHAR(255) NULL,
  CONSTRAINT fk_pay_student_fee FOREIGN KEY (student_fee_id) REFERENCES student_fees(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_tranche FOREIGN KEY (tranche_id) REFERENCES fee_tranches(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budgets (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(150) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  type ENUM('REVENUE','EXPENSE') NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_budget_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_budget_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  occurred_at DATETIME NOT NULL,
  label VARCHAR(150) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  direction ENUM('IN','OUT') NOT NULL,
  related_type VARCHAR(50) NULL,
  related_id BIGINT UNSIGNED NULL,
  CONSTRAINT fk_tx_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_models (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_pr_model_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  payroll_model_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(50) NOT NULL,
  label VARCHAR(150) NOT NULL,
  is_allowance TINYINT(1) NOT NULL DEFAULT 1,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pr_item (payroll_model_id, code),
  CONSTRAINT fk_pr_item_model FOREIGN KEY (payroll_model_id) REFERENCES payroll_models(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_runs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NOT NULL,
  run_month TINYINT NOT NULL,
  run_year SMALLINT NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_pr_run_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  CONSTRAINT fk_pr_run_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_slips (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  payroll_run_id BIGINT UNSIGNED NOT NULL,
  teacher_id BIGINT UNSIGNED NOT NULL,
  gross_amount DECIMAL(12,2) NOT NULL,
  net_amount DECIMAL(12,2) NOT NULL,
  created_at DATETIME NULL,
  CONSTRAINT fk_pr_slip_run FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
  CONSTRAINT fk_pr_slip_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bus_passes (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  student_id BIGINT UNSIGNED NOT NULL,
  card_number VARCHAR(50) NOT NULL,
  route VARCHAR(150) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_bus_card (student_id, card_number),
  CONSTRAINT fk_bus_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Messagerie / Providers
-- ==========================
CREATE TABLE IF NOT EXISTS message_templates (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(100) NOT NULL,
  channel ENUM('EMAIL','SMS','WHATSAPP') NOT NULL,
  subject VARCHAR(190) NULL,
  body TEXT NOT NULL,
  UNIQUE KEY uq_msg_tpl (school_id, code, channel),
  CONSTRAINT fk_tpl_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS outbox (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  channel ENUM('EMAIL','SMS','WHATSAPP') NOT NULL,
  recipient VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NULL,
  body TEXT NOT NULL,
  status ENUM('QUEUED','SENT','FAILED') NOT NULL DEFAULT 'QUEUED',
  provider_message_id VARCHAR(150) NULL,
  created_at DATETIME NOT NULL,
  sent_at DATETIME NULL,
  error_message VARCHAR(255) NULL,
  CONSTRAINT fk_outbox_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS providers (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  type ENUM('SMTP','SMS','WHATSAPP') NOT NULL,
  name VARCHAR(150) NOT NULL,
  config JSON NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_provider_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- API / Intégrations
-- ==========================
CREATE TABLE IF NOT EXISTS api_clients (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  school_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  allowed_ips TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_api_client_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_keys (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  api_client_id BIGINT UNSIGNED NOT NULL,
  key_value VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  revoked_at DATETIME NULL,
  UNIQUE KEY uq_api_key (api_client_id, key_value),
  CONSTRAINT fk_api_key_client FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================
-- Seed minimal
-- ==========================
INSERT IGNORE INTO modules (code, name, is_enabled) VALUES
 ('economat','Economat',1),
 ('scolarite','Scolarité',1),
 ('etudes','Études',1),
 ('examens','Examens',1),
 ('statistiques','Statistiques',1),
 ('discipline','Discipline',1),
 ('bibliotheque','Bibliothèque',1),
 ('messagerie','Messagerie',1),
 ('personnel','Personnel',1),
 ('securite','Sécurité',1);

SET sql_notes = 1;