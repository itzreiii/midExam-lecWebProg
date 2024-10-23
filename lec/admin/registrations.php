<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Fetch all registrations with user and event info
$query = "SELECT r.id AS registration_id, r.registration_date, r.status, 
                 u.name AS user_name, u.email, 
                 e.name AS event_name
          FROM event_registrations r
          JOIN users u ON r.user_id = u.id
          JOIN events e ON r.event_id = e.id
          ORDER BY r.registration_date DESC";
$registrations = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/adminheader.php';  // Include the header/navbar
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrations</title>
</head>
<body>
    <h1>All Registrations</h1>
    <table border="1">
        <tr>
            <th>Registration ID</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Event Name</th>
            <th>Registration Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($registrations as $registration): ?>
        <tr>
            <td><?= htmlspecialchars($registration['registration_id']) ?></td>
            <td><?= htmlspecialchars($registration['user_name']) ?></td>
            <td><?= htmlspecialchars($registration['email']) ?></td>
            <td><?= htmlspecialchars($registration['event_name']) ?></td>
            <td><?= htmlspecialchars($registration['registration_date']) ?></td>
            <td><?= htmlspecialchars($registration['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>