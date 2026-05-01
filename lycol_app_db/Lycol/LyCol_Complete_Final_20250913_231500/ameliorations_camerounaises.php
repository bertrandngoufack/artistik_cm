<?php
/**
 * Améliorations spécifiques au contexte camerounais
 * Projet LyCol - Système de Gestion Scolaire
 * Date : 26 Août 2025
 */

namespace App\Services;

/**
 * Service de notation adapté au système camerounais
 */
class CameroonianGradeService
{
    /**
     * Calculer la mention selon le système camerounais
     */
    public static function calculateMention(float $average): string
    {
        if ($average >= 16.0) return 'Très Bien';
        if ($average >= 14.0) return 'Bien';
        if ($average >= 12.0) return 'Assez Bien';
        if ($average >= 10.0) return 'Passable';
        return 'Insuffisant';
    }

    /**
     * Calculer la moyenne par trimestre
     */
    public static function calculateTermAverage(int $studentId, string $term, string $academicYear): float
    {
        $db = \Config\Database::connect();
        
        $termDates = self::getTermDates($term, $academicYear);
        
        $query = $db->table('grades g')
                   ->select('AVG(g.marks_obtained) as average')
                   ->join('exams e', 'g.exam_id = e.id')
                   ->where('g.student_id', $studentId)
                   ->where('e.exam_date >=', $termDates['start'])
                   ->where('e.exam_date <=', $termDates['end'])
                   ->where('e.academic_year', $academicYear);
        
        $result = $query->get()->getRow();
        return $result ? (float)$result->average : 0.0;
    }

    /**
     * Calculer la moyenne générale de l'année
     */
    public static function calculateYearAverage(int $studentId, string $academicYear): float
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('grades g')
                   ->select('AVG(g.marks_obtained) as average')
                   ->join('exams e', 'g.exam_id = e.id')
                   ->where('g.student_id', $studentId)
                   ->where('e.academic_year', $academicYear);
        
        $result = $query->get()->getRow();
        return $result ? (float)$result->average : 0.0;
    }

    /**
     * Obtenir les dates des trimestres
     */
    private static function getTermDates(string $term, string $academicYear): array
    {
        $year = explode('-', $academicYear)[0];
        
        $terms = [
            '1ER_TRIMESTRE' => [
                'start' => $year . '-09-01',
                'end' => $year . '-12-20'
            ],
            '2EME_TRIMESTRE' => [
                'start' => ($year + 1) . '-01-06',
                'end' => ($year + 1) . '-03-28'
            ],
            '3EME_TRIMESTRE' => [
                'start' => ($year + 1) . '-04-07',
                'end' => ($year + 1) . '-06-30'
            ]
        ];
        
        return $terms[$term] ?? $terms['1ER_TRIMESTRE'];
    }

    /**
     * Déterminer si l'élève passe en classe supérieure
     */
    public static function determinePromotion(int $studentId, string $academicYear): bool
    {
        $yearAverage = self::calculateYearAverage($studentId, $academicYear);
        return $yearAverage >= 10.0; // Moyenne de passage au Cameroun
    }
}

/**
 * Service de gestion multilingue
 */
class MultilingualService
{
    /**
     * Traductions des matières
     */
    private static $subjectTranslations = [
        'fr' => [
            'MATH' => 'Mathématiques',
            'FR' => 'Français',
            'EN' => 'Anglais',
            'HG' => 'Histoire-Géographie',
            'SC' => 'Sciences',
            'PHY' => 'Physique',
            'CHI' => 'Chimie',
            'BIO' => 'Biologie',
            'ECO' => 'Économie',
            'PHI' => 'Philosophie'
        ],
        'en' => [
            'MATH' => 'Mathematics',
            'FR' => 'French',
            'EN' => 'English',
            'HG' => 'History-Geography',
            'SC' => 'Science',
            'PHY' => 'Physics',
            'CHI' => 'Chemistry',
            'BIO' => 'Biology',
            'ECO' => 'Economics',
            'PHI' => 'Philosophy'
        ]
    ];

