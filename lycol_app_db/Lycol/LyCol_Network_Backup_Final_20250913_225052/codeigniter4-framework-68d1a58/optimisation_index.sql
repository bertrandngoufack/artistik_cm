-- ========================================
-- OPTIMISATION DES INDEX DE BASE DE DONNÉES
-- KISSAI SCHOOL - Expert Senior
-- ========================================

-- 1. INDEX POUR LA TABLE STUDENTS
-- ===============================

-- Index sur les colonnes fréquemment utilisées
CREATE INDEX IF NOT EXISTS idx_students_status ON students(status);
CREATE INDEX IF NOT EXISTS idx_students_gender ON students(gender);
CREATE INDEX IF NOT EXISTS idx_students_admission_date ON students(admission_date);
CREATE INDEX IF NOT EXISTS idx_students_parent_phone ON students(parent_phone);
CREATE INDEX IF NOT EXISTS idx_students_parent_email ON students(parent_email);

-- Index composite pour les recherches fréquentes
CREATE INDEX IF NOT EXISTS idx_students_class_year ON students(current_class_id, academic_year);
CREATE INDEX IF NOT EXISTS idx_students_name_search ON students(first_name, last_name);

-- 2. INDEX POUR LA TABLE PAYMENTS
-- ===============================

-- Index sur les colonnes de paiement
CREATE INDEX IF NOT EXISTS idx_payments_amount ON payments(amount);
CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status);
CREATE INDEX IF NOT EXISTS idx_payments_method ON payments(payment_method);
CREATE INDEX IF NOT EXISTS idx_payments_academic_year ON payments(academic_year);

-- Index composite pour les rapports
CREATE INDEX IF NOT EXISTS idx_payments_student_date ON payments(student_id, payment_date);
CREATE INDEX IF NOT EXISTS idx_payments_status_date ON payments(status, payment_date);

-- 3. INDEX POUR LA TABLE BOOKS
-- ============================

-- Index sur les livres
CREATE INDEX IF NOT EXISTS idx_books_title ON books(title);
CREATE INDEX IF NOT EXISTS idx_books_author ON books(author);
CREATE INDEX IF NOT EXISTS idx_books_isbn ON books(isbn);
CREATE INDEX IF NOT EXISTS idx_books_status ON books(status);
CREATE INDEX IF NOT EXISTS idx_books_category ON books(category);

-- Index composite pour la recherche
CREATE INDEX IF NOT EXISTS idx_books_title_author ON books(title, author);

-- 4. INDEX POUR LA TABLE LOANS (NOUVELLE TABLE)
-- =============================================

-- Index déjà créés dans la création de table, mais vérification
CREATE INDEX IF NOT EXISTS idx_loans_return_date ON loans(return_date);
CREATE INDEX IF NOT EXISTS idx_loans_loan_due ON loans(loan_date, due_date);

-- 5. INDEX POUR LA TABLE TEMPLATES (NOUVELLE TABLE)
-- ================================================

-- Index déjà créés dans la création de table, mais vérification
CREATE INDEX IF NOT EXISTS idx_templates_name ON templates(name);

-- 6. INDEX POUR LA TABLE GRADES
-- =============================

CREATE INDEX IF NOT EXISTS idx_grades_student ON grades(student_id);
CREATE INDEX IF NOT EXISTS idx_grades_exam ON grades(exam_id);
CREATE INDEX IF NOT EXISTS idx_grades_subject ON grades(subject_id);
CREATE INDEX IF NOT EXISTS idx_grades_score ON grades(score);
CREATE INDEX IF NOT EXISTS idx_grades_academic_year ON grades(academic_year);

-- Index composite pour les rapports
CREATE INDEX IF NOT EXISTS idx_grades_student_subject ON grades(student_id, subject_id);
CREATE INDEX IF NOT EXISTS idx_grades_exam_subject ON grades(exam_id, subject_id);

-- 7. INDEX POUR LA TABLE ABSENCES
-- ===============================

CREATE INDEX IF NOT EXISTS idx_absences_student ON absences(student_id);
CREATE INDEX IF NOT EXISTS idx_absences_date ON absences(absence_date);
CREATE INDEX IF NOT EXISTS idx_absences_reason ON absences(reason);
CREATE INDEX IF NOT EXISTS idx_absences_academic_year ON absences(academic_year);

-- Index composite
CREATE INDEX IF NOT EXISTS idx_absences_student_date ON absences(student_id, absence_date);

-- 8. INDEX POUR LA TABLE MESSAGES
-- ===============================

CREATE INDEX IF NOT EXISTS idx_messages_type ON messages(type);
CREATE INDEX IF NOT EXISTS idx_messages_status ON messages(status);
CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at);
CREATE INDEX IF NOT EXISTS idx_messages_recipient ON messages(recipient);

-- Index composite
CREATE INDEX IF NOT EXISTS idx_messages_type_status ON messages(type, status);

-- 9. VÉRIFICATION DES INDEX CRÉÉS
-- ===============================

SELECT 
    'Index créés avec succès' as status,
    COUNT(*) as total_indexes
FROM information_schema.statistics 
WHERE table_schema = 'lycol_db' 
AND index_name LIKE 'idx_%';

-- 10. ANALYSE DES PERFORMANCES
-- ============================

-- Vérification des index sur les tables principales
SHOW INDEX FROM students WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM payments WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM books WHERE Key_name LIKE 'idx_%';





