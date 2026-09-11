<?php
/**
 * CLEA System - Database Connection Class (PDO)
 * Handles all database operations with prepared statements
 */

class Database {
    private $pdo;
    private $host;
    private $db_name;
    private $user;
    private $pass;
    private $charset;

    public function __construct($host, $db_name, $user, $pass, $charset = 'utf8mb4') {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->user = $user;
        $this->pass = $pass;
        $this->charset = $charset;
        
        $this->connect();
    }

    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }

    public function prepare($query) {
        return $this->pdo->prepare($query);
    }

    public function execute($query, $params = []) {
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }

    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->execute($query, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $where_params = []) {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $query = "UPDATE $table SET $set WHERE $where";
        
        $params = array_merge(array_values($data), $where_params);
        return $this->execute($query, $params);
    }

    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM $table WHERE $where";
        return $this->execute($query, $params);
    }

    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        $result = $this->fetch($query, $params);
        return $result['count'] ?? 0;
    }

    public function getPDO() {
        return $this->pdo;
    }
}
