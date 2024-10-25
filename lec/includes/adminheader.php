<?php
// includes/header.php
include_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2828a7;
            --hover-color: #3a3ab8;
            --text-color: #ffffff;
        }

        body {
            padding-top: 70px;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .navbar-brand img {
            max-height: 40px;
            transition: transform 0.3s ease;
        }

        .navbar-nav .nav-link {
            position: relative;
            color: var(--text-color) !important;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: #FFD700;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 80%;
        }

        .navbar-nav .nav-link:hover {
            color: #FFD700 !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            cursor: pointer;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        .navbar-toggler-icon {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        /* Animation for hamburger icon */
        .navbar-toggler[aria-expanded="true"] .navbar-toggler-icon {
            transform: rotate(90deg);
        }

        /* User profile button */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: var(--hover-color);
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: var(--primary-color);
                padding: 1rem;
                border-radius: 0 0 10px 10px;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                transition: transform 0.3s ease-in-out;
            }

            .navbar-collapse.collapsing {
                height: auto;
                transition: all 0.3s ease;
                overflow: hidden;
            }

            .navbar-collapse.show {
                display: block;
            }

            .navbar-nav {
                padding: 1rem 0;
            }

            .user-profile {
                margin: 1rem 0;
                justify-content: center;
            }

            .navbar-nav .nav-link::after {
                display: none;
            }

            .navbar-nav .nav-link {
                padding: 0.7rem 1rem;
                border-radius: 5px;
                text-align: center;
            }

            .navbar-nav .nav-link:hover {
                background-color: var(--hover-color);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand p-0" href="#">
                <img src="../assets/images/logo.png" alt="Logo" class="img-fluid">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="event-management.php">
                            <i class="fas fa-calendar-alt me-1"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user-management.php">
                            <i class="fas fa-users me-1"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registrations.php">
                            <i class="fas fa-clipboard-list me-1"></i> Registrations
                        </a>
                    </li>
                </ul>
                
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-lg-inline">Admin</span>
                    <a href="../auth/logout.php" class="nav-link d-inline ms-2">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the navbar toggler and collapse element
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');

            // Add click event listener to the toggler
            navbarToggler.addEventListener('click', function() {
                // Toggle the collapsed state
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInside = navbarToggler.contains(event.target) || navbarCollapse.contains(event.target);
                
                if (!isClickInside && navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });

            // Close menu when clicking on a nav link
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarToggler.click();
                    }
                });
            });
        });
    </script>
</body>
</html>