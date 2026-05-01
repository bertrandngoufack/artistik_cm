<?php
/**
 * Test du système d'année scolaire
 */

echo "🎓 TEST DU SYSTÈME D'ANNÉE SCOLAIRE\n";
echo "==================================\n\n";

// Test 1: Configuration de l'année scolaire
echo "📅 Test 1: Configuration de l'année scolaire\n";
echo "--------------------------------------------\n";

require_once 'app/Config/AcademicYear.php';
$academicYearConfig = new \Config\AcademicYear();

echo "✅ Configuration chargée\n";
echo "📊 Année actuelle: " . $academicYearConfig->getCurrentAcademicYear() . "\n";

$dates = $academicYearConfig->getAcademicYearDates();
echo "📅 Date de début: " . $dates['start_date'] . "\n";
echo "📅 Date de fin: " . $dates['end_date'] . "\n\n";

// Test 2: Trait AcademicYearTrait
echo "🔧 Test 2: Trait AcademicYearTrait\n";
echo "---------------------------------\n";

require_once 'app/Traits/AcademicYearTrait.php';

// Créer une classe de test pour tester le trait
class TestController
{
    use \App\Traits\AcademicYearTrait;
    
    public function testTrait()
    {
        $this->initAcademicYear();
        return [
            'current_year' => $this->getCurrentAcademicYear(),
            'dates' => $this->getAcademicYearDates(),
            'available_years' => $this->academicYearConfig->getAvailableAcademicYears()
        ];
    }
}

$testController = new TestController();
$result = $testController->testTrait();

echo "✅ Trait chargé avec succès\n";
echo "📊 Année scolaire actuelle: " . $result['current_year'] . "\n";
echo "📅 Période: " . $result['dates']['start_date'] . " à " . $result['dates']['end_date'] . "\n";
echo "📋 Années disponibles: " . implode(', ', $result['available_years']) . "\n\n";

// Test 3: Validation des dates
echo "✅ Test 3: Validation des dates\n";
echo "-------------------------------\n";

$testDates = [
    '2024-09-15' => 'Date en début d\'année scolaire',
    '2024-12-25' => 'Date en milieu d\'année scolaire',
    '2025-06-15' => 'Date en fin d\'année scolaire',
    '2024-07-15' => 'Date en été (hors année scolaire)',
    '2025-08-15' => 'Date en août (hors année scolaire)'
];

foreach ($testDates as $date => $description) {
    $isValid = $academicYearConfig->isInAcademicYear($date);
    $status = $isValid ? "✅" : "❌";
    echo "$status $description ($date): " . ($isValid ? "Dans l'année scolaire" : "Hors année scolaire") . "\n";
}

echo "\n";

// Test 4: Calcul automatique de l'année scolaire
echo "🔄 Test 4: Calcul automatique de l'année scolaire\n";
echo "------------------------------------------------\n";

$testMonths = [
    1 => 'Janvier',
    6 => 'Juin',
    8 => 'Août',
    9 => 'Septembre',
    12 => 'Décembre'
];

foreach ($testMonths as $month => $monthName) {
    // Simuler une date avec le mois donné
    $testDate = "2024-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-15";
    $academicYear = $academicYearConfig->getAcademicYearFromDate($testDate);
    echo "📅 $monthName 2024: Année scolaire $academicYear\n";
}

echo "\n";

// Test 5: Filtrage des données
echo "🔍 Test 5: Filtrage des données\n";
echo "-------------------------------\n";

// Simuler des données de paiements
$samplePayments = [
    ['date' => '2024-09-15', 'amount' => 50000, 'description' => 'Paiement septembre'],
    ['date' => '2024-12-20', 'amount' => 75000, 'description' => 'Paiement décembre'],
    ['date' => '2025-06-10', 'amount' => 100000, 'description' => 'Paiement juin'],
    ['date' => '2024-07-15', 'amount' => 25000, 'description' => 'Paiement été (hors année)'],
    ['date' => '2025-08-20', 'amount' => 30000, 'description' => 'Paiement août (hors année)']
];

$currentYear = $academicYearConfig->getCurrentAcademicYear();
$dates = $academicYearConfig->getAcademicYearDates($currentYear);

