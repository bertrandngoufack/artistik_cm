-- ========================================
-- CORRECTION DES ENREGISTREMENTS ORPHELINS
-- KISSAI SCHOOL - Expert Senior
-- ========================================

-- 1. IDENTIFICATION DES ENREGISTREMENTS ORPHELINS
-- ==============================================

SELECT 
    'Enregistrements orphelins identifiés' as info,
    COUNT(*) as count
FROM classes c 
LEFT JOIN teachers t ON c.teacher_id = t.id 
WHERE c.teacher_id IS NOT NULL AND t.id IS NULL;

-- 2. AFFICHAGE DES CLASSES AVEC TEACHER_ID ORPHELIN
-- =================================================

SELECT 
    c.id,
    c.name,
    c.code,
    c.teacher_id,
    'ORPHELIN' as status
FROM classes c 
LEFT JOIN teachers t ON c.teacher_id = t.id 
WHERE c.teacher_id IS NOT NULL AND t.id IS NULL;

-- 3. CORRECTION : MISE À NULL DES TEACHER_ID ORPHELINS
-- ===================================================

UPDATE classes c 
LEFT JOIN teachers t ON c.teacher_id = t.id 
SET c.teacher_id = NULL 
WHERE c.teacher_id IS NOT NULL AND t.id IS NULL;

-- 4. VÉRIFICATION DE LA CORRECTION
-- ================================

SELECT 
    'Vérification après correction' as info,
    COUNT(*) as orphelins_restants
FROM classes c 
LEFT JOIN teachers t ON c.teacher_id = t.id 
WHERE c.teacher_id IS NOT NULL AND t.id IS NULL;

-- 5. STATISTIQUES FINALES
-- =======================

SELECT 
    'Statistiques des classes' as info,
    COUNT(*) as total_classes,
    COUNT(teacher_id) as classes_avec_teacher,
    COUNT(*) - COUNT(teacher_id) as classes_sans_teacher
FROM classes;





