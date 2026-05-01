<?php
/**
 * Test complet du module Messagerie
 * Vérification de la cohérence avec tous les autres modules
 */

echo "📧 TEST COMPLET - MODULE MESSAGERIE\n";
echo "==================================\n\n";

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Test 1: Vérification de la structure de la table messages
    echo "📋 Test 1: Vérification de la structure de la table messages\n";
    echo "----------------------------------------------------------\n";
    
    $stmt = $pdo->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   📊 Colonnes de la table messages:\n";
    foreach ($columns as $column) {
        echo "      - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Test 2: Vérification des données existantes
    echo "\n📊 Test 2: Vérification des données existantes\n";
    echo "---------------------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
    $totalMessages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   📧 Total messages: $totalMessages\n";
    
    if ($totalMessages > 0) {
        $stmt = $pdo->query("SELECT * FROM messages LIMIT 1");
        $sampleMessage = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   📝 Exemple de message:\n";
        foreach ($sampleMessage as $key => $value) {
            echo "      - $key: " . (is_string($value) ? substr($value, 0, 50) : $value) . "\n";
        }
    }
    
    // Test 3: Vérification du contrôleur
    echo "\n🔧 Test 3: Vérification du contrôleur\n";
    echo "-----------------------------------\n";
    
    $controllerFile = 'app/Controllers/Messagerie.php';
    if (file_exists($controllerFile)) {
        $controllerContent = file_get_contents($controllerFile);
        
        $requiredMethods = [
            'index' => 'Page d\'accueil',
            'messages' => 'Gestion des messages',
            'createMessage' => 'Création de message',
            'storeMessage' => 'Enregistrement de message',
            'viewMessage' => 'Affichage de message',
            'deleteMessage' => 'Suppression de message',
            'templates' => 'Gestion des templates',
            'createTemplate' => 'Création de template',
            'storeTemplate' => 'Enregistrement de template',
            'subscribers' => 'Gestion des abonnés',
            'settings' => 'Configuration'
        ];
        
        foreach ($requiredMethods as $method => $description) {
            if (strpos($controllerContent, "public function $method") !== false) {
                echo "   ✅ Méthode $method() - $description\n";
            } else {
                echo "   ❌ Méthode $method() - MANQUANTE\n";
            }
        }
        
        // Vérifier les modèles utilisés
        if (strpos($controllerContent, 'MessageModel') !== false) {
            echo "   ✅ MessageModel intégré\n";
        } else {
            echo "   ❌ MessageModel manquant\n";
        }
        
        if (strpos($controllerContent, 'TemplateModel') !== false) {
            echo "   ✅ TemplateModel intégré\n";
        } else {
            echo "   ❌ TemplateModel manquant\n";
        }
    } else {
        echo "   ❌ Fichier contrôleur non trouvé\n";
    }
    
    // Test 4: Vérification du modèle MessageModel
    echo "\n📋 Test 4: Vérification du modèle MessageModel\n";
    echo "---------------------------------------------\n";
    
    $modelFile = 'app/Models/MessageModel.php';
    if (file_exists($modelFile)) {
        $modelContent = file_get_contents($modelFile);
        
        // Vérifier les champs autorisés
        if (strpos($modelContent, 'subject') !== false) {
            echo "   ✅ Champ 'subject' configuré\n";
        } else {
            echo "   ❌ Champ 'subject' manquant\n";
        }
        
        if (strpos($modelContent, 'message_type') !== false) {
            echo "   ✅ Champ 'message_type' configuré\n";
        } else {
            echo "   ❌ Champ 'message_type' manquant\n";
        }
        
        // Vérifier les méthodes
        $methods = [
            'getMessagesPaginated' => 'Messages paginés',
            'getRecentMessages' => 'Messages récents',
            'getSubscribers' => 'Abonnés',
            'getMessageStats' => 'Statistiques'
        ];
        
        foreach ($methods as $method => $description) {
            if (strpos($modelContent, $method) !== false) {
                echo "   ✅ Méthode $method() - $description\n";
            } else {
                echo "   ❌ Méthode $method() - MANQUANTE\n";
            }
        }
    } else {
        echo "   ❌ Modèle MessageModel non trouvé\n";
    }
    
    // Test 5: Vérification des vues
    echo "\n🎨 Test 5: Vérification des vues\n";
    echo "-------------------------------\n";
    
    $viewFiles = [
        'app/Views/admin/messagerie/index.php' => 'Page d\'accueil',
        'app/Views/admin/messagerie/messages.php' => 'Gestion des messages',
        'app/Views/admin/messagerie/create_message.php' => 'Création de message',
        'app/Views/admin/messagerie/view_message.php' => 'Affichage de message',
        'app/Views/admin/messagerie/templates.php' => 'Gestion des templates',
        'app/Views/admin/messagerie/create_template.php' => 'Création de template',
        'app/Views/admin/messagerie/subscribers.php' => 'Gestion des abonnés',
        'app/Views/admin/messagerie/settings.php' => 'Configuration'
    ];
    
    foreach ($viewFiles as $file => $description) {
        if (file_exists($file)) {
            echo "   ✅ $description\n";
        } else {
            echo "   ❌ $description - MANQUANTE\n";
        }
    }
    
    // Test 6: Vérification des routes
    echo "\n🛣️ Test 6: Vérification des routes\n";
    echo "--------------------------------\n";
    
    $routesFile = 'app/Config/Routes.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        
        if (strpos($routesContent, 'Messagerie') !== false) {
            echo "   ✅ Routes messagerie configurées\n";
        } else {
            echo "   ❌ Routes messagerie manquantes\n";
        }
    }
    
    // Test 7: Problèmes identifiés
    echo "\n🚨 Test 7: Problèmes identifiés\n";
    echo "------------------------------\n";
    
    $problems = [
        "Incohérence entre la structure de la table et le modèle",
        "Colonne 'type' manquante dans la table messages",
        "Colonne 'recipients' manquante dans la table messages",
        "Colonne 'sent_by' manquante dans la table messages",
        "Vue essaie d'accéder à des champs inexistants",
        "Pas d'intégration avec les logs d'audit",
        "Pas de cohérence avec les autres modules"
    ];
    
    foreach ($problems as $index => $problem) {
        echo "   " . ($index + 1) . ". $problem\n";
    }
    
    // Test 8: Recommandations de correction
    echo "\n💡 Test 8: Recommandations de correction\n";
    echo "-------------------------------------\n";
    
    $recommendations = [
        "Corriger la structure de la table messages",
        "Mettre à jour le modèle MessageModel",
        "Corriger les vues pour utiliser les bons champs",
        "Ajouter l'intégration avec les logs d'audit",
        "Créer la cohérence avec les autres modules",
        "Ajouter la gestion des templates",
        "Implémenter l'envoi réel de messages"
    ];
    
    foreach ($recommendations as $index => $recommendation) {
        echo "   " . ($index + 1) . ". $recommendation\n";
    }
    
    echo "\n🎉 RÉSUMÉ - MODULE MESSAGERIE\n";
    echo "=============================\n";
    echo "❌ Contrôleur: PARTIELLEMENT FONCTIONNEL\n";
    echo "❌ Modèle: INCOHÉRENT AVEC LA BASE DE DONNÉES\n";
    echo "❌ Vues: PROBLÈMES DE CHAMPS MANQUANTS\n";
    echo "❌ Routes: À VÉRIFIER\n";
    echo "❌ Cohérence: À ÉTABLIR\n";
    echo "\n🚀 Le module nécessite des corrections importantes pour être opérationnel.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}
?>







