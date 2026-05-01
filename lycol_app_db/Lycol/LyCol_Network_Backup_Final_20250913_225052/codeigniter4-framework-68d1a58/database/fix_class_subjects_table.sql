-- =====================================================
-- CORRECTION DE LA TABLE CLASS_SUBJECTS
-- Ajout de la colonne academic_year manquante
-- =====================================================

-- Ajouter la colonne academic_year si elle n'existe pas
ALTER TABLE class_subjects ADD COLUMN IF NOT EXISTS academic_year VARCHAR(9) NOT NULL DEFAULT '2024-2025';

-- Ajouter des données de test pour les assignations
INSERT INTO class_subjects (class_id, subject_id, teacher_id, academic_year) VALUES
-- Assignations pour Jean Dupont (ID: 1) - Mathématiques
(1, 1, 1, '2024-2025'),  -- CP A - Mathématiques
(2, 1, 1, '2024-2025'),  -- CP B - Mathématiques
(3, 1, 1, '2024-2025'),  -- CE1 A - Mathématiques

-- Assignations pour Marie Martin (ID: 2) - Français
(1, 2, 2, '2024-2025'),  -- CP A - Français
(2, 2, 2, '2024-2025'),  -- CP B - Français

-- Assignations pour Pierre Bernard (ID: 3) - Histoire-Géographie
(4, 3, 3, '2024-2025'),  -- CE1 B - Histoire-Géographie
(5, 3, 3, '2024-2025'),  -- CE2 A - Histoire-Géographie

-- Assignations pour Sophie Petit (ID: 4) - SVT
(6, 4, 4, '2024-2025'),  -- CE2 B - Sciences de la Vie et de la Terre
(7, 4, 4, '2024-2025'),  -- CM1 A - Sciences de la Vie et de la Terre

-- Assignations pour Michel Robert (ID: 5) - Physique-Chimie
(8, 5, 5, '2024-2025'),  -- CM1 B - Physique-Chimie
(9, 5, 5, '2024-2025');  -- CM2 A - Physique-Chimie

-- Vérification
SELECT 'Table class_subjects corrigée avec succès' as status;
SELECT COUNT(*) as nombre_assignations FROM class_subjects;
SELECT COUNT(*) as assignations_avec_enseignant FROM class_subjects WHERE teacher_id IS NOT NULL;








