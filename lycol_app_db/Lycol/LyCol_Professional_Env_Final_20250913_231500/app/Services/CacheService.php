<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use Config\Services;
use App\Services\AcademicYearService;

/**
 * Service de Cache pour KISSAI SCHOOL
 * Optimisation des requêtes lourdes
 */
class CacheService
{
    protected $cache;
    protected $defaultTTL = 3600; // 1 heure par défaut
    protected $academicYearService;

    public function __construct()
    {
        $this->cache = Services::cache();
        $this->academicYearService = new AcademicYearService();
    }

    /**
     * Génère une clé de cache unique
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        return $key;
    }

    /**
     * Récupère des données du cache ou les génère
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTTL;
        
        // Essayer de récupérer du cache
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        // Générer les données
        $data = $callback();
        
        // Stocker en cache
        $this->cache->save($key, $data, $ttl);
        
        return $data;
    }

    /**
     * Supprime une clé du cache
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * Supprime toutes les clés du cache
     */
    public function clear(): bool
    {
        return $this->cache->clean();
    }

    /**
     * Vérifie si une clé existe dans le cache
     */
    public function has(string $key): bool
    {
        return $this->cache->get($key) !== null;
    }

    /**
     * Obtenir l'année académique actuelle
     */
    protected function getCurrentAcademicYear(): string
    {
        return $this->academicYearService->getCurrentAcademicYear();
    }

    /**
     * Cache pour les statistiques des étudiants
     */
    public function getStudentStats(string $academicYear, ?int $classId = null, ?string $status = null): array
    {
        $key = $this->generateKey('student_stats', [
            'academic_year' => $academicYear,
            'class_id' => $classId,
            'status' => $status
        ]);

        return $this->remember($key, function() use ($academicYear, $classId, $status) {
            $db = \Config\Database::connect();
            
            $where = ['academic_year' => $academicYear];
            if ($classId) $where['current_class_id'] = $classId;
            if ($status) $where['status'] = $status;

            $builder = $db->table('students');
            $builder->where($where);

            return [
                'total' => $builder->countAllResults(),
                'male' => $builder->where('gender', 'M')->countAllResults(),
                'female' => $builder->where('gender', 'F')->countAllResults(),
                'active' => $builder->where('status', 'ACTIVE')->countAllResults(),
                'inactive' => $builder->where('status', 'INACTIVE')->countAllResults()
            ];
        }, 1800); // 30 minutes
    }

    /**
     * Cache pour les statistiques de paiements
     */
    public function getPaymentStats(string $academicYear, ?string $status = null): array
    {
        $key = $this->generateKey('payment_stats', [
            'academic_year' => $academicYear,
            'status' => $status
        ]);

        return $this->remember($key, function() use ($academicYear, $status) {
            $db = \Config\Database::connect();
            
            $where = ['academic_year' => $academicYear];
            if ($status) $where['status'] = $status;

            $builder = $db->table('payments');
            $builder->where($where);

            $total = $builder->selectSum('amount')->get()->getRow()->amount ?? 0;

            return [
                'total_amount' => $total,
                'total_payments' => $builder->countAllResults(),
                'paid' => $builder->where('status', 'PAID')->countAllResults(),
                'pending' => $builder->where('status', 'PENDING')->countAllResults(),
                'overdue' => $builder->where('status', 'OVERDUE')->countAllResults()
            ];
        }, 1800); // 30 minutes
    }

    /**
     * Cache pour les statistiques de bibliothèque
     */
    public function getLibraryStats(): array
    {
        $key = $this->generateKey('library_stats');

        return $this->remember($key, function() {
            $db = \Config\Database::connect();
            
            return [
                'total_books' => $db->table('books')->countAllResults(),
                'available_books' => $db->table('books')->where('status', 'AVAILABLE')->countAllResults(),
                'borrowed_books' => $db->table('books')->where('status', 'BORROWED')->countAllResults(),
                'active_loans' => $db->table('loans')->where('status', 'ACTIVE')->countAllResults(),
                'overdue_loans' => $db->table('loans')->where('status', 'OVERDUE')->countAllResults()
            ];
        }, 3600); // 1 heure
    }

    /**
     * Cache pour les données de configuration
     */
    public function getConfigurationData(): array
    {
        $key = $this->generateKey('configuration_data');

        return $this->remember($key, function() {
            $db = \Config\Database::connect();
            
            $settings = $db->table('settings')->get()->getResultArray();
            $config = [];
            
            foreach ($settings as $setting) {
                $config[$setting['key']] = $setting['value'];
            }
            
            return $config;
        }, 7200); // 2 heures
    }

