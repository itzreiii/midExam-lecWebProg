<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_Admin()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $query = "INSERT INTO events (name, description, date, time, location, max_participants) 
                         VALUES (:name, :description, :date, :time, :location, :max_participants)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':name' => $_POST['name'],
                    ':description' => $_POST['description'],
                    ':date' => $_POST['date'],
                    ':time' => $_POST['time'],
                    ':location' => $_POST['location'],
                    ':max_participants' => $_POST['max_participants']
                ]);
                break;

            case 'update':
                $query = "UPDATE events SET 
                         name = :name, 
                         description = :description,
                         date = :date,
                         time = :time,
                         location = :location,
                         max_participants = :max_participants 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':id' => $_POST['event_id'],
                    ':name' => $_POST['name'],
                    ':description' => $_POST['description'],
                    ':date' => $_POST['date'],
                    ':time' => $_POST['time'],
                    ':location' => $_POST['location'],
                    ':max_participants' => $_POST['max_participants']
                ]);
                break;

            case 'delete':
                $query = "DELETE FROM events WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $_POST['event_id']]);
                break;
        }
    }
}

// Fetch all events
$query = "SELECT * FROM events ORDER BY date DESC";
$stmt = $db->query($query);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Management</title>
</head>
<body>
    <h1>Event Management</h1>

    <!-- Create Event Form -->
    <h2>Create New Event</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div>
            <label>name: <input type="text" name="name" required></label>
        </div>
        <div>
            <label>Description: <textarea name="description" required></textarea></label>
        </div>
        <div>
            <label>Date: <input type="date" name="date" required></label>
        </div>
        <div>
            <label>Time: <input type="time" name="time" required></label>
        </div>
        <div>
            <label>Location: <input type="text" name="location" required></label>
        </div>
        <div>
            <label>max_participants: <input type="number" name="max_participants" required></label>
        </div>
        <button type="submit">Create Event</button>
    </form>

    <!-- Button to go back to the dashboard -->
    <br><br>
    <a href="dashboard.php">
        <button type="button">Back to Dashboard</button>
    </a>

    <!-- List of Events -->
    <h2>Existing Events</h2>
    <table border="1">
        <tr>
            <th>name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Location</th>
            <th>max_participants</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['name']) ?></td>
            <td><?= $event['date'] ?></td>
            <td><?= $event['time'] ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td><?= $event['max_participants'] ?></td>
            <td> 
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
                <button onclick="editEvent(<?= $event['id'] ?>)">Edit</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
