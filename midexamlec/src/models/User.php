<?php
class User {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>
