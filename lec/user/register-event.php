<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';


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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
/* Body background */
body {
    background-color: #0f0f1e; /* Lebih gelap dari #1a1a2e */
    color: #ffffff; /* Putih untuk teks */
    font-family: 'Arial', sans-serif; /* Gunakan font yang sederhana dan mudah dibaca */
    margin: 0;
    padding: 0;
    min-height: 100vh; /* Pastikan body mengisi seluruh tinggi layar */
}

/* Styling untuk kartu */
.card {
    background-color: #1a1a2e; /* Warna latar belakang kartu yang gelap */
    color: #ffffff; /* Teks berwarna putih di dalam kartu */
    border-radius: 15px; /* Membuat sudut kartu menjadi melengkung */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4); /* Memberikan efek bayangan pada kartu */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Animasi halus ketika hover */
}

.card:hover {
    transform: translateY(-10px); /* Mengangkat kartu saat hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.7); /* Efek bayangan yang lebih kuat saat hover */
}

/* Styling untuk modal */
#registerModal .modal-content {
    background-color: #1a1a2e; /* Warna latar belakang modal gelap */
    color: #ffffff; /* Teks putih */
    border-radius: 15px; /* Membuat sudut modal melengkung */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.7); /* Efek bayangan untuk modal */
    border: none;
}

#registerModal .modal-header {
    background-color: #ff2e63; /* Warna latar belakang merah cerah */
    color: #fff; /* Teks berwarna putih */
    border-radius: 15px 15px 0 0; /* Sudut atas yang melengkung */
    padding: 20px;
}

#registerModal .modal-footer {
    border-top: none; /* Hilangkan border pada bagian footer modal */
}

/* Efek hover pada tombol di dalam modal */
#registerModal .btn-primary {
    background-color: #ff2e63; /* Warna tombol utama */
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

#registerModal .btn-primary:hover {
    background-color: #ff6f91; /* Warna yang lebih terang saat hover */
}

/* Teks lain di dalam modal */
#registerModal .card-text {
    font-size: 16px;
    line-height: 1.5;
}

/* Styling untuk carousel */
.carousel-inner img {
    width: 100%; /* Mengatur lebar gambar menjadi 100% */
    height: auto; /* Mempertahankan rasio aspek */
}

.carousel-caption {
    background: rgba(0, 0, 0, 0.6); /* Latar belakang transparan hitam */
    padding: 10px;
    border-radius: 5px;
}

.carousel-control-prev-icon, .carousel-control-next-icon {
    background-color: black;
}

#event_image {
    width: 100%;
    height: auto;
    border-radius: 10px;
}

    </style>
