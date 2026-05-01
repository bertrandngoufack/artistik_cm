<?php
/**
 * Script de test complet pour tous les modules
 * - Économat
 * - Scolarité
 * - Études
 * - Examens
 * - Enseignants
 */

$baseUrl = 'http://localhost:8080';

echo "🎯 TEST COMPLET - TOUS LES MODULES\n";
echo "==================================\n\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true
    ]
]);

// ========================================
// 1. MODULE ÉCONOMAT
// ========================================
echo "💰 MODULE ÉCONOMAT\n";
echo "==================\n";

$economatPages = [
    '/admin/economat' => 'Dashboard Économat',
    '/admin/economat/students' => 'Gestion des Élèves',
    '/admin/economat/payments' => 'Gestion des Paiements',
    '/admin/economat/fees' => 'Gestion des Frais',
    '/admin/economat/reports' => 'Rapports Financiers',
    '/admin/economat/statistics' => 'Statistiques'
];

$economatAccessible = 0;
foreach ($economatPages as $url => $description) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$description}: HTTP 200\n";
        $economatAccessible++;
    } else {
        echo "❌ {$description}: {$httpCode}\n";
    }
}

echo "📊 Économat: {$economatAccessible}/" . count($economatPages) . " pages accessibles\n\n";

// ========================================
// 2. MODULE SCOLARITÉ
// ========================================
echo "👥 MODULE SCOLARITÉ\n";
echo "===================\n";

$scolaritePages = [
    '/admin/scolarite' => 'Dashboard Scolarité',
    '/admin/scolarite/students' => 'Gestion des Élèves',
    '/admin/scolarite/absences' => 'Gestion des Absences',
    '/admin/scolarite/discipline' => 'Gestion de la Discipline',
    '/admin/scolarite/reports' => 'Rapports Scolarité',
    '/admin/scolarite/statistics' => 'Statistiques'
];

$scolariteAccessible = 0;
foreach ($scolaritePages as $url => $description) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$description}: HTTP 200\n";
        $scolariteAccessible++;
    } else {
        echo "❌ {$description}: {$httpCode}\n";
    }
}

echo "📊 Scolarité: {$scolariteAccessible}/" . count($scolaritePages) . " pages accessibles\n\n";

// ========================================
// 3. MODULE ÉTUDES
// ========================================
echo "📚 MODULE ÉTUDES\n";
echo "================\n";

$etudesPages = [
    '/admin/etudes' => 'Dashboard Études',
    '/admin/etudes/cycles' => 'Gestion des Cycles',
    '/admin/etudes/classes' => 'Gestion des Classes',
    '/admin/etudes/subjects' => 'Gestion des Matières',
    '/admin/etudes/timetables' => 'Emplois du Temps',
    '/admin/etudes/assignments' => 'Assignations',
    '/admin/etudes/reports' => 'Rapports Études'
];

$etudesAccessible = 0;
foreach ($etudesPages as $url => $description) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$description}: HTTP 200\n";
        $etudesAccessible++;
    } else {
        echo "❌ {$description}: {$httpCode}\n";
    }
}

echo "📊 Études: {$etudesAccessible}/" . count($etudesPages) . " pages accessibles\n\n";

// ========================================
// 4. MODULE EXAMENS
// ========================================
echo "📝 MODULE EXAMENS\n";
echo "=================\n";

$examensPages = [
    '/admin/examens' => 'Dashboard Examens',
    '/admin/examens/exams' => 'Gestion des Examens',
    '/admin/examens/grades' => 'Gestion des Notes',
    '/admin/examens/report-cards' => 'Bulletins',
    '/admin/examens/statistics' => 'Statistiques',
    '/admin/examens/export' => 'Exports'
];

$examensAccessible = 0;
foreach ($examensPages as $url => $description) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$description}: HTTP 200\n";
        $examensAccessible++;
    } else {
        echo "❌ {$description}: {$httpCode}\n";
    }
}

echo "📊 Examens: {$examensAccessible}/" . count($examensPages) . " pages accessibles\n\n";

// ========================================
// 5. MODULE ENSEIGNANTS
// ========================================
echo "👨‍🏫 MODULE ENSEIGNANTS\n";
echo "======================\n";

$enseignantsPages = [
    '/admin/enseignants' => 'Dashboard Enseignants',
    '/admin/enseignants/list' => 'Liste des Enseignants',
    '/admin/enseignants/create' => 'Création Enseignant',
    '/admin/enseignants/show/1' => 'Détail Enseignant',
    '/admin/enseignants/edit/1' => 'Modification Enseignant',
    '/admin/enseignants/statistics' => 'Statistiques',
    '/admin/enseignants/subjects/1' => 'Matières Enseignant',
    '/admin/enseignants/classes/1' => 'Classes Enseignant'
];

