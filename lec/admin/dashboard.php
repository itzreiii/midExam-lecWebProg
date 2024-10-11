<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'total_events' => $db->query("SELECT COUNT(*) FROM events")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_registrations' => $db->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
    'upcoming_events' => $db->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()")->fetchColumn()
];

// Get recent registrations
$query = "SELECT r.*, u.username, e.title 
          FROM registrations r 
          JOIN users u ON r.user_id = u.id 
          JOIN events e ON r.event_id = e.id 
          ORDER BY r.registration_date DESC 
          LIMIT 10";
$recent_registrations = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    
    <!-- Statistics -->
    <h2>Overview</h2>
    <div class="stats">
        <div>
            <h3>Total Events</h3>
            <p><?= $stats['total_events'] ?></p>
        </div>
        <div>
            <h3>Total Users</h3>
            <p><?= $stats['total_users'] ?></p>
        </div>
        <div>
            <h3>Total Registrations</h3>
            <p><?= $stats['total_registrations'] ?></p>
        </div>
        <div>
            <h3>Upcoming Events</h3>
            <p><?= $stats['upcoming_events'] ?></p>
        </div>
    </div>

    <!-- Quick Links -->
    <h2>Quick Links</h2>
    <ul>
        <li><a href="event-management.php">Manage Events</a></li>
        <li><a href="user-management.php">Manage Users</a></li>
        <li><a href="registrations.php">View All Registrations</a></li>
    </ul>

    <!-- Recent Registrations -->
    <h2>Recent Registrations</h2>
    <table border="1">
        <tr>
            <th>User</th>
            <th>Event</th>
            <th>Registration Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($recent_registrations as $reg): ?>
        <tr>
            <td><?= htmlspecialchars($reg['username']) ?></td>
            <td><?= htmlspecialchars($reg['title']) ?></td>
            <td><?= $reg['registration_date'] ?></td>
            <td><?= $reg['status'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>