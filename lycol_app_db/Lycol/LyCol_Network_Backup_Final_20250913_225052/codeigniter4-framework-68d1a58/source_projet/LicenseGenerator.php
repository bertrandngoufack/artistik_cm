<?php

declare(strict_types=1);

namespace App\Licensing;

final class LicenseGenerator
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const SEGMENTS = 4;
    private const SEGMENT_LENGTH = 4;

    public static function hashString(string $str): string
    {
        $hash = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $char = ord($str[$i]);
            $hash = (($hash << 5) - $hash) + $char;
            $hash = $hash & $hash; // int overflow behavior kept intentionally
        }
        return strtoupper(dechex(abs($hash)));
    }

    private static function generateSegment(): string
    {
        $segment = '';
        for ($i = 0; $i < self::SEGMENT_LENGTH; $i++) {
            $randomIndex = random_int(0, strlen(self::ALPHABET) - 1);
            $segment .= self::ALPHABET[$randomIndex];
        }
        return $segment;
    }

    public static function generateLicenseKey(string $clientId, string $licenseType, string $expiryDate, string $secretSeed): string
    {
        if ($clientId === '' || $expiryDate === '' || $secretSeed === '') {
            throw new \InvalidArgumentException('Paramètres manquants: clientId, expiryDate et secretSeed sont requis');
        }

        $signatureData = $clientId . $licenseType . $expiryDate . $secretSeed;
        $signatureHash = self::hashString($signatureData);

        $segments = [];
        for ($i = 0; $i < self::SEGMENTS - 1; $i++) {
            $segments[] = self::generateSegment();
        }

        $yearSegment = substr($expiryDate, 0, 4);
        $segments[] = $yearSegment;

        return implode('-', $segments);
    }

    public static function validateLicenseKey(string $licenseKey, string $clientId, string $licenseType, string $expiryDate, string $secretSeed): array
    {
        try {
            $segments = explode('-', $licenseKey);
            if (count($segments) !== self::SEGMENTS) {
                return ['valid' => false, 'reason' => 'Format invalide'];
            }

            $yearSegment = $segments[3];
            if ($yearSegment !== substr($expiryDate, 0, 4)) {
                return ['valid' => false, 'reason' => "Année d'expiration ne correspond pas"];
            }

            $signatureData = $clientId . $licenseType . $expiryDate . $secretSeed;
            $expectedSignature = self::hashString($signatureData);

            return [
                'valid' => true,
                'details' => [
                    'clientId' => $clientId,
                    'type' => $licenseType,
                    'expiry' => $expiryDate,
                    'expectedSignature' => substr($expectedSignature, 0, 8),
                    'generated' => (new \DateTimeImmutable())->format(DATE_ATOM),
                ],
            ];
        } catch (\Throwable $e) {
            return ['valid' => false, 'reason' => 'Erreur de validation: ' . $e->getMessage()];
        }
    }

    public static function decodeLicenseInfo(string $licenseKey): array
    {
        $segments = explode('-', $licenseKey);
        if (count($segments) !== self::SEGMENTS) {
            throw new \InvalidArgumentException('Format de licence invalide');
        }

        return [
            'segments' => $segments,
            'expiryYear' => $segments[3],
            'format' => 'VALID',
            'checksum' => substr(self::hashString($licenseKey), 0, 8),
        ];
    }
}