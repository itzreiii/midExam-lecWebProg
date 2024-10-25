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
        }

        .navbar-brand img {
            max-height: 40px;
        }

        .navbar-nav .nav-link {
            color: var(--text-color) !important;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
        }

        .navbar-nav .nav-link:hover {
            color: #FFD700 !important;
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
        }

        /* Navbar collapse animation */
        .navbar-collapse {
            transition: height 0.35s ease;
            height: 0;
            overflow: hidden;
        }

        .navbar-collapse.show {
            height: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: var(--primary-color);
                padding: 1rem;
            }

            .user-profile {
                margin: 1rem 0;
                justify-content: center;
            }

            .navbar-nav .nav-link {
                text-align: center;
            }
        }

        @media (min-width: 992px) {
            .navbar-collapse {
                height: auto !important;
                overflow: visible;
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
            
            <!-- Tombol hamburger -->
            <button class="navbar-toggler" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navbar yang bisa di-collapse -->
            <div class="navbar-collapse" id="navbarNav">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            let isOpen = false;

            // Function to set navbar height
            function setNavbarHeight() {
                if (window.innerWidth < 992) { // Only for mobile view
                    if (isOpen) {
                        const height = navbarCollapse.scrollHeight;
                        navbarCollapse.style.height = height + 'px';
                    } else {
                        navbarCollapse.style.height = '0px';
                    }
                } else {
                    navbarCollapse.style.height = 'auto';
                }
            }

            // Toggle navbar
            toggler.addEventListener('click', function() {
                isOpen = !isOpen;
                if (isOpen) {
                    navbarCollapse.classList.add('show');
                } else {
                    navbarCollapse.classList.remove('show');
                }
                setNavbarHeight();
            });

            // Close navbar when clicking a link
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 992 && isOpen) {
                        isOpen = false;
                        navbarCollapse.classList.remove('show');
                        setNavbarHeight();
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', setNavbarHeight);

            // Initial setup
            setNavbarHeight();
        });
    </script>
</body>
</html>