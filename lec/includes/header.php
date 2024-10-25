<?php
// includes/header.php
include_once 'functions.php';

// $logo_url = __DIR__ . '../assets/images/logo.png';
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
            color: rgba(0, 0, 0, 1); /* Set text color to black */
            transition: color 0.3s, text-shadow 0.3s; /* Hover transition effect */
        }
        .navbar-nav .nav-link:hover {
            color: rgba(206, 21, 218, 0.8); /* Hover color */
            text-shadow: 0 0 10px rgba(206, 21, 218, 0.8), 0 0 20px rgba(206, 21, 218, 0.5); /* Shadow effect */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#6028a7;">
    <div class="container">
        <a class="navbar-brand p-0" href="register-event.php">
            <img src="../assets/images/logo.png" alt="Logo" class="img-fluid" style="max-height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
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

<div class="container mt-5 pt-4"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