    /**
     * Cache pour les listes de classes
     */
    public function getActiveClasses(string $academicYear): array
    {
        $key = $this->generateKey('active_classes', ['academic_year' => $academicYear]);

        return $this->remember($key, function() use ($academicYear) {
            $db = \Config\Database::connect();
            
            return $db->table('classes')
                ->where('academic_year', $academicYear)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
        }, 3600); // 1 heure
    }

    /**
     * Cache pour les listes d'enseignants
     */
    public function getActiveTeachers(): array
    {
        $key = $this->generateKey('active_teachers');

        return $this->remember($key, function() {
            $db = \Config\Database::connect();
            
            return $db->table('teachers')
                ->where('is_active', 1)
                ->orderBy('last_name', 'ASC')
                ->get()
                ->getResultArray();
        }, 3600); // 1 heure
    }

    /**
     * Cache pour les rapports fréquents
     */
    public function getReportData(string $reportType, array $params = []): array
    {
        $key = $this->generateKey('report_' . $reportType, $params);

        return $this->remember($key, function() use ($reportType, $params) {
            return $this->generateReportData($reportType, $params);
        }, 1800); // 30 minutes
    }

    /**
     * Génère les données de rapport
     */
    private function generateReportData(string $reportType, array $params): array
    {
        $db = \Config\Database::connect();
        
        switch ($reportType) {
            case 'student_performance':
                return $this->generateStudentPerformanceReport($db, $params);
            case 'financial_summary':
                return $this->generateFinancialSummaryReport($db, $params);
            case 'library_activity':
                return $this->generateLibraryActivityReport($db, $params);
            default:
                return [];
        }
    }

    /**
     * Génère le rapport de performance des étudiants
     */
    private function generateStudentPerformanceReport($db, array $params): array
    {
        $academicYear = $params['academic_year'] ?? $this->getCurrentAcademicYear();
        
        $query = $db->query("
            SELECT 
                s.first_name,
                s.last_name,
                c.name as class_name,
                AVG(g.score) as average_score,
                COUNT(g.id) as total_grades
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN grades g ON s.id = g.student_id AND g.academic_year = ?
            WHERE s.academic_year = ?
            GROUP BY s.id, s.first_name, s.last_name, c.name
            ORDER BY average_score DESC
        ", [$academicYear, $academicYear]);

        return $query->getResultArray();
    }

    /**
     * Génère le rapport financier
     */
    private function generateFinancialSummaryReport($db, array $params): array
    {
        $academicYear = $params['academic_year'] ?? $this->getCurrentAcademicYear();
        
        $query = $db->query("
            SELECT 
                MONTH(payment_date) as month,
                SUM(amount) as total_amount,
            COUNT(*) as payment_count,
                status
            FROM payments
            WHERE academic_year = ?
            GROUP BY MONTH(payment_date), status
            ORDER BY month, status
        ", [$academicYear]);

        return $query->getResultArray();
    }

    /**
     * Génère le rapport d'activité de la bibliothèque
     */
    private function generateLibraryActivityReport($db, array $params): array
    {
        $academicYear = $params['academic_year'] ?? $this->getCurrentAcademicYear();
        
        $query = $db->query("
            SELECT 
                b.title,
                b.author,
                COUNT(l.id) as loan_count,
                AVG(DATEDIFF(l.return_date, l.loan_date)) as avg_loan_duration
            FROM books b
            LEFT JOIN loans l ON b.id = l.book_id AND l.academic_year = ?
            WHERE l.academic_year = ? OR l.academic_year IS NULL
            GROUP BY b.id, b.title, b.author
            ORDER BY loan_count DESC
        ", [$academicYear, $academicYear]);

        return $query->getResultArray();
    }

    /**
     * Efface le cache pour une clé spécifique
     */
    public function forget(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * Efface tout le cache
     */
    public function flush(): bool
    {
        return $this->cache->clean();
    }

    /**
     * Efface le cache par préfixe
     */
    public function forgetByPrefix(string $prefix): bool
    {
        // Cette méthode dépend de l'implémentation du cache
        // Pour l'instant, on efface tout le cache
        return $this->flush();
    }

    /**
     * Obtient les statistiques du cache
     */
    public function getCacheStats(): array
    {
        return [
            'driver' => get_class($this->cache),
            'default_ttl' => $this->defaultTTL,
            'cache_info' => $this->cache->getCacheInfo()
        ];
    }
}
