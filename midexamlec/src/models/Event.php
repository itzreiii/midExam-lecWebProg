<?php
class Event {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    public function getAll() {
        // Use the initialized database connection to query
        $stmt = $this->db->query('SELECT * FROM events');
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare('SELECT * FROM events WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function getUpcomingEvents() {
        $stmt = $this->db->prepare(
            "SELECT * FROM events WHERE date >= CURRENT_DATE ORDER BY date ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
