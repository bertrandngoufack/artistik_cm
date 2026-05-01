<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle License pour LyCol
 */
class LicenseModel extends Model
{
    protected $table = 'licenses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'license_key', 'client_id', 'license_type', 'issued_date', 
        'expiry_date', 'status', 'features'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'license_key' => 'required|min_length[10]|max_length[50]|is_unique[licenses.license_key,id,{id}]',
        'client_id' => 'required|min_length[3]|max_length[50]',
        'license_type' => 'required|in_list[TRIAL,ANNUAL,BIENNIAL]',
        'issued_date' => 'required|valid_date',
        'expiry_date' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'license_key' => [
            'required' => 'La clé de licence est requise',
            'min_length' => 'La clé de licence doit contenir au moins 10 caractères',
            'max_length' => 'La clé de licence ne peut pas dépasser 50 caractères',
            'is_unique' => 'Cette clé de licence existe déjà'
        ],
        'client_id' => [
            'required' => 'L\'identifiant client est requis',
            'min_length' => 'L\'identifiant client doit contenir au moins 3 caractères',
            'max_length' => 'L\'identifiant client ne peut pas dépasser 50 caractères'
        ],
        'license_type' => [
            'required' => 'Le type de licence est requis',
            'in_list' => 'Le type de licence doit être TRIAL, ANNUAL ou BIENNIAL'
        ],
        'issued_date' => [
            'required' => 'La date de début est requise',
            'valid_date' => 'Veuillez saisir une date valide'
        ],
        'expiry_date' => [
            'required' => 'La date d\'expiration est requise',
            'valid_date' => 'Veuillez saisir une date valide'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtenir la licence active
     */
    public function getActiveLicense()
    {
        return $this->where('status', 'ACTIVE')
                   ->where('expiry_date >=', date('Y-m-d'))
                   ->first();
    }

    /**
     * Vérifier si une licence est valide
     */
    public function isLicenseValid($licenseKey)
    {
        $license = $this->where('license_key', $licenseKey)
                       ->where('status', 'ACTIVE')
                       ->where('expiry_date >=', date('Y-m-d'))
                       ->first();
        
        return $license !== null;
    }

    /**
     * Obtenir les licences expirées
     */
    public function getExpiredLicenses()
    {
        return $this->where('expiry_date <', date('Y-m-d'))
                   ->findAll();
    }

    /**
     * Obtenir les licences expirant bientôt
     */
    public function getExpiringSoon($days = 30)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        return $this->where('expiry_date <=', $expiryDate)
                   ->where('expiry_date >=', date('Y-m-d'))
                   ->where('status', 'ACTIVE')
                   ->findAll();
    }

    /**
     * Désactiver une licence
     */
    public function deactivateLicense($id)
    {
        return $this->update($id, ['status' => 'REVOKED']);
    }

    /**
     * Activer une licence
     */
    public function activateLicense($id)
    {
        return $this->update($id, ['status' => 'ACTIVE']);
    }

    /**
     * Renouveler une licence
     */
    public function renewLicense($id, $newExpiryDate)
    {
        return $this->update($id, [
            'expiry_date' => $newExpiryDate,
            'status' => 'ACTIVE'
        ]);
    }

    /**
     * Obtenir les statistiques des licences
     */
    public function getLicenseStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('status', 'ACTIVE')->countAllResults(),
            'expired' => $this->where('expiry_date <', date('Y-m-d'))->countAllResults(),
            'expiring_soon' => $this->getExpiringSoon()->count(),
            'by_type' => $this->select('license_type, COUNT(*) as count')
                             ->groupBy('license_type')
                             ->findAll()
        ];
    }

    /**
     * Rechercher des licences
     */
    public function searchLicenses($query)
    {
        return $this->like('license_key', $query)
                   ->orLike('client_id', $query)
                   ->orLike('license_type', $query)
                   ->findAll();
    }

    /**
     * Vérifier si une clé de licence existe
     */
    public function licenseKeyExists($licenseKey, $excludeId = null)
    {
        $builder = $this->where('license_key', $licenseKey);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Obtenir les licences par page
     */
    public function getLicensesPaginated($page = 1, $perPage = 20)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * Obtenir le pager pour la pagination
     */
    public function getLicensesPager()
    {
        return $this->pager;
    }
}
