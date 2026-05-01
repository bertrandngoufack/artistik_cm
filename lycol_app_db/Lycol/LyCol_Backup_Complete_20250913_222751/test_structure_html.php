<?php
// Test de la structure HTML du tableau
echo "=== TEST DE LA STRUCTURE HTML DU TABLEAU ===\n\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

// Extraire la section du tableau
preg_match('/<table class="table is-fullwidth is-striped is-hoverable">(.*?)<\/table>/s', $content, $tableMatches);

if (isset($tableMatches[1])) {
    echo "Tableau trouvé:\n";
    
    // Extraire les lignes du tableau
    preg_match_all('/<tr>(.*?)<\/tr>/s', $tableMatches[1], $rowMatches, PREG_SET_ORDER);
    
    echo "Nombre de lignes: " . count($rowMatches) . "\n\n";
    
    foreach ($rowMatches as $index => $row) {
        echo "=== LIGNE {$index} ===\n";
        echo "Contenu brut: " . substr($row[1], 0, 200) . "...\n\n";
        
        // Extraire les cellules
        preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $row[1], $cellMatches, PREG_SET_ORDER);
        
        echo "Cellules trouvées: " . count($cellMatches) . "\n";
        
        foreach ($cellMatches as $cellIndex => $cell) {
            $cellContent = trim(strip_tags($cell[1]));
            echo "  Cellule {$cellIndex}: '{$cellContent}'\n";
        }
        
        echo "\n";
    }
} else {
    echo "❌ Tableau non trouvé\n";
}

// Vérifier aussi le filtre enseignant
echo "=== VÉRIFICATION DU FILTRE ENSEIGNANT ===\n";

preg_match('/<select id="teacher_filter">(.*?)<\/select>/s', $content, $filterMatches);

if (isset($filterMatches[1])) {
    echo "Filtre enseignant trouvé:\n";
    
    // Extraire les options
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/s', $filterMatches[1], $optionMatches, PREG_SET_ORDER);
    
    echo "Options trouvées: " . count($optionMatches) . "\n";
    
    foreach ($optionMatches as $option) {
        $value = trim($option[1]);
        $text = trim($option[2]);
        echo "  Value: '{$value}' | Text: '{$text}'\n";
    }
} else {
    echo "❌ Filtre enseignant non trouvé\n";
}

echo "\n=== FIN DU TEST ===\n";
?>





