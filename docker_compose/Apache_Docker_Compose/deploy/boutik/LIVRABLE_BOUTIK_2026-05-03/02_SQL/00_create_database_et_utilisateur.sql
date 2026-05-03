-- =============================================================================
-- Boutik — création de la base et de l'utilisateur MariaDB (référence livrable)
-- Exécuter en tant que root (ou compte disposant de CREATE USER / GRANT).
-- Mots de passe : voir ANNEXE_IDENTIFIANTS_ET_MOTS_DE_PASSE.md
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `boutik_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'boutik_user'@'%' IDENTIFIED BY 'Boutik_Strong_Pass_2026!';
ALTER USER 'boutik_user'@'%' IDENTIFIED BY 'Boutik_Strong_Pass_2026!';

GRANT ALL PRIVILEGES ON `boutik_db`.* TO 'boutik_user'@'%';

FLUSH PRIVILEGES;

-- Pour n’autoriser que le sous-réseau Docker (exemple, à adapter) :
-- REVOKE ALL PRIVILEGES ON `boutik_db`.* FROM 'boutik_user'@'%';
-- CREATE USER IF NOT EXISTS 'boutik_user'@'172.%.%' IDENTIFIED BY 'Boutik_Strong_Pass_2026!';
-- GRANT ALL PRIVILEGES ON `boutik_db`.* TO 'boutik_user'@'172.%.%';
-- FLUSH PRIVILEGES;
