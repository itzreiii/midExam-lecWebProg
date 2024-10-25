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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-bg: #f8f9fa;
        }
        
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 15px;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .table-responsive {
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .page-title {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid px-4 py-5">
        <h1 class="page-title text-primary fw-bold">Admin Dashboard</h1>
        
        <!-- Statistics -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-card card h-100 bg-white p-4">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <h3 class="fs-1 fw-bold text-primary mb-2"><?= $stats['total_events'] ?></h3>
                    <p class="text-muted mb-0">Total Events</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-card card h-100 bg-white p-4">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <h3 class="fs-1 fw-bold text-success mb-2"><?= $stats['total_users'] ?></h3>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-card card h-100 bg-white p-4">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-ticket-alt fa-lg"></i>
                    </div>
                    <h3 class="fs-1 fw-bold text-warning mb-2"><?= $stats['total_registrations'] ?></h3>
                    <p class="text-muted mb-0">Total Registrations</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-card card h-100 bg-white p-4">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <h3 class="fs-1 fw-bold text-info mb-2"><?= $stats['upcoming_events'] ?></h3>
                    <p class="text-muted mb-0">Upcoming Events</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Registrations -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h2 class="card-title h5 mb-0">Recent Registrations</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3">User ID</th>
                                <th class="border-0 py-3">Event</th>
                                <th class="border-0 py-3">Registration Date</th>
                                <th class="border-0 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_registrations as $reg): ?>
                            <tr>
                                <td class="py-3"><?= htmlspecialchars($reg['user_id']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($reg['name']) ?></td>
                                <td class="py-3"><?= date('M d, Y', strtotime($reg['registration_date'])) ?></td>
                                <td class="py-3">
                                    <?php
                                    $statusClass = match($reg['status']) {
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success text-white',
                                        'rejected' => 'bg-danger text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>"><?= ucfirst($reg['status']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
