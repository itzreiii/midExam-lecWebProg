<?php
// includes/header.php
include_once 'functions.php';

// $logo_url = __DIR__ . '../assets/images/ticket.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .navbar-nav .nav-link {
            color: black; /* Mengatur warna teks menjadi hitam */
            transition: color 0.3s, text-shadow 0.3s; /* Efek transisi untuk hover */
        }
        .navbar-nav .nav-link:hover {
            color: #FFD700; /* Warna saat hover (kuning cerah) */
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7), 0 0 20px rgba(255, 215, 0, 0.5); /* Efek bayangan saat hover */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#6028a7;">
    <div class="container">
        <a class="navbar-brand p-0" href="register-event.php">
            <img src="../assets/images/ticket.png" alt="Logo" class="img-fluid" style="max-height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto"> <!-- Memindahkan nav links ke kanan -->
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard.php">Admin Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./register-event.php">Available Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/my-events.php">My Events</a>
                        </li>  
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/register.php">Register</a>
                    </li> 
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4"></div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
