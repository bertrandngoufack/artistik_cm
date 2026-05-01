<?php
// Test de débogage du filtre enseignant
echo "=== DÉBOGAGE DU FILTRE ENSEIGNANT ===\n\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

// Extraire la section du filtre enseignant
preg_match('/<select id="teacher_filter">(.*?)<\/select>/s', $content, $matches);

if (isset($matches[1])) {
    echo "Contenu du filtre enseignant:\n";
    echo $matches[1] . "\n\n";
    
    // Extraire toutes les options
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/', $matches[1], $optionMatches, PREG_SET_ORDER);
    
    echo "Options trouvées:\n";
    foreach ($optionMatches as $option) {
        $value = trim($option[1]);
        $text = trim($option[2]);
        echo "  Value: '{$value}' | Text: '{$text}'\n";
    }
} else {
    echo "❌ Section du filtre enseignant non trouvée\n";
}

// Vérifier aussi le tableau
echo "\n=== VÉRIFICATION DU TABLEAU ===\n";

// Extraire les lignes du tableau
preg_match_all('/<tr>(.*?)<\/tr>/s', $content, $rowMatches, PREG_SET_ORDER);

echo "Lignes du tableau trouvées: " . count($rowMatches) . "\n\n";

foreach ($rowMatches as $index => $row) {
    if ($index === 0) continue; // Ignorer l'en-tête
    
    // Extraire les cellules
    preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $row[1], $cellMatches, PREG_SET_ORDER);
    
    if (count($cellMatches) >= 3) {
        $class = trim(strip_tags($cellMatches[0][1]));
        $subject = trim(strip_tags($cellMatches[1][1]));
        $teacher = trim(strip_tags($cellMatches[2][1]));
        
        echo "Ligne {$index}:\n";
        echo "  Classe: '{$class}'\n";
        echo "  Matière: '{$subject}'\n";
        echo "  Enseignant: '{$teacher}'\n";
        echo "\n";
    }
}

echo "=== FIN DU DÉBOGAGE ===\n";
?>





