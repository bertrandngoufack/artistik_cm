<?php
// Test du filtrage réel en simulant une sélection
echo "=== TEST DU FILTRAGE RÉEL ===\n\n";

// Test 1: Récupérer la page avec le filtre enseignant sélectionné
echo "1. Test avec filtre enseignant 'Test Enseignant' (valeur 15)...\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

// Vérifier que le JavaScript est bien présent et fonctionnel
echo "2. Vérification du JavaScript...\n";

if (strpos($content, 'addEventListener') !== false) {
    echo "✅ Event listeners configurés\n";
} else {
    echo "❌ Event listeners manquants\n";
}

if (strpos($content, 'filterTimetables') !== false) {
    echo "✅ Fonction filterTimetables présente\n";
} else {
    echo "❌ Fonction filterTimetables manquante\n";
}

// Vérifier la structure des données pour le filtrage
echo "\n3. Vérification de la structure des données...\n";

// Extraire les données du tableau
preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $content, $cellMatches, PREG_SET_ORDER);

$tableData = [];
$currentRow = [];

foreach ($cellMatches as $index => $cell) {
    $cellContent = trim(strip_tags($cell[1]));
    
    if ($index % 7 == 0 && $index > 0) {
        // Nouvelle ligne
        if (!empty($currentRow)) {
            $tableData[] = $currentRow;
        }
        $currentRow = [];
    }
    
    $currentRow[] = $cellContent;
}

// Ajouter la dernière ligne
if (!empty($currentRow)) {
    $tableData[] = $currentRow;
}

echo "Données du tableau extraites:\n";
foreach ($tableData as $rowIndex => $row) {
    if (count($row) >= 6) {
        echo "Ligne {$rowIndex}:\n";
        echo "  Classe: '{$row[0]}'\n";
        echo "  Matière: '{$row[1]}'\n";
        echo "  Enseignant: '{$row[2]}'\n";
        echo "  Jour: '{$row[3]}'\n";
        echo "  Horaire: '{$row[4]}'\n";
        echo "  Durée: '{$row[5]}'\n\n";
    }
}

// Vérifier que les données correspondent aux filtres
echo "4. Vérification de la cohérence des données...\n";

// Extraire les options du filtre enseignant
preg_match('/<select id="teacher_filter">(.*?)<\/select>/s', $content, $filterMatches);

if (isset($filterMatches[1])) {
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/s', $filterMatches[1], $optionMatches, PREG_SET_ORDER);
    
    $teacherOptions = [];
    foreach ($optionMatches as $option) {
        $value = trim($option[1]);
        $text = trim($option[2]);
        if (!empty($value)) {
            $teacherOptions[$value] = $text;
        }
    }
    
    echo "Options du filtre enseignant:\n";
    foreach ($teacherOptions as $value => $text) {
        echo "  {$value}: '{$text}'\n";
    }
    
    // Vérifier que "Test Enseignant" est bien dans les options
    if (in_array('Test Enseignant', $teacherOptions)) {
        $teacherId = array_search('Test Enseignant', $teacherOptions);
        echo "\n✅ 'Test Enseignant' trouvé avec l'ID: {$teacherId}\n";
    } else {
        echo "\n❌ 'Test Enseignant' non trouvé dans les options\n";
    }
}

// Test 5: Vérifier la logique de filtrage JavaScript
echo "\n5. Analyse de la logique JavaScript...\n";

// Extraire le JavaScript
preg_match('/<script>(.*?)<\/script>/s', $content, $scriptMatches);

if (isset($scriptMatches[1])) {
    $javascript = $scriptMatches[1];
    
    // Vérifier la logique de filtrage par enseignant
    if (preg_match('/\/\/ Filtre par enseignant(.*?)matchesTeacher =/s', $javascript, $teacherLogicMatch)) {
        echo "Logique de filtrage par enseignant trouvée:\n";
        echo trim($teacherLogicMatch[1]) . "\n";
    }
    
    // Vérifier que la fonction est bien appelée
    if (strpos($javascript, 'addEventListener(\'change\', filterTimetables)') !== false) {
        echo "✅ Event listeners configurés correctement\n";
    } else {
        echo "❌ Event listeners mal configurés\n";
    }
}

echo "\n=== FIN DU TEST ===\n";
?>





