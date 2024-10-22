<?php
// register.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            $activation_token = bin2hex(random_bytes(16));

            $activation_token_hash = hash("sha256", $activation_token);       
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, account_activation_hash) VALUES (?, ?, ?, ?)");
            
            try {
                // DIR AKAN DIGANTI KALO UDA DIHOSTING
                $dir = "localhost/uts/webprog-lecture/lec/activate-account.php?token=$activation_token";
                $stmt->execute([$name, $email, $hashed_password, $activation_token_hash]);
                $success = "Registration successful! Please check your email to activate your account.";

                // Muat mailer.php, dan pastikan $mail didefinisikan di sana
                $mail = require __DIR__ . "/mailer.php";

                $mail->setFrom("noreply@example.com");
                $mail->addAddress($_POST['email']);
                $mail->Subject = "Activate Account";

                // Sisipkan variabel $dir dalam Body
                $mail->Body = <<<END
                Click <a href="$dir">here</a> 
                to activate your account.
                END;

                try {

                    $mail->send();

                } catch (Exception $e) {

                    echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                    exit;

                }
                header("refresh:2;url=login.php");
            } catch(PDOException $e) {
                $error = "Registration failed. Please try again.";
            } 
        }
    }
}

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    
<div class="auth-form">
    <h2 class="text-center mb-4">Register</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    
    <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
    
</body>
</html>
