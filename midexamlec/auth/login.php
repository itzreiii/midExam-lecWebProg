<?php
session_start();
require_once '../config/database.php'; // Adjust path if needed
require_once '../src/controllers/AuthController.php'; // Adjust path if needed

$authController = new AuthController();

$error = '';

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ?action=login');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Call the AuthController's login function and check the result
    if (!$authController->login($email, $password)) {
        $error = "Invalid email or password!";
    } else {
        // Redirect to the homepage if login is successful
        header("Location: ../index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Update if necessary -->
</head>
<body>
    <h2>Login</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p> <!-- Display error if login fails -->
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
