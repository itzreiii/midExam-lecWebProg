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
        $imagePath = null;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = "../uploads/";
            $fileName = basename($_FILES['image']['name']);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            // Allow only certain file formats
            $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = $targetFilePath; // Set image path for database insertion
                } else {
                    echo "Error uploading the file.";
                }
            } else {
                echo "Invalid file type. Only JPG, PNG, JPEG, and GIF files are allowed.";
            }
        }

        // Database operation with try-catch for error handling
        try {
            switch ($_POST['action']) {
                case 'create':
                    $query = "INSERT INTO events (name, description, date, time, location, max_participants, image) 
                              VALUES (:name, :description, :date, :time, :location, :max_participants, :image)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':name' => $_POST['name'] ?? '',
                        ':description' => $_POST['description'] ?? '',
                        ':date' => $_POST['date'] ?? '',
                        ':time' => $_POST['time'] ?? '',
                        ':location' => $_POST['location'] ?? '',
                        ':max_participants' => $_POST['max_participants'] ?? 0,
                        ':image' => $imagePath
                    ]);
                    break;

                case 'update':
                    $query = "UPDATE events SET 
                              name = :name, 
                              description = :description,
                              date = :date,
                              time = :time,
                              location = :location,
                              max_participants = :max_participants,
                              image = IFNULL(:image, image) 
                              WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':id' => $_POST['event_id'] ?? 0,
                        ':name' => $_POST['name'] ?? '',
                        ':description' => $_POST['description'] ?? '',
                        ':date' => $_POST['date'] ?? '',
                        ':time' => $_POST['time'] ?? '',
                        ':location' => $_POST['location'] ?? '',
                        ':max_participants' => $_POST['max_participants'] ?? 0,
                        ':image' => $imagePath
                    ]);
                    break;

                case 'delete':
                    $query = "DELETE FROM events WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':id' => $_POST['event_id'] ?? 0]);
                    break;

                case 'change_status':
                    $new_status = $_POST['current_status'] === 'open' ? 'closed' : 'open';
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
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Fetch all events
$query = "SELECT * FROM events ORDER BY date DESC";
$stmt = $db->query($query);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/adminheader.php';  // Include the header/navbar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            margin: 0;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-color);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            background-color: var(--light-bg);
            border-bottom: 2px solid #e0e0e0;
            color: #555;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            margin: 0.25rem;
        }
        
        .btn-icon {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 15px;
            }
            
            .btn-action {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid px-4 py-5">
    <h1 class="page-title text-primary fw-bold">Event Management</h1>
    
    <!-- Create/Edit Event Form -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-calendar-plus me-2 text-primary"></i>
            <h2><?= isset($event) ? 'Edit Event' : 'Create New Event' ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" id="eventForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= isset($event) ? 'update' : 'create' ?>">
                <?php if (isset($event)): ?>
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-bold">Event Name</label>
                        <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($event['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label fw-bold">Location</label>
                        <input type="text" id="location" name="location" class="form-control" required value="<?= htmlspecialchars($event['location'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label fw-bold">Date</label>
                        <input type="date" id="date" name="date" class="form-control" required value="<?= htmlspecialchars($event['date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="time" class="form-label fw-bold">Time</label>
                        <input type="time" id="time" name="time" class="form-control" required value="<?= htmlspecialchars($event['time'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="max_participants" class="form-label fw-bold">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" required value="<?= htmlspecialchars($event['max_participants'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="image" class="form-label fw-bold">Event Image</label>
                    <input type="file" id="image" name="image" class="form-control">
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas <?= isset($event) ? 'fa-save' : 'fa-plus' ?> btn-icon"></i>
                        <?= isset($event) ? 'Update Event' : 'Create Event' ?>
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left btn-icon"></i>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- List of Events -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2 text-primary"></i>
            <h2>Existing Events</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Max Participants</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($event['name']) ?></td>
                                <td><?= date('M d, Y', strtotime($event['date'])) ?></td>
                                <td><?= date('h:i A', strtotime($event['time'])) ?></td>
                                <td><?= htmlspecialchars($event['location']) ?></td>
                                <td><?= htmlspecialchars($event['max_participants']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = $event['status'] === 'open' 
                                        ? 'bg-success text-white' 
                                        : 'bg-danger text-white';
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst(htmlspecialchars($event['status'] ?? 'N/A')) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                        <button type="submit" class="btn btn-info btn-action">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                        <input type="hidden" name="current_status" value="<?= htmlspecialchars($event['status'] ?? '') ?>">
                                        <button type="submit" class="btn btn-warning btn-action">
                                            <i class="fas <?= $event['status'] === 'open' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-action" onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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