<?php
/**
 * Script de finalisation de tous les modules KISSAI SCHOOL
 * Crée des données cohérentes pour tous les modules
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage() . "\n");
}

echo "\n🚀 Début de la finalisation des modules KISSAI SCHOOL\n";
echo "================================================\n\n";

// 1. Configuration générale
echo "1. Configuration générale...\n";
$pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, is_public, description) VALUES
('school_name', 'KISSAI SCHOOL', 'string', 1, 'Nom de l\'établissement'),
('school_address', 'Douala, Cameroun', 'string', 1, 'Adresse de l\'établissement'),
('school_phone', '+237 233 123 456', 'string', 1, 'Téléphone de l\'établissement'),
('school_email', 'contact@kissai-school.cm', 'string', 1, 'Email de l\'établissement'),
('current_academic_year', '2024-2025', 'string', 1, 'Année scolaire en cours'),
('school_logo', '/assets/images/logo.png', 'string', 1, 'Logo de l\'établissement'),
('currency', 'FCFA', 'string', 1, 'Devise utilisée'),
('timezone', 'Africa/Douala', 'string', 1, 'Fuseau horaire'),
('smtp_host', 'smtp.gmail.com', 'string', 0, 'Serveur SMTP'),
('smtp_port', '587', 'integer', 0, 'Port SMTP'),
('smtp_username', 'noreply@kissai-school.cm', 'string', 0, 'Nom d\'utilisateur SMTP'),
('smtp_password', 'password123', 'string', 0, 'Mot de passe SMTP'),
('sms_provider', 'twilio', 'string', 0, 'Fournisseur SMS'),
('whatsapp_api_key', 'your_whatsapp_key', 'string', 0, 'Clé API WhatsApp'),
('license_secret_seed', 'KISSAI_SECRET_2024', 'string', 0, 'Clé secrète pour les licences')
");
echo "✅ Configuration générale terminée\n\n";

// 2. Cycles et niveaux éducatifs
echo "2. Cycles et niveaux éducatifs...\n";
$pdo->exec("INSERT IGNORE INTO cycles (name, code, description) VALUES
('Maternelle', 'MAT', 'Cycle maternel (3-6 ans)'),
('Primaire', 'PRI', 'Cycle primaire (6-12 ans)'),
('Secondaire', 'SEC', 'Cycle secondaire (12-18 ans)'),
('Supérieur', 'SUP', 'Cycle supérieur (18+ ans)')
");

$pdo->exec("INSERT IGNORE INTO levels (name, code, cycle_id, description) VALUES
('Petite Section', 'PS', 1, 'PS - 3 ans'),
('Moyenne Section', 'MS', 1, 'MS - 4 ans'),
('Grande Section', 'GS', 1, 'GS - 5 ans'),
('CP', 'CP', 2, 'Cours Préparatoire'),
('CE1', 'CE1', 2, 'Cours Élémentaire 1'),
('CE2', 'CE2', 2, 'Cours Élémentaire 2'),
('CM1', 'CM1', 2, 'Cours Moyen 1'),
('CM2', 'CM2', 2, 'Cours Moyen 2'),
('6ème', '6E', 3, 'Sixième'),
('5ème', '5E', 3, 'Cinquième'),
('4ème', '4E', 3, 'Quatrième'),
('3ème', '3E', 3, 'Troisième'),
('2nde', '2ND', 3, 'Seconde'),
('1ère', '1ER', 3, 'Première'),
('Tle', 'TLE', 3, 'Terminale')
");
echo "✅ Cycles et niveaux créés\n\n";

// 3. Matières
echo "3. Matières...\n";
$pdo->exec("INSERT IGNORE INTO subjects (name, code, description, coefficient) VALUES
('Mathématiques', 'MATH', 'Mathématiques', 4),
('Français', 'FR', 'Français', 3),
('Anglais', 'ENG', 'Anglais', 2),
('Histoire-Géographie', 'HIST', 'Histoire-Géographie', 2),
('Sciences de la Vie et de la Terre', 'SVT', 'Sciences de la Vie et de la Terre', 2),
('Physique-Chimie', 'PC', 'Physique-Chimie', 3),
('Éducation Physique et Sportive', 'EPS', 'Éducation Physique et Sportive', 1),
('Arts Plastiques', 'ART', 'Arts Plastiques', 1),
('Musique', 'MUS', 'Musique', 1),
('Informatique', 'INFO', 'Informatique', 1)
");
echo "✅ Matières créées\n\n";

echo "🎉 Finalisation de la base terminée !\n";
echo "Continuez avec le script suivant pour les données spécifiques aux modules.\n";
?>
