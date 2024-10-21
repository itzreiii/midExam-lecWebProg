<?php
session_start();
require_once '../config/database.php'; // Perhatikan path yang benar
require_once '../includes/functions.php'; // Perhatikan
include '../includes/header.php';


// Check if user is logged in
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
<html>
<head>
    <title>My Events</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
</head>
<body>
    <div class="container">
        <div class="grid">
            <div><h1>My Registered Events</h1>
                <?php if (empty($registered_events)): ?>
                    <p>You haven't registered for any events yet.</p>
            </div>
                <?php else: ?>
            
        
            <div>
            <table>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($registered_events as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['name']) ?></td>
                    <td><?= $event['date'] ?></td>
                    <td><?= $event['time'] ?></td>  
                    <td><?= htmlspecialchars($event['location']) ?></td>
                    <td><?= $event['status'] ?></td>
                    <td>
                        <?php if ($event['status'] != 'cancelled'): ?>
                            <form method="POST" action="cancel-registration.php">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn-cancel">Cancel Registration</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
        <?php endif; ?>
        </div>
        
    </div>
    
    <script src="../assets/js/main.js"></script> <!-- Perhatikan path yang benar -->
</body>
<?php include '../includes/footer.php'; ?>
</html>