<?php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_In()) {
    echo json_encode(['error' => 'Not logged in.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ensure an event ID is passed
if (isset($_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        // Lakukan proses pendaftaran di sini
        try {
            // Check if the user is already registered for the event
            $check_query = "SELECT id FROM event_registrations WHERE user_id = :user_id AND event_id = :event_id";
            $stmt = $db->prepare($check_query);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':event_id' => $event_id
            ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['error' => 'You are already registered for this event.']);
            exit();
        }

        // Check event details for capacity and status
        $capacity_query = "SELECT max_participants, current_participants, status FROM events WHERE id = :event_id";
        $stmt = $db->prepare($capacity_query);
        $stmt->execute([':event_id' => $event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the event was found
        if (!$event) {
            echo json_encode(['error' => 'Event not found.']);
            exit();
        }

        if ($event['current_participants'] >= $event['max_participants'] || $event['status'] === 'closed') {
            echo json_encode(['error' => 'This event is already at full capacity or closed.']);
            exit();
        }

        // Begin the transaction
        $db->beginTransaction();

        // Register the user
        $register_query = "INSERT INTO event_registrations (user_id, event_id, status) VALUES (:user_id, :event_id, 'confirmed')";
        $stmt = $db->prepare($register_query);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':event_id' => $event_id
        ]);

        // Update the event's participant count
        $update_event_query = "UPDATE events SET current_participants = current_participants + 1 WHERE id = :event_id";
        $stmt = $db->prepare($update_event_query);
        $stmt->execute([':event_id' => $event_id]);

        // Commit the transaction
        $db->commit();

        // Success response
        echo json_encode(['success' => 'You have successfully registered for the event.']);
        exit();
    
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $db->rollBack();
        echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
        exit();
    }

} else {
    echo json_encode(['error' => 'Event ID not received']);
}



