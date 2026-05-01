<?php
// Test de la page corrigée
echo "=== TEST DE LA PAGE CORRIGÉE ===\n\n";

$url = "http://localhost:8080/admin/etudes/timetable";
$content = file_get_contents($url);

if ($content === false) {
    echo "❌ Erreur: Impossible de récupérer la page\n";
    exit;
}

// Vérifier que les corrections ont été appliquées
echo "1. Vérification des corrections appliquées...\n";

if (strpos($content, 'DOMContentLoaded') !== false) {
    echo "✅ DOMContentLoaded ajouté\n";
} else {
    echo "❌ DOMContentLoaded manquant\n";
}

if (strpos($content, 'console.log') !== false) {
    echo "✅ Console.log ajoutés pour le débogage\n";
} else {
    echo "❌ Console.log manquants\n";
}

if (strpos($content, 'Filtres initialisés avec succès') !== false) {
    echo "✅ Message de confirmation d'initialisation ajouté\n";
} else {
    echo "❌ Message de confirmation manquant\n";
}

// Vérifier la structure finale
echo "\n2. Vérification de la structure finale...\n";

// Extraire le JavaScript
preg_match('/<script>(.*?)<\/script>/s', $content, $scriptMatches);

if (isset($scriptMatches[1])) {
    $javascript = $scriptMatches[1];
    
    // Vérifier les améliorations
    $improvements = [
        'DOMContentLoaded' => 'Gestion du timing DOM',
        'console.log' => 'Logs de débogage',
        'Filtres initialisés avec succès' => 'Message de confirmation',
        'textContent.trim()' => 'Nettoyage des chaînes',
        'row.cells[0] ?' => 'Vérification de sécurité des cellules'
    ];
    
    foreach ($improvements as $feature => $description) {
        if (strpos($javascript, $feature) !== false) {
            echo "✅ {$description}: {$feature}\n";
        } else {
            echo "❌ {$description}: {$feature} manquant\n";
        }
    }
}

// Vérifier que les données sont toujours présentes
echo "\n3. Vérification des données...\n";

if (strpos($content, 'Test Enseignant') !== false) {
    echo "✅ 'Test Enseignant' toujours présent\n";
} else {
    echo "❌ 'Test Enseignant' manquant\n";
}

if (strpos($content, 'id="teacher_filter"') !== false) {
    echo "✅ Filtre enseignant toujours présent\n";
} else {
    echo "❌ Filtre enseignant manquant\n";
}

echo "\n=== FIN DU TEST ===\n";

echo "\n=== INSTRUCTIONS POUR TESTER ===\n";
echo "1. Ouvrez la page dans votre navigateur\n";
echo "2. Ouvrez la console développeur (F12)\n";
echo "3. Sélectionnez un enseignant dans le filtre\n";
echo "4. Vérifiez les logs dans la console\n";
echo "5. Vérifiez que le filtrage fonctionne\n";
?>





