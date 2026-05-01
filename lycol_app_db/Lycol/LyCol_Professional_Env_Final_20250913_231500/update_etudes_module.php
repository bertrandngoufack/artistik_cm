<?php

/**
 * Script de mise à jour du module Études
 * Ce script met à jour la base de données et crée les tables nécessaires
 */

// Charger CodeIgniter correctement
require_once 'public/index.php';

use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Config\Factories;

echo "=== Mise à jour du module Études ===\n\n";

try {
    // Initialiser la base de données
    $db = \Config\Database::connect();
    
    echo "✓ Connexion à la base de données établie\n";
    
    // Vérifier si les tables existent déjà
    $existingTables = $db->listTables();
    
    // Créer les tables si elles n'existent pas
    $tablesToCreate = ['cycles', 'classes', 'subjects', 'class_subjects', 'timetables', 'teacher_assignments'];
    
    foreach ($tablesToCreate as $table) {
        if (!in_array($table, $existingTables)) {
            echo "Création de la table: $table\n";
            
            switch ($table) {
                case 'cycles':
                    $db->query("
                        CREATE TABLE `cycles` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(100) NOT NULL,
                            `code` varchar(20) NOT NULL,
                            `description` text,
                            `is_active` tinyint(1) DEFAULT '1',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `code` (`code`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
                    
                case 'classes':
                    $db->query("
                        CREATE TABLE `classes` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(100) NOT NULL,
                            `code` varchar(20) NOT NULL,
                            `cycle_id` int(11) unsigned NOT NULL,
                            `level` int(11) NOT NULL,
                            `capacity` int(11) NOT NULL,
                            `description` text,
                            `is_active` tinyint(1) DEFAULT '1',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `code` (`code`),
                            KEY `cycle_id` (`cycle_id`),
                            CONSTRAINT `classes_cycle_id_fk` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
                    
                case 'subjects':
                    $db->query("
                        CREATE TABLE `subjects` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(100) NOT NULL,
                            `code` varchar(20) NOT NULL,
                            `description` text,
                            `coefficient` decimal(3,2) DEFAULT '1.00',
                            `is_active` tinyint(1) DEFAULT '1',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `code` (`code`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
                    
                case 'class_subjects':
                    $db->query("
                        CREATE TABLE `class_subjects` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `class_id` int(11) unsigned NOT NULL,
                            `subject_id` int(11) unsigned NOT NULL,
                            `hours_per_week` int(11) DEFAULT '0',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `class_subject_unique` (`class_id`,`subject_id`),
                            KEY `subject_id` (`subject_id`),
                            CONSTRAINT `class_subjects_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `class_subjects_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
                    
                case 'timetables':
                    $db->query("
                        CREATE TABLE `timetables` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `class_id` int(11) unsigned NOT NULL,
                            `day_of_week` tinyint(1) NOT NULL COMMENT '1=Lundi, 2=Mardi, 3=Mercredi, 4=Jeudi, 5=Vendredi, 6=Samedi',
                            `start_time` time NOT NULL,
                            `end_time` time NOT NULL,
                            `subject_id` int(11) unsigned NOT NULL,
                            `teacher_id` int(11) unsigned DEFAULT NULL,
                            `room` varchar(50) DEFAULT NULL,
                            `is_active` tinyint(1) DEFAULT '1',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `class_id` (`class_id`),
                            KEY `subject_id` (`subject_id`),
                            KEY `teacher_id` (`teacher_id`),
                            CONSTRAINT `timetables_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `timetables_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `timetables_teacher_id_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
                    
                case 'teacher_assignments':
                    $db->query("
                        CREATE TABLE `teacher_assignments` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `teacher_id` int(11) unsigned NOT NULL,
                            `class_id` int(11) unsigned NOT NULL,
                            `subject_id` int(11) unsigned NOT NULL,
                            `is_principal` tinyint(1) DEFAULT '0' COMMENT '1=Enseignant principal de la classe',
                            `academic_year` varchar(9) NOT NULL COMMENT 'Format: 2024-2025',
                            `is_active` tinyint(1) DEFAULT '1',
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `teacher_assignment_unique` (`teacher_id`,`class_id`,`subject_id`,`academic_year`),
                            KEY `class_id` (`class_id`),
                            KEY `subject_id` (`subject_id`),
                            CONSTRAINT `teacher_assignments_teacher_id_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `teacher_assignments_class_id_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `teacher_assignments_subject_id_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    break;
            }
            
            echo "✓ Table $table créée avec succès\n";
        } else {
            echo "✓ Table $table existe déjà\n";
        }
    }
    
    // Insérer des données de test pour les cycles
    $cyclesData = [
        ['name' => 'Primaire', 'code' => 'PRIM', 'description' => 'Cycle primaire'],
        ['name' => 'Secondaire', 'code' => 'SEC', 'description' => 'Cycle secondaire'],
        ['name' => 'Supérieur', 'code' => 'SUP', 'description' => 'Cycle supérieur']
    ];
    
    foreach ($cyclesData as $cycle) {
        $existingCycle = $db->table('cycles')->where('code', $cycle['code'])->get()->getRowArray();
        if (!$existingCycle) {
            $db->table('cycles')->insert($cycle);
            echo "✓ Cycle {$cycle['name']} ajouté\n";
        } else {
            echo "✓ Cycle {$cycle['name']} existe déjà\n";
        }
    }
    
    // Insérer des données de test pour les matières
    $subjectsData = [
        ['name' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 4.00],
        ['name' => 'Français', 'code' => 'FRAN', 'coefficient' => 3.00],
        ['name' => 'Anglais', 'code' => 'ANGL', 'coefficient' => 2.00],
        ['name' => 'Histoire', 'code' => 'HIST', 'coefficient' => 2.00],
        ['name' => 'Géographie', 'code' => 'GEO', 'coefficient' => 2.00],
        ['name' => 'Sciences', 'code' => 'SCI', 'coefficient' => 3.00],
        ['name' => 'Physique', 'code' => 'PHY', 'coefficient' => 3.00],
        ['name' => 'Chimie', 'code' => 'CHIM', 'coefficient' => 2.00],
        ['name' => 'Biologie', 'code' => 'BIO', 'coefficient' => 2.00],
        ['name' => 'Informatique', 'code' => 'INFO', 'coefficient' => 2.00]
    ];
    
    foreach ($subjectsData as $subject) {
        $existingSubject = $db->table('subjects')->where('code', $subject['code'])->get()->getRowArray();
        if (!$existingSubject) {
            $db->table('subjects')->insert($subject);
            echo "✓ Matière {$subject['name']} ajoutée\n";
        } else {
            echo "✓ Matière {$subject['name']} existe déjà\n";
        }
    }
    
    // Insérer des classes de test si elles n'existent pas
    $classesData = [
        ['name' => '6ème A', 'code' => '6A', 'cycle_id' => 2, 'level' => 6, 'capacity' => 40],
        ['name' => '6ème B', 'code' => '6B', 'cycle_id' => 2, 'level' => 6, 'capacity' => 40],
        ['name' => '5ème A', 'code' => '5A', 'cycle_id' => 2, 'level' => 5, 'capacity' => 40],
        ['name' => '5ème B', 'code' => '5B', 'cycle_id' => 2, 'level' => 5, 'capacity' => 40],
        ['name' => '4ème A', 'code' => '4A', 'cycle_id' => 2, 'level' => 4, 'capacity' => 40],
        ['name' => '4ème B', 'code' => '4B', 'cycle_id' => 2, 'level' => 4, 'capacity' => 40],
        ['name' => '3ème A', 'code' => '3A', 'cycle_id' => 2, 'level' => 3, 'capacity' => 40],
        ['name' => '3ème B', 'code' => '3B', 'cycle_id' => 2, 'level' => 3, 'capacity' => 40],
        ['name' => '2nde A', 'code' => '2A', 'cycle_id' => 2, 'level' => 2, 'capacity' => 35],
        ['name' => '2nde B', 'code' => '2B', 'cycle_id' => 2, 'level' => 2, 'capacity' => 35],
        ['name' => '1ère A', 'code' => '1A', 'cycle_id' => 2, 'level' => 1, 'capacity' => 35],
        ['name' => '1ère B', 'code' => '1B', 'cycle_id' => 2, 'level' => 1, 'capacity' => 35],
        ['name' => 'Terminale A', 'code' => 'TA', 'cycle_id' => 2, 'level' => 0, 'capacity' => 35],
        ['name' => 'Terminale B', 'code' => 'TB', 'cycle_id' => 2, 'level' => 0, 'capacity' => 35]
    ];
    
    foreach ($classesData as $class) {
        $existingClass = $db->table('classes')->where('code', $class['code'])->get()->getRowArray();
        if (!$existingClass) {
            $db->table('classes')->insert($class);
            echo "✓ Classe {$class['name']} ajoutée\n";
        } else {
            echo "✓ Classe {$class['name']} existe déjà\n";
        }
    }
    
    echo "\n=== Mise à jour terminée avec succès ===\n";
    echo "Le module Études est maintenant opérationnel.\n";
    echo "Vous pouvez accéder à: http://localhost:8080/admin/etudes\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
