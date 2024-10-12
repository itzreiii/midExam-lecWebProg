<?php
include_once '../config/database.php'; // Database connection file
include_once '../includes/header.php'; // Header file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Check if the event ID is passed via GET request
if (!isset($_GET['event_id'])) {
    echo "<p>Event ID not provided.</p>";
    exit();
}

// Get the event ID from the GET request
$event_id = $_GET['event_id'];

try {
    // Check if the user is registered for the event
    $sql = "SELECT * FROM registrations WHERE user_id = :user_id AND event_id = :event_id";
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

    // Redirect or display a confirmation message
    echo "<p>You have successfully canceled your registration for the event.</p>";
    echo "<a href='my-events.php'>Go back to my events</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

include_once '../includes/footer.php'; // Footer file
