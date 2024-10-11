<?php
require_once '../src/models/User.php';
require_once '../src/utils/Security.php';

class AuthController {
    public function login($email, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php"); // Redirect to homepage
            exit();
        } else {
            echo "Invalid email or password!";
        }
    }

    public function register($data) {
        global $pdo;
        $name = Security::sanitizeInput($data['name']);
        $email = Security::sanitizeInput($data['email']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT); // Encrypt password

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo "Email already exists!";
            return;
        }

        // Insert user into the database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $password]);

        echo "Registration successful! You can now <a href='login.php'>login</a>.";
    }
}
?>
