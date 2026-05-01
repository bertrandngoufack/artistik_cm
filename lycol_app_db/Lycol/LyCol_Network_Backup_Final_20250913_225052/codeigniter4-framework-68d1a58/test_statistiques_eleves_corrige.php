<?php

/**
 * TEST DES STATISTIQUES ÉLÈVES CORRIGÉES
 * Vérification après correction du problème "Total des élèves est absent"
 */

echo "🔍 TEST DES STATISTIQUES ÉLÈVES CORRIGÉES\n";
echo "==========================================\n\n";

$baseUrl = 'http://localhost:8080';

// Test de la page des classes
echo "📊 VÉRIFICATION DES STATISTIQUES\n";
echo "--------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/etudes/classes');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page accessible (HTTP $httpCode)\n";
    
    // Vérifier les statistiques
    $stats = [];
    
    // Total Classes
    if (preg_match('/Total Classes.*?title has-text-white">(\d+)</', $response, $matches)) {
        $stats['total_classes'] = $matches[1];
        echo "✅ Total Classes: {$stats['total_classes']}\n";
    } else {
        echo "❌ Total Classes: Non trouvé\n";
    }
    
    // Classes Actives
    if (preg_match('/Classes Actives.*?title has-text-white">(\d+)</', $response, $matches)) {
        $stats['active_classes'] = $matches[1];
        echo "✅ Classes Actives: {$stats['active_classes']}\n";
    } else {
        echo "❌ Classes Actives: Non trouvé\n";
    }
    
    // Total Élèves (PROBLÈME CORRIGÉ)
    if (preg_match('/Total Élèves.*?title has-text-white">(\d+)</', $response, $matches)) {
        $stats['total_students'] = $matches[1];
        echo "✅ Total Élèves: {$stats['total_students']} (CORRIGÉ)\n";
    } else {
        echo "❌ Total Élèves: Non trouvé (PROBLÈME PERSISTANT)\n";
    }
    
    // Cycles
    if (preg_match('/Cycles.*?title has-text-white">(\d+)</', $response, $matches)) {
        $stats['total_cycles'] = $matches[1];
        echo "✅ Cycles: {$stats['total_cycles']}\n";
    } else {
        echo "❌ Cycles: Non trouvé\n";
    }
    
    echo "\n📈 ANALYSE DES STATISTIQUES:\n";
    echo "-----------------------------\n";
    
    // Vérifier la cohérence
    if (isset($stats['total_students']) && $stats['total_students'] > 0) {
        echo "✅ PROBLÈME RÉSOLU: Total des élèves affiche maintenant {$stats['total_students']}\n";
    } else {
        echo "❌ PROBLÈME PERSISTANT: Total des élèves toujours à 0\n";
    }
    
    if (isset($stats['total_classes']) && isset($stats['active_classes'])) {
        if ($stats['total_classes'] == $stats['active_classes']) {
            echo "✅ Cohérence: Toutes les classes sont actives\n";
        } else {
            echo "⚠️ Incohérence: {$stats['total_classes']} classes totales vs {$stats['active_classes']} actives\n";
        }
    }
    
    if (isset($stats['total_students']) && isset($stats['total_classes'])) {
        $avgStudentsPerClass = $stats['total_students'] > 0 ? round($stats['total_students'] / $stats['total_classes'], 1) : 0;
        echo "📊 Moyenne d'élèves par classe: {$avgStudentsPerClass}\n";
    }
    
} else {
    echo "❌ Page inaccessible (HTTP $httpCode)\n";
}

echo "\n🔧 CORRECTION APPLIQUÉE:\n";
echo "------------------------\n";
echo "Problème: Le contrôleur cherchait 'total_students' mais la méthode retournait 'total'\n";
echo "Solution: Modification de la ligne dans app/Controllers/Etudes.php\n";
echo "AVANT: 'total_students' => \$this->studentModel->getStudentStats()['total_students'] ?? 0,\n";
echo "APRÈS: 'total_students' => \$this->studentModel->getStudentStats()['total'] ?? 0,\n";

echo "\n🌐 Interface accessible sur: {$baseUrl}/admin/etudes/classes\n";
echo "📋 Test terminé le: " . date('Y-m-d H:i:s') . "\n";


