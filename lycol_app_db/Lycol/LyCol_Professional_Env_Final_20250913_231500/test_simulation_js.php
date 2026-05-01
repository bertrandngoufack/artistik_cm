<?php
// Simulation de la logique JavaScript de filtrage
echo "=== SIMULATION DE LA LOGIQUE JAVASCRIPT ===\n\n";

// Données simulées (comme dans le JavaScript)
$teacherFilter = "15"; // Valeur sélectionnée dans le filtre
$selectedTeacherName = "Test Enseignant"; // Nom de l'enseignant sélectionné

echo "Filtre sélectionné: '{$teacherFilter}'\n";
echo "Nom de l'enseignant sélectionné: '{$selectedTeacherName}'\n\n";

// Simuler les données du tableau (comme dans le JavaScript)
$tableData = [
    [
        'class' => 'Test Actions Corrigées 2025-08-27 15:29:58',
        'subject' => 'Mathématiques',
        'teacher' => 'Test Enseignant',
        'day' => 'Lundi',
        'time' => '08:00:00 - 09:00:00',
        'duration' => '1h'
    ]
];

echo "Données du tableau:\n";
foreach ($tableData as $index => $row) {
    echo "Ligne {$index}:\n";
    echo "  Classe: '{$row['class']}'\n";
    echo "  Matière: '{$row['subject']}'\n";
    echo "  Enseignant: '{$row['teacher']}'\n";
    echo "  Jour: '{$row['day']}'\n";
    echo "  Horaire: '{$row['time']}'\n";
    echo "  Durée: '{$row['duration']}'\n\n";
}

// Simuler la logique de filtrage JavaScript
echo "=== SIMULATION DU FILTRAGE ===\n";

foreach ($tableData as $index => $row) {
    echo "Vérification de la ligne {$index}:\n";
    
    // Simuler la logique JavaScript
    $matchesTeacher = true;
    
    if ($teacherFilter) {
        // Simuler: const selectedTeacherOption = document.querySelector('#teacher_filter option[value="' + teacherFilter + '"]');
        // Simuler: const selectedTeacherName = selectedTeacherOption ? selectedTeacherOption.textContent : '';
        
        // Simuler: matchesTeacher = teacherCell.includes(selectedTeacherName);
        $teacherCell = $row['teacher'];
        $matchesTeacher = strpos($teacherCell, $selectedTeacherName) !== false;
        
        echo "  teacherFilter: '{$teacherFilter}'\n";
        echo "  selectedTeacherName: '{$selectedTeacherName}'\n";
        echo "  teacherCell: '{$teacherCell}'\n";
        echo "  teacherCell.includes(selectedTeacherName): " . ($matchesTeacher ? 'true' : 'false') . "\n";
        echo "  matchesTeacher: " . ($matchesTeacher ? 'true' : 'false') . "\n";
    }
    
    // Simuler: const shouldShow = matchesClass && matchesSubject && matchesTeacher && matchesDay;
    $shouldShow = $matchesTeacher; // Simplifié pour ce test
    
    echo "  shouldShow: " . ($shouldShow ? 'true' : 'false') . "\n";
    echo "  Résultat: " . ($shouldShow ? 'AFFICHER' : 'MASQUER') . "\n\n";
}

// Test de la méthode includes() en PHP
echo "=== TEST DE LA MÉTHODE INCLUDE EN PHP ===\n";

$testStrings = [
    'Test Enseignant',
    'Jean Dupont',
    'Marie Martin',
    'Test Enseignant Test',
    'Enseignant Test'
];

foreach ($testStrings as $testString) {
    $result = strpos($testString, $selectedTeacherName) !== false;
    echo "'{$testString}'.includes('{$selectedTeacherName}'): " . ($result ? 'true' : 'false') . "\n";
}

echo "\n=== FIN DE LA SIMULATION ===\n";
?>





