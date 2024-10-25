<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {

    private $host, $db, $user, $pass;
    private $charset = 'utf8mb4';
    private $pdo;
    private $stmt;

    public function __construct() {
        
        $this->host = $_ENV['DB_HOST'];
        $this->db = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASSWORD'];
        
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function query($sql, $params = []) {
        $this->stmt = $this->pdo->prepare($sql);

        // Iterate through parameters and bind them
        foreach ($params as $key => $value) {
            // Convert to int if it's numeric
            if (is_numeric($value)) {
                $this->stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
            } else {
                // Bind as a string by default
                $this->stmt->bindValue($key, $value);
            }
        }

        $this->stmt->execute();
    }

    public function fetchAll() {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function fetchAllObjects() {
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetch() {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function fetchObject() {
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }
}
