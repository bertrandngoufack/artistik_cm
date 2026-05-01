<?php
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASSWORD');

echo "<h1>✅ Stack Docker fonctionne !</h1>";
echo "<h2>Configuration:</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Extensions: " . implode(', ', get_loaded_extensions()) . "</li>";
echo "<li>DB Host: $db_host</li>";
echo "<li>DB Name: $db_name</li>";
echo "</ul>";

// Test connexion DB
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    echo "<p style='color:green'>✅ Connexion à MariaDB réussie !</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erreur DB: " . $e->getMessage() . "</p>";
}

phpinfo();
?>