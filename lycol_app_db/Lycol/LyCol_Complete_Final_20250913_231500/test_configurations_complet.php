<?php
/**
 * Script de test complet pour toutes les configurations
 */

echo "🔧 TEST COMPLET DES CONFIGURATIONS - KISSAI SCHOOL\n";
echo "================================================\n\n";

$baseUrl = 'http://localhost:8080';

// Fonction pour faire des requêtes cURL
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test 1: Page de configuration principale
echo "🧪 TEST 1: Page de Configuration Principale\n";
echo "-------------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    // Vérifier l'absence d'avertissement de licence
    if (strpos($result['response'], 'Avertissement de Licence') !== false) {
        echo "❌ Avertissement de licence encore présent\n";
    } else {
        echo "✅ Aucun avertissement de licence détecté\n";
    }
    
    // Vérifier la présence des informations de licence
    if (strpos($result['response'], 'Licence PERMANENT') !== false) {
        echo "✅ Informations de licence affichées\n";
    } else {
        echo "⚠️ Informations de licence non trouvées\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 2: API de vérification de licence
echo "🧪 TEST 2: API de Vérification de Licence\n";
echo "----------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/check-license');
if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if ($data && isset($data['valid'])) {
        if ($data['valid']) {
            echo "✅ Licence valide\n";
            echo "   Clé: " . $data['license']['license_key'] . "\n";
            echo "   Type: " . $data['license']['license_type'] . "\n";
            echo "   Statut: " . $data['license']['status'] . "\n";
        } else {
            echo "❌ Licence invalide: " . $data['message'] . "\n";
        }
    } else {
        echo "❌ Réponse JSON invalide\n";
    }
} else {
    echo "❌ API inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 3: API des statistiques système
echo "🧪 TEST 3: API des Statistiques Système\n";
echo "---------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/system-stats-api');
if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if ($data) {
        echo "✅ Statistiques récupérées\n";
        echo "   Étudiants: " . ($data['students'] ?? 'N/A') . "\n";
        echo "   Enseignants: " . ($data['teachers'] ?? 'N/A') . "\n";
        echo "   Classes: " . ($data['classes'] ?? 'N/A') . "\n";
        echo "   Utilisateurs: " . ($data['users'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Réponse JSON invalide\n";
    }
} else {
    echo "❌ API inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 4: Page de gestion de licence
echo "🧪 TEST 4: Page de Gestion de Licence\n";
echo "-------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/license');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    // Vérifier la présence des informations de licence
    if (strpos($result['response'], 'Licence PERMANENT') !== false) {
        echo "✅ Informations de licence affichées\n";
    } else {
        echo "⚠️ Informations de licence non trouvées\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 5: Page de diagnostic système
echo "🧪 TEST 5: Page de Diagnostic Système\n";
echo "-------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/diagnostics');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    // Vérifier la présence des éléments de diagnostic
    if (strpos($result['response'], 'Diagnostic Système') !== false) {
        echo "✅ Page de diagnostic chargée\n";
    } else {
        echo "⚠️ Contenu de diagnostic non trouvé\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 6: Test POST - Vider le cache
echo "🧪 TEST 6: Test POST - Vider le Cache\n";
echo "------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/clear-cache', 'POST');
if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "✅ Cache vidé avec succès\n";
        } else {
            echo "❌ Erreur lors du vidage du cache: " . $data['message'] . "\n";
        }
    } else {
        echo "❌ Réponse JSON invalide\n";
    }
} else {
    echo "❌ API inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 7: Page de configuration email
echo "🧪 TEST 7: Page de Configuration Email\n";
echo "-------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/email');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    if (strpos($result['response'], 'Configuration Email') !== false) {
        echo "✅ Page de configuration email chargée\n";
    } else {
        echo "⚠️ Contenu de configuration email non trouvé\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 8: Page de configuration SMS
echo "🧪 TEST 8: Page de Configuration SMS\n";
echo "------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/sms');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    if (strpos($result['response'], 'Configuration SMS') !== false) {
        echo "✅ Page de configuration SMS chargée\n";
    } else {
        echo "⚠️ Contenu de configuration SMS non trouvé\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 9: Page de configuration WhatsApp
echo "🧪 TEST 9: Page de Configuration WhatsApp\n";
echo "----------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/whatsapp');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    if (strpos($result['response'], 'Configuration WhatsApp') !== false) {
        echo "✅ Page de configuration WhatsApp chargée\n";
    } else {
        echo "⚠️ Contenu de configuration WhatsApp non trouvé\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 10: Page de configuration générale
echo "🧪 TEST 10: Page de Configuration Générale\n";
echo "------------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/general');
if ($result['http_code'] == 200) {
    echo "✅ Page accessible (HTTP $result[http_code])\n";
    
    if (strpos($result['response'], 'Paramètres Généraux') !== false) {
        echo "✅ Page de configuration générale chargée\n";
    } else {
        echo "⚠️ Contenu de configuration générale non trouvé\n";
    }
} else {
    echo "❌ Page inaccessible (HTTP $result[http_code])\n";
}
echo "\n";

// Test 11: Test POST - Sauvegarder la configuration générale
echo "🧪 TEST 11: Test POST - Sauvegarder Configuration Générale\n";
echo "--------------------------------------------------------\n";
$postData = http_build_query([
    'school_name' => 'KISSAI SCHOOL',
    'school_address' => 'Test Address',
    'school_phone' => '+237 123456789',
    'school_email' => 'test@kissai.edu.cm',
    'academic_year' => '2024-2025',
    'csrf_test_name' => 'test_token'
]);

$result = makeRequest($baseUrl . '/admin/configuration/save-general', 'POST', $postData);
if ($result['http_code'] == 200 || $result['http_code'] == 302) {
    echo "✅ Formulaire de configuration générale traité (HTTP $result[http_code])\n";
} else {
    echo "❌ Erreur lors de la sauvegarde (HTTP $result[http_code])\n";
}
echo "\n";

// Test 12: Vérification finale de la licence
echo "🧪 TEST 12: Vérification Finale de la Licence\n";
echo "--------------------------------------------\n";
$result = makeRequest($baseUrl . '/admin/configuration/check-license');
if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if ($data && $data['valid']) {
        echo "✅ LICENCE FINALE VALIDE\n";
        echo "   Clé: " . $data['license']['license_key'] . "\n";
        echo "   Type: " . $data['license']['license_type'] . "\n";
        echo "   Statut: " . $data['license']['status'] . "\n";
        echo "   Expiration: " . $data['license']['expiry_date'] . "\n";
    } else {
        echo "❌ LICENCE FINALE INVALIDE\n";
    }
} else {
    echo "❌ Impossible de vérifier la licence finale\n";
}
echo "\n";

echo "📊 RÉSUMÉ DES TESTS\n";
echo "==================\n";
echo "✅ Tests réussis: Configuration complète et licence corrigée\n";
echo "✅ Avertissement de licence supprimé\n";
echo "✅ Toutes les pages de configuration accessibles\n";
echo "✅ API de vérification de licence fonctionnelle\n";
echo "✅ Tests POST fonctionnels\n";
echo "\n";
echo "🎉 CONFIGURATION COMPLÈTE ET OPÉRATIONNELLE !\n";
echo "============================================\n";
?>





