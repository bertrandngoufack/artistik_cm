<?php
// Test du filtrage JavaScript
echo "=== TEST DU FILTRAGE JAVASCRIPT ===\n\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

// Extraire le JavaScript de filtrage
preg_match('/<script>(.*?)<\/script>/s', $content, $scriptMatches);

if (isset($scriptMatches[1])) {
    echo "JavaScript de filtrage trouvé:\n";
    echo "Longueur: " . strlen($scriptMatches[1]) . " caractères\n\n";
    
    // Vérifier la présence de fonctions clés
    if (strpos($scriptMatches[1], 'filterTimetables') !== false) {
        echo "✅ Fonction filterTimetables présente\n";
    } else {
        echo "❌ Fonction filterTimetables manquante\n";
    }
    
    if (strpos($scriptMatches[1], 'teacherFilter') !== false) {
        echo "✅ Variable teacherFilter présente\n";
    } else {
        echo "❌ Variable teacherFilter manquante\n";
    }
    
    if (strpos($scriptMatches[1], 'matchesTeacher') !== false) {
        echo "✅ Variable matchesTeacher présente\n";
    } else {
        echo "❌ Variable matchesTeacher manquante\n";
    }
    
    // Extraire la logique de filtrage par enseignant
    if (preg_match('/\/\/ Filtre par enseignant(.*?)matchesTeacher =/s', $scriptMatches[1], $teacherFilterMatch)) {
        echo "\nLogique de filtrage par enseignant:\n";
        echo trim($teacherFilterMatch[1]) . "\n";
    }
    
    // Vérifier la logique de comparaison
    if (strpos($scriptMatches[1], 'includes(selectedTeacherName)') !== false) {
        echo "✅ Méthode includes() utilisée pour la comparaison\n";
    } else {
        echo "❌ Méthode includes() non trouvée\n";
    }
    
    if (strpos($scriptMatches[1], 'textContent') !== false) {
        echo "✅ textContent utilisé pour récupérer le contenu\n";
    } else {
        echo "❌ textContent non trouvé\n";
    }
    
} else {
    echo "❌ Section JavaScript non trouvée\n";
}

// Vérifier la structure HTML pour le filtrage
echo "\n=== VÉRIFICATION DE LA STRUCTURE HTML ===\n";

// Vérifier que les cellules du tableau ont bien le bon contenu
if (strpos($content, '<td>Test Enseignant</td>') !== false) {
    echo "✅ Cellule 'Test Enseignant' trouvée dans le tableau\n";
} else {
    echo "❌ Cellule 'Test Enseignant' non trouvée\n";
}

// Vérifier que le filtre enseignant a la bonne valeur
if (strpos($content, 'value="15">Test Enseignant') !== false) {
    echo "✅ Option 'Test Enseignant' avec valeur 15 trouvée dans le filtre\n";
} else {
    echo "❌ Option 'Test Enseignant' avec valeur 15 non trouvée\n";
}

echo "\n=== FIN DU TEST ===\n";
?>