    /**
     * Obtenir le nom traduit d'une matière
     */
    public static function getSubjectName(string $subjectCode, string $language = 'fr'): string
    {
        return self::$subjectTranslations[$language][$subjectCode] ?? $subjectCode;
    }

    /**
     * Traductions des mentions
     */
    private static $mentionTranslations = [
        'fr' => [
            'Très Bien' => 'Très Bien',
            'Bien' => 'Bien',
            'Assez Bien' => 'Assez Bien',
            'Passable' => 'Passable',
            'Insuffisant' => 'Insuffisant'
        ],
        'en' => [
            'Très Bien' => 'Very Good',
            'Bien' => 'Good',
            'Assez Bien' => 'Fairly Good',
            'Passable' => 'Passable',
            'Insuffisant' => 'Insufficient'
        ]
    ];

    /**
     * Obtenir la mention traduite
     */
    public static function getMention(string $mention, string $language = 'fr'): string
    {
        return self::$mentionTranslations[$language][$mention] ?? $mention;
    }
}

/**
 * Service de calendrier académique camerounais
 */
class CameroonianAcademicCalendar
{
    /**
     * Jours fériés camerounais
     */
    private static $holidays = [
        '01-01' => 'Jour de l\'An',
        '02-11' => 'Fête de la Jeunesse',
        '05-01' => 'Fête du Travail',
        '05-20' => 'Fête Nationale',
        '08-15' => 'Assomption',
        '12-25' => 'Noël'
    ];

    /**
     * Périodes de vacances
     */
    private static $vacationPeriods = [
        'vacances_noel' => [
            'start' => '12-20',
            'end' => '01-05',
            'name' => 'Vacances de Noël'
        ],
        'vacances_paques' => [
            'start' => '03-28',
            'end' => '04-07',
            'name' => 'Vacances de Pâques'
        ],
        'vacances_ete' => [
            'start' => '06-30',
            'end' => '09-01',
            'name' => 'Grandes Vacances'
        ]
    ];

    /**
     * Vérifier si une date est un jour férié
     */
    public static function isHoliday(string $date): bool
    {
        $dayMonth = date('m-d', strtotime($date));
        return isset(self::$holidays[$dayMonth]);
    }

