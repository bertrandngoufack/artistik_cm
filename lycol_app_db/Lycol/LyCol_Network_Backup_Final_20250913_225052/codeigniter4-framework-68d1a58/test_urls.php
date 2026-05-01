<?php

/**
 * Script de test pour vérifier toutes les URLs de l'application LyCol
 * Usage: php test_urls.php
 */

echo "=== TEST DES URLs LYCOL ===\n\n";

$baseUrl = 'http://localhost:8080';
$urls = [
    // Pages publiques
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/auth/parents' => 'Espace parents',
    '/auth/mobile' => 'Interface mobile',
    
    // Pages d'administration (redirigeront vers login si non connecté)
    '/admin/dashboard' => 'Dashboard administration',
    '/admin/economat' => 'Module Économat',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/messagerie' => 'Module Messagerie',
    '/admin/securite' => 'Module Sécurité',
    '/admin/configuration' => 'Configuration',
    
    // API
    '/api/docs' => 'Documentation API',
    
    // Pages publiques
    '/about' => 'À propos',
    '/contact' => 'Contact',
    '/help' => 'Aide',
    '/privacy' => 'Confidentialité',
    '/terms' => 'Conditions d\'utilisation'
];

$results = [];

foreach ($urls as $url => $description) {
    $fullUrl = $baseUrl . $url;
    echo "Test: {$description} ({$fullUrl})... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "ERREUR: {$error}\n";
        $results[$url] = ['status' => 'ERROR', 'code' => 0, 'message' => $error];
    } else {
        $status = match($httpCode) {
            200 => 'OK',
            302, 301 => 'REDIRECT',
            404 => 'NOT_FOUND',
            500 => 'SERVER_ERROR',
            default => 'UNKNOWN'
        };
        
        echo "{$status} ({$httpCode})\n";
        $results[$url] = ['status' => $status, 'code' => $httpCode, 'message' => ''];
    }
}

echo "\n=== RÉSUMÉ DES TESTS ===\n";
$summary = [
    'OK' => 0,
    'REDIRECT' => 0,
    'NOT_FOUND' => 0,
    'SERVER_ERROR' => 0,
    'ERROR' => 0,
    'UNKNOWN' => 0
];

foreach ($results as $url => $result) {
    $summary[$result['status']]++;
}

foreach ($summary as $status => $count) {
    if ($count > 0) {
        echo "{$status}: {$count} URLs\n";
    }
}

echo "\n=== DÉTAILS ===\n";
foreach ($results as $url => $result) {
    $status = $result['status'];
    $code = $result['code'];
    echo "{$url}: {$status} ({$code})\n";
}

echo "\n=== RECOMMANDATIONS ===\n";
if ($summary['ERROR'] > 0) {
    echo "- Vérifiez que le serveur CodeIgniter est démarré sur le port 8081\n";
}
if ($summary['SERVER_ERROR'] > 0) {
    echo "- Il y a des erreurs 500, vérifiez les logs d'erreur\n";
}
if ($summary['NOT_FOUND'] > 0) {
    echo "- Certaines routes ne sont pas trouvées, vérifiez la configuration des routes\n";
}
if ($summary['REDIRECT'] > 0) {
    echo "- {$summary['REDIRECT']} URLs redirigent (normal pour les pages protégées)\n";
}

echo "\nTest terminé !\n";
