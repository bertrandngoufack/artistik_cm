<?php

namespace App\Libraries;

/**
 * Classe de génération et validation des licences LyCol
 * 
 * Cette classe gère la génération sécurisée des clés de licence
 * et leur validation pour le système de gestion scolaire LyCol
 */
class LicenseGenerator
{
    /**
     * Alphabet utilisé pour la génération des segments
     */
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    /**
     * Nombre de segments dans une clé de licence
     */
    private const SEGMENTS = 4;
    
    /**
     * Longueur de chaque segment
     */
    private const SEGMENT_LENGTH = 4;
    
    /**
     * Clé secrète pour la génération des signatures
     */
    private const SECRET_SEED = 'LYCOL_SECRET_KEY_2025';
    
    /**
     * Génère une clé de licence complète
     * 
     * @param string $clientId Identifiant unique du client
     * @param string $licenseType Type de licence (TRIAL, BASIC, PRO, ENTERPRISE)
     * @param string $expiryDate Date d'expiration (format: Y-m-d)
     * @return string Clé de licence générée
     * @throws \Exception Si les paramètres sont invalides
     */
    public static function generateLicenseKey(string $clientId, string $licenseType, string $expiryDate): string
    {
        // Validation des paramètres
        if (empty($clientId) || empty($expiryDate)) {
            throw new \Exception('Paramètres manquants: clientId et expiryDate sont requis');
        }
        
        if (!in_array($licenseType, ['TRIAL', 'BASIC', 'PRO', 'ENTERPRISE', 'PERMANENT'])) {
            throw new \Exception('Type de licence invalide');
        }
        
        // Validation du format de date
        if (!self::isValidDate($expiryDate)) {
            throw new \Exception('Format de date invalide. Utilisez Y-m-d');
        }
        
        // Création de la signature
        $signatureData = $clientId . $licenseType . $expiryDate . self::SECRET_SEED;
        $signatureHash = self::hashString($signatureData);
        
        // Génération des segments
        $segments = [];
        
        // Premier segment basé sur le clientId
        $segments[] = self::generateSegmentFromString($clientId);
        
        // Deuxième segment basé sur le type de licence
        $segments[] = self::generateSegmentFromString($licenseType);
        
        // Troisième segment basé sur la signature
        $segments[] = substr($signatureHash, 0, self::SEGMENT_LENGTH);
        
        // Dernier segment avec l'année d'expiration
        $yearSegment = substr($expiryDate, 0, 4);
        $segments[] = $yearSegment;
        
        return implode('-', $segments);
    }
    
