<?php

namespace App\Services;

use PDO;

/**
 * Service de base de données pour centraliser la configuration
 */
class DatabaseService
{
    private static $instance = null;
    private $pdo = null;
    
    private function __construct()
    {
        // Configuration de base de données depuis les variables d'environnement
        $host = getenv('database.default.hostname') ?: '100.69.65.33';
        $port = getenv('database.default.port') ?: '13306';
        $dbname = getenv('database.default.database') ?: 'lycol_db';
        $username = getenv('database.default.username') ?: 'root';
        $password = getenv('database.default.password') ?: 'Bateau123';
        
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtenir l'instance singleton
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
    
    /**
     * Exécuter une requête préparée
     */
    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log("Erreur lors de l'exécution de la requête: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Récupérer une ligne
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer plusieurs lignes
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Insérer une ligne
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->executeQuery($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Mettre à jour une ligne
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "$column = :$column";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer une ligne
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
}
