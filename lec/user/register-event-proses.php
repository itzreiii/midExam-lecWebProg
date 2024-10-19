session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_In()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ensure an event ID is passed
if (!isset($_GET['event_id'])) {
    header("Location: my-events.php"); // Redirect to user's events if no event is selected
    exit();
}

$event_id = $_GET['event_id'];

// Fetch event details (for displaying information)
$event_query = "SELECT * FROM events WHERE id = :event_id";
$stmt = $db->prepare($event_query);
$stmt->execute([':event_id' => $event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle the registration when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is already registered for the event
    $check_query = "SELECT id FROM event_registrations WHERE user_id = :user_id AND event_id = :event_id";
    $stmt = $db->prepare($check_query);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':event_id' => $event_id
    ]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "You are already registered for this event.";
        header("Location: my-events.php");
        exit();
    }

    // Check the event's current participant count and capacity
    $capacity_query = "SELECT max_participants, current_participants, status 
                       FROM events 
                       WHERE id = :event_id";
    $stmt = $db->prepare($capacity_query);
    $stmt->execute([':event_id' => $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the event is already full or closed
    if ($event['current_participants'] >= $event['max_participants'] || $event['status'] === 'closed') {
        $_SESSION['error'] = "This event is already at full capacity or closed.";
        header("Location: my-events.php");
        exit();
    }

    try {
        // Start a transaction to ensure data consistency
        $db->beginTransaction();

        // Register the user for the event
        $register_query = "INSERT INTO event_registrations (user_id, event_id, status) 
                           VALUES (:user_id, :event_id, 'confirmed')";
        $stmt = $db->prepare($register_query);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':event_id' => $event_id
        ]);

        // Update the current participants count for the event
        $update_event_query = "UPDATE events 
                               SET current_participants = current_participants + 1 
                               WHERE id = :event_id";
        $stmt = $db->prepare($update_event_query);
        $stmt->execute([':event_id' => $event_id]);

        // Re-fetch the updated event details
        $stmt = $db->prepare($capacity_query);
        $stmt->execute([':event_id' => $event_id]);
        $updated_event = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the event is full after the new registration
        if ($updated_event['current_participants'] >= $updated_event['max_participants']) {
            // Update the event status to "closed"
            $close_event_query = "UPDATE events SET status = 'closed' WHERE id = :event_id";
            $stmt = $db->prepare($close_event_query);
            $stmt->execute([':event_id' => $event_id]);
        }

        // Commit the transaction
        $db->commit();

        // Registration successful
        $_SESSION['success'] = "You have successfully registered for the event!";
        header("Location: my-events.php");
        exit();

    } catch (PDOException $e) {
        // Rollback the transaction in case of any error
        $db->rollBack();
        $_SESSION['error'] = "There was an error during registration: " . $e->getMessage();
        header("Location: my-events.php");
        exit();
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

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
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