    /**
     * Valide une clé de licence
     * 
     * @param string $licenseKey Clé de licence à valider
     * @param string $clientId Identifiant du client
     * @param string $licenseType Type de licence attendu
     * @param string $expiryDate Date d'expiration attendue
     * @return array Résultat de la validation
     */
    public static function validateLicenseKey(string $licenseKey, string $clientId, string $licenseType, string $expiryDate): array
    {
        try {
            $segments = explode('-', $licenseKey);
            
            // Vérification du format
            if (count($segments) !== self::SEGMENTS) {
                return [
                    'valid' => false, 
                    'reason' => 'Format invalide: nombre de segments incorrect'
                ];
            }
            
            // Vérification de l'année d'expiration
            $yearSegment = $segments[3];
            $expectedYear = substr($expiryDate, 0, 4);
            
            if ($yearSegment !== $expectedYear) {
                return [
                    'valid' => false, 
                    'reason' => 'Année d\'expiration ne correspond pas'
                ];
            }
            
            // Vérification de la signature
            $signatureData = $clientId . $licenseType . $expiryDate . self::SECRET_SEED;
            $expectedSignature = self::hashString($signatureData);
            $providedSignature = $segments[2];
            
            if ($providedSignature !== substr($expectedSignature, 0, self::SEGMENT_LENGTH)) {
                return [
                    'valid' => false, 
                    'reason' => 'Signature invalide'
                ];
            }
            
            // Vérification de la date d'expiration (sauf pour les licences PERMANENT)
            if ($licenseType !== 'PERMANENT') {
                $currentDate = date('Y-m-d');
                if ($expiryDate < $currentDate) {
                    return [
                        'valid' => false, 
                        'reason' => 'Licence expirée',
                        'expired' => true
                    ];
                }
            }
            
            return [
                'valid' => true,
                'details' => [
                    'clientId' => $clientId,
                    'type' => $licenseType,
                    'expiry' => $expiryDate,
                    'daysRemaining' => self::calculateDaysRemaining($expiryDate),
                    'validated' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $error) {
            return [
                'valid' => false, 
                'reason' => 'Erreur de validation: ' . $error->getMessage()
            ];
        }
    }
    
    /**
     * Décode les informations d'une licence (pour affichage)
     * 
     * @param string $licenseKey Clé de licence
     * @return array Informations décodées
     * @throws \Exception Si le format est invalide
     */
    public static function decodeLicenseInfo(string $licenseKey): array
    {
        $segments = explode('-', $licenseKey);
        
        if (count($segments) !== self::SEGMENTS) {
            throw new \Exception('Format de licence invalide');
        }
        
        return [
            'segments' => $segments,
            'expiryYear' => $segments[3],
            'format' => 'VALID',
            'checksum' => substr(self::hashString($licenseKey), 0, 8),
            'decoded' => [
                'segment1' => $segments[0],
                'segment2' => $segments[1],
                'signature' => $segments[2],
                'year' => $segments[3]
            ]
        ];
    }
    
    /**
     * Génère une clé de licence d'essai
     * 
     * @param string $clientId Identifiant du client
     * @return string Clé de licence d'essai
     */
    public static function generateTrialLicense(string $clientId): string
    {
        $expiryDate = date('Y-m-d', strtotime('+3 months'));
        return self::generateLicenseKey($clientId, 'TRIAL', $expiryDate);
    }
    
    /**
     * Génère une clé de licence annuelle
     * 
     * @param string $clientId Identifiant du client
     * @param string $licenseType Type de licence
     * @return string Clé de licence annuelle
     */
    public static function generateAnnualLicense(string $clientId, string $licenseType): string
    {
        $expiryDate = date('Y-m-d', strtotime('+1 year'));
        return self::generateLicenseKey($clientId, $licenseType, $expiryDate);
    }
    
    /**
     * Génère une clé de licence pour 2 ans
     * 
     * @param string $clientId Identifiant du client
     * @param string $licenseType Type de licence
     * @return string Clé de licence pour 2 ans
     */
    public static function generateBiennialLicense(string $clientId, string $licenseType): string
    {
        $expiryDate = date('Y-m-d', strtotime('+2 years'));
        return self::generateLicenseKey($clientId, $licenseType, $expiryDate);
    }
    
    /**
     * Calcule le nombre de jours restants avant expiration
     * 
     * @param string $expiryDate Date d'expiration
     * @return int Nombre de jours restants
     */
    public static function calculateDaysRemaining(string $expiryDate): int
    {
        $expiry = new \DateTime($expiryDate);
        $current = new \DateTime();
        $interval = $current->diff($expiry);
        return $interval->invert ? 0 : $interval->days;
    }
    
    /**
     * Vérifie si une licence est proche de l'expiration
     * 
     * @param string $expiryDate Date d'expiration
     * @param int $warningDays Nombre de jours d'avertissement (défaut: 30)
     * @return bool True si la licence expire bientôt
     */
    public static function isExpiringSoon(string $expiryDate, int $warningDays = 30): bool
    {
        $daysRemaining = self::calculateDaysRemaining($expiryDate);
        return $daysRemaining <= $warningDays && $daysRemaining > 0;
    }
    
    /**
     * Génère un segment aléatoire
     * 
     * @return string Segment généré
     */
    private static function generateSegment(): string
    {
        $segment = '';
        $alphabetLength = strlen(self::ALPHABET);
        
        for ($i = 0; $i < self::SEGMENT_LENGTH; $i++) {
            $randomIndex = random_int(0, $alphabetLength - 1);
            $segment .= self::ALPHABET[$randomIndex];
        }
        
        return $segment;
    }
    
    /**
     * Génère un segment basé sur une chaîne de caractères
     * 
     * @param string $input Chaîne d'entrée
     * @return string Segment généré
     */
    private static function generateSegmentFromString(string $input): string
    {
        $hash = self::hashString($input);
        $segment = '';
        $alphabetLength = strlen(self::ALPHABET);
        
        for ($i = 0; $i < self::SEGMENT_LENGTH; $i++) {
            $charCode = ord($hash[$i % strlen($hash)]);
            $index = $charCode % $alphabetLength;
            $segment .= self::ALPHABET[$index];
        }
        
        return $segment;
    }
    
    /**
     * Fonction de hachage simple
     * 
     * @param string $str Chaîne à hasher
     * @return string Hash généré
     */
    private static function hashString(string $str): string
    {
        $hash = 0;
        $strLength = strlen($str);
        
        for ($i = 0; $i < $strLength; $i++) {
            $charCode = ord($str[$i]);
            $hash = (($hash << 5) - $hash) + $charCode;
            // Conversion en entier 32 bits avec gestion des valeurs négatives
            $hash = (int)($hash & 0xFFFFFFFF);
        }
        
        return strtoupper(dechex(abs($hash)));
    }
    
    /**
     * Valide le format d'une date
     * 
     * @param string $date Date à valider
     * @return bool True si la date est valide
     */
    private static function isValidDate(string $date): bool
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
    
    /**
     * Génère un identifiant client unique
     * 
     * @param string $schoolName Nom de l'école
     * @param string $schoolCode Code de l'école
     * @return string Identifiant client unique
     */
    public static function generateClientId(string $schoolName, string $schoolCode): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', $schoolCode));
        $timestamp = time();
        $hash = substr(self::hashString($schoolName . $timestamp), 0, 6);
        
        return $base . $hash;
    }
    
    /**
     * Obtient les informations de licence à partir de la base de données
     * 
     * @param string $licenseKey Clé de licence
     * @return array|null Informations de licence ou null si non trouvée
     */
    public static function getLicenseInfo(string $licenseKey): ?array
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('licenses')
                   ->where('license_key', $licenseKey)
                   ->where('is_active', true)
                   ->get();
        
        if ($query->getNumRows() > 0) {
            $license = $query->getRowArray();
            
            return [
                'id' => $license['id'],
                'license_key' => $license['license_key'],
                'client_id' => $license['client_id'],
                'license_type' => $license['license_type'],
                'start_date' => $license['start_date'],
                'expiry_date' => $license['expiry_date'],
                'max_users' => $license['max_users'],
                'features' => json_decode($license['features'], true),
                'is_active' => $license['is_active'],
                'days_remaining' => self::calculateDaysRemaining($license['expiry_date']),
                'is_expiring_soon' => self::isExpiringSoon($license['expiry_date'])
            ];
        }
        
        return null;
    }
    
