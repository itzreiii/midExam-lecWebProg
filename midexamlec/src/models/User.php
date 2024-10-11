<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function createUser($userData) {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role) 
             VALUES (:name, :email, :password, :role)"
        );
        
        $stmt->execute([
            ':name' => $userData['name'],
            ':email' => $userData['email'],
            ':password' => $userData['password'],
            ':role' => $userData['role']
        ]);

        return $this->db->lastInsertId();
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateUser($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        return $stmt->execute([$data['name'], $data['email'], $id]);
    }
}
