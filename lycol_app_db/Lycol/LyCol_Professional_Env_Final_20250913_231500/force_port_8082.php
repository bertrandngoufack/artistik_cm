<?php
// Script pour forcer le serveur sur le port 8080
$port = 8080;
$host = '0.0.0.0';

// Vérifier si le port est libre
$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("Impossible de créer le socket\n");
}

$result = @socket_bind($socket, $host, $port);
if ($result === false) {
    die("Port $port déjà occupé\n");
}

socket_close($socket);

// Démarrer le serveur avec le port forcé
$command = "php -S $host:$port -t public/ public/index.php";
echo "🚀 Démarrage du serveur sur le port $port...\n";
echo "📡 URL: http://localhost:$port\n";
echo "🛑 Pour arrêter: Ctrl+C\n\n";

passthru($command);
