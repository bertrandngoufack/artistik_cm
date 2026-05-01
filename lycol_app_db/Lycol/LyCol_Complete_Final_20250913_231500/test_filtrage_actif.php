<?php
// Test du filtrage actif en simulant une sélection
echo "=== TEST DU FILTRAGE ACTIF ===\n\n";

// Test 1: Vérifier que la page se charge correctement
echo "1. Test de chargement de la page...\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

echo "✅ Page chargée avec succès\n";

// Test 2: Vérifier la présence de données de test
echo "\n2. Vérification des données de test...\n";

if (strpos($content, 'Test Enseignant') !== false) {
    echo "✅ 'Test Enseignant' trouvé dans le contenu\n";
} else {
    echo "❌ 'Test Enseignant' non trouvé\n";
}

if (strpos($content, 'Test Actions Corrigées') !== false) {
    echo "✅ 'Test Actions Corrigées' trouvé dans le contenu\n";
} else {
    echo "❌ 'Test Actions Corrigées' non trouvé\n";
}

// Test 3: Vérifier la structure du JavaScript
echo "\n3. Vérification de la structure JavaScript...\n";

// Extraire le JavaScript complet
preg_match('/<script>(.*?)<\/script>/s', $content, $scriptMatches);

if (isset($scriptMatches[1])) {
    $javascript = $scriptMatches[1];
    
    // Vérifier que toutes les fonctions nécessaires sont présentes
    $requiredFunctions = [
        'filterTimetables',
        'updateVisibleCount',
        'resetFilters'
    ];
    
    foreach ($requiredFunctions as $function) {
        if (strpos($javascript, "function {$function}") !== false) {
            echo "✅ Fonction {$function} présente\n";
        } else {
            echo "❌ Fonction {$function} manquante\n";
        }
    }
    
    // Vérifier que les event listeners sont configurés
    $eventListeners = [
        'class_filter',
        'teacher_filter',
        'subject_filter',
        'day_filter'
    ];
    
    foreach ($eventListeners as $filter) {
        if (strpos($javascript, "getElementById('{$filter}')") !== false) {
            echo "✅ Event listener pour {$filter} configuré\n";
        } else {
            echo "❌ Event listener pour {$filter} manquant\n";
        }
    }
}

// Test 4: Vérifier la logique de filtrage spécifique
echo "\n4. Vérification de la logique de filtrage...\n";

if (isset($javascript)) {
    // Vérifier la logique de filtrage par enseignant
    if (strpos($javascript, '// Filtre par enseignant') !== false) {
        echo "✅ Commentaire 'Filtre par enseignant' trouvé\n";
    } else {
        echo "❌ Commentaire 'Filtre par enseignant' manquant\n";
    }
    
    if (strpos($javascript, 'matchesTeacher =') !== false) {
        echo "✅ Variable matchesTeacher définie\n";
    } else {
        echo "❌ Variable matchesTeacher manquante\n";
    }
    
    if (strpos($javascript, 'selectedTeacherName') !== false) {
        echo "✅ Variable selectedTeacherName utilisée\n";
    } else {
        echo "❌ Variable selectedTeacherName manquante\n";
    }
    
    if (strpos($javascript, 'textContent') !== false) {
        echo "✅ textContent utilisé\n";
    } else {
        echo "❌ textContent manquant\n";
    }
    
    if (strpos($javascript, 'includes(') !== false) {
        echo "✅ Méthode includes() utilisée\n";
    } else {
        echo "❌ Méthode includes() manquante\n";
    }
}

// Test 5: Vérifier la structure HTML finale
echo "\n5. Vérification de la structure HTML finale...\n";

// Vérifier que le tableau a bien la structure attendue
if (strpos($content, '<table class="table is-fullwidth is-striped is-hoverable">') !== false) {
    echo "✅ Tableau avec la bonne classe trouvé\n";
} else {
    echo "❌ Tableau avec la bonne classe non trouvé\n";
}

// Vérifier que les en-têtes sont corrects
$expectedHeaders = ['Classe', 'Matière', 'Enseignant', 'Jour', 'Horaire', 'Durée', 'Actions'];
foreach ($expectedHeaders as $header) {
    if (strpos($content, "<th>{$header}</th>") !== false) {
        echo "✅ En-tête '{$header}' trouvé\n";
    } else {
        echo "❌ En-tête '{$header}' manquant\n";
    }
}

// Test 6: Vérifier la présence de données dans le tableau
echo "\n6. Vérification des données du tableau...\n";

// Compter les lignes de données (excluant l'en-tête)
preg_match_all('/<tr>(.*?)<\/tr>/s', $content, $rowMatches, PREG_SET_ORDER);

$dataRows = 0;
foreach ($rowMatches as $row) {
    if (strpos($row[1], '<td') !== false) {
        $dataRows++;
    }
}

echo "Lignes de données trouvées: {$dataRows}\n";

if ($dataRows > 0) {
    echo "✅ Données présentes dans le tableau\n";
} else {
    echo "❌ Aucune donnée dans le tableau\n";
}

echo "\n=== FIN DU TEST ===\n";

// Résumé des problèmes potentiels
echo "\n=== DIAGNOSTIC ===\n";

if (strpos($content, 'Test Enseignant') !== false && 
    strpos($content, 'id="teacher_filter"') !== false &&
    strpos($content, 'filterTimetables') !== false) {
    
    echo "✅ Tous les éléments nécessaires sont présents\n";
    echo "⚠️  Le problème pourrait être:\n";
    echo "   - JavaScript bloqué par le navigateur\n";
    echo "   - Erreur JavaScript non visible\n";
    echo "   - Problème de timing (DOM pas encore chargé)\n";
    echo "   - Conflit avec d'autres scripts\n";
    
} else {
    echo "❌ Certains éléments nécessaires sont manquants\n";
}
?>





