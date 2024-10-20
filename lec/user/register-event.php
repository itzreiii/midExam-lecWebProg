<?php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';



// Check if user is logged in
if (!is_logged_In()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    // Check if already registered
    $check_query = "SELECT id FROM event_registrations 
                   WHERE user_id = :user_id AND event_id = :event_id";
    $stmt = $db->prepare($check_query);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':event_id' => $event_id
    ]);
    
    if ($stmt->rowCount() == 0) {
        // Check event capacity
        $capacity_query = "SELECT e.max_participants, COUNT(r.id) as registered
                          FROM events e
                          LEFT JOIN event_registrations r ON e.id = r.event_id
                          WHERE e.id = :event_id
                          GROUP BY e.id";
        $stmt = $db->prepare($capacity_query);
        $stmt->execute([':event_id' => $event_id]);
        $capacity_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($capacity_info['registered'] < $capacity_info['max_participants']) {
            // Register user
            $register_query = "INSERT INTO event_registrations (user_id, event_id, status) 
                             VALUES (:user_id, :event_id, 'confirmed')";
            $stmt = $db->prepare($register_query);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':event_id' => $event_id
            ]);
            
            $message = "Successfully registered for the event!";
        } else {
            $error = "Sorry, this event is already at full capacity.";
        }
    } else {
        $error = "You are already registered for this event.";
    }
}

// Fetch available events
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) as registered_count
          FROM events e 
          WHERE e.date >= CURDATE() 
          ORDER BY e.date ASC";
$events = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
 
<!DOCTYPE html>
<html>
<head>
    <title>Register for Events</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
    <h1>Available Events</h1>
    
    <?php if (isset($message)): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <p>No upcoming events available.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>Event</th>
                <th>Description</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Available Spots</th>
                <th>Action</th>
            </tr>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?= htmlspecialchars($event['name']) ?></td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td><?= $event['date'] ?></td>
                <td><?= $event['time'] ?></td>
                <td><?= htmlspecialchars($event['location']) ?></td>
                <td><?= $event['current_participants'] ?> / <?= $event['max_participants'] ?></td>

                <!-- $capacity_query = "SELECT e.max_participants, COUNT(r.id) as registered
                          FROM events e
                          LEFT JOIN event_registrations r ON e.id = r.event_id
                          WHERE e.id = :event_id
                          GROUP BY e.id";
                $stmt = $db->prepare($capacity_query);
                $stmt->execute([':event_id' => $event_id]); -->
                
                <td>
                    <?php if ($event['current_participants'] < $event['max_participants']): ?>
                       
                        
                        <!-- Button trigger modal -->
                        <!-- Button to open modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#registerModal">
                            Register for Event
                        </button>

                        <!-- Modal for registration -->
                        <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="registerModalLabel">Register for Event</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" data-event-id="1">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="registerForm">
                                            <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                            <p>Event: <?= htmlspecialchars($event['name']) ?></p>
                                            <p>Description: <?= htmlspecialchars($event['description']) ?></p>
                                            <p>Date: <?= $event['date'] ?></p>
                                            <p>Location: <?= htmlspecialchars($event['location']) ?></p>
                                            <button type="submit" class="btn btn-primary">Confirm Registration</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                    <?php else: ?>
                        <button disabled>Full</button>
                    <?php endif; ?>
                </td>

            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                $.ajax({
                    url: 'register-event-proses.php',
                    type: 'POST',
                    data: {
                        event_id: 1
                    },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            alert(res.success);
                            $('#registerModal').modal('hide'); // Hide modal
                            window.location.href = 'my-events.php'; // Redirect to events page
                        } else if (res.error) {
                            alert(res.error); // Display error
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText); // Lihat isi respons dari server
                        alert('Something went wrong! Please try again.');
                    }

                });
            });
        });
    </script>
    <script>
    function confirmRegistration() {
        return confirm('Are you sure you want to register for this event?');
    }

    // Add event listener to all registration forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirmRegistration()) {
                e.preventDefault();
            }
        });
    }); 
    </script>
    </div>
</body>
<?php include '../includes/footer.php'; ?>

</html>