</head>
<body style="background-color: #0f0f1e;">
    <!-- Header Include -->
    <?php include '../includes/header.php'; ?>


    
    <div class="container-fluid p-0">
        <!-- Carousel as Website Banner -->
        <?php if (empty($events)): ?>
            <p>No upcoming events available.</p>
        <?php else: ?>
            <div id="eventsCarousel" class="carousel slide" data-ride="carousel" data-interval="5000" data-pause="hover">
                <div class="carousel-inner">
                    <?php
                    // Placeholder images
                    $carouselImages = [
                        '../assets/images/welcome.png', // Image 1
                        '../assets/images/ads.png', // Image 2
                        '../assets/images/quotes.png', // Image 3
                        '../assets/images/gambar4.jpg'  // Image 4
                    ];
                    $active_class = 'active';
                    foreach ($carouselImages as $index => $image):
                    ?>
                        <div class="carousel-item <?= $active_class ?>">
                            <img src="<?= htmlspecialchars($image) ?>" alt="Event Image <?= $index + 1 ?>">
                        </div>
                    <?php
                    $active_class = ''; // Clear active class for the next slides
                    endforeach;
                    ?>
                </div>

                <!-- Carousel Controls -->
                <a class="carousel-control-prev" href="#eventsCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#eventsCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-5"> 
    <h2 style="color: #ffffff;">Available Events</h2>
    <?php if (empty($events)): ?>
        <p>No upcoming events available.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4">
    <div class="card mb-4 shadow-lg" style="border-radius: 15px; background-color: #1a1a2e; color: #ffffff;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div style="background-color: #ff2e63; color: white; font-size: 24px; font-weight: bold; padding: 10px 15px; border-radius: 10px;">
                    <?= date('d', strtotime($event['date'])) ?>
                    <br>
                    <span style="font-size: 16px;"><?= date('M', strtotime($event['date'])) ?></span>
                </div>
                <div class="ml-3">
                    <h5 class="card-title" style="font-size: 20px; font-weight: 700;"><?= htmlspecialchars($event['name']) ?></h5>
                </div>
            </div>
            <p class="card-text" style="font-size: 14px;"><?= htmlspecialchars($event['description']) ?></p>
            <?php if (!empty($event['image'])): ?>
                <div style="height: 200px; background: url('<?= htmlspecialchars($event['image']) ?>') no-repeat center center; background-size: cover; border-radius: 10px;"></div>
            <?php else: ?>
                <div style="height: 200px; background-color: #e0e0e0; border-radius: 10px;">No Image Available</div>
            <?php endif; ?>
            <p class="mt-3"><?= $event['registered_count'] ?> / <?= $event['max_participants'] ?> Participants</p>
            <?php if ($event['is_registered'] > 0): ?>
                <button class="btn btn-secondary" disabled>Registered</button>
            <?php elseif ($event['registered_count'] < $event['max_participants']): ?>
               <button 
    type="button" 
    class="btn open-modal-btn" 
    style="background-color: #28a745; color: white;"  
    data-event-id="<?= $event['id'] ?>" 
    data-event-name="<?= htmlspecialchars($event['name']) ?>"
    data-event-description="<?= htmlspecialchars($event['description']) ?>"
    data-event-date="<?= date('d M Y', strtotime($event['date'])) ?>"
    data-event-location="<?= htmlspecialchars($event['location']) ?>"
    data-event-image="<?= htmlspecialchars($event['image']) ?>"
    data-toggle="modal" 
    data-target="#registerModal">
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
</div>

<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #1a1a2e; color: rgba(0, 0, 0, 0.72); border-radius: 15px; border: none;">
            <div class="modal-header" style="background-color: #ff2e63; color: #fff; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" id="registerModalLabel">Register for Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: black;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Gambar event -->
                <img id="event_image" src="" alt="Event Image" style="width: 100%; height: auto; border-radius: 10px; margin-bottom: 20px;">
                
                <form id="registrationForm" action="register-event-proses.php" method="POST">
                    <input type="hidden" id="event_id" name="event_id" value="">
                    
                    <h5 id="event_name" style="margin: 0;"></h5>
                    <br />
                    <p id="event_description"></p>
                    <p class="card-text">Date: <span id="event_date"></span></p>
                    <p class="card-text">Location: <span id="event_location"></span></p>

                    <button type="submit" class="btn btn-primary" style="background-color: #ff2e63; border: none;">Confirm Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>





<script>
    $(document).ready(function() {
    // Ketika tombol register diklik, modal akan dibuka dengan data yang sesuai
    $('.open-modal-btn').on('click', function() {
        var eventId = $(this).data('event-id');
        var eventName = $(this).data('event-name');
        var eventDescription = $(this).data('event-description');
        var eventDate = $(this).data('event-date');
        var eventLocation = $(this).data('event-location');
        var eventImage = $(this).data('event-image');

        // Setel data event ke dalam modal
        $('#registerModal #event_id').val(eventId);
        $('#registerModal #event_name').text(eventName);
        $('#registerModal #event_description').text(eventDescription);
        $('#registerModal #event_date').text(eventDate);
        $('#registerModal #event_location').text(eventLocation);

        // Setel gambar, jika tidak ada gambar maka tampilkan gambar default
        if (eventImage && eventImage !== '') {
            $('#registerModal #event_image').attr('src', eventImage);
        } else {
            $('#registerModal #event_image').attr('src', '../assets/images/default-event.jpg');
        }
    });

    // Penanganan form pendaftaran
    $('#registrationForm').on('submit', function(e) {
    e.preventDefault(); // Mencegah pengiriman form secara default
    var formData = $(this).serialize(); // Ambil data form

    // AJAX request untuk mendaftarkan event
    $.ajax({
        type: 'POST',
        url: 'register-event-proses.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registered!',
                    text: 'You have successfully registered for the event.',
                    showConfirmButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // Muat ulang halaman untuk memperbarui jumlah peserta
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error,
                    showConfirmButton: true
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong. Please try again later.',
                showConfirmButton: true
            });
        }
    });
});
 });
   




</script>

</body>
</html>