    /**
     * Enregistre une nouvelle licence dans la base de données
     * 
     * @param array $licenseData Données de la licence
     * @return bool True si l'enregistrement a réussi
     */
    public static function saveLicense(array $licenseData): bool
    {
        $db = \Config\Database::connect();
        
        $data = [
            'license_key' => $licenseData['license_key'],
            'client_id' => $licenseData['client_id'],
            'license_type' => $licenseData['license_type'],
            'start_date' => $licenseData['start_date'],
            'expiry_date' => $licenseData['expiry_date'],
            'max_users' => $licenseData['max_users'] ?? 100,
            'features' => json_encode($licenseData['features'] ?? []),
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $db->table('licenses')->insert($data);
    }
    
    /**
     * Désactive une licence
     * 
     * @param string $licenseKey Clé de licence
     * @return bool True si la désactivation a réussi
     */
    public static function deactivateLicense(string $licenseKey): bool
    {
        $db = \Config\Database::connect();
        
        return $db->table('licenses')
                 ->where('license_key', $licenseKey)
                 ->update(['is_active' => false]);
    }
    
    /**
     * Renouvelle une licence
     * 
     * @param string $licenseKey Clé de licence actuelle
     * @param int $years Nombre d'années à ajouter
     * @return string Nouvelle clé de licence
     */
    public static function renewLicense(string $licenseKey, int $years = 1): string
    {
        $licenseInfo = self::getLicenseInfo($licenseKey);
        
        if (!$licenseInfo) {
            throw new \Exception('Licence non trouvée');
        }
        
        // Calculer la nouvelle date d'expiration
        $currentExpiry = new \DateTime($licenseInfo['expiry_date']);
        $newExpiry = $currentExpiry->add(new \DateInterval("P{$years}Y"));
        
        // Générer une nouvelle clé
        $newLicenseKey = self::generateLicenseKey(
            $licenseInfo['client_id'],
            $licenseInfo['license_type'],
            $newExpiry->format('Y-m-d')
        );
        
        // Désactiver l'ancienne licence
        self::deactivateLicense($licenseKey);
        
        // Enregistrer la nouvelle licence
        $newLicenseData = [
            'license_key' => $newLicenseKey,
            'client_id' => $licenseInfo['client_id'],
            'license_type' => $licenseInfo['license_type'],
            'start_date' => date('Y-m-d'),
            'expiry_date' => $newExpiry->format('Y-m-d'),
            'max_users' => $licenseInfo['max_users'],
            'features' => $licenseInfo['features']
        ];
        
        self::saveLicense($newLicenseData);
        
        return $newLicenseKey;
    }
}








