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
    <title>Event Registrations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
        }
        
        body {
            background-color: #f5f7fa;
            padding-top: 2rem;
        }
        
        .stats-card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .table-responsive {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }
        
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 2em;
            font-weight: 500;
        }
        
        .back-btn {
            border-radius: 2rem;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Single Stats Card for Total Participants -->
        <div class="row justify-content-center mb-4 pt-5 mt-3">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h2 class="card-title mb-2">
                            <?php echo count($registrations); ?>
                        </h2>
                        <p class="card-text text-muted h5">Total Participants</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations Table -->
        <div class="table-responsive shadow-sm p-3 mb-4">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="rounded-start">ID</th>
                        <th>User</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th>Event</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="rounded-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($registration['registration_id']) ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <?= strtoupper(substr($registration['user_name'], 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($registration['user_name']) ?>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($registration['email']) ?></td>
                        <td><?= htmlspecialchars($registration['event_name']) ?></td>
                        <td class="d-none d-md-table-cell">
                            <?= date('M d, Y', strtotime($registration['registration_date'])) ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($registration['status']) {
                                'Confirmed' => 'bg-success',
                                'Pending' => 'bg-warning',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="status-badge <?= $statusClass ?> text-white">
                                <?= htmlspecialchars($registration['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Back Button -->
        <div class="text-center mb-4">
            <a href="dashboard.php" class="btn btn-primary back-btn">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>