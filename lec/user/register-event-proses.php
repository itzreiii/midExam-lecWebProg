<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Check if user is logged in
if (!is_logged_In()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get the event ID from the URL
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Fetch event details
    $query = "SELECT * FROM events WHERE id = :event_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':event_id' => $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        echo "Event not found.";
        exit();
    }
} else {
    echo "No event specified.";
    exit();
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Similar registration logic as before
    $check_query = "SELECT id FROM event_registrations 
                   WHERE user_id = :user_id AND event_id = :event_id";
    $stmt = $db->prepare($check_query);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':event_id' => $event_id
    ]);

    if ($stmt->rowCount() == 0) {
        // Check event capacity
        $capacity_query = "SELECT e.max_participants, COUNT(r.id) as registered
                          FROM events e
                          LEFT JOIN event_registrations r ON e.id = r.event_id
                          WHERE e.id = :event_id
                          GROUP BY e.id";
        $stmt = $db->prepare($capacity_query);
        $stmt->execute([':event_id' => $event_id]);
        $capacity_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($capacity_info['registered'] < $capacity_info['max_participants']) {
            // Register user
            $register_query = "INSERT INTO event_registrations (user_id, event_id, status) 
                             VALUES (:user_id, :event_id, 'confirmed')";
            $stmt = $db->prepare($register_query);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':event_id' => $event_id
            ]);

            $message = "Successfully registered for the event!";
        } else {
            $error = "Sorry, this event is already at full capacity.";
        }
    } else {
        $error = "You are already registered for this event.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Registration</title>
</head>
<body>

<h1>Register for Event: <?= htmlspecialchars($event['name']) ?></h1>

<?php if (isset($message)): ?>
    <div class="success"><?= $message ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
    <p>Event: <?= htmlspecialchars($event['name']) ?></p>
    <p>Description: <?= htmlspecialchars($event['description']) ?></p>
    <p>Date: <?= $event['date'] ?></p>
    <p>Location: <?= htmlspecialchars($event['location']) ?></p>
    <button type="submit">Confirm Registration</button>
</form>

</body>
<?php include '../includes/footer.php'; ?>
</html>
