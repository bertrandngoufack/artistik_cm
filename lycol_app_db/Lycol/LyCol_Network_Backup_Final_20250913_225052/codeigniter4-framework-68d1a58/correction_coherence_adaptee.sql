-- ==========================================
-- SCRIPT DE CORRECTION DE LA COHÉRENCE ADAPTÉ
-- ENTRE LES MODULES ÉCONOMAT, SCOLARITÉ ET ÉTUDES
-- ==========================================

-- IMPORTANT: Exécuter ce script après avoir fait une sauvegarde de la base de données
-- BACKUP: mysqldump -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db > backup_before_coherence_fix.sql

USE lycol_db;

-- ==========================================
-- 1. CORRECTION DES RELATIONS ORPHELINES
-- ==========================================

-- 1.1 Supprimer les paiements sans élève valide
DELETE FROM payments 
WHERE student_id IS NULL 
   OR student_id = 0 
   OR student_id NOT IN (SELECT id FROM students);

-- 1.2 Supprimer les paiements sans type de frais valide
DELETE FROM payments 
WHERE fee_type_id IS NULL 
   OR fee_type_id = 0 
   OR fee_type_id NOT IN (SELECT id FROM fee_types);

-- 1.3 Supprimer les emplois du temps sans classe valide
DELETE FROM timetables 
WHERE class_id IS NULL 
   OR class_id = 0 
   OR class_id NOT IN (SELECT id FROM classes);

-- 1.4 Supprimer les emplois du temps sans matière valide
DELETE FROM timetables 
WHERE subject_id IS NULL 
   OR subject_id = 0 
   OR subject_id NOT IN (SELECT id FROM subjects);

-- 1.5 Supprimer les emplois du temps sans enseignant valide
DELETE FROM timetables 
WHERE teacher_id IS NULL 
   OR teacher_id = 0 
   OR teacher_id NOT IN (SELECT id FROM teachers);

-- 1.6 Supprimer les assignations sans enseignant valide
DELETE FROM teacher_assignments 
WHERE teacher_id IS NULL 
   OR teacher_id = 0 
   OR teacher_id NOT IN (SELECT id FROM teachers);

-- 1.7 Supprimer les assignations sans classe valide
DELETE FROM teacher_assignments 
WHERE class_id IS NULL 
   OR class_id = 0 
   OR class_id NOT IN (SELECT id FROM classes);

-- 1.8 Supprimer les assignations sans matière valide
DELETE FROM teacher_assignments 
WHERE subject_id IS NULL 
   OR subject_id = 0 
   OR subject_id NOT IN (SELECT id FROM subjects);

-- ==========================================
-- 2. CORRECTION DES DONNÉES MANQUANTES
-- ==========================================

-- 2.1 Assigner une classe par défaut aux élèves sans classe
UPDATE students 
SET current_class_id = (
    SELECT id FROM classes 
    WHERE is_active = 1 
    ORDER BY level ASC, name ASC 
    LIMIT 1
)
WHERE current_class_id IS NULL 
   OR current_class_id = 0;

-- 2.2 Assigner un cycle par défaut aux classes sans cycle
UPDATE classes 
SET cycle_id = (
    SELECT id FROM cycles 
    WHERE is_active = 1 
    ORDER BY name ASC 
    LIMIT 1
)
WHERE cycle_id IS NULL 
   OR cycle_id = 0;

-- 2.3 Standardiser les années académiques
UPDATE students 
SET academic_year = '2024-2025' 
WHERE academic_year IS NULL 
   OR academic_year = '';

UPDATE payments 
SET academic_year = '2024-2025' 
WHERE academic_year IS NULL 
   OR academic_year = '';

