<?php
session_start();
include_once '../config/database.php'; // Database connection file
// include_once '../includes/header.php'; // Header file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Check if the event ID is passed via POST request
if (!isset($_POST['event_id'])) {
    echo "<p>Event ID not provided.</p>";
    exit();
}

// Get the event ID from the POST request
$event_id = $_POST['event_id'];

// Create a new instance of the Database class and get the connection
$database = new Database();
$conn = $database->getConnection();

try {
    // Start a transaction to ensure data consistency
    $conn->beginTransaction();

    // Check if the user is registered for the event
    $sql = "SELECT * FROM event_registrations WHERE user_id = :user_id AND event_id = :event_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Check if registration exists
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$registration) {
        echo "<p>You are not registered for this event or the event doesn't exist.</p>";
        exit();
    }


    // Delete the registration record
    $delete_sql = "DELETE FROM event_registrations WHERE user_id = :user_id AND event_id = :event_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $delete_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $delete_stmt->execute();
    
    // Update the current participants count for the event
    $update_event_query = "UPDATE events SET current_participants = current_participants - 1 WHERE id = :event_id";
    $update_stmt = $conn->prepare($update_event_query);
    $update_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $update_stmt->execute();

    // Fetch the updated event details
    $event_query = "SELECT current_participants, max_participants FROM events WHERE id = :event_id";
    $event_stmt = $conn->prepare($event_query);
    $event_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $event_stmt->execute();
    $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

    // Check and update event status if needed
    if ($event['current_participants'] < $event['max_participants']) {
        $status_query = "UPDATE events SET status = 'open' WHERE id = :event_id";
        $status_stmt = $conn->prepare($status_query);
        $status_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $status_stmt->execute();
    }

    // Commit the transaction
    $conn->commit();

    // Display the alert and redirect using JavaScript
    echo "<script>
        window.location.href = 'my-events.php'; 
    </script>";

    exit();

} catch (PDOException $e) {
    // Rollback the transaction in case of error
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

// Include the footer file (if necessary)
include_once '../includes/footer.php';
