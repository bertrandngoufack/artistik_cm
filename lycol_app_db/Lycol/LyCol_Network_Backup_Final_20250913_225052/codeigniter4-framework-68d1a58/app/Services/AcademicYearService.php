<?php

namespace App\Services;

use Config\AcademicYear as AcademicYearConfig;

/**
 * Service de gestion des années académiques
 * Centralise la logique de gestion des années scolaires
 */
class AcademicYearService
{
    protected $config;
    protected $cacheService;

    public function __construct()
    {
        $this->config = new AcademicYearConfig();
        // Temporairement désactivé pour éviter les problèmes de connexion
        // $this->cacheService = new CacheService();
    }

    /**
     * Obtenir l'année académique actuelle
     */
    public function getCurrentAcademicYear(): string
    {
        // Temporairement sans cache pour éviter les problèmes de connexion
        // return $this->cacheService->remember('current_academic_year', function() {
        //     return $this->config->getCurrentAcademicYear();
        // }, 3600); // Cache 1 heure
        return $this->config->getCurrentAcademicYear();
    }

    /**
     * Obtenir l'année académique par défaut (pour les formulaires)
     */
    public function getDefaultAcademicYear(): string
    {
        return $this->getCurrentAcademicYear();
    }

    /**
     * Obtenir la liste des années académiques disponibles
     */
    public function getAvailableAcademicYears(int $count = 5): array
    {
        // Temporairement sans cache pour éviter les problèmes de connexion
        // return $this->cacheService->remember('available_academic_years', function() use ($count) {
        //     return $this->config->getAvailableAcademicYears($count);
        // }, 3600);
        return $this->config->getAvailableAcademicYears($count);
    }

    /**
     * Obtenir les années académiques pour les formulaires
     */
    public function getAcademicYearsForForms(): array
    {
        $years = $this->getAvailableAcademicYears(5);
        $formatted = [];
        
        foreach ($years as $year) {
            $formatted[$year] = $year;
        }
        
        return $formatted;
    }

    /**
     * Vérifier si une année académique est valide
     */
    public function isValidAcademicYear(string $academicYear): bool
    {
        return preg_match('/^\d{4}-\d{4}$/', $academicYear) === 1;
    }

    /**
     * Obtenir l'année académique à partir d'une date
     */
    public function getAcademicYearFromDate(string $date): string
    {
        return $this->config->getAcademicYearFromDate($date);
    }

    /**
     * Obtenir les dates de début et fin d'une année académique
     */
    public function getAcademicYearDates(?string $academicYear = null): array
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }
        
        return $this->config->getAcademicYearDates($academicYear);
    }

    /**
     * Vérifier si une date est dans l'année académique actuelle
     */
    public function isInCurrentAcademicYear(string $date): bool
    {
        return $this->config->isInAcademicYear($date, $this->getCurrentAcademicYear());
    }

    /**
     * Obtenir l'année académique précédente
     */
    public function getPreviousAcademicYear(): string
    {
        $current = $this->getCurrentAcademicYear();
        $years = explode('-', $current);
        return ($years[0] - 1) . '-' . ($years[1] - 1);
    }

    /**
     * Obtenir l'année académique suivante
     */
    public function getNextAcademicYear(): string
    {
        $current = $this->getCurrentAcademicYear();
        $years = explode('-', $current);
        return ($years[0] + 1) . '-' . ($years[1] + 1);
    }

    /**
     * Formater une année académique pour l'affichage
     */
    public function formatAcademicYear(string $academicYear): string
    {
        return 'Année scolaire ' . $academicYear;
    }

    /**
     * Obtenir l'année académique depuis la session ou la requête
     */
    public function getAcademicYearFromRequest($request, $default = null): string
    {
        $academicYear = $request->getPost('academic_year') 
                     ?? $request->getGet('academic_year') 
                     ?? session()->get('current_academic_year')
                     ?? $default;
        
        if (!$academicYear || !$this->isValidAcademicYear($academicYear)) {
            $academicYear = $this->getCurrentAcademicYear();
        }
        
        // Mettre en cache dans la session
        session()->set('current_academic_year', $academicYear);
        
        return $academicYear;
    }

    /**
     * Mettre à jour l'année académique actuelle
     */
    public function updateCurrentAcademicYear(string $academicYear): bool
    {
        if (!$this->isValidAcademicYear($academicYear)) {
            return false;
        }
        
        // Mettre à jour la session
        session()->set('current_academic_year', $academicYear);
        
        // Temporairement sans cache pour éviter les problèmes de connexion
        // Vider le cache
        // $this->cacheService->forget('current_academic_year');
        // $this->cacheService->forget('available_academic_years');
        
        return true;
    }
}
