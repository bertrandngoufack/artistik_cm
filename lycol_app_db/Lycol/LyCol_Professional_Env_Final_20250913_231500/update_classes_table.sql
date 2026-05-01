-- Script de mise à jour de la table classes existante
-- Ajouter la colonne cycle_id

ALTER TABLE `classes` ADD COLUMN `cycle_id` int(11) NOT NULL DEFAULT 2 AFTER `code`;
ALTER TABLE `classes` ADD COLUMN `level` int(11) NOT NULL DEFAULT 6 AFTER `cycle_id`;
ALTER TABLE `classes` ADD COLUMN `description` text AFTER `capacity`;

-- Ajouter la contrainte de clé étrangère
ALTER TABLE `classes` ADD CONSTRAINT `classes_cycle_id_fk` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Mettre à jour les niveaux basés sur level_id existant
UPDATE `classes` SET `level` = `level_id` WHERE `level_id` IS NOT NULL;

-- Mettre à jour les cycles pour les classes existantes (par défaut cycle secondaire)
UPDATE `classes` SET `cycle_id` = 2 WHERE `cycle_id` = 0 OR `cycle_id` IS NULL;
