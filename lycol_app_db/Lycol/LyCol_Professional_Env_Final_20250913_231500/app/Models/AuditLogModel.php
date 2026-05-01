<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // Pas de timestamps automatiques
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null; // Pas de mise à jour pour les logs

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'action' => 'required|max_length[50]',
        'table_name' => 'required|max_length[100]',
        'record_id' => 'permit_empty|integer',
        'old_values' => 'permit_empty',
        'new_values' => 'permit_empty',
        'ip_address' => 'permit_empty|max_length[45]',
        'user_agent' => 'permit_empty|max_length[500]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];

    protected function beforeInsert(array $data)
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        
        // Encoder les valeurs en JSON si ce sont des tableaux
        if (is_array($data['data']['old_values'])) {
            $data['data']['old_values'] = json_encode($data['data']['old_values']);
        }
        
        if (is_array($data['data']['new_values'])) {
            $data['data']['new_values'] = json_encode($data['data']['new_values']);
        }
        
        return $data;
    }

    /**
     * Enregistrer une action d'audit
     */
    public function logAction($userId, $action, $tableName, $recordId = null, $oldValues = null, $newValues = null)
    {
        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $this->getUserAgent()
        ];

        return $this->insert($logData);
    }

    /**
     * Récupérer l'adresse IP du client
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Récupérer l'agent utilisateur
     */
    private function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Récupérer les logs d'un utilisateur
     */
    public function getUserLogs($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Récupérer les logs d'une table
     */
    public function getTableLogs($tableName, $limit = 50)
    {
        return $this->where('table_name', $tableName)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Récupérer les logs d'un enregistrement spécifique
     */
    public function getRecordLogs($tableName, $recordId, $limit = 50)
    {
        return $this->where('table_name', $tableName)
                    ->where('record_id', $recordId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Récupérer les logs par action
     */
    public function getActionLogs($action, $limit = 50)
    {
        return $this->where('action', $action)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Récupérer les logs avec les informations utilisateur
     */
    public function getLogsWithUser($limit = 100)
    {
        return $this->select('audit_logs.*, users.username, users.first_name, users.last_name')
                    ->join('users', 'users.id = audit_logs.user_id', 'left')
                    ->orderBy('audit_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Récupérer les statistiques des logs
     */
    public function getLogStats()
    {
        $db = \Config\Database::connect();
        
        // Total des logs
        $totalLogs = $this->countAllResults();
        
        // Logs par action
        $actionStats = $db->table('audit_logs')
                         ->select('action, COUNT(*) as count')
                         ->groupBy('action')
                         ->orderBy('count', 'DESC')
                         ->get()
                         ->getResultArray();
        
        // Logs par table
        $tableStats = $db->table('audit_logs')
                        ->select('table_name, COUNT(*) as count')
                        ->groupBy('table_name')
                        ->orderBy('count', 'DESC')
                        ->get()
                        ->getResultArray();
        
        // Logs par jour (7 derniers jours)
        $dailyStats = $db->table('audit_logs')
                        ->select('DATE(created_at) as date, COUNT(*) as count')
                        ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                        ->groupBy('DATE(created_at)')
                        ->orderBy('date', 'DESC')
                        ->get()
                        ->getResultArray();
        
        return [
            'total_logs' => $totalLogs,
            'action_stats' => $actionStats,
            'table_stats' => $tableStats,
            'daily_stats' => $dailyStats
        ];
    }

    /**
     * Nettoyer les anciens logs (plus de 90 jours)
     */
    public function cleanOldLogs($days = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
    
    /**
     * Récupérer les logs paginés avec filtres
     */
    public function getLogsPaginated($page = 1, $perPage = 50, $module = null, $action = null, $userId = null)
    {
        $builder = $this->select('audit_logs.*, users.username, users.first_name, users.last_name')
                        ->join('users', 'users.id = audit_logs.user_id', 'left');
        
        // Appliquer les filtres
        if ($module) {
            $builder->where('audit_logs.table_name', $module);
        }
        
        if ($action) {
            $builder->where('audit_logs.action', $action);
        }
        
        if ($userId) {
            $builder->where('audit_logs.user_id', $userId);
        }
        
        // Trier par date de création décroissante
        $builder->orderBy('audit_logs.created_at', 'DESC');
        
        // Paginer les résultats
        return $builder->paginate($perPage, 'default', $page);
    }
    
    /**
     * Obtenir le pager pour la pagination
     */
    public function getLogsPager()
    {
        return $this->pager;
    }
}
