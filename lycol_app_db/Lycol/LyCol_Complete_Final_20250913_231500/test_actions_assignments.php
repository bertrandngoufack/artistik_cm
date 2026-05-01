<?php
// Test des boutons d'action et du tri de la colonne enseignant
echo "=== TEST DES BOUTONS D'ACTION ET DU TRI ===\n\n";

// Configuration
$baseUrl = "http://localhost:8080";
$assignmentsUrl = $baseUrl . "/admin/etudes/assignments";

// Test 1: Vérification de la page principale des assignations
echo "1. Test de la page principale des assignations...\n";
$mainPage = file_get_contents($assignmentsUrl);

if ($mainPage === false) {
    echo "❌ Erreur: Impossible d'accéder à la page des assignations\n";
    exit;
}

echo "✅ Page des assignations accessible\n";

// Vérifier la présence des boutons d'action
echo "\n2. Vérification des boutons d'action...\n";

// Bouton Vue
if (strpos($mainPage, 'admin/etudes/assignments/view/') !== false) {
    echo "✅ Bouton 'Vue' trouvé dans les actions\n";
} else {
    echo "❌ Bouton 'Vue' manquant dans les actions\n";
}

// Bouton Édition
if (strpos($mainPage, 'admin/etudes/assignments/edit/') !== false) {
    echo "✅ Bouton 'Édition' trouvé dans les actions\n";
} else {
    echo "❌ Bouton 'Édition' manquant dans les actions\n";
}

// Bouton Suppression
if (strpos($mainPage, 'admin/etudes/assignments/delete/') !== false) {
    echo "✅ Bouton 'Suppression' trouvé dans les actions\n";
} else {
    echo "❌ Bouton 'Suppression' manquant dans les actions\n";
}

// Vérifier la présence de la fonction JavaScript deleteAssignment
if (strpos($mainPage, 'deleteAssignment') !== false) {
    echo "✅ Fonction JavaScript 'deleteAssignment' trouvée\n";
} else {
    echo "❌ Fonction JavaScript 'deleteAssignment' manquante\n";
}

// Test 3: Vérification du tri de la colonne enseignant
echo "\n3. Test du tri de la colonne enseignant...\n";

// Vérifier la présence de l'attribut de tri
if (strpos($mainPage, 'data-kint-table-sort') !== false || strpos($mainPage, 'onclick="sortTable') !== false) {
    echo "✅ Attributs de tri trouvés\n";
} else {
    echo "❌ Attributs de tri manquants\n";
}

// Vérifier la présence de JavaScript pour le tri
if (strpos($mainPage, 'sortTable') !== false || strpos($mainPage, 'sortAssignments') !== false) {
    echo "✅ Fonction JavaScript de tri trouvée\n";
} else {
    echo "❌ Fonction JavaScript de tri manquante\n";
}

// Test 4: Test des liens d'action avec cURL
echo "\n4. Test des liens d'action avec cURL...\n";

// Extraire les IDs des assignations depuis la page
preg_match_all('/admin\/etudes\/assignments\/view\/(\d+)/', $mainPage, $matches);
$assignmentIds = $matches[1] ?? [];

if (empty($assignmentIds)) {
    echo "⚠️ Aucun ID d'assignation trouvé pour les tests\n";
} else {
    echo "📋 IDs d'assignations trouvés: " . implode(', ', $assignmentIds) . "\n";
    
    // Tester le premier lien de vue
    $firstId = $assignmentIds[0];
    $viewUrl = $baseUrl . "/admin/etudes/assignments/view/" . $firstId;
    
    echo "\n   Test du lien de vue pour l'assignation ID: {$firstId}\n";
    $viewPage = file_get_contents($viewUrl);
    
    if ($viewPage === false) {
        echo "   ❌ Erreur: Impossible d'accéder à la page de vue\n";
    } else {
        if (strpos($viewPage, '404') !== false || strpos($viewPage, 'Page non trouvée') !== false) {
            echo "   ❌ Erreur 404: Page de vue non trouvée\n";
        } else {
            echo "   ✅ Page de vue accessible\n";
        }
    }
    
    // Tester le lien d'édition
    $editUrl = $baseUrl . "/admin/etudes/assignments/edit/" . $firstId;
    
    echo "\n   Test du lien d'édition pour l'assignation ID: {$firstId}\n";
    $editPage = file_get_contents($editUrl);
    
    if ($editPage === false) {
        echo "   ❌ Erreur: Impossible d'accéder à la page d'édition\n";
    } else {
        if (strpos($editPage, '404') !== false || strpos($editPage, 'Page non trouvée') !== false) {
            echo "   ❌ Erreur 404: Page d'édition non trouvée\n";
        } else {
            echo "   ✅ Page d'édition accessible\n";
        }
    }
}

// Test 5: Test du filtrage par enseignant
echo "\n5. Test du filtrage par enseignant...\n";

// Extraire les IDs des enseignants depuis la page
preg_match_all('/value="(\d+)"[^>]*>([^<]+)<\/option>/', $mainPage, $teacherMatches);
$teacherOptions = [];

if (!empty($teacherMatches[1])) {
    for ($i = 0; $i < count($teacherMatches[1]); $i++) {
        if ($teacherMatches[1][$i] !== '') { // Exclure l'option "Tous les enseignants"
            $teacherOptions[$teacherMatches[1][$i]] = trim($teacherMatches[2][$i]);
        }
    }
}