-- ==========================================
-- 3. AJOUT DES CONTRAINTES DE CLÉS ÉTRANGÈRES (SI ELLES N'EXISTENT PAS)
-- ==========================================

-- 3.1 Contraintes pour la table students
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'students' AND REFERENCED_TABLE_NAME = 'classes') = 0,
    'ALTER TABLE students ADD CONSTRAINT fk_students_class FOREIGN KEY (current_class_id) REFERENCES classes(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Constraint fk_students_class already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3.2 Contraintes pour la table classes
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'classes' AND REFERENCED_TABLE_NAME = 'cycles') = 0,
    'ALTER TABLE classes ADD CONSTRAINT fk_classes_cycle FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "Constraint fk_classes_cycle already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3.3 Contraintes pour la table payments
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'payments' AND REFERENCED_TABLE_NAME = 'students') = 0,
    'ALTER TABLE payments ADD CONSTRAINT fk_payments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "Constraint fk_payments_student already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'payments' AND REFERENCED_TABLE_NAME = 'fee_types') = 0,
    'ALTER TABLE payments ADD CONSTRAINT fk_payments_fee_type FOREIGN KEY (fee_type_id) REFERENCES fee_types(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "Constraint fk_payments_fee_type already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3.4 Contraintes pour la table timetables
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'classes') = 0,
    'ALTER TABLE timetables ADD CONSTRAINT fk_timetables_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "Constraint fk_timetables_class already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'subjects') = 0,
    'ALTER TABLE timetables ADD CONSTRAINT fk_timetables_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "Constraint fk_timetables_subject already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'timetables' AND REFERENCED_TABLE_NAME = 'teachers') = 0,
    'ALTER TABLE timetables ADD CONSTRAINT fk_timetables_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "Constraint fk_timetables_teacher already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3.5 Contraintes pour la table teacher_assignments
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'teachers') = 0,
    'ALTER TABLE teacher_assignments ADD CONSTRAINT fk_assignments_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "Constraint fk_assignments_teacher already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'classes') = 0,
    'ALTER TABLE teacher_assignments ADD CONSTRAINT fk_assignments_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "Constraint fk_assignments_class already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = 'teacher_assignments' AND REFERENCED_TABLE_NAME = 'subjects') = 0,
    'ALTER TABLE teacher_assignments ADD CONSTRAINT fk_assignments_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "Constraint fk_assignments_subject already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==========================================
-- 4. AJOUT DES INDEX POUR OPTIMISER LES PERFORMANCES (SI ILS N'EXISTENT PAS)
-- ==========================================

-- 4.1 Index pour les jointures fréquentes
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'students' AND INDEX_NAME = 'idx_students_class_id') = 0,
    'CREATE INDEX idx_students_class_id ON students(current_class_id)',
    'SELECT "Index idx_students_class_id already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'students' AND INDEX_NAME = 'idx_students_academic_year') = 0,
    'CREATE INDEX idx_students_academic_year ON students(academic_year)',
    'SELECT "Index idx_students_academic_year already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'students' AND INDEX_NAME = 'idx_students_status') = 0,
    'CREATE INDEX idx_students_status ON students(status)',
    'SELECT "Index idx_students_status already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'classes' AND INDEX_NAME = 'idx_classes_cycle_id') = 0,
    'CREATE INDEX idx_classes_cycle_id ON classes(cycle_id)',
    'SELECT "Index idx_classes_cycle_id already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'classes' AND INDEX_NAME = 'idx_classes_active') = 0,
    'CREATE INDEX idx_classes_active ON classes(is_active)',
    'SELECT "Index idx_classes_active already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_student_id') = 0,
    'CREATE INDEX idx_payments_student_id ON payments(student_id)',
    'SELECT "Index idx_payments_student_id already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_fee_type_id') = 0,
    'CREATE INDEX idx_payments_fee_type_id ON payments(fee_type_id)',
    'SELECT "Index idx_payments_fee_type_id already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_academic_year') = 0,
    'CREATE INDEX idx_payments_academic_year ON payments(academic_year)',
    'SELECT "Index idx_payments_academic_year already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_date') = 0,
    'CREATE INDEX idx_payments_date ON payments(payment_date)',
    'SELECT "Index idx_payments_date already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==========================================
