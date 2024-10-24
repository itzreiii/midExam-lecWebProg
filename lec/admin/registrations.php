<?php
session_start();
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5 pt-5">
    <h1 class="mb-4">All Registrations</h1>

    <!-- Registrations Table -->
    <div class="card">
        <div class="card-header">
            <h2>Registrations List</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Event Name</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
        </div>
    </div>

    <!-- Button to go back to the dashboard -->
    <a href="dashboard.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>