    /**
     * Vérifier si une date est en période de vacances
     */
    public static function isVacation(string $date): bool
    {
        $dayMonth = date('m-d', strtotime($date));
        
        foreach (self::$vacationPeriods as $period) {
            if ($dayMonth >= $period['start'] && $dayMonth <= $period['end']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtenir le nom du jour férié
     */
    public static function getHolidayName(string $date): ?string
    {
        $dayMonth = date('m-d', strtotime($date));
        return self::$holidays[$dayMonth] ?? null;
    }

    /**
     * Obtenir les périodes académiques
     */
    public static function getAcademicPeriods(string $academicYear): array
    {
        $year = explode('-', $academicYear)[0];
        
        return [
            '1ER_TRIMESTRE' => [
                'start' => $year . '-09-01',
                'end' => $year . '-12-20',
                'name' => '1er Trimestre'
            ],
            '2EME_TRIMESTRE' => [
                'start' => ($year + 1) . '-01-06',
                'end' => ($year + 1) . '-03-28',
                'name' => '2ème Trimestre'
            ],
            '3EME_TRIMESTRE' => [
                'start' => ($year + 1) . '-04-07',
                'end' => ($year + 1) . '-06-30',
                'name' => '3ème Trimestre'
            ]
        ];
    }
}

/**
 * Service de promotion automatique
 */
class AutomaticPromotionService
{
    /**
     * Promouvoir automatiquement les élèves
     */
    public static function promoteStudents(string $academicYear): array
    {
        $db = \Config\Database::connect();
        $results = [
            'promoted' => 0,
            'retained' => 0,
            'errors' => []
        ];

        // Récupérer tous les élèves actifs
        $students = $db->table('students')
                      ->where('status', 'ACTIVE')
                      ->where('academic_year', $academicYear)
                      ->get()
                      ->getResultArray();

        foreach ($students as $student) {
            try {
                $yearAverage = CameroonianGradeService::calculateYearAverage($student['id'], $academicYear);
                $shouldPromote = CameroonianGradeService::determinePromotion($student['id'], $academicYear);

                if ($shouldPromote) {
                    // Logique de promotion
                    $newClassId = self::getNextClass($student['current_class_id']);
                    if ($newClassId) {
                        $db->table('students')
                           ->where('id', $student['id'])
                           ->update([
                               'current_class_id' => $newClassId,
                               'academic_year' => self::getNextAcademicYear($academicYear)
                           ]);
                        $results['promoted']++;
                    }
                } else {
                    // Redoublement
                    $results['retained']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Erreur pour l'élève {$student['id']}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Obtenir la classe suivante
     */
    private static function getNextClass(int $currentClassId): ?int
    {
        $db = \Config\Database::connect();
        
        $currentClass = $db->table('classes')
                          ->where('id', $currentClassId)
                          ->get()
                          ->getRowArray();

        if (!$currentClass) return null;

        // Logique de progression des classes
        $classProgression = [
            'CP' => 'CE1',
            'CE1' => 'CE2',
            'CE2' => 'CM1',
            'CM1' => 'CM2',
            'CM2' => '6ème',
            '6ème' => '5ème',
            '5ème' => '4ème',
            '4ème' => '3ème'
        ];

        $currentLevel = $currentClass['level'];
        $nextLevel = $currentLevel + 1;

        $nextClass = $db->table('classes')
                       ->where('level', $nextLevel)
                       ->where('is_active', 1)
                       ->get()
                       ->getRowArray();

        return $nextClass ? $nextClass['id'] : null;
    }

    /**
     * Obtenir l'année académique suivante
     */
    private static function getNextAcademicYear(string $academicYear): string
    {
        $years = explode('-', $academicYear);
        return $years[1] . '-' . ($years[1] + 1);
    }
}

/**
 * Service de validation des données camerounaises
 */
class CameroonianValidationService
{
    /**
     * Valider un numéro de téléphone camerounais
     */
    public static function validateCameroonianPhone(string $phone): bool
    {
        // Format : +237 6XX XXX XXX ou 6XX XXX XXX
        $pattern = '/^(\+237\s?)?6[0-9]{8}$/';
        return preg_match($pattern, $phone);
    }

    /**
     * Valider un matricule d'élève
     */
    public static function validateMatricule(string $matricule): bool
    {
        // Format : AAAAXXX (année + 3 chiffres)
        $pattern = '/^\d{4}\d{3}$/';
        return preg_match($pattern, $matricule);
    }

    /**
     * Valider une note selon le système camerounais
     */
    public static function validateGrade(float $grade): bool
    {
        return $grade >= 0 && $grade <= 20;
    }

    /**
     * Valider un montant en FCFA
     */
    public static function validateAmount(float $amount): bool
    {
        return $amount > 0 && $amount <= 1000000; // Max 1 million FCFA
    }
}

// Exemple d'utilisation
if (php_sapi_name() === 'cli') {
    echo "=== AMÉLIORATIONS CAMEROUNAISES - LYCOL ===\n";
    echo "Date : " . date('Y-m-d H:i:s') . "\n\n";

    // Test du service de notation
    $average = 14.5;
    $mention = CameroonianGradeService::calculateMention($average);
    echo "Moyenne : $average/20 - Mention : $mention\n";

    // Test du service multilingue
    $subjectName = MultilingualService::getSubjectName('MATH', 'fr');
    echo "Matière MATH en français : $subjectName\n";

    // Test du calendrier
    $isHoliday = CameroonianAcademicCalendar::isHoliday('2025-05-20');
    echo "20 mai 2025 est férié : " . ($isHoliday ? 'Oui' : 'Non') . "\n";

    echo "\n=== FIN DES TESTS ===\n";
}
?>





