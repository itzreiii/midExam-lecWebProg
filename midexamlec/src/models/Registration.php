<?php
class Registration {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    public function registerUser($eventId, $userId) {
        $stmt = $this->db->prepare('INSERT INTO registrations (event_id, user_id) VALUES (?, ?)');
        return $stmt->execute([$eventId, $userId]);
    }

    public function getUserEvents($userId) {
        $stmt = $this->db->prepare('SELECT * FROM registrations WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function cancelRegistration($userId, $eventId) {
        $stmt = $this->db->prepare('DELETE FROM registrations WHERE event_id = ? AND user_id = ?');
        return $stmt->execute([$eventId, $userId]);
    }
}
?>
