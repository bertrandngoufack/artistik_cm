<?php
// Script pour ajouter la table settings si elle n'existe pas

try {
    $pdo = new PDO(
        'mysql:host=100.69.65.33;port=13306;dbname=lycol_db;charset=utf8mb4',
        'root',
        'Bateau123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier si la table settings existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "❌ Table 'settings' n'existe pas. Création en cours...\n";
        
        // Créer la table settings
        $sql = "CREATE TABLE settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('STRING', 'INTEGER', 'BOOLEAN', 'JSON') DEFAULT 'STRING',
            description TEXT,
            module VARCHAR(50),
            is_public TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Table 'settings' créée avec succès\n";
        
        // Insérer les paramètres par défaut
        $defaultSettings = [
            ['app.name', 'KISSAI SCHOOL', 'STRING', 'Nom de l\'établissement', 'app', 1],
            ['app.version', '1.0.0', 'STRING', 'Version de l\'application', 'app', 1],
            ['app.debug', 'false', 'BOOLEAN', 'Mode debug', 'app', 1],
            ['app.timezone', 'Africa/Douala', 'STRING', 'Fuseau horaire', 'app', 1],
            ['app.language', 'fr', 'STRING', 'Langue par défaut', 'app', 1],
            ['smtp.host', 'localhost', 'STRING', 'Serveur SMTP', 'smtp', 0],
            ['smtp.port', '587', 'INTEGER', 'Port SMTP', 'smtp', 0],
            ['smtp.username', '', 'STRING', 'Nom d\'utilisateur SMTP', 'smtp', 0],
            ['smtp.password', '', 'STRING', 'Mot de passe SMTP', 'smtp', 0],
            ['smtp.encryption', 'tls', 'STRING', 'Chiffrement SMTP', 'smtp', 0],
            ['smtp.from_email', 'noreply@kissai-school.com', 'STRING', 'Email d\'expédition', 'smtp', 0],
            ['smtp.from_name', 'KISSAI SCHOOL', 'STRING', 'Nom d\'expédition', 'smtp', 0],
            ['sms.provider', 'default', 'STRING', 'Fournisseur SMS', 'sms', 0],
            ['sms.api_key', '', 'STRING', 'Clé API SMS', 'sms', 0],
            ['sms.api_secret', '', 'STRING', 'Secret API SMS', 'sms', 0],
            ['sms.sender_id', 'KISSAI', 'STRING', 'ID expéditeur SMS', 'sms', 0],
            ['whatsapp.provider', 'default', 'STRING', 'Fournisseur WhatsApp', 'whatsapp', 0],
            ['whatsapp.api_key', '', 'STRING', 'Clé API WhatsApp', 'whatsapp', 0],
            ['whatsapp.api_secret', '', 'STRING', 'Secret API WhatsApp', 'whatsapp', 0],
            ['whatsapp.phone_number', '', 'STRING', 'Numéro WhatsApp', 'whatsapp', 0],
            ['license.secret_seed', 'KISSAI_SECRET_KEY_2025', 'STRING', 'Clé secrète pour les licences', 'license', 0],
            ['license.trial_duration', '90', 'INTEGER', 'Durée d\'essai en jours', 'license', 0],
            ['license.max_users', '1000', 'INTEGER', 'Nombre maximum d\'utilisateurs', 'license', 0],
            ['license.features', '[\"economat\",\"scolarite\",\"etudes\",\"examens\",\"statistiques\",\"bibliotheque\",\"messagerie\",\"securite\"]', 'JSON', 'Fonctionnalités activées', 'license', 0]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description, module, is_public) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        echo "✅ Paramètres par défaut insérés avec succès\n";
        
    } else {
        echo "✅ Table 'settings' existe déjà\n";
        
        // Vérifier s'il y a des paramètres
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "⚠️  Table 'settings' vide. Insertion des paramètres par défaut...\n";
            
            // Insérer les paramètres par défaut
            $defaultSettings = [
                ['app.name', 'KISSAI SCHOOL', 'STRING', 'Nom de l\'établissement', 'app', 1],
                ['app.version', '1.0.0', 'STRING', 'Version de l\'application', 'app', 1],
                ['app.debug', 'false', 'BOOLEAN', 'Mode debug', 'app', 1],
                ['app.timezone', 'Africa/Douala', 'STRING', 'Fuseau horaire', 'app', 1],
                ['app.language', 'fr', 'STRING', 'Langue par défaut', 'app', 1],
                ['smtp.host', 'localhost', 'STRING', 'Serveur SMTP', 'smtp', 0],
                ['smtp.port', '587', 'INTEGER', 'Port SMTP', 'smtp', 0],
                ['smtp.username', '', 'STRING', 'Nom d\'utilisateur SMTP', 'smtp', 0],
                ['smtp.password', '', 'STRING', 'Mot de passe SMTP', 'smtp', 0],
                ['smtp.encryption', 'tls', 'STRING', 'Chiffrement SMTP', 'smtp', 0],
                ['smtp.from_email', 'noreply@kissai-school.com', 'STRING', 'Email d\'expédition', 'smtp', 0],
                ['smtp.from_name', 'KISSAI SCHOOL', 'STRING', 'Nom d\'expédition', 'smtp', 0],
                ['sms.provider', 'default', 'STRING', 'Fournisseur SMS', 'sms', 0],
                ['sms.api_key', '', 'STRING', 'Clé API SMS', 'sms', 0],
                ['sms.api_secret', '', 'STRING', 'Secret API SMS', 'sms', 0],
                ['sms.sender_id', 'KISSAI', 'STRING', 'ID expéditeur SMS', 'sms', 0],
                ['whatsapp.provider', 'default', 'STRING', 'Fournisseur WhatsApp', 'whatsapp', 0],
                ['whatsapp.api_key', '', 'STRING', 'Clé API WhatsApp', 'whatsapp', 0],
                ['whatsapp.api_secret', '', 'STRING', 'Secret API WhatsApp', 'whatsapp', 0],
                ['whatsapp.phone_number', '', 'STRING', 'Numéro WhatsApp', 'whatsapp', 0],
                ['license.secret_seed', 'KISSAI_SECRET_KEY_2025', 'STRING', 'Clé secrète pour les licences', 'license', 0],
                ['license.trial_duration', '90', 'INTEGER', 'Durée d\'essai en jours', 'license', 0],
                ['license.max_users', '1000', 'INTEGER', 'Nombre maximum d\'utilisateurs', 'license', 0],
                ['license.features', '[\"economat\",\"scolarite\",\"etudes\",\"examens\",\"statistiques\",\"bibliotheque\",\"messagerie\",\"securite\"]', 'JSON', 'Fonctionnalités activées', 'license', 0]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description, module, is_public) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($defaultSettings as $setting) {
                $stmt->execute($setting);
            }
            
            echo "✅ Paramètres par défaut insérés avec succès\n";
        } else {
            echo "✅ Table 'settings' contient déjà {$count} paramètres\n";
        }
    }
    
    echo "\n🎉 Script terminé avec succès !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>
