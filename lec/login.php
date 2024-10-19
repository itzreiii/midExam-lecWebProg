<?php
// login.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, password, role, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
         
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            if ($user['role'] === 'admin') {
                redirect('./admin/dashboard.php');
            } else {
                redirect('./user/my-events.php');
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}

include 'includes/header.php';
?>

<div class="auth-form">
    <br />
    <br />
    <h2 class="text-center mb-4">Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    
    <p class="text-center mt-3">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>