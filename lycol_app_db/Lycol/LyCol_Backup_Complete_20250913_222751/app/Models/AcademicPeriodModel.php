<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicPeriodModel extends Model
{
    protected $table = 'academic_periods';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'period_type', 'start_date', 'end_date', 'academic_year', 
        'is_active', 'description', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'period_type' => 'required|in_list[1ER_TRIMESTRE,2EME_TRIMESTRE,3EME_TRIMESTRE]',
        'start_date' => 'required|valid_date',
        'end_date' => 'required|valid_date',
        'academic_year' => 'required|min_length[4]|max_length[9]',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Récupérer toutes les périodes académiques actives
     */
    public function getActivePeriods($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        return $this->where('academic_year', $academicYear)
                   ->where('is_active', 1)
                   ->orderBy('start_date', 'ASC')
                   ->findAll();
    }

    /**
     * Récupérer la période académique actuelle
     */
    public function getCurrentPeriod($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $currentDate = date('Y-m-d');
        
        return $this->where('academic_year', $academicYear)
                   ->where('is_active', 1)
                   ->where('start_date <=', $currentDate)
                   ->where('end_date >=', $currentDate)
                   ->first();
    }

    /**
     * Récupérer une période par type et année
     */
    public function getPeriodByType($periodType, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        return $this->where('period_type', $periodType)
                   ->where('academic_year', $academicYear)
                   ->where('is_active', 1)
                   ->first();
    }

    /**
     * Créer les périodes par défaut pour une nouvelle année académique
     */
    public function createDefaultPeriods($academicYear)
    {
        // Extraire l'année de début et de fin
        $years = explode('-', $academicYear);
        $startYear = $years[0];
        $endYear = $years[1];
        
        $defaultPeriods = [
            [
                'name' => '1er Trimestre',
                'period_type' => '1ER_TRIMESTRE',
                'start_date' => $startYear . '-09-01',
                'end_date' => $startYear . '-12-20',
                'academic_year' => $academicYear,
                'is_active' => 1,
                'description' => 'Premier trimestre de l\'année académique ' . $academicYear
            ],
            [
                'name' => '2ème Trimestre',
                'period_type' => '2EME_TRIMESTRE',
                'start_date' => $endYear . '-01-06',
                'end_date' => $endYear . '-03-28',
                'academic_year' => $academicYear,
                'is_active' => 1,
                'description' => 'Deuxième trimestre de l\'année académique ' . $academicYear
            ],
            [
                'name' => '3ème Trimestre',
                'period_type' => '3EME_TRIMESTRE',
                'start_date' => $endYear . '-04-07',
                'end_date' => $endYear . '-06-30',
                'academic_year' => $academicYear,
                'is_active' => 1,
                'description' => 'Troisième trimestre de l\'année académique ' . $academicYear
            ]
        ];

        $success = true;
        foreach ($defaultPeriods as $period) {
            if (!$this->insert($period)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Vérifier si une année académique existe déjà
     */
    public function academicYearExists($academicYear)
    {
        return $this->where('academic_year', $academicYear)->countAllResults() > 0;
    }

    /**
     * Récupérer toutes les années académiques disponibles
     */
    public function getAvailableAcademicYears()
    {
        return $this->select('DISTINCT(academic_year) as academic_year')
                   ->orderBy('academic_year', 'DESC')
                   ->findAll();
    }

    /**
     * Calculer la durée d'une période en jours
     */
    public function calculateDuration($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        
        $months = $interval->m;
        $days = $interval->d;
        
        if ($months > 0) {
            return $months . ' mois ' . $days . ' jours';
        } else {
            return $days . ' jours';
        }
    }

    /**
     * Déterminer le statut d'une période
     */
    public function getPeriodStatus($startDate, $endDate)
    {
        $currentDate = date('Y-m-d');
        
        if ($currentDate < $startDate) {
            return 'À venir';
        } elseif ($currentDate >= $startDate && $currentDate <= $endDate) {
            return 'En cours';
        } else {
            return 'Terminé';
        }
    }

    /**
     * Récupérer l'année académique actuelle
     */
    public function getCurrentAcademicYear()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Si nous sommes entre septembre et décembre, c'est l'année en cours
        // Sinon, c'est l'année précédente
        if ($currentMonth >= 9) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    /**
     * Récupérer les statistiques des périodes
     */
    public function getPeriodStats($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->getCurrentAcademicYear();
        }

        $periods = $this->getActivePeriods($academicYear);
        $stats = [];

        foreach ($periods as $period) {
            $stats[$period['period_type']] = [
                'name' => $period['name'],
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'duration' => $this->calculateDuration($period['start_date'], $period['end_date']),
                'status' => $this->getPeriodStatus($period['start_date'], $period['end_date'])
            ];
        }

        return $stats;
    }

    /**
     * Mettre à jour une période académique
     */
    public function updatePeriod($periodId, $data)
    {
        // Vérifier que la période existe
        $period = $this->find($periodId);
        if (!$period) {
            return false;
        }

        // Vérifier que les dates sont cohérentes
        if (isset($data['start_date']) && isset($data['end_date'])) {
            if ($data['start_date'] >= $data['end_date']) {
                return false;
            }
        }

        return $this->update($periodId, $data);
    }

    /**
     * Désactiver toutes les périodes d'une année académique
     */
    public function deactivateYear($academicYear)
    {
        return $this->where('academic_year', $academicYear)
                   ->set(['is_active' => 0])
                   ->update();
    }

    /**
     * Activer une année académique
     */
    public function activateYear($academicYear)
    {
        return $this->where('academic_year', $academicYear)
                   ->set(['is_active' => 1])
                   ->update();
    }
}
