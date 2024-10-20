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
                    ':name' => $_POST['name'] ?? '',
                    ':description' => $_POST['description'] ?? '',
                    ':date' => $_POST['date'] ?? '',
                    ':time' => $_POST['time'] ?? '',
                    ':location' => $_POST['location'] ?? '',
                    ':max_participants' => $_POST['max_participants'] ?? 0
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
                    ':id' => $_POST['event_id'] ?? 0,
                    ':name' => $_POST['name'] ?? '',
                    ':description' => $_POST['description'] ?? '',
                    ':date' => $_POST['date'] ?? '',
                    ':time' => $_POST['time'] ?? '',
                    ':location' => $_POST['location'] ?? '',
                    ':max_participants' => $_POST['max_participants'] ?? 0
                ]);
                break;

            case 'delete':
                $query = "DELETE FROM events WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $_POST['event_id'] ?? 0]);
                break;
                
            case 'change_status':
                $new_status = ($_POST['current_status'] ?? '') === 'open' ? 'closed' : 'open';
                $query = "UPDATE events SET status = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':id' => $_POST['event_id'] ?? 0,
                    ':status' => $new_status
                ]);
                break;

            case 'edit':
                $query = "SELECT * FROM events WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $_POST['event_id'] ?? 0]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
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

    <!-- Create/Edit Event Form -->
    <h2><?= isset($event) ? 'Edit Event' : 'Create New Event' ?></h2>
    <form method="POST" id="eventForm">
        <input type="hidden" name="action" value="<?= isset($event) ? 'update' : 'create' ?>">
        <?php if (isset($event)): ?>
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
        <?php endif; ?>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($event['name'] ?? '') ?>"><br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($event['description'] ?? '') ?></textarea><br>
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required value="<?= htmlspecialchars($event['date'] ?? '') ?>"><br>
        <label for="time">Time:</label>
        <input type="time" id="time" name="time" required value="<?= htmlspecialchars($event['time'] ?? '') ?>"><br>
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" required value="<?= htmlspecialchars($event['location'] ?? '') ?>"><br>
        <label for="max_participants">Max Participants:</label>
        <input type="number" id="max_participants" name="max_participants" required value="<?= htmlspecialchars($event['max_participants'] ?? '') ?>"><br>
        <button type="submit"><?= isset($event) ? 'Update Event' : 'Create Event' ?></button>
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
            <th>Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Location</th>
            <th>Max Participants</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['name']) ?></td>
            <td><?= htmlspecialchars($event['date']) ?></td>
            <td><?= htmlspecialchars($event['time']) ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td><?= htmlspecialchars($event['max_participants']) ?></td>
            <td><?= htmlspecialchars($event['status'] ?? 'N/A') ?></td>
            <td> 
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                    <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($event['status'] ?? '') ?>">
                    <button type="submit">
                        <?= ($event['status'] ?? '') === 'open' ? 'Close Event' : 'Open Event' ?>
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                    <button type="submit">Edit</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>