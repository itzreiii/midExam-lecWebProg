<?php
// Start the session and include the necessary files
session_start();
require_once '../config/database.php'; // Include the database connection
require_once '../includes/functions.php'; // Include functions
include_once '../includes/header.php'; // Include header

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Fetch user's registered events
$query = "SELECT e.*, r.status 
          FROM events e 
          JOIN event_registrations r ON e.id = r.event_id 
          WHERE r.user_id = :user_id 
          ORDER BY e.date ASC";
$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$registered_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registered Events</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1e1e2f 0%, #2d2d44 100%);
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #e91e63, #ff9800);
            border-radius: 2px;
        }

        .event-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .event-card {
            background: rgba(45, 45, 68, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #e91e63, #ff9800);
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .event-info {
            margin-bottom: 1rem;
        }

        .event-info p {
            margin: 0.7rem 0;
            display: flex;
            align-items: center;
        }

        .event-info i {
            width: 24px;
            margin-right: 10px;
            color: #e91e63;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 1rem;
            background: rgba(233, 30, 99, 0.2);
            color: #e91e63;
        }

        .btn-cancel {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            background: linear-gradient(45deg, #e91e63, #ff9800);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
            margin-top: 1rem;
        }

        .btn-cancel:hover {
            opacity: 0.9;
            transform: scale(0.98);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(45, 45, 68, 0.9);
            border-radius: 15px;
            margin: 2rem auto;
            max-width: 500px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #e91e63;
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 1.2rem;
            color: #fff;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .event-list {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
<br />
<div class="container">
    <div class="page-header">
        <h1>My Registered Events</h1>
    </div>
    
    <?php if (empty($registered_events)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-alt"></i>
            <p>You haven't registered for any events yet.</p>
        </div>
    <?php else: ?>
        <div class="event-list">
            <?php foreach ($registered_events as $event): ?>
                <div class="event-card">
                    <div class="event-info">
                        <p><i class="fas fa-calendar-day"></i> <strong><?= htmlspecialchars($event['name']) ?></strong></p>
                        <p><i class="fas fa-clock"></i> <?= date('F j, Y', strtotime($event['date'])) ?></p>
                        <p><i class="fas fa-hourglass-half"></i> <?= htmlspecialchars($event['time']) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></p>
                        <div class="status-badge">
                            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($event['status']) ?>
                        </div>
                    </div>
                    
                    <?php if ($event['status'] != 'cancelled'): ?>
                        <button type="button" class="btn-cancel" onclick="confirmCancellation(<?= $event['id'] ?>)">
                            <i class="fas fa-times-circle"></i> Cancel Registration
                        </button>
                        
                        <form id="cancel-form-<?= $event['id'] ?>" method="POST" action="cancel-registration.php" style="display: none;">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Fungsi untuk konfirmasi pembatalan menggunakan SweetAlert2
function confirmCancellation(eventId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Jika user konfirmasi, kirim form
            document.getElementById('cancel-form-' + eventId).submit();
        }
    })
}
</script>

<script src="../assets/js/main.js"></script>
</body>
</html>
