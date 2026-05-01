<?php
// Test du filtre de la colonne enseignant sur la page timetable
echo "=== TEST DU FILTRE ENSEIGNANT ===\n\n";

// Test 1: Récupération de la page principale
echo "1. Test de récupération de la page principale...\n";
$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

echo "✅ Page récupérée avec succès\n";

// Test 2: Vérification de la présence des filtres
echo "\n2. Vérification de la présence des filtres...\n";

if (strpos($content, 'id="teacher_filter"') !== false) {
    echo "✅ Filtre enseignant trouvé\n";
} else {
    echo "❌ Filtre enseignant manquant\n";
}

if (strpos($content, 'id="class_filter"') !== false) {
    echo "✅ Filtre classe trouvé\n";
} else {
    echo "❌ Filtre classe manquant\n";
}

if (strpos($content, 'id="subject_filter"') !== false) {
    echo "✅ Filtre matière trouvé\n";
} else {
    echo "❌ Filtre matière manquant\n";
}

if (strpos($content, 'id="day_filter"') !== false) {
    echo "✅ Filtre jour trouvé\n";
} else {
    echo "❌ Filtre jour manquant\n";
}

// Test 3: Vérification de la présence des options dans le filtre enseignant
echo "\n3. Vérification des options du filtre enseignant...\n";

// Extraire les options du filtre enseignant
preg_match_all('/<option value="(\d+)">([^<]+)<\/option>/', $content, $matches, PREG_SET_ORDER);

$teacherOptions = [];
foreach ($matches as $match) {
    if (strpos($match[2], 'Tous les enseignants') === false) {
        $teacherOptions[] = [
            'value' => $match[1],
            'text' => trim($match[2])
        ];
    }
}

if (count($teacherOptions) > 0) {
    echo "✅ " . count($teacherOptions) . " options d'enseignants trouvées:\n";
    foreach (array_slice($teacherOptions, 0, 5) as $option) {
        echo "   - {$option['value']}: {$option['text']}\n";
    }
    if (count($teacherOptions) > 5) {
        echo "   ... et " . (count($teacherOptions) - 5) . " autres\n";
    }
} else {
    echo "❌ Aucune option d'enseignant trouvée\n";
}

// Test 4: Vérification de la présence du JavaScript de filtrage
echo "\n4. Vérification du JavaScript de filtrage...\n";

if (strpos($content, 'filterTimetables') !== false) {
    echo "✅ Fonction filterTimetables trouvée\n";
} else {
    echo "❌ Fonction filterTimetables manquante\n";
}

if (strpos($content, 'teacherFilter') !== false) {
    echo "✅ Variable teacherFilter trouvée\n";
} else {
    echo "❌ Variable teacherFilter manquante\n";
}

// Test 5: Vérification de la structure du tableau
echo "\n5. Vérification de la structure du tableau...\n";

if (strpos($content, '<th>Enseignant</th>') !== false) {
    echo "✅ En-tête de colonne Enseignant trouvé\n";
} else {
    echo "❌ En-tête de colonne Enseignant manquant\n";
}

// Compter les lignes du tableau
preg_match_all('/<tr>/', $content, $matches);
$totalRows = count($matches[0]);

if ($totalRows > 0) {
    echo "✅ " . $totalRows . " lignes de tableau trouvées\n";
} else {
    echo "❌ Aucune ligne de tableau trouvée\n";
}

// Test 6: Vérification des données d'enseignants dans le tableau
echo "\n6. Vérification des données d'enseignants dans le tableau...\n";

// Extraire les noms d'enseignants du tableau
preg_match_all('/<td>([^<]+)<\/td>/', $content, $matches, PREG_SET_ORDER);

$teacherNamesInTable = [];
foreach ($matches as $match) {
    $text = trim($match[1]);
    if (!empty($text) && $text !== 'N/A' && !preg_match('/^(Lundi|Mardi|Mercredi|Jeudi|Vendredi|Samedi|Dimanche)$/', $text)) {
        $teacherNamesInTable[] = $text;
    }
}

if (count($teacherNamesInTable) > 0) {
    echo "✅ " . count($teacherNamesInTable) . " noms d'enseignants trouvés dans le tableau:\n";
    foreach (array_slice($teacherNamesInTable, 0, 5) as $name) {
        echo "   - {$name}\n";
    }
    if (count($teacherNamesInTable) > 5) {
        echo "   ... et " . (count($teacherNamesInTable) - 5) . " autres\n";
    }
} else {
    echo "❌ Aucun nom d'enseignant trouvé dans le tableau\n";
}

// Test 7: Vérification de la cohérence entre les filtres et le tableau
echo "\n7. Vérification de la cohérence entre filtres et tableau...\n";

$filterTeacherNames = array_column($teacherOptions, 'text');
$tableTeacherNames = array_unique($teacherNamesInTable);

$commonNames = array_intersect($filterTeacherNames, $tableTeacherNames);
$filterOnly = array_diff($filterTeacherNames, $tableTeacherNames);
$tableOnly = array_diff($tableTeacherNames, $filterTeacherNames);

echo "   Noms communs: " . count($commonNames) . "\n";
echo "   Uniquement dans le filtre: " . count($filterOnly) . "\n";
echo "   Uniquement dans le tableau: " . count($tableOnly) . "\n";

if (count($commonNames) > 0) {
    echo "✅ Cohérence partielle trouvée\n";
} else {
    echo "❌ Aucune cohérence trouvée entre filtres et tableau\n";
}

echo "\n=== FIN DU TEST ===\n";
?>





