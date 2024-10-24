<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [ 
    'total_events' => $db->query("SELECT COUNT(*) FROM events")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_registrations' => $db->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn(),
    'upcoming_events' => $db->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()")->fetchColumn()
];

$query = "SELECT r.*, e.name 
          FROM event_registrations r 
          JOIN events e ON r.event_id = e.id 
          ORDER BY r.registration_date DESC 
          LIMIT 10;";
$recent_registrations = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/adminheader.php';  // Include the header/navbar
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5 pt-5">
    <h1>Admin Dashboard</h1>
    <br />
    <!-- Statistics -->
    <h2>Overview</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Events</h5>
                    <p class="card-text"><?= $stats['total_events'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?= $stats['total_users'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Registrations</h5>
                    <p class="card-text"><?= $stats['total_registrations'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Events</h5>
                    <p class="card-text"><?= $stats['upcoming_events'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <h2 class="mt-4">Recent Registrations</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Event</th>
                <th>Registration Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_registrations as $reg): ?>
            <tr>
                <td><?= htmlspecialchars($reg['user_id']) ?></td>
                <td><?= htmlspecialchars($reg['name']) ?></td>
                <td><?= $reg['registration_date'] ?></td>
                <td><?= $reg['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