if (empty($teacherOptions)) {
    echo "⚠️ Aucun enseignant trouvé dans les options de filtre\n";
} else {
    echo "👥 Enseignants trouvés dans les filtres:\n";
    foreach ($teacherOptions as $id => $name) {
        echo "   - ID {$id}: {$name}\n";
    }
    
    // Tester le filtrage par le premier enseignant
    $firstTeacherId = array_key_first($teacherOptions);
    $firstTeacherName = $teacherOptions[$firstTeacherId];
    
    echo "\n   Test du filtrage pour l'enseignant: {$firstTeacherName} (ID: {$firstTeacherId})\n";
    
    $filteredUrl = $assignmentsUrl . "?teacher_id=" . $firstTeacherId;
    $filteredPage = file_get_contents($filteredUrl);
    
    if ($filteredPage === false) {
        echo "   ❌ Erreur: Impossible d'accéder à la page filtrée\n";
    } else {
        echo "   ✅ Page filtrée accessible\n";
        
        // Vérifier que le filtre est pré-sélectionné
        if (strpos($filteredPage, 'selected') !== false) {
            echo "   ✅ Filtre pré-sélectionné\n";
        } else {
            echo "   ❌ Filtre non pré-sélectionné\n";
        }
        
        // Vérifier le message de filtrage actif
        if (strpos($filteredPage, 'Filtrage actif') !== false) {
            echo "   ✅ Message de filtrage actif affiché\n";
        } else {
            echo "   ❌ Message de filtrage actif manquant\n";
        }
    }
}

// Test 6: Test de la suppression avec cURL
echo "\n6. Test de la suppression avec cURL...\n";

if (!empty($assignmentIds)) {
    $deleteUrl = $baseUrl . "/admin/etudes/assignments/delete/" . $firstId;
    
    echo "   Test de la suppression pour l'assignation ID: {$firstId}\n";
    
    // Utiliser cURL pour tester la suppression
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $deleteUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Code HTTP: {$httpCode}\n";
    
    if ($httpCode == 200) {
        echo "   ✅ Route de suppression accessible\n";
    } elseif ($httpCode == 302 || $httpCode == 301) {
        echo "   ✅ Route de suppression redirige (normal pour la suppression)\n";
    } else {
        echo "   ❌ Route de suppression non accessible (Code: {$httpCode})\n";
    }
}

// Test 7: Vérification de la structure HTML des boutons d'action
echo "\n7. Vérification de la structure HTML des boutons d'action...\n";

// Vérifier la structure des boutons dans le tableau
if (strpos($mainPage, '<div class="buttons are-small">') !== false) {
    echo "✅ Structure des boutons d'action trouvée\n";
} else {
    echo "❌ Structure des boutons d'action manquante\n";
}

// Vérifier les icônes FontAwesome
if (strpos($mainPage, 'fas fa-eye') !== false) {
    echo "✅ Icône 'Vue' (fa-eye) trouvée\n";
} else {
    echo "❌ Icône 'Vue' (fa-eye) manquante\n";
}

if (strpos($mainPage, 'fas fa-edit') !== false) {
    echo "✅ Icône 'Édition' (fa-edit) trouvée\n";
} else {
    echo "❌ Icône 'Édition' (fa-edit) manquante\n";
}

if (strpos($mainPage, 'fas fa-trash') !== false) {
    echo "✅ Icône 'Suppression' (fa-trash) trouvée\n";
} else {
    echo "❌ Icône 'Suppression' (fa-trash) manquante\n";
}

echo "\n=== FIN DES TESTS ===\n";

// Résumé des problèmes identifiés
echo "\n=== RÉSUMÉ DES PROBLÈMES IDENTIFIÉS ===\n";

$problems = [];

if (strpos($mainPage, 'admin/etudes/assignments/view/') === false) {
    $problems[] = "Bouton 'Vue' manquant dans les actions";
}

if (strpos($mainPage, 'admin/etudes/assignments/edit/') === false) {
    $problems[] = "Bouton 'Édition' manquant dans les actions";
}

if (strpos($mainPage, 'admin/etudes/assignments/delete/') === false) {
    $problems[] = "Bouton 'Suppression' manquant dans les actions";
}

if (strpos($mainPage, 'deleteAssignment') === false) {
    $problems[] = "Fonction JavaScript 'deleteAssignment' manquante";
}

if (strpos($mainPage, 'sortTable') === false && strpos($mainPage, 'sortAssignments') === false) {
    $problems[] = "Fonction JavaScript de tri manquante";
}

if (empty($problems)) {
    echo "✅ Aucun problème majeur identifié\n";
} else {
    echo "❌ Problèmes identifiés:\n";
    foreach ($problems as $problem) {
        echo "   - {$problem}\n";
    }
}

echo "\n=== RECOMMANDATIONS ===\n";
echo "1. Vérifier que toutes les routes sont définies dans Routes.php\n";
echo "2. S'assurer que les méthodes view() et edit() existent dans le contrôleur\n";
echo "3. Implémenter la fonction JavaScript de tri si elle manque\n";
echo "4. Tester manuellement chaque bouton d'action\n";
echo "5. Vérifier la cohérence des URLs dans les vues\n";
?>





