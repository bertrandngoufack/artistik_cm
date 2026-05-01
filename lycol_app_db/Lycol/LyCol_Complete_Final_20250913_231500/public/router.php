<?php
/**
 * Router personnalisé pour le serveur de développement PHP
 * Gestion correcte des routes CodeIgniter 4
 */

// Définir le chemin du document root
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

// Vérifier si le fichier demandé existe physiquement
$requestUri = $_SERVER['REQUEST_URI'];
$filePath = $documentRoot . $requestUri;

// Si c'est un fichier physique qui existe, le servir directement
if (is_file($filePath)) {
    return false;
}

// Vérifier les assets statiques
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
$extension = pathinfo($requestUri, PATHINFO_EXTENSION);
if (in_array($extension, $staticExtensions)) {
    $filePath = $documentRoot . $requestUri;
    if (is_file($filePath)) {
        return false; // Servir le fichier statique directement
    }
}

// Pour toutes les autres requêtes, passer à CodeIgniter
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $documentRoot . '/index.php';
?>
