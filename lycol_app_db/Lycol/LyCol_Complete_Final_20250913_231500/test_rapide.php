<?php
/**
 * Test rapide des URLs principales de KISSAI SCHOOL
 */

echo "=== TEST RAPIDE KISSAI SCHOOL ===\n\n";

$baseUrl = 'http://localhost:8080';
$urls = [
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/about' => 'Page À propos',
    '/contact' => 'Page Contact',
    '/parents/dashboard' => 'Dashboard parents',
    '/mobile/grades' => 'Notes mobile',
    '/api/docs' => 'Documentation API'
];

echo "🔍 Test des URLs principales...\n";
$successCount = 0;

foreach ($urls as $url => $description) {
    $fullUrl = $baseUrl . $url;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ $description\n";
        $successCount++;
    } else {
        echo "❌ $description (Code: $httpCode)\n";
    }
}

echo "\n📊 RÉSULTATS:\n";
echo "✅ URLs fonctionnelles: $successCount/" . count($urls) . "\n";
echo "📈 Taux de réussite: " . round(($successCount / count($urls)) * 100, 1) . "%\n\n";

if ($successCount == count($urls)) {
    echo "🎊 EXCELLENT ! KISSAI SCHOOL fonctionne parfaitement !\n";
    echo "🌐 Accédez à l'application: $baseUrl\n";
} else {
    echo "⚠️  Certaines URLs nécessitent des corrections.\n";
}

echo "\n🔗 LIENS D'ACCÈS:\n";
echo "- Accueil: $baseUrl/\n";
echo "- Connexion: $baseUrl/auth/login\n";
echo "- À propos: $baseUrl/about\n";
echo "- Parents: $baseUrl/parents/dashboard\n";
echo "- Mobile: $baseUrl/mobile/grades\n";
echo "- API: $baseUrl/api/docs\n";




