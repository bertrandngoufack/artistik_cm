-- ==========================================
-- SCRIPT DE CORRECTION DE LA COHÉRENCE
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
-- 3. AJOUT DES CONTRAINTES DE CLÉS ÉTRANGÈRES
-- ==========================================

-- 3.1 Contraintes pour la table students
ALTER TABLE students 
ADD CONSTRAINT fk_students_class 
FOREIGN KEY (current_class_id) REFERENCES classes(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- 3.2 Contraintes pour la table classes
ALTER TABLE classes 
ADD CONSTRAINT fk_classes_cycle 
FOREIGN KEY (cycle_id) REFERENCES cycles(id) 
ON DELETE RESTRICT ON UPDATE CASCADE;

-- 3.3 Contraintes pour la table payments
ALTER TABLE payments 
ADD CONSTRAINT fk_payments_student 
FOREIGN KEY (student_id) REFERENCES students(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE payments 
ADD CONSTRAINT fk_payments_fee_type 
FOREIGN KEY (fee_type_id) REFERENCES fee_types(id) 
ON DELETE RESTRICT ON UPDATE CASCADE;

-- 3.4 Contraintes pour la table timetables
ALTER TABLE timetables 
ADD CONSTRAINT fk_timetables_class 
FOREIGN KEY (class_id) REFERENCES classes(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE timetables 
ADD CONSTRAINT fk_timetables_subject 
FOREIGN KEY (subject_id) REFERENCES subjects(id) 
ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE timetables 
ADD CONSTRAINT fk_timetables_teacher 
FOREIGN KEY (teacher_id) REFERENCES teachers(id) 
ON DELETE RESTRICT ON UPDATE CASCADE;

-- 3.5 Contraintes pour la table teacher_assignments
ALTER TABLE teacher_assignments 
ADD CONSTRAINT fk_assignments_teacher 
FOREIGN KEY (teacher_id) REFERENCES teachers(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE teacher_assignments 
ADD CONSTRAINT fk_assignments_class 
FOREIGN KEY (class_id) REFERENCES classes(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE teacher_assignments 
ADD CONSTRAINT fk_assignments_subject 
FOREIGN KEY (subject_id) REFERENCES subjects(id) 
ON DELETE RESTRICT ON UPDATE CASCADE;

-- ==========================================
-- 4. AJOUT DES INDEX POUR OPTIMISER LES PERFORMANCES
-- ==========================================

-- 4.1 Index pour les jointures fréquentes
CREATE INDEX idx_students_class_id ON students(current_class_id);
CREATE INDEX idx_students_academic_year ON students(academic_year);
CREATE INDEX idx_students_status ON students(status);

CREATE INDEX idx_classes_cycle_id ON classes(cycle_id);
CREATE INDEX idx_classes_active ON classes(is_active);

CREATE INDEX idx_payments_student_id ON payments(student_id);
CREATE INDEX idx_payments_fee_type_id ON payments(fee_type_id);
CREATE INDEX idx_payments_academic_year ON payments(academic_year);
CREATE INDEX idx_payments_date ON payments(payment_date);

CREATE INDEX idx_timetables_class_id ON timetables(class_id);
CREATE INDEX idx_timetables_teacher_id ON timetables(teacher_id);
CREATE INDEX idx_timetables_subject_id ON timetables(subject_id);
CREATE INDEX idx_timetables_day_time ON timetables(day_of_week, start_time);

CREATE INDEX idx_assignments_teacher_id ON teacher_assignments(teacher_id);
CREATE INDEX idx_assignments_class_id ON teacher_assignments(class_id);
CREATE INDEX idx_assignments_subject_id ON teacher_assignments(subject_id);

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
-- 6. TRIGGERS POUR MAINTENIR LA COHÉRENCE
-- ==========================================

-- 6.1 Trigger pour vérifier la capacité des classes
DELIMITER //
CREATE TRIGGER tr_check_class_capacity
BEFORE INSERT ON students
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    DECLARE max_capacity INT;
    
    IF NEW.current_class_id IS NOT NULL THEN
        SELECT COUNT(*) INTO current_count 
        FROM students 
        WHERE current_class_id = NEW.current_class_id 
          AND status = 'ACTIVE';
        
        SELECT capacity INTO max_capacity 
        FROM classes 
        WHERE id = NEW.current_class_id;
        
        IF current_count >= max_capacity THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La classe a atteint sa capacité maximale';
        END IF;
    END IF;
END//
DELIMITER ;

-- 6.2 Trigger pour vérifier les conflits d'emploi du temps
DELIMITER //
CREATE TRIGGER tr_check_timetable_conflicts
BEFORE INSERT ON timetables
FOR EACH ROW
BEGIN
    DECLARE conflict_count INT;
    
    -- Vérifier les conflits de classe
    SELECT COUNT(*) INTO conflict_count
    FROM timetables
    WHERE class_id = NEW.class_id
      AND day_of_week = NEW.day_of_week
      AND id != NEW.id
      AND (
          (start_time <= NEW.start_time AND end_time > NEW.start_time) OR
          (start_time < NEW.end_time AND end_time >= NEW.end_time) OR
          (start_time >= NEW.start_time AND end_time <= NEW.end_time)
      );
    
    IF conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Conflit d\'emploi du temps pour cette classe';
    END IF;
    
    -- Vérifier les conflits d'enseignant
    IF NEW.teacher_id IS NOT NULL THEN
        SELECT COUNT(*) INTO conflict_count
        FROM timetables
        WHERE teacher_id = NEW.teacher_id
          AND day_of_week = NEW.day_of_week
          AND id != NEW.id
          AND (
              (start_time <= NEW.start_time AND end_time > NEW.start_time) OR
              (start_time < NEW.end_time AND end_time >= NEW.end_time) OR
              (start_time >= NEW.start_time AND end_time <= NEW.end_time)
          );
        
        IF conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflit d\'emploi du temps pour cet enseignant';
        END IF;
    END IF;
END//
DELIMITER ;

-- ==========================================
-- 7. PROCÉDURES STOCKÉES POUR LES OPÉRATIONS CRITIQUES
-- ==========================================

-- 7.1 Procédure pour changer la classe d'un élève
DELIMITER //
CREATE PROCEDURE sp_change_student_class(
    IN p_student_id INT,
    IN p_new_class_id INT,
    IN p_academic_year VARCHAR(9)
)
BEGIN
    DECLARE current_count INT;
    DECLARE max_capacity INT;
    
    -- Vérifier la capacité de la nouvelle classe
    SELECT COUNT(*) INTO current_count 
    FROM students 
    WHERE current_class_id = p_new_class_id 
      AND status = 'ACTIVE';
    
    SELECT capacity INTO max_capacity 
    FROM classes 
    WHERE id = p_new_class_id;
    
    IF current_count >= max_capacity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La classe a atteint sa capacité maximale';
    END IF;
    
    -- Mettre à jour la classe de l'élève
    UPDATE students 
    SET current_class_id = p_new_class_id,
        academic_year = p_academic_year,
        updated_at = NOW()
    WHERE id = p_student_id;
    
    SELECT 'Classe changée avec succès' as message;
END//
DELIMITER ;

-- 7.2 Procédure pour calculer les statistiques par cycle
DELIMITER //
CREATE PROCEDURE sp_get_cycle_statistics()
BEGIN
    SELECT 
        cy.id,
        cy.name as cycle_name,
        cy.code as cycle_code,
        COUNT(DISTINCT c.id) as class_count,
        COUNT(s.id) as student_count,
        SUM(c.capacity) as total_capacity,
        ROUND((COUNT(s.id) / SUM(c.capacity)) * 100, 2) as occupancy_rate
    FROM cycles cy
    LEFT JOIN classes c ON cy.id = c.cycle_id AND c.is_active = 1
    LEFT JOIN students s ON c.id = s.current_class_id AND s.status = 'ACTIVE'
    WHERE cy.is_active = 1
    GROUP BY cy.id, cy.name, cy.code
    ORDER BY cy.name;
END//
DELIMITER ;

-- ==========================================
-- 8. VÉRIFICATION FINALE
-- ==========================================

-- 8.1 Vérifier qu'il n'y a plus d'orphelins
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

-- 8.2 Vérifier la cohérence des années académiques
SELECT 'Cohérence des années académiques' as check_type;

SELECT DISTINCT academic_year FROM students ORDER BY academic_year;
SELECT DISTINCT academic_year FROM payments ORDER BY academic_year;

-- 8.3 Statistiques finales
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
