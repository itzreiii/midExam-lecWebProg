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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5 pt-5">
    <h1 class="mb-4">Event Management</h1>

    <!-- Create/Edit Event Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h2><?= isset($event) ? 'Edit Event' : 'Create New Event' ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" id="eventForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= isset($event) ? 'update' : 'create' ?>">
                <?php if (isset($event)): ?>
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($event['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label">Date:</label>
                        <input type="date" id="date" name="date" class="form-control" required value="<?= htmlspecialchars($event['date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="time" class="form-label">Time:</label>
                        <input type="time" id="time" name="time" class="form-control" required value="<?= htmlspecialchars($event['time'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="location" class="form-label">Location:</label>
                        <input type="text" id="location" name="location" class="form-control" required value="<?= htmlspecialchars($event['location'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="max_participants" class="form-label">Max Participants:</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-control" required value="<?= htmlspecialchars($event['max_participants'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Event Image:</label>
                    <input type="file" id="image" name="image" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><?= isset($event) ? 'Update Event' : 'Create Event' ?></button>
            </form>
        </div>
    </div>

    <!-- Button to go back to the dashboard -->
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <!-- List of Events -->
    <div class="card">
        <div class="card-header">
            <h2>Existing Events</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Max Participants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['name']) ?></td>
                            <td><?= htmlspecialchars($event['date']) ?></td>
                            <td><?= htmlspecialchars($event['time']) ?></td>
                            <td><?= htmlspecialchars($event['location']) ?></td>
                            <td><?= htmlspecialchars($event['max_participants']) ?></td>
                            <td><?= htmlspecialchars($event['status'] ?? 'N/A') ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($event['status'] ?? '') ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <?= $event['status'] === 'open' ? 'Close Event' : 'Open Event' ?>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
                                    <button type="submit" class="btn btn-info btn-sm">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>