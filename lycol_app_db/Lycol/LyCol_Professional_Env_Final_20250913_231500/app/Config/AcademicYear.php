<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AcademicYear extends BaseConfig
{
    /**
     * Année scolaire actuelle
     * Format: 2024-2025 (septembre 2024 à juin 2025)
     */
    public string $currentYear = '2024-2025';

    /**
     * Date de début de l'année scolaire (1er septembre)
     */
    public string $startDate = '2024-09-01';

    /**
     * Date de fin de l'année scolaire (30 juin)
     */
    public string $endDate = '2025-06-30';

    /**
     * Obtenir l'année scolaire actuelle basée sur la date
     */
    public function getCurrentAcademicYear(): string
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        // Si nous sommes entre septembre et décembre, c'est l'année scolaire en cours
        if ($currentMonth >= 9) {
            return $currentYear . '-' . ($currentYear + 1);
        }
        // Si nous sommes entre janvier et août, c'est l'année scolaire précédente
        else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    /**
     * Obtenir les dates de début et fin de l'année scolaire
     */
    public function getAcademicYearDates(?string $academicYear = null): array
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $years = explode('-', $academicYear);
        $startYear = $years[0];
        $endYear = $years[1];

        return [
            'start_date' => $startYear . '-09-01',
            'end_date' => $endYear . '-06-30',
            'academic_year' => $academicYear
        ];
    }

    /**
     * Vérifier si une date est dans l'année scolaire
     */
    public function isInAcademicYear(string $date, ?string $academicYear = null): bool
    {
        $dates = $this->getAcademicYearDates($academicYear);
        return $date >= $dates['start_date'] && $date <= $dates['end_date'];
    }

    /**
     * Obtenir la liste des années scolaires disponibles
     */
    public function getAvailableAcademicYears(int $count = 5): array
    {
        $years = [];
        $currentYear = $this->getCurrentAcademicYear();
        $currentStartYear = (int)explode('-', $currentYear)[0];

        for ($i = 0; $i < $count; $i++) {
            $year = ($currentStartYear - $i) . '-' . ($currentStartYear - $i + 1);
            $years[] = $year;
        }

        return $years;
    }

    /**
     * Obtenir l'année scolaire à partir d'une date
     */
    public function getAcademicYearFromDate(string $date): string
    {
        $year = (int)date('Y', strtotime($date));
        $month = (int)date('n', strtotime($date));

        if ($month >= 9) {
            return $year . '-' . ($year + 1);
        } else {
            return ($year - 1) . '-' . $year;
        }
    }
}


