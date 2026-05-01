-- Script pour ajouter la colonne avatar à la table users
-- Exécuté le: 25/08/2025

-- Ajout de la colonne avatar
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL COMMENT 'Nom du fichier avatar de l\'utilisateur';

-- Ajout d'un index pour optimiser les requêtes
CREATE INDEX idx_users_avatar ON users(avatar);

-- Vérification de l'ajout de la colonne
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'lycol_db' 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME = 'avatar';

-- Affichage de la structure mise à jour de la table users
DESCRIBE users;







