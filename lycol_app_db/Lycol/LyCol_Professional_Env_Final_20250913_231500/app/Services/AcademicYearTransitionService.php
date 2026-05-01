<?php
/**
 * Service de transition d'année académique
 * Gère automatiquement l'évolution des années scolaires
 * Projet LyCol - Système de Gestion Scolaire
 */

namespace App\Services;

use Config\Database;
use PDO;
use PDOException;

class AcademicYearTransitionService
{
    private $db;
    private $currentYear;
    private $nextYear;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->currentYear = $this->getCurrentAcademicYear();
        $this->nextYear = $this->getNextAcademicYear();
    }

    /**
     * Obtenir l'année académique actuelle
     */
    private function getCurrentAcademicYear(): string
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        if ($currentMonth >= 9) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    /**
     * Obtenir l'année académique suivante
     */
    private function getNextAcademicYear(): string
    {
        $years = explode('-', $this->currentYear);
        return $years[1] . '-' . ($years[1] + 1);
    }

    /**
     * Vérifier si une transition d'année est nécessaire
     */
    public function isTransitionNeeded(): bool
    {
        $currentMonth = (int)date('n');
        $currentDay = (int)date('j');
        
        // Transition nécessaire en septembre (mois 9)
        return $currentMonth === 9 && $currentDay <= 15;
    }

    /**
     * Effectuer la transition vers la nouvelle année académique
     */
    public function performTransition(): array
    {
        $results = [
            'success' => true,
            'errors' => [],
            'actions' => [],
            'statistics' => []
        ];

        try {
            $this->db->transStart();

            // 1. Sauvegarder les données de l'année précédente
            $this->backupPreviousYearData($results);

            // 2. Promouvoir les élèves
            $this->promoteStudents($results);

            // 3. Créer les nouvelles classes
            $this->createNewClasses($results);

            // 4. Réinitialiser les données temporaires
            $this->resetTemporaryData($results);

            // 5. Mettre à jour les configurations
            $this->updateConfigurations($results);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Erreur lors de la transaction');
            }

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Sauvegarder les données de l'année précédente
     */
    private function backupPreviousYearData(array &$results): void
    {
        $tables = ['students', 'payments', 'exams', 'grades', 'absences', 'discipline_incidents'];
        
        foreach ($tables as $table) {
            $backupTable = $table . '_' . str_replace('-', '_', $this->currentYear);
            
            $sql = "CREATE TABLE IF NOT EXISTS $backupTable AS 
                   SELECT * FROM $table WHERE academic_year = ?";
            
            $this->db->query($sql, [$this->currentYear]);
            
            $results['actions'][] = "Sauvegarde de la table $table vers $backupTable";
        }
        
        $results['statistics']['backup_tables'] = count($tables);
    }

    /**
     * Promouvoir les élèves vers la classe supérieure
     */
    private function promoteStudents(array &$results): void
    {
        // Récupérer tous les élèves actifs de l'année précédente
        $students = $this->db->table('students')
                            ->where('academic_year', $this->currentYear)
                            ->where('status', 'ACTIVE')
                            ->get()
                            ->getResultArray();

        $promoted = 0;
        $retained = 0;

        foreach ($students as $student) {
            try {
                // Calculer la moyenne de l'élève
                $average = $this->calculateStudentAverage($student['id']);
                
                if ($average >= 10.0) { // Moyenne de passage au Cameroun
                    // Promouvoir l'élève
                    $newClassId = $this->getNextClass($student['current_class_id']);
                    
                    if ($newClassId) {
                        $this->db->table('students')
                                ->where('id', $student['id'])
                                ->update([
                                    'current_class_id' => $newClassId,
                                    'academic_year' => $this->nextYear,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                        $promoted++;
                    }
                } else {
                    // L'élève redouble
                    $retained++;
                }
                
            } catch (\Exception $e) {
                $results['errors'][] = "Erreur promotion élève {$student['id']}: " . $e->getMessage();
            }
        }

        $results['statistics']['students_promoted'] = $promoted;
        $results['statistics']['students_retained'] = $retained;
        $results['actions'][] = "Promotion de $promoted élèves, $retained redoublent";
    }

    /**
     * Calculer la moyenne d'un élève
     */
    private function calculateStudentAverage(int $studentId): float
    {
        $result = $this->db->table('grades g')
                          ->select('AVG(g.marks_obtained) as average')
                          ->join('exams e', 'g.exam_id = e.id')
                          ->where('g.student_id', $studentId)
                          ->where('e.academic_year', $this->currentYear)
                          ->get()
                          ->getRow();

        return $result ? (float)$result->average : 0.0;
    }

    /**
     * Obtenir la classe suivante
     */
    private function getNextClass(int $currentClassId): ?int
    {
        $currentClass = $this->db->table('classes')
                                ->where('id', $currentClassId)
                                ->get()
                                ->getRowArray();

        if (!$currentClass) return null;

        $nextLevel = $currentClass['level'] + 1;
        
        $nextClass = $this->db->table('classes')
                             ->where('level', $nextLevel)
                             ->where('is_active', 1)
                             ->get()
                             ->getRowArray();

        return $nextClass ? $nextClass['id'] : null;
    }

    /**
     * Créer les nouvelles classes pour l'année suivante
     */
    private function createNewClasses(array &$results): void
    {
        // Copier les classes existantes pour la nouvelle année
        $existingClasses = $this->db->table('classes')
                                   ->where('academic_year', $this->currentYear)
                                   ->where('is_active', 1)
                                   ->get()
                                   ->getResultArray();

        $created = 0;
        foreach ($existingClasses as $class) {
            $newClassData = [
                'name' => $class['name'],
                'code' => $class['code'],
                'cycle_id' => $class['cycle_id'],
                'level' => $class['level'],
                'series_id' => $class['series_id'],
                'teacher_id' => null, // À assigner manuellement
                'academic_year' => $this->nextYear,
                'capacity' => $class['capacity'],
                'description' => $class['description'],
                'current_students' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('classes')->insert($newClassData);
            $created++;
        }

        $results['statistics']['classes_created'] = $created;
        $results['actions'][] = "Création de $created nouvelles classes";
    }

    /**
     * Réinitialiser les données temporaires
     */
    private function resetTemporaryData(array &$results): void
    {
        // Réinitialiser les compteurs d'élèves par classe
        $this->db->query("
            UPDATE classes 
            SET current_students = 0 
            WHERE academic_year = ?
        ", [$this->nextYear]);

        // Nettoyer les données temporaires
        $this->db->query("
            DELETE FROM absences 
            WHERE academic_year = ? AND date < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ", [$this->currentYear]);

        $results['actions'][] = "Réinitialisation des données temporaires";
    }

    /**
     * Mettre à jour les configurations
     */
    private function updateConfigurations(array &$results): void
    {
        // Mettre à jour la configuration de l'année académique
        $this->db->table('system_settings')
                ->where('setting_type', 'general')
                ->update([
                    'setting_value' => json_encode([
                        'academic_year' => $this->nextYear,
                        'transition_date' => date('Y-m-d H:i:s')
                    ]),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

        $results['actions'][] = "Mise à jour de la configuration vers $this->nextYear";
    }

    /**
     * Générer un rapport de transition
     */
    public function generateTransitionReport(): array
    {
        $report = [
            'transition_date' => date('Y-m-d H:i:s'),
            'from_year' => $this->currentYear,
            'to_year' => $this->nextYear,
            'statistics' => [
                'total_students' => $this->countStudents($this->currentYear),
                'active_students' => $this->countActiveStudents($this->currentYear),
                'total_payments' => $this->countPayments($this->currentYear),
                'total_revenue' => $this->calculateTotalRevenue($this->currentYear),
                'total_exams' => $this->countExams($this->currentYear),
                'total_grades' => $this->countGrades($this->currentYear)
            ],
            'recommendations' => [
                'Vérifier les promotions automatiques',
                'Assigner les enseignants aux nouvelles classes',
                'Configurer les nouveaux frais de scolarité',
                'Planifier les examens de la nouvelle année',
                'Former les utilisateurs sur les nouvelles fonctionnalités'
            ]
        ];

        return $report;
    }

    /**
     * Méthodes utilitaires pour les statistiques
     */
    private function countStudents(string $academicYear): int
    {
        return $this->db->table('students')
                       ->where('academic_year', $academicYear)
                       ->countAllResults();
    }

    private function countActiveStudents(string $academicYear): int
    {
        return $this->db->table('students')
                       ->where('academic_year', $academicYear)
                       ->where('status', 'ACTIVE')
                       ->countAllResults();
    }

    private function countPayments(string $academicYear): int
    {
        return $this->db->table('payments')
                       ->where('academic_year', $academicYear)
                       ->countAllResults();
    }

    private function calculateTotalRevenue(string $academicYear): float
    {
        $result = $this->db->table('payments')
                          ->selectSum('amount_paid')
                          ->where('academic_year', $academicYear)
                          ->get()
                          ->getRow();

        return $result ? (float)$result->amount_paid : 0.0;
    }

    private function countExams(string $academicYear): int
    {
        return $this->db->table('exams')
                       ->where('academic_year', $academicYear)
                       ->countAllResults();
    }

    private function countGrades(string $academicYear): int
    {
        return $this->db->table('grades g')
                       ->join('exams e', 'g.exam_id = e.id')
                       ->where('e.academic_year', $academicYear)
                       ->countAllResults();
    }

    /**
     * Vérifier la santé de la transition
     */
    public function checkTransitionHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'warnings' => []
        ];

        // Vérifier les données de l'année précédente
        if ($this->countStudents($this->currentYear) === 0) {
            $health['warnings'][] = "Aucun élève trouvé pour l'année $this->currentYear";
        }

        // Vérifier les classes
        if ($this->db->table('classes')->where('academic_year', $this->nextYear)->countAllResults() === 0) {
            $health['warnings'][] = "Aucune classe créée pour l'année $this->nextYear";
        }

        // Vérifier les enseignants
        $classesWithoutTeachers = $this->db->table('classes')
                                          ->where('academic_year', $this->nextYear)
                                          ->where('teacher_id IS NULL')
                                          ->countAllResults();

        if ($classesWithoutTeachers > 0) {
            $health['warnings'][] = "$classesWithoutTeachers classes sans enseignant assigné";
        }

        if (!empty($health['issues'])) {
            $health['status'] = 'critical';
        } elseif (!empty($health['warnings'])) {
            $health['status'] = 'warning';
        }

        return $health;
    }
}
?>





