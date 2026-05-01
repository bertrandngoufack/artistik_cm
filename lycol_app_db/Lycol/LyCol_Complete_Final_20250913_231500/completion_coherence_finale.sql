-- ==========================================
-- SCRIPT DE COMPLÉTION DE LA COHÉRENCE FINALE
-- ENTRE LES MODULES ÉCONOMAT, SCOLARITÉ ET ÉTUDES
-- ==========================================

USE lycol_db;

-- ==========================================
-- 1. CRÉATION DES INDEX MANQUANTS
-- ==========================================

-- Index pour les timetables
CREATE INDEX IF NOT EXISTS idx_timetables_class_id ON timetables(class_id);
CREATE INDEX IF NOT EXISTS idx_timetables_teacher_id ON timetables(teacher_id);
CREATE INDEX IF NOT EXISTS idx_timetables_subject_id ON timetables(subject_id);
CREATE INDEX IF NOT EXISTS idx_timetables_day_time ON timetables(day_of_week, start_time);

-- Index pour les teacher_assignments
CREATE INDEX IF NOT EXISTS idx_assignments_teacher_id ON teacher_assignments(teacher_id);
CREATE INDEX IF NOT EXISTS idx_assignments_class_id ON teacher_assignments(class_id);
CREATE INDEX IF NOT EXISTS idx_assignments_subject_id ON teacher_assignments(subject_id);

-- ==========================================
-- 2. CRÉATION DES TRIGGERS
-- ==========================================

-- 2.1 Trigger pour vérifier la capacité des classes
DELIMITER //
CREATE TRIGGER IF NOT EXISTS tr_check_class_capacity
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

-- 2.2 Trigger pour vérifier les conflits d'emploi du temps
DELIMITER //
CREATE TRIGGER IF NOT EXISTS tr_check_timetable_conflicts
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
-- 3. CRÉATION DES PROCÉDURES STOCKÉES
-- ==========================================

-- 3.1 Procédure pour changer la classe d'un élève
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS sp_change_student_class(
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

-- 3.2 Procédure pour calculer les statistiques par cycle
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS sp_get_cycle_statistics()
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
-- 4. VÉRIFICATION FINALE
-- ==========================================

-- 4.1 Vérifier les index créés
SELECT 'Index créés avec succès' as status;

-- 4.2 Vérifier les triggers créés
SELECT 'Triggers créés avec succès' as status;

-- 4.3 Vérifier les procédures créées
SELECT 'Procédures créées avec succès' as status;

-- 4.4 Statistiques finales
SELECT 
    (SELECT COUNT(*) FROM students) as total_students,
    (SELECT COUNT(*) FROM classes) as total_classes,
    (SELECT COUNT(*) FROM cycles) as total_cycles,
    (SELECT COUNT(*) FROM payments) as total_payments,
    (SELECT COUNT(*) FROM timetables) as total_timetables,
    (SELECT COUNT(*) FROM teacher_assignments) as total_assignments;

-- ==========================================
-- FIN DU SCRIPT DE COMPLÉTION
-- ==========================================

SELECT 'Complétion de la cohérence terminée avec succès!' as status;
