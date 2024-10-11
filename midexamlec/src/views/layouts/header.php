<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration System</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Corrected path to CSS -->
</head>
<body>
<header class="header">
    <h1>Welcome to the Event Registration System</h1>
    <nav>
        <ul>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="../auth/login.php">Login</a></li> <!-- Corrected link for login -->
                <li><a href="../auth/register.php">Register</a></li> <!-- Corrected link for register -->
            <?php else: ?>
                <li><a href="../auth/logout.php">Logout</a></li> <!-- Corrected link for logout -->
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="container">
