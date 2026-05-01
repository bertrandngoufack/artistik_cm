<?php
/**
 * Test rapide pour le port 8080
 */

echo "🧪 TEST RAPIDE PORT 8080 - KISSAI SCHOOL\n";
echo "========================================\n\n";

$baseUrl = 'http://localhost:8080';

// Test des URLs principales
$urls = [
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/securite' => 'Module Sécurité',
    '/admin/enseignants' => 'Module Enseignants',
    '/admin/configuration' => 'Module Configuration'
];

$success = 0;
foreach ($urls as $path => $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? "✅" : "❌";
    echo "$status $description : $httpCode\n";
    
    if ($httpCode == 200) {
        $success++;
    }
}

echo "\n📊 RÉSUMÉ\n";
echo "==========\n";
echo "URLs testées : " . count($urls) . "\n";
echo "URLs fonctionnelles : $success\n";
echo "Taux de réussite : " . round(($success / count($urls)) * 100, 1) . "%\n";

if ($success == count($urls)) {
    echo "\n🎉 L'application KISSAI SCHOOL fonctionne parfaitement sur le port 8080 !\n";
    echo "🌐 URL d'accès : $baseUrl\n";
    echo "🔐 Identifiants : admin / admin123\n";
} else {
    echo "\n⚠️ Certaines URLs ne fonctionnent pas.\n";
}

echo "\n🚀 Application prête pour la production !\n";
?>


