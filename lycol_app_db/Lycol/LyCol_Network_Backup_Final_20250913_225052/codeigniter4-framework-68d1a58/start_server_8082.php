<?php
/**
 * Script de démarrage optimisé pour KISSAI SCHOOL sur le port 8080
 */

echo "🚀 DÉMARRAGE DE KISSAI SCHOOL SUR LE PORT 8080\n";
echo "==============================================\n\n";

// Vérifier si le port 8080 est disponible
echo "🔍 Vérification du port 8080...\n";
$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "❌ Erreur lors de la création du socket\n";
    exit(1);
}

$result = @socket_bind($socket, '0.0.0.0', 8080);
if ($result === false) {
    echo "❌ Le port 8080 est déjà utilisé\n";
    socket_close($socket);
    exit(1);
}

socket_close($socket);
echo "✅ Port 8080 disponible\n\n";

// Arrêter tous les processus PHP existants
echo "🔄 Arrêt des processus existants...\n";
shell_exec("pkill -f 'php -S' 2>/dev/null");
shell_exec("pkill -f 'php spark serve' 2>/dev/null");
sleep(2);

// Vérifier la configuration
echo "🔧 Vérification de la configuration...\n";

$configFiles = [
    '.env' => 'Fichier .env',
    'app/Config/App.php' => 'Configuration App.php',
    'public/router.php' => 'Routeur personnalisé'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: Présent\n";
    } else {
        echo "❌ $description: Manquant\n";
        exit(1);
    }
}

// Vérifier que le port 8080 est configuré
$envContent = file_get_contents('.env');
if (strpos($envContent, '8080') === false) {
    echo "❌ Port 8080 non configuré dans .env\n";
    exit(1);
}
echo "✅ Port 8080 configuré dans .env\n\n";

// Démarrer le serveur avec le routeur personnalisé
echo "🌐 Démarrage du serveur PHP...\n";
echo "📡 URL: http://localhost:8080\n";
echo "🛑 Appuyez sur Ctrl+C pour arrêter\n\n";

// Commande de démarrage
$command = 'php -S 0.0.0.0:8080 -t public public/router.php';

// Démarrer le serveur
passthru($command);
?>
