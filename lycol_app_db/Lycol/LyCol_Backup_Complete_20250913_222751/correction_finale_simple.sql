-- =====================================================
-- SCRIPT DE CORRECTION FINALE SIMPLIFIÉ - PROJET LYCOL
-- Date : 26 Août 2025
-- Contexte : Système éducatif camerounais
-- =====================================================

USE lycol_db;

-- =====================================================
-- 1. CORRECTION DES DOUBLONS DE CLASSES
-- =====================================================

SELECT '=== CORRECTION DES DOUBLONS DE CLASSES ===' as action;

-- Identifier les doublons de classes
SELECT name, code, COUNT(*) as count 
FROM classes 
GROUP BY name, code 
HAVING count > 1;

-- Supprimer les doublons de classes (garder l'ID le plus petit)
DELETE c1 FROM classes c1
INNER JOIN classes c2 
WHERE c1.id > c2.id 
AND c1.name = c2.name 
AND c1.code = c2.code;

SELECT 'Doublons supprimés avec succès' as result;

-- =====================================================
-- 2. CORRECTION DES DONNÉES INCOHÉRENTES
-- =====================================================

SELECT '=== CORRECTION DES DONNÉES INCOHÉRENTES ===' as action;

-- Mettre à jour les classes sans année académique
UPDATE classes 
SET academic_year = '2024-2025' 
WHERE academic_year IS NULL OR academic_year = '';

-- Mettre à jour les élèves sans classe
UPDATE students 
SET current_class_id = 1 
WHERE current_class_id IS NULL AND status = 'ACTIVE';

-- Corriger les dates d'examens incohérentes
UPDATE exams 
SET exam_date = '2024-10-15' 
WHERE exam_date < '2024-09-01' AND academic_year = '2024-2025';

SELECT 'Données corrigées avec succès' as result;

-- =====================================================
-- 3. VÉRIFICATIONS POST-CORRECTION
-- =====================================================

SELECT '=== VÉRIFICATIONS POST-CORRECTION ===' as action;

-- Compter les élèves par classe
SELECT c.name as class_name, COUNT(s.id) as student_count 
FROM classes c 
LEFT JOIN students s ON c.id = s.current_class_id 
WHERE c.is_active = 1 
GROUP BY c.id, c.name 
ORDER BY c.level, c.name;

-- Vérifier les paiements par année
SELECT academic_year, COUNT(*) as payment_count, SUM(amount_paid) as total_amount 
FROM payments 
GROUP BY academic_year;

-- Vérifier les notes
SELECT 
    COUNT(*) as total_grades,
    ROUND(AVG(marks_obtained), 2) as average_grade,
    MIN(marks_obtained) as min_grade,
    MAX(marks_obtained) as max_grade
FROM grades;

-- Vérifier les absences
SELECT 
    COUNT(*) as total_absences,
    COUNT(CASE WHEN justified = 1 THEN 1 END) as justified_absences,
    COUNT(CASE WHEN justified = 0 THEN 1 END) as unjustified_absences
FROM absences;

-- =====================================================
-- 4. STATISTIQUES FINALES
-- =====================================================

SELECT '=== STATISTIQUES FINALES ===' as action;

-- Statistiques générales
SELECT 
    (SELECT COUNT(*) FROM students WHERE status = 'ACTIVE') as active_students,
    (SELECT COUNT(*) FROM classes WHERE is_active = 1) as active_classes,
    (SELECT COUNT(*) FROM payments WHERE academic_year = '2024-2025') as payments_2024_2025,
    (SELECT COUNT(*) FROM grades) as total_grades,
    (SELECT COUNT(*) FROM absences) as total_absences,
    (SELECT COUNT(*) FROM discipline_incidents WHERE academic_year = '2024-2025') as discipline_incidents;

-- Répartition par genre
SELECT 
    gender,
    COUNT(*) as count
FROM students 
WHERE status = 'ACTIVE' 
GROUP BY gender;

-- Répartition par cycle
SELECT 
    cy.name as cycle_name,
    COUNT(s.id) as student_count
FROM students s
JOIN classes c ON s.current_class_id = c.id
JOIN cycles cy ON c.cycle_id = cy.id
WHERE s.status = 'ACTIVE'
GROUP BY cy.id, cy.name
ORDER BY cy.id;

-- =====================================================
-- 5. MESSAGES DE CONFIRMATION
-- =====================================================

SELECT '=== CORRECTION TERMINÉE AVEC SUCCÈS ===' as status;
SELECT 'Les problèmes de cohérence principaux ont été corrigés' as message;
SELECT 'Le système est maintenant plus stable' as optimization;
SELECT 'N\'oubliez pas de tester toutes les fonctionnalités' as reminder;

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================





