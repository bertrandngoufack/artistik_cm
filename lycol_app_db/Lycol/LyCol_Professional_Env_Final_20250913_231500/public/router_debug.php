<?php
/**
 * Router personnalisé avec debug pour le serveur PHP intégré
 */

// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log pour debug
$logFile = __DIR__ . '/router_debug.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Début de requête\n", FILE_APPEND);

// Définir le chemin vers les assets statiques
$staticPath = __DIR__ . '/assets';

// Vérifier si la requête est pour un asset statique
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

file_put_contents($logFile, date('Y-m-d H:i:s') . " - URI: $requestUri, Path: $path\n", FILE_APPEND);

// Extensions d'assets statiques
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'pdf'];

// Vérifier si c'est un asset statique
$isStatic = false;
foreach ($staticExtensions as $ext) {
    if (strpos($path, '.' . $ext) !== false) {
        $isStatic = true;
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Asset statique détecté: $ext\n", FILE_APPEND);
        break;
    }
}

if ($isStatic) {
    // Construire le chemin complet vers l'asset
    $assetPath = $staticPath . $path;
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Chemin asset: $assetPath\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Fichier existe: " . (file_exists($assetPath) ? 'OUI' : 'NON') . "\n", FILE_APPEND);
    
    // Vérifier si le fichier existe
    if (file_exists($assetPath) && is_file($assetPath)) {
        // Déterminer le type MIME
        $extension = pathinfo($assetPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'pdf' => 'application/pdf'
        ];
        
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Type MIME: $contentType\n", FILE_APPEND);
        
        // Envoyer les headers
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        
        // Servir le fichier
        readfile($assetPath);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Fichier servi avec succès\n", FILE_APPEND);
        exit;
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Fichier non trouvé\n", FILE_APPEND);
    }
}

file_put_contents($logFile, date('Y-m-d H:i:s') . " - Passage à CodeIgniter\n", FILE_APPEND);

// Si ce n'est pas un asset statique ou s'il n'existe pas, passer à CodeIgniter
require_once __DIR__ . '/index.php';
?>