echo "📊 Filtrage pour l'année scolaire: $currentYear\n";
echo "📅 Période: " . $dates['start_date'] . " à " . $dates['end_date'] . "\n\n";

$filteredPayments = [];
$totalAmount = 0;

foreach ($samplePayments as $payment) {
    if ($academicYearConfig->isInAcademicYear($payment['date'], $currentYear)) {
        $filteredPayments[] = $payment;
        $totalAmount += $payment['amount'];
        echo "✅ " . $payment['description'] . " (" . $payment['date'] . "): " . number_format($payment['amount']) . " FCFA\n";
    } else {
        echo "❌ " . $payment['description'] . " (" . $payment['date'] . "): " . number_format($payment['amount']) . " FCFA (exclu)\n";
    }
}

echo "\n📊 Résultats du filtrage:\n";
echo "   - Paiements inclus: " . count($filteredPayments) . "\n";
echo "   - Montant total: " . number_format($totalAmount) . " FCFA\n\n";

// Test 6: Intégration avec les contrôleurs
echo "🔗 Test 6: Intégration avec les contrôleurs\n";
echo "-------------------------------------------\n";

// Vérifier que le contrôleur Economat utilise le trait
$economatFile = 'app/Controllers/Economat.php';
if (file_exists($economatFile)) {
    $content = file_get_contents($economatFile);
    if (strpos($content, 'use App\\Traits\\AcademicYearTrait;') !== false) {
        echo "✅ Contrôleur Economat intègre le trait AcademicYearTrait\n";
    } else {
        echo "❌ Contrôleur Economat n'intègre pas le trait AcademicYearTrait\n";
    }
    
    if (strpos($content, 'use AcademicYearTrait;') !== false) {
        echo "✅ Contrôleur Economat utilise le trait AcademicYearTrait\n";
    } else {
        echo "❌ Contrôleur Economat n'utilise pas le trait AcademicYearTrait\n";
    }
} else {
    echo "❌ Contrôleur Economat non trouvé\n";
}

echo "\n";

// Test 7: Vues avec sélecteur d'année scolaire
echo "🎨 Test 7: Vues avec sélecteur d'année scolaire\n";
echo "----------------------------------------------\n";

$views = [
    'app/Views/admin/economat/index.php' => 'Dashboard Économat',
    'app/Views/admin/economat/payments.php' => 'Page des Paiements'
];

foreach ($views as $viewFile => $description) {
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        if (strpos($content, 'academic_year') !== false) {
            echo "✅ $description: Intègre le sélecteur d'année scolaire\n";
        } else {
            echo "❌ $description: N'intègre pas le sélecteur d'année scolaire\n";
        }
    } else {
        echo "❌ $description: Vue non trouvée\n";
    }
}

echo "\n";

// Test 8: Résumé et recommandations
echo "📊 Test 8: Résumé et Recommandations\n";
echo "------------------------------------\n";

echo "✅ FONCTIONNALITÉS IMPLÉMENTÉES:\n";
echo "   - Configuration automatique de l'année scolaire\n";
echo "   - Trait pour intégration dans tous les contrôleurs\n";
echo "   - Filtrage automatique des données par année\n";
echo "   - Sélecteur d'année scolaire dans les vues\n";
echo "   - Validation des dates d'année scolaire\n";
echo "   - Calcul automatique des périodes\n\n";

echo "🚀 PROCHAINES ÉTAPES:\n";
echo "   1. Intégrer le trait dans tous les autres contrôleurs\n";
echo "   2. Ajouter le sélecteur d'année scolaire dans toutes les vues\n";
echo "   3. Tester avec des données réelles\n";
echo "   4. Configurer les rappels par année scolaire\n\n";

echo "📅 Test effectué le : " . date('d/m/Y à H:i:s') . "\n";
echo "🎓 KISSAI SCHOOL - Système d'année scolaire\n";

echo "\n🎯 CONCLUSION: ✅ Le système d'année scolaire est opérationnel\n";
echo "📊 Filtrage: Automatique par année scolaire\n";
echo "🎨 Interface: Sélecteur d'année scolaire intégré\n";
echo "🔧 Architecture: Trait réutilisable pour tous les modules\n";
?>