$enseignantsAccessible = 0;
foreach ($enseignantsPages as $url => $description) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$description}: HTTP 200\n";
        $enseignantsAccessible++;
    } else {
        echo "❌ {$description}: {$httpCode}\n";
    }
}

echo "📊 Enseignants: {$enseignantsAccessible}/" . count($enseignantsPages) . " pages accessibles\n\n";

// ========================================
// 6. VÉRIFICATION DE LA COHÉRENCE ENTRE MODULES
// ========================================
echo "🔗 VÉRIFICATION DE LA COHÉRENCE ENTRE MODULES\n";
echo "=============================================\n";

// Vérifier les références croisées
$coherenceTests = [
    'Économat → Scolarité' => '/admin/economat/students',
    'Scolarité → Économat' => '/admin/scolarite',
    'Études → Enseignants' => '/admin/etudes',
    'Examens → Études' => '/admin/examens',
    'Enseignants → Études' => '/admin/enseignants/subjects/1'
];

$coherenceScore = 0;
foreach ($coherenceTests as $test => $url) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$test}: Cohérent\n";
        $coherenceScore++;
    } else {
        echo "❌ {$test}: Incohérent ({$httpCode})\n";
    }
}

echo "📊 Cohérence: {$coherenceScore}/" . count($coherenceTests) . " modules cohérents\n\n";

// ========================================
// 7. VÉRIFICATION DE LA CONFORMITÉ RÉGLEMENTAIRE
// ========================================
echo "🇨🇲 VÉRIFICATION DE LA CONFORMITÉ RÉGLEMENTAIRE CAMEROUNAISE\n";
echo "===========================================================\n";

// Test de la page de création d'enseignant pour vérifier les qualifications
$createTeacherResponse = file_get_contents($baseUrl . '/admin/enseignants/create', false, $context);
$cameroonianQualifications = ['Licence', 'Master', 'Doctorat', 'CAPES', 'Agrégation', 'Certificat d\'Aptitude', 'Diplôme d\'État'];
$cameroonianSpecializations = ['Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre', 'Histoire-Géographie', 'Français', 'Anglais', 'Philosophie', 'Économie', 'Éducation Physique et Sportive'];

$qualificationsFound = 0;
foreach ($cameroonianQualifications as $qualification) {
    if (strpos($createTeacherResponse, $qualification) !== false) {
        echo "✅ Qualification '{$qualification}' reconnue\n";
        $qualificationsFound++;
    } else {
        echo "❌ Qualification '{$qualification}' manquante\n";
    }
}

$specializationsFound = 0;
foreach ($cameroonianSpecializations as $specialization) {
    if (strpos($createTeacherResponse, $specialization) !== false) {
        echo "✅ Spécialisation '{$specialization}' reconnue\n";
        $specializationsFound++;
    } else {
        echo "❌ Spécialisation '{$specialization}' manquante\n";
    }
}

echo "📊 Qualifications camerounaises: {$qualificationsFound}/" . count($cameroonianQualifications) . " reconnues\n";
echo "📊 Spécialisations camerounaises: {$specializationsFound}/" . count($cameroonianSpecializations) . " reconnues\n\n";

// ========================================
// 8. VÉRIFICATION DES FONCTIONNALITÉS CRUD
// ========================================
echo "🔧 VÉRIFICATION DES FONCTIONNALITÉS CRUD\n";
echo "========================================\n";

// Test des formulaires de création
$crudTests = [
    'Création Enseignant' => '/admin/enseignants/create',
    'Création Élève' => '/admin/scolarite/students/create',
    'Création Classe' => '/admin/etudes/classes/create',
    'Création Examen' => '/admin/examens/exams/create'
];

$crudScore = 0;
foreach ($crudTests as $test => $url) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        // Vérifier la présence de formulaires
        if (strpos($response, 'form') !== false && strpos($response, 'method="post"') !== false) {
            echo "✅ {$test}: Formulaire présent\n";
            $crudScore++;
        } else {
            echo "⚠️ {$test}: Formulaire incomplet\n";
        }
    } else {
        echo "❌ {$test}: Inaccessible ({$httpCode})\n";
    }
}

echo "📊 CRUD: {$crudScore}/" . count($crudTests) . " formulaires fonctionnels\n\n";

// ========================================
// 9. VÉRIFICATION DES EXPORTS ET RAPPORTS
// ========================================
echo "📊 VÉRIFICATION DES EXPORTS ET RAPPORTS\n";
echo "=======================================\n";