-- 5. CRÉATION DE VUES POUR SIMPLIFIER LES REQUÊTES
-- ==========================================

-- 5.1 Vue pour les élèves avec leurs informations complètes
CREATE OR REPLACE VIEW v_students_complete AS
SELECT 
    s.*,
    c.name as class_name,
    c.code as class_code,
    cy.name as cycle_name,
    cy.code as cycle_code
FROM students s
LEFT JOIN classes c ON s.current_class_id = c.id
LEFT JOIN cycles cy ON c.cycle_id = cy.id;

-- 5.2 Vue pour les paiements avec détails
CREATE OR REPLACE VIEW v_payments_complete AS
SELECT 
    p.*,
    s.first_name,
    s.last_name,
    s.matricule,
    c.name as class_name,
    cy.name as cycle_name,
    ft.name as fee_type_name,
    ft.amount as fee_amount
FROM payments p
JOIN students s ON p.student_id = s.id
LEFT JOIN classes c ON s.current_class_id = c.id
LEFT JOIN cycles cy ON c.cycle_id = cy.id
JOIN fee_types ft ON p.fee_type_id = ft.id;

-- 5.3 Vue pour les emplois du temps complets
CREATE OR REPLACE VIEW v_timetables_complete AS
SELECT 
    t.*,
    c.name as class_name,
    c.code as class_code,
    cy.name as cycle_name,
    s.name as subject_name,
    s.code as subject_code,
    th.first_name as teacher_first_name,
    th.last_name as teacher_last_name
FROM timetables t
JOIN classes c ON t.class_id = c.id
JOIN cycles cy ON c.cycle_id = cy.id
JOIN subjects s ON t.subject_id = s.id
JOIN teachers th ON t.teacher_id = th.id;

-- 5.4 Vue pour les assignations complètes
CREATE OR REPLACE VIEW v_assignments_complete AS
SELECT 
    ta.*,
    th.first_name as teacher_first_name,
    th.last_name as teacher_last_name,
    c.name as class_name,
    c.code as class_code,
    cy.name as cycle_name,
    s.name as subject_name,
    s.code as subject_code
FROM teacher_assignments ta
JOIN teachers th ON ta.teacher_id = th.id
JOIN classes c ON ta.class_id = c.id
JOIN cycles cy ON c.cycle_id = cy.id
JOIN subjects s ON ta.subject_id = s.id;

-- ==========================================
-- 6. VÉRIFICATION FINALE
-- ==========================================

-- 6.1 Vérifier qu'il n'y a plus d'orphelins
SELECT 'Vérification des orphelins' as check_type;

SELECT COUNT(*) as orphan_payments 
FROM payments 
WHERE student_id NOT IN (SELECT id FROM students);

SELECT COUNT(*) as orphan_timetables 
FROM timetables 
WHERE class_id NOT IN (SELECT id FROM classes);

SELECT COUNT(*) as orphan_assignments 
FROM teacher_assignments 
WHERE teacher_id NOT IN (SELECT id FROM teachers);

-- 6.2 Vérifier la cohérence des années académiques
SELECT 'Cohérence des années académiques' as check_type;

SELECT DISTINCT academic_year FROM students ORDER BY academic_year;
SELECT DISTINCT academic_year FROM payments ORDER BY academic_year;

-- 6.3 Statistiques finales
SELECT 'Statistiques finales' as check_type;

SELECT 
    (SELECT COUNT(*) FROM students) as total_students,
    (SELECT COUNT(*) FROM classes) as total_classes,
    (SELECT COUNT(*) FROM cycles) as total_cycles,
    (SELECT COUNT(*) FROM payments) as total_payments,
    (SELECT COUNT(*) FROM timetables) as total_timetables,
    (SELECT COUNT(*) FROM teacher_assignments) as total_assignments;

-- ==========================================
-- FIN DU SCRIPT DE CORRECTION
-- ==========================================

-- Message de confirmation
SELECT 'Correction de la cohérence terminée avec succès!' as status;
