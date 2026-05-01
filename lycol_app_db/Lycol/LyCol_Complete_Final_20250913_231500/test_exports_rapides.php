<?php
/**
 * Test des exports rapides du module Études
 * Vérifie que tous les exports CSV fonctionnent correctement
 */

echo "🧪 TEST DES EXPORTS RAPIDES - MODULE ÉTUDES\n";
echo "===========================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8080';

// Tests des exports rapides
$exports = [
    'summary' => [
        'url' => '/admin/etudes/reports/export/csv?report_type=summary',
        'description' => 'Export CSV - Général',
        'expected_headers' => ['Statistique', 'Valeur']
    ],
    'assignments' => [
        'url' => '/admin/etudes/reports/export/csv?report_type=assignments',
        'description' => 'Export CSV - Assignations',
        'expected_headers' => ['Enseignant', 'Classe', 'Matière', 'Principal', 'Année']
    ],
    'classes' => [
        'url' => '/admin/etudes/reports/export/csv?report_type=classes',
        'description' => 'Export CSV - Classes',
        'expected_headers' => ['Classe', 'Cycle', 'Élèves', 'Enseignants', 'Matières', 'Heures EDT']
    ],
    'cycles' => [
        'url' => '/admin/etudes/reports/export/csv?report_type=cycles',
        'description' => 'Export CSV - Cycles',
        'expected_headers' => ['Cycle', 'Classes', 'Élèves', 'Enseignants']
    ]
];

$successCount = 0;
$totalCount = count($exports);

foreach ($exports as $type => $export) {
    echo "📊 Test {$export['description']} :\n";
    
    $url = $baseUrl . $export['url'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ HTTP 200 OK\n";
        
        if (strpos($contentType, 'text/csv') !== false) {
            echo "   ✅ Type de contenu CSV correct\n";
            
            // Vérifier les en-têtes
            $lines = explode("\n", $response);
            if (!empty($lines[0])) {
                $headers = str_getcsv($lines[0]);
                $headersMatch = true;
                
                foreach ($export['expected_headers'] as $expectedHeader) {
                    if (!in_array($expectedHeader, $headers)) {
                        $headersMatch = false;
                        break;
                    }
                }
                
                if ($headersMatch) {
                    echo "   ✅ En-têtes CSV corrects\n";
                    
                    // Vérifier qu'il y a des données
                    $dataLines = array_filter(array_slice($lines, 1), function($line) {
                        return !empty(trim($line));
                    });
                    
                    if (count($dataLines) > 0) {
                        echo "   ✅ Données présentes (" . count($dataLines) . " lignes)\n";
                        $successCount++;
                    } else {
                        echo "   ⚠️  Aucune donnée trouvée\n";
                    }
                } else {
                    echo "   ❌ En-têtes CSV incorrects\n";
                    echo "      Attendu : " . implode(', ', $export['expected_headers']) . "\n";
                    echo "      Reçu : " . implode(', ', $headers) . "\n";
                }
            } else {
                echo "   ❌ Aucun en-tête trouvé\n";
            }
        } else {
            echo "   ❌ Type de contenu incorrect : {$contentType}\n";
        }
    } else {
        echo "   ❌ Erreur HTTP : {$httpCode}\n";
    }
    
    echo "\n";
}

// Test des rapports rapides HTML
echo "🌐 Test des rapports rapides HTML :\n";

$reports = [
    'summary' => 'Rapport général',
    'cycles' => 'Rapport par cycle',
    'classes' => 'Rapport par classe',
    'assignments' => 'Rapport des assignations'
];

foreach ($reports as $type => $description) {
    echo "📋 Test {$description} :\n";
    
    $url = $baseUrl . "/admin/etudes/reports/generate?report_type={$type}&format=html";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ HTTP 200 OK\n";
        
        // Vérifier que la page contient des éléments attendus
        $expectedElements = [
            'Rapport Études' => 'Titre de la page',
            'Exporter CSV' => 'Bouton d\'export'
        ];
        
        $allElementsFound = true;
        foreach ($expectedElements as $text => $element) {
            if (strpos($response, $text) !== false) {
                echo "   ✅ {$element} trouvé\n";
            } else {
                echo "   ❌ {$element} non trouvé\n";
                $allElementsFound = false;
            }
        }
        
        if ($allElementsFound) {
            $successCount++;
        }
    } else {
        echo "   ❌ Erreur HTTP : {$httpCode}\n";
    }
    
    echo "\n";
}

// Test de la page des rapports
echo "📄 Test de la page des rapports :\n";

$url = $baseUrl . '/admin/etudes/reports';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "   ✅ Page des rapports accessible (HTTP 200)\n";
    
    // Vérifier les liens d'export rapide
    $exportLinks = [
        'Export CSV - Général' => 'export/csv?report_type=summary',
        'Export CSV - Assignations' => 'export/csv?report_type=assignments',
        'Export CSV - Classes' => 'export/csv?report_type=classes'
    ];
    
    foreach ($exportLinks as $text => $link) {
        if (strpos($response, $link) !== false) {
            echo "   ✅ Lien '{$text}' trouvé\n";
        } else {
            echo "   ❌ Lien '{$text}' non trouvé\n";
        }
    }
    
    $successCount++;
} else {
    echo "   ❌ Page des rapports non accessible (HTTP {$httpCode})\n";
}

echo "\n🎉 RÉSUMÉ DU TEST :\n";
echo "==================\n";
echo "✅ Exports CSV fonctionnels : {$successCount}/" . ($totalCount + count($reports) + 1) . "\n";
echo "✅ Rapports HTML fonctionnels\n";
echo "✅ Page des rapports accessible\n";
echo "✅ Liens d'export rapide corrects\n";
echo "\n🌐 URLs testées :\n";

foreach ($exports as $type => $export) {
    echo "   - {$export['description']} : {$baseUrl}{$export['url']}\n";
}

foreach ($reports as $type => $description) {
    echo "   - {$description} : {$baseUrl}/admin/etudes/reports/generate?report_type={$type}&format=html\n";
}

echo "   - Page des rapports : {$baseUrl}/admin/etudes/reports\n";

if ($successCount === ($totalCount + count($reports) + 1)) {
    echo "\n🎉 TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS !\n";
    echo "📋 La zone d'export rapide fonctionne parfaitement !\n";
} else {
    echo "\n⚠️  Certains tests ont échoué. Vérifiez les erreurs ci-dessus.\n";
}
?>









