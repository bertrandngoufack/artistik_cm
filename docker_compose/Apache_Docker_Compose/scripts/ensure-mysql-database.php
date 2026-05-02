<?php
/**
 * Crée la base DB_NAME et accorde les droits à DB_USER (idempotent).
 * Nécessite : DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, DB_ROOT_PASSWORD.
 */
declare(strict_types=1);

$dbHost = getenv('DB_HOST') ?: 'mariadb';
$dbName = getenv('DB_NAME') ?: '';
$dbUser = getenv('DB_USER') ?: '';
$dbPass = getenv('DB_PASSWORD');
$rootPass = getenv('DB_ROOT_PASSWORD');

if ($dbName === '' || $dbUser === '' || $rootPass === false || $rootPass === '') {
	fwrite(STDERR, "ensure-mysql-database: variables DB_NAME, DB_USER ou DB_ROOT_PASSWORD manquantes.\n");
	exit(0);
}

if (! preg_match('/^[a-zA-Z0-9_]+$/', $dbName) || ! preg_match('/^[a-zA-Z0-9_]+$/', $dbUser)) {
	fwrite(STDERR, "ensure-mysql-database: nom de base ou utilisateur invalide.\n");
	exit(1);
}

$attempts = (int) (getenv('ENSURE_DB_RETRIES') ?: '15');
$sleepMs = (int) (getenv('ENSURE_DB_SLEEP_MS') ?: '2000');

$dsnRoot = sprintf('mysql:host=%s;charset=utf8mb4', $dbHost);

for ($i = 1; $i <= $attempts; $i++) {
	try {
		$pdo = new PDO(
			$dsnRoot,
			'root',
			(string) $rootPass,
			[ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
		);

		$safeDb = str_replace('`', '', $dbName);

		$pdo->exec(
			"CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
		);

		if ( $dbPass !== false && $dbPass !== '' ) {
			$pdo->exec(
				'CREATE USER IF NOT EXISTS ' . $pdo->quote( $dbUser ) . '@' . $pdo->quote( '%' )
				. ' IDENTIFIED BY ' . $pdo->quote( (string) $dbPass )
			);
		}

		$grantTo = $pdo->quote( $dbUser ) . '@' . $pdo->quote( '%' );
		$pdo->exec("GRANT ALL PRIVILEGES ON `{$safeDb}`.* TO {$grantTo}");

		try {
			if ($dbPass !== false && $dbPass !== '') {
				$pdo->exec(
					'ALTER USER ' . $pdo->quote($dbUser) . '@' . $pdo->quote('%')
					. ' IDENTIFIED BY ' . $pdo->quote((string) $dbPass)
				);
			}
		} catch (Throwable $e) {
			fwrite(STDERR, 'ensure-mysql-database: ALTER USER (mot de passe) — ' . $e->getMessage() . "\n");
		}

		$pdo->exec('FLUSH PRIVILEGES');

		echo "ensure-mysql-database: base « {$dbName} » prête pour « {$dbUser} ».\n";
		exit(0);
	} catch (Throwable $e) {
		fwrite(STDERR, "ensure-mysql-database: tentative {$i}/{$attempts}: {$e->getMessage()}\n");
		if ($i >= $attempts) {
			exit(1);
		}
		usleep($sleepMs * 1000);
	}
}

exit(1);
