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
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) as registered_count,
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.user_id = :user_id) as is_registered
          FROM events e
          WHERE e.date >= CURDATE() 
          ORDER BY e.date ASC";

$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);


// $query_untuk_button = "SELECT r.user_id FROM event_registrations r
//                        ";
// $button_status = $db->query($query_untuk_button)->fetch(PDO::FETCH_ASSOC);
?>

 
<!DOCTYPE html>
<html>
<head>
    <title>Register for Events</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Available Events</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (empty($events)): ?>
            <p>No upcoming events available.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                                <p class="card-text"><?= $event['date'] ?></p>
                                <p class="card-text"><?= $event['time'] ?></p>
                                <p class="card-text"><?= htmlspecialchars($event['location']) ?></p>
                                <p class="card-text"><?= $event['registered_count'] ?> / <?= $event['max_participants'] ?></p>
                                <?php if ($event['is_registered'] > 0): ?>
                                    <button class="btn btn-secondary" disabled>Registered</button>
                                <?php elseif ($event['registered_count'] < $event['max_participants']): ?>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#registerModal" data-event-id="<?= $event['id'] ?>">
                                        Register for Event
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-danger" disabled>Full</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Modal for registration -->
        <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Register for Event</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="registrationForm" action="register-event-proses.php" method="POST"> 
                            <input type="hidden" id="event_id" name="event_id" value="">
                            <p id="event_name"></p>
                            <p id="event_description"></p>
                            <p id="event_date"></p>
                            <p id="event_location"></p>
                            <button type="submit" class="btn btn-primary">Confirm Registration</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function() {
            // Handle opening modal with the correct event information
            $('#registerModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var eventId = button.data('event-id'); // Extract event ID from data-* attribute
                var eventName = button.closest('.card').find('.card-title').text(); // Get event name
                var eventDescription = button.closest('.card').find('.card-text').eq(0).text(); // Get event description
                var eventDate = button.closest('.card').find('.card-text').eq(1).text(); // Get event date
                var eventLocation = button.closest('.card').find('.card-text').eq(3).text(); // Get event location

                // Update the modal's content
                $('#registerModal #event_id').val(eventId); // Set the event_id in the hidden input
                $('#registerModal #event_name').text('Name: ' + eventName);
                $('#registerModal #event_description').text('Desc: ' + eventDescription);
                $('#registerModal #event_date').text('Date: ' + eventDate);
                $('#registerModal #event_location').text('Location: ' + eventLocation);
            });

            // Handle form submission
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission
                var formData = $(this).serialize(); // Serialize the form data

                // AJAX request to register the event
                $.ajax({
                    type: 'POST',
                    url: 'register-event-proses.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // Handle success or error response
                        if (response.success) {
                            alert(response.success);
                            location.reload(); // Reload the page to see updated participants
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>
