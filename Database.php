<?php
namespace RentCar\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    public static function init($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            throw new \Exception("Database not initialized. Call init() first.");
        }
        return self::$instance;
    }

    private function __construct($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Test la connexion
            $this->connection->query("SELECT 1");
            
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }
}