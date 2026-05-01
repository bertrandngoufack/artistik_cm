<?php
/**
 * Test complet de tous les modules avec les données créées
 */

echo "🧪 TEST COMPLET DE TOUS LES MODULES AVEC DONNÉES\n";
echo "===============================================\n\n";

$baseUrl = 'http://localhost:8080';

// Fonction pour tester une URL
function testUrl($url, $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'KISSAI-SCHOOL-TEST/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? "✅" : "❌";
    echo "$status $description : $httpCode";
    
    if ($error) {
        echo " (Erreur: $error)";
    }
    
    if ($httpCode == 200) {
        // Vérifier le contenu
        if (strpos($response, 'KISSAI SCHOOL') !== false) {
            echo " - Titre OK";
        }
        
        if (strpos($response, 'Module') !== false) {
            echo " - Module OK";
        }
        
        $size = strlen($response);
        echo " - Taille: " . number_format($size) . " octets";
    }
    
    echo "\n";
    
    return $httpCode == 200;
}

// Test de connexion au serveur
echo "🔗 Test de connexion au serveur...\n";
echo "----------------------------------\n";
$serverTest = testUrl($baseUrl, "Serveur principal");
echo "\n";

if (!$serverTest) {
    echo "❌ Impossible de se connecter au serveur.\n";
    exit(1);
}

// Test des pages publiques
echo "🌐 Test des pages publiques...\n";
echo "-----------------------------\n";
$publicPages = [
    '/' => 'Page d\'accueil',
    '/auth/login' => 'Page de connexion',
    '/about' => 'Page À propos',
    '/contact' => 'Page Contact'
];

$publicSuccess = 0;
foreach ($publicPages as $path => $description) {
    if (testUrl($baseUrl . $path, $description)) {
        $publicSuccess++;
    }
}
echo "📊 Pages publiques : $publicSuccess/" . count($publicPages) . " fonctionnelles\n\n";

// Test des modules admin
echo "🔐 Test des modules admin...\n";
echo "---------------------------\n";
$adminModules = [
    '/admin/economat' => 'Module Économat',
    '/admin/scolarite' => 'Module Scolarité',
    '/admin/etudes' => 'Module Études',
    '/admin/examens' => 'Module Examens',
    '/admin/statistiques' => 'Module Statistiques',
    '/admin/bibliotheque' => 'Module Bibliothèque',
    '/admin/messagerie' => 'Module Messagerie',
    '/admin/securite' => 'Module Sécurité',
    '/admin/enseignants' => 'Module Enseignants',
    '/admin/configuration' => 'Module Configuration'
];

$adminSuccess = 0;
foreach ($adminModules as $path => $description) {
    if (testUrl($baseUrl . $path, $description)) {
        $adminSuccess++;
    }
}
echo "📊 Modules admin : $adminSuccess/" . count($adminModules) . " fonctionnels\n\n";

// Test de l'authentification
echo "🔐 Test d'authentification...\n";
echo "----------------------------\n";

function testAuth() {
    global $baseUrl;
    
    $postData = [
        'username' => 'admin',
        'password' => 'admin123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/authenticate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 302 || $httpCode == 303) ? "✅" : "❌";
    echo "$status Authentification admin : $httpCode";
    
    if ($httpCode == 302 || $httpCode == 303) {
        echo " - Redirection OK";
    }
    
    echo "\n";
    
    return $httpCode == 302 || $httpCode == 303;
}

$authSuccess = testAuth();
echo "\n";

// Test des données en base
echo "📊 Test des données en base...\n";
echo "-----------------------------\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Compter les données
    $counts = [
        'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'teachers' => $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
        'classes' => $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
        'subjects' => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
        'exams' => $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn(),
        'grades' => $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn(),
        'payments' => $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn(),
        'absences' => $pdo->query("SELECT COUNT(*) FROM absences")->fetchColumn()
    ];
    
    foreach ($counts as $table => $count) {
        $status = ($count > 0) ? "✅" : "❌";
        echo "$status $table : $count enregistrements\n";
    }
    
    // Calculer les statistiques
    $totalRevenue = $pdo->query("SELECT SUM(amount_paid) FROM payments")->fetchColumn();
    $avgGrade = $pdo->query("SELECT AVG(marks_obtained) FROM grades")->fetchColumn();
    
    echo "\n📈 Statistiques :\n";
    echo "💰 Total recettes : " . number_format($totalRevenue ?? 0) . " FCFA\n";
    echo "📊 Moyenne générale : " . number_format($avgGrade ?? 0, 2) . "/20\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
}

echo "\n";

// Résumé final
echo "📊 RÉSUMÉ FINAL\n";
echo "===============\n";
echo "🌐 Pages publiques : $publicSuccess/" . count($publicPages) . " ✅\n";
echo "🔐 Modules admin : $adminSuccess/" . count($adminModules) . " ✅\n";
echo "🔐 Authentification : " . ($authSuccess ? "✅" : "❌") . "\n";

$totalTests = count($publicPages) + count($adminModules) + 1;
$totalSuccess = $publicSuccess + $adminSuccess + ($authSuccess ? 1 : 0);

echo "\n🎯 TAUX DE RÉUSSITE GLOBAL : " . round(($totalSuccess / $totalTests) * 100, 1) . "%\n";

if ($totalSuccess == $totalTests) {
    echo "\n🎉 TOUS LES TESTS SONT PASSÉS !\n";
    echo "🚀 L'application KISSAI SCHOOL fonctionne parfaitement avec les données d'exemple !\n";
} else {
    echo "\n⚠️ Certains tests ont échoué.\n";
}

echo "\n📋 DONNÉES CRÉÉES :\n";
echo "==================\n";
echo "👥 Élèves : {$counts['students']}\n";
echo "👨‍🏫 Enseignants : {$counts['teachers']}\n";
echo "🏫 Classes : {$counts['classes']}\n";
echo "📝 Matières : {$counts['subjects']}\n";
echo "📊 Examens : {$counts['exams']}\n";
echo "📈 Notes : {$counts['grades']}\n";
echo "💰 Paiements : {$counts['payments']}\n";
echo "📅 Absences : {$counts['absences']}\n";

echo "\n🎓 L'application est prête pour la gestion scolaire !\n";
?>


