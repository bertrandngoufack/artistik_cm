<?php

namespace App\Traits;

use Config\AcademicYear;

trait AcademicYearTrait
{
    protected $academicYearConfig;
    protected $currentAcademicYear;
    protected $academicYearDates;

    /**
     * Initialiser la configuration de l'année scolaire
     */
    protected function initAcademicYear()
    {
        $this->academicYearConfig = new AcademicYear();
        $this->currentAcademicYear = $this->academicYearConfig->getCurrentAcademicYear();
        $this->academicYearDates = $this->academicYearConfig->getAcademicYearDates();
    }

    /**
     * Obtenir l'année scolaire actuelle
     */
    protected function getCurrentAcademicYear(): string
    {
        if (!$this->currentAcademicYear) {
            $this->initAcademicYear();
        }
        return $this->currentAcademicYear;
    }

    /**
     * Obtenir les dates de l'année scolaire
     */
    protected function getAcademicYearDates(): array
    {
        if (!$this->academicYearDates) {
            $this->initAcademicYear();
        }
        return $this->academicYearDates;
    }

    /**
     * Appliquer le filtre d'année scolaire à une requête
     */
    protected function applyAcademicYearFilter($query, $dateColumn = 'created_at', $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        return $query->where("$dateColumn >=", $dates['start_date'])
                    ->where("$dateColumn <=", $dates['end_date']);
    }

    /**
     * Obtenir les données filtrées par année scolaire
     */
    protected function getDataByAcademicYear($model, $academicYear = null, $dateColumn = 'created_at')
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        return $model->where("$dateColumn >=", $dates['start_date'])
                    ->where("$dateColumn <=", $dates['end_date'])
                    ->findAll();
    }

    /**
     * Compter les données par année scolaire
     */
    protected function countDataByAcademicYear($model, $academicYear = null, $dateColumn = 'created_at')
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        return $model->where("$dateColumn >=", $dates['start_date'])
                    ->where("$dateColumn <=", $dates['end_date'])
                    ->countAllResults();
    }

    /**
     * Calculer la somme des données par année scolaire
     */
    protected function sumDataByAcademicYear($model, $sumColumn, $academicYear = null, $dateColumn = 'created_at')
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        return $model->selectSum($sumColumn)
                    ->where("$dateColumn >=", $dates['start_date'])
                    ->where("$dateColumn <=", $dates['end_date'])
                    ->get()
                    ->getRow()
                    ->$sumColumn ?? 0;
    }

    /**
     * Préparer les données pour la vue avec l'année scolaire
     */
    protected function prepareViewData(array $data = []): array
    {
        $this->initAcademicYear();
        
        $academicYearData = [
            'current_academic_year' => $this->currentAcademicYear,
            'academic_year_dates' => $this->academicYearDates,
            'available_academic_years' => $this->academicYearConfig->getAvailableAcademicYears(),
            'academic_year_start' => $this->academicYearDates['start_date'],
            'academic_year_end' => $this->academicYearDates['end_date']
        ];

        return array_merge($data, $academicYearData);
    }

    /**
     * Valider qu'une date est dans l'année scolaire
     */
    protected function validateAcademicYearDate(string $date, ?string $academicYear = null): bool
    {
        return $this->academicYearConfig->isInAcademicYear($date, $academicYear);
    }

    /**
     * Obtenir l'année scolaire d'une date
     */
    protected function getAcademicYearFromDate(string $date): string
    {
        return $this->academicYearConfig->getAcademicYearFromDate($date);
    }
}


