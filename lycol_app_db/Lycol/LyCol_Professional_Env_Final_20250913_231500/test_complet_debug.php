<?php
/**
 * Script de test complet pour vérifier toutes les URLs et fonctionnalités
 * de l'application KISSAI SCHOOL
 */

echo "=== TEST COMPLET DEBUG KISSAI SCHOOL ===\n\n";

$baseUrl = 'http://localhost:8080';
$urls = [
    // Pages publiques
    '/' => 'Page d\'accueil',
    '/about' => 'Page À propos',
    '/contact' => 'Page Contact',
    '/help' => 'Page Aide',
    '/privacy' => 'Page Confidentialité',
    '/terms' => 'Page Conditions',
    
    // Authentification
    '/auth/login' => 'Page de connexion',
    '/auth/parents' => 'Espace parents',
    '/auth/mobile' => 'Interface mobile',
    
    // Administration
    '/admin/dashboard' => 'Tableau de bord admin',
    '/admin/economat' => 'Module Économat',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/messagerie' => 'Module Messagerie',
    '/admin/securite' => 'Module Sécurité',
    '/admin/configuration' => 'Module Configuration',
    '/admin/licenses' => 'Gestion des licences',
    
    // Espace parents
    '/parents/dashboard' => 'Dashboard parents',
    '/parents/grades' => 'Notes parents',
    '/parents/absences' => 'Absences parents',
    '/parents/payments' => 'Paiements parents',
    '/parents/discipline' => 'Discipline parents',
    '/parents/profile' => 'Profil parents',
    
    // Interface mobile
    '/mobile/grades' => 'Notes mobile',
    '/mobile/absences' => 'Absences mobile',
    '/mobile/profile' => 'Profil mobile',
    '/mobile/enter-grades' => 'Saisie notes mobile',
    '/mobile/create-absence' => 'Création absence mobile',
    
    // API
    '/api/docs' => 'Documentation API',
    '/api/docs/show' => 'Documentation API détaillée',
    '/api/students' => 'API Étudiants',
    '/api/grades' => 'API Notes',
    '/api/absences' => 'API Absences',
    '/api/discipline' => 'API Discipline',
    '/api/export/students' => 'Export CSV Étudiants',
    '/api/export/grades' => 'Export CSV Notes',
    '/api/export/absences' => 'Export CSV Absences',
    
    // Tests
    '/test/license' => 'Test Licences',
    '/test/database' => 'Test Base de données',
    '/test/email' => 'Test Email',
    
    // Pages d'erreur
    '/erreur-404' => 'Page 404',
    '/erreur-403' => 'Page 403',
    '/erreur-500' => 'Page 500',
];

echo "🔍 Test de connectivité au serveur...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 400) {
    echo "✅ Serveur accessible sur $baseUrl (Code: $httpCode)\n\n";
} else {
    echo "❌ Serveur non accessible (Code: $httpCode)\n\n";
    exit(1);
}

echo "🔍 Test des URLs principales...\n";
$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($urls as $url => $description) {
    $fullUrl = $baseUrl . $url;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = '';
    if ($error) {
        $status = "❌ ERREUR: $error";
        $errorCount++;
    } elseif ($httpCode >= 200 && $httpCode < 400) {
        $status = "✅ OK ($httpCode)";
        $successCount++;
    } elseif ($httpCode == 404) {
        $status = "⚠️  NOT_FOUND ($httpCode)";
        $errorCount++;
    } elseif ($httpCode == 500) {
        $status = "💥 SERVER_ERROR ($httpCode)";
        $errorCount++;
    } else {
        $status = "❓ CODE $httpCode";
        $errorCount++;
    }
    
    $results[] = [
        'url' => $url,
        'description' => $description,
        'status' => $status,
        'code' => $httpCode
    ];
    
    echo sprintf("%-50s %s\n", $description, $status);
}

echo "\n📊 RÉSUMÉ DES TESTS:\n";
echo "✅ URLs fonctionnelles: $successCount\n";
echo "❌ URLs en erreur: $errorCount\n";
echo "📈 Taux de réussite: " . round(($successCount / count($urls)) * 100, 1) . "%\n\n";

echo "🔍 Test du contenu de la page d'accueil...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content = curl_exec($ch);
curl_close($ch);

if ($content !== false) {
    if (strpos($content, 'KISSAI SCHOOL') !== false) {
        echo "✅ Nom de l'application correctement affiché\n";
    } else {
        echo "⚠️  Nom de l'application non trouvé dans le contenu\n";
    }
    
    if (strpos($content, 'bulma') !== false) {
        echo "✅ Framework Bulma détecté\n";
    } else {
        echo "⚠️  Framework Bulma non détecté\n";
    }
    
    if (strpos($content, 'font-awesome') !== false) {
        echo "✅ Font Awesome détecté\n";
    } else {
        echo "⚠️  Font Awesome non détecté\n";
    }
} else {
    echo "❌ Impossible de récupérer le contenu de la page d'accueil\n";
}

echo "\n🔍 Test de la page de connexion...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content = curl_exec($ch);
curl_close($ch);

if ($content !== false) {
    if (strpos($content, 'KISSAI SCHOOL') !== false) {
        echo "✅ Page de connexion avec le bon nom\n";
    } else {
        echo "⚠️  Nom incorrect dans la page de connexion\n";
    }
    
    if (strpos($content, 'username') !== false && strpos($content, 'password') !== false) {
        echo "✅ Formulaire de connexion présent\n";
    } else {
        echo "⚠️  Formulaire de connexion incomplet\n";
    }
} else {
    echo "❌ Impossible d'accéder à la page de connexion\n";
}

echo "\n🔍 Test des assets CSS et JS...\n";
$assets = [
    '/assets/bulma/css/bulma.min.css' => 'CSS Bulma',
    '/assets/bulma/js/bulma.js' => 'JS Bulma'
];

foreach ($assets as $asset => $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $asset);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // curl_setopt($ch, CURLOPT_NOBODY, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ $description accessible\n";
    } else {
        echo "❌ $description non accessible (Code: $httpCode)\n";
    }
}

echo "\n🎯 TESTS SPÉCIAUX:\n";

// Test de la base de données
echo "🔍 Test de la base de données...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/test/database');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page de test de base de données accessible\n";
} else {
    echo "⚠️  Page de test de base de données non accessible (Code: $httpCode)\n";
}

// Test des licences
echo "🔍 Test du système de licences...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/test/license');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page de test des licences accessible\n";
} else {
    echo "⚠️  Page de test des licences non accessible (Code: $httpCode)\n";
}

echo "\n🎉 TOUR DE DEBUG TERMINÉ !\n";
echo "📋 Application: KISSAI SCHOOL\n";
echo "🌐 URL: $baseUrl\n";
echo "🔧 Port: 8080\n";
echo "📊 Résultats: $successCount/$errorCount URLs testées\n\n";

if ($errorCount > 0) {
    echo "⚠️  RECOMMANDATIONS:\n";
    echo "- Vérifier les routes dans app/Config/Routes.php\n";
    echo "- Vérifier les contrôleurs manquants\n";
    echo "- Vérifier les vues manquantes\n";
    echo "- Vérifier les permissions des fichiers\n";
    echo "- Consulter les logs d'erreur\n";
} else {
    echo "🎊 FÉLICITATIONS ! L'application KISSAI SCHOOL fonctionne parfaitement !\n";
}
