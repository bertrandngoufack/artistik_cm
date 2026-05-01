<?php

if (!function_exists('get_current_academic_year')) {
    /**
     * Obtenir l'année académique actuelle
     */
    function get_current_academic_year(): string
    {
        $service = new \App\Services\AcademicYearService();
        return $service->getCurrentAcademicYear();
    }
}

if (!function_exists('get_available_academic_years')) {
    /**
     * Obtenir la liste des années académiques disponibles
     */
    function get_available_academic_years(int $count = 5): array
    {
        $service = new \App\Services\AcademicYearService();
        return $service->getAvailableAcademicYears($count);
    }
}

if (!function_exists('get_academic_years_for_forms')) {
    /**
     * Obtenir les années académiques formatées pour les formulaires
     */
    function get_academic_years_for_forms(): array
    {
        $service = new \App\Services\AcademicYearService();
        return $service->getAcademicYearsForForms();
    }
}

if (!function_exists('is_valid_academic_year')) {
    /**
     * Vérifier si une année académique est valide
     */
    function is_valid_academic_year(string $academicYear): bool
    {
        $service = new \App\Services\AcademicYearService();
        return $service->isValidAcademicYear($academicYear);
    }
}

if (!function_exists('format_academic_year')) {
    /**
     * Formater une année académique pour l'affichage
     */
    function format_academic_year(string $academicYear): string
    {
        $service = new \App\Services\AcademicYearService();
        return $service->formatAcademicYear($academicYear);
    }
}

if (!function_exists('get_academic_year_dates')) {
    /**
     * Obtenir les dates de début et fin d'une année académique
     */
    function get_academic_year_dates(?string $academicYear = null): array
    {
        $service = new \App\Services\AcademicYearService();
        return $service->getAcademicYearDates($academicYear);
    }
}

