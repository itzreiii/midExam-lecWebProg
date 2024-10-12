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

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    // Check if already registered
    $check_query = "SELECT id FROM registrations 
                   WHERE user_id = :user_id AND event_id = :event_id";
    $stmt = $db->prepare($check_query);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':event_id' => $event_id
    ]);
    
    if ($stmt->rowCount() == 0) {
        // Check event capacity
        $capacity_query = "SELECT e.capacity, COUNT(r.id) as registered
                          FROM events e
                          LEFT JOIN registrations r ON e.id = r.event_id
                          WHERE e.id = :event_id
                          GROUP BY e.id";
        $stmt = $db->prepare($capacity_query);
        $stmt->execute([':event_id' => $event_id]);
        $capacity_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($capacity_info['registered'] < $capacity_info['capacity']) {
            // Register user
            $register_query = "INSERT INTO registrations (user_id, event_id, status) 
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

// Fetch available events
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) as registered_count
          FROM events e 
          WHERE e.date >= CURDATE() 
          ORDER BY e.date ASC";
$events = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register for Events</title>
</head>
<body>
    <h1>Available Events</h1>
    
    <?php if (isset($message)): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <p>No upcoming events available.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>Event</th>
                <th>Description</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Available Spots</th>
                <th>Action</th>
            </tr>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?= htmlspecialchars($event['title']) ?></td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td><?= $event['date'] ?></td>
                <td><?= $event['time'] ?></td>
                <td><?= htmlspecialchars($event['location']) ?></td>
                <td><?= $event['capacity'] - $event['registered_count'] ?> / <?= $event['capacity'] ?></td>
                <td>
                    <?php if ($event['registered_count'] < $event['capacity']): ?>
                        <form method="POST">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit">Register</button><button type="submit">Register</button>
                        </form>
                    <?php else: ?>
                        <button disabled>Full</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <script>
    function confirmRegistration() {
        return confirm('Are you sure you want to register for this event?');
    }

    // Add event listener to all registration forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirmRegistration()) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>