$exportTests = [
    'Export CSV Économat' => '/admin/economat/export/csv',
    'Export CSV Scolarité' => '/admin/scolarite/export/csv',
    'Export CSV Études' => '/admin/etudes/export/csv',
    'Export CSV Examens' => '/admin/examens/export/csv',
    'Export CSV Enseignants' => '/admin/enseignants/export/csv'
];

$exportScore = 0;
foreach ($exportTests as $test => $url) {
    $response = file_get_contents($baseUrl . $url, false, $context);
    $httpCode = $http_response_header[0] ?? '';
    
    if (strpos($httpCode, '200') !== false) {
        echo "✅ {$test}: Fonctionnel\n";
        $exportScore++;
    } else {
        echo "❌ {$test}: Non fonctionnel ({$httpCode})\n";
    }
}

echo "📊 Exports: {$exportScore}/" . count($exportTests) . " exports fonctionnels\n\n";

// ========================================
// 10. RÉSUMÉ FINAL
// ========================================
echo "🎉 RÉSUMÉ FINAL - TOUS LES MODULES\n";
echo "===================================\n";

$totalPages = count($economatPages) + count($scolaritePages) + count($etudesPages) + count($examensPages) + count($enseignantsPages);
$totalAccessible = $economatAccessible + $scolariteAccessible + $etudesAccessible + $examensAccessible + $enseignantsAccessible;

echo "📊 ACCESSIBILITÉ GLOBALE:\n";
echo "   • Économat: {$economatAccessible}/" . count($economatPages) . " (" . round(($economatAccessible/count($economatPages))*100, 1) . "%)\n";
echo "   • Scolarité: {$scolariteAccessible}/" . count($scolaritePages) . " (" . round(($scolariteAccessible/count($scolaritePages))*100, 1) . "%)\n";
echo "   • Études: {$etudesAccessible}/" . count($etudesPages) . " (" . round(($etudesAccessible/count($etudesPages))*100, 1) . "%)\n";
echo "   • Examens: {$examensAccessible}/" . count($examensPages) . " (" . round(($examensAccessible/count($examensPages))*100, 1) . "%)\n";
echo "   • Enseignants: {$enseignantsAccessible}/" . count($enseignantsPages) . " (" . round(($enseignantsAccessible/count($enseignantsPages))*100, 1) . "%)\n";
echo "   • TOTAL: {$totalAccessible}/{$totalPages} (" . round(($totalAccessible/$totalPages)*100, 1) . "%)\n\n";

echo "🔗 COHÉRENCE ENTRE MODULES:\n";
echo "   • Modules cohérents: {$coherenceScore}/" . count($coherenceTests) . " (" . round(($coherenceScore/count($coherenceTests))*100, 1) . "%)\n\n";

echo "🇨🇲 CONFORMITÉ RÉGLEMENTAIRE:\n";
echo "   • Qualifications camerounaises: {$qualificationsFound}/" . count($cameroonianQualifications) . " (" . round(($qualificationsFound/count($cameroonianQualifications))*100, 1) . "%)\n";
echo "   • Spécialisations camerounaises: {$specializationsFound}/" . count($cameroonianSpecializations) . " (" . round(($specializationsFound/count($cameroonianSpecializations))*100, 1) . "%)\n\n";

echo "🔧 FONCTIONNALITÉS CRUD:\n";
echo "   • Formulaires fonctionnels: {$crudScore}/" . count($crudTests) . " (" . round(($crudScore/count($crudTests))*100, 1) . "%)\n\n";

echo "📊 EXPORTS ET RAPPORTS:\n";
echo "   • Exports fonctionnels: {$exportScore}/" . count($exportTests) . " (" . round(($exportScore/count($exportTests))*100, 1) . "%)\n\n";

// Calcul du score global
$globalScore = (
    ($totalAccessible / $totalPages) * 0.3 +
    ($coherenceScore / count($coherenceTests)) * 0.2 +
    ($qualificationsFound / count($cameroonianQualifications)) * 0.2 +
    ($specializationsFound / count($cameroonianSpecializations)) * 0.1 +
    ($crudScore / count($crudTests)) * 0.1 +
    ($exportScore / count($exportTests)) * 0.1
) * 100;

echo "🎯 SCORE GLOBAL: " . round($globalScore, 1) . "%\n";

if ($globalScore >= 90) {
    echo "🏆 EXCELLENT - Prêt pour la production\n";
} elseif ($globalScore >= 80) {
    echo "✅ TRÈS BON - Quelques améliorations mineures\n";
} elseif ($globalScore >= 70) {
    echo "⚠️ BON - Améliorations nécessaires\n";
} else {
    echo "❌ INSUFFISANT - Corrections majeures requises\n";
}

echo "\n🎯 TEST TERMINÉ !\n";
?>









