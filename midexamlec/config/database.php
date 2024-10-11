<?php
// Database Connection
$host = 'localhost';
$dbname = 'web_event_registration';
$user = 'root';
$pass = '';

class Database {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
