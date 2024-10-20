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
                // Create event logic
                break;

            case 'update':
                // Update event logic
                break;

            case 'delete':
                // Delete event logic
                break;

            case 'change_status':
                $new_status = $_POST['current_status'] === 'open' ? 'closed' : 'open';
                $query = "UPDATE events SET status = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':id' => $_POST['event_id'],
                    ':status' => $new_status
                ]);
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

<<<<<<< HEAD
    <!-- Create Event Form -->
    <h2>Create New Event</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <!-- Existing fields -->
        <button type="submit">Create Event</button>
=======
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
>>>>>>> 4dab52c5a0139cec7d48c1317366af604f482fcb
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
<<<<<<< HEAD
            <td><?= $event['max_participants'] ?></td>
            <td><?= htmlspecialchars($event['status']) ?></td>
=======
            <td><?= htmlspecialchars($event['max_participants']) ?></td>
            <td><?= htmlspecialchars($event['status'] ?? 'N/A') ?></td>
>>>>>>> 4dab52c5a0139cec7d48c1317366af604f482fcb
            <td> 
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                    <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="change_status">
<<<<<<< HEAD
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($event['status']) ?>">
                    <button type="submit">
                        <?= $event['status'] === 'open' ? 'Close Event' : 'Open Event' ?>
                    </button>
                </form>
                <button onclick="editEvent(<?= $event['id'] ?>)">Edit</button>
=======
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
>>>>>>> 4dab52c5a0139cec7d48c1317366af604f482fcb
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>