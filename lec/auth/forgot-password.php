<?php
// forgot-password.php
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    
    $db = new Database();
    $conn = $db->getConnection();

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        // Generate reset token
        $reset_token = bin2hex(random_bytes(16));
        $reset_token_hash = hash("sha256", $reset_token);
        
        // Set token expiration (1 hour from now)
        $expiry = date("Y-m-d H:i:s", time() + 60 * 60);
        
        // Save reset token in database
        $stmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?");
        
        try {
            $stmt->execute([$reset_token_hash, $expiry, $email]);
            
            // Send reset email
            $mail = require __DIR__ . "/mailer.php";
            
            $mail->setFrom("noreply@example.com");
            $mail->addAddress($email);
            $mail->Subject = "Password Reset";
            
            $reset_link = "http://localhost/uts/webprog-lecture/lec/reset-password.php?token=" . $reset_token;            
            $mail->Body = <<<END
            Click <a href="$reset_link">here</a> to reset your password.
            This link will expire in 1 hour.
            END;
            
            try {
                $mail->send();
                $success = "Password reset link sent! Please check your email.";
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            }
            
        } catch(PDOException $e) {
            $error = "Error occurred. Please try again.";
        }
    } else {
        // Don't reveal if email exists for security
        $success = "If that email exists in our system, you will receive a password reset link.";
    }
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    
<div class="auth-form">
    <h2 class="text-center mb-4">Forgot Password</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    
    <p class="text-center mt-3">
        <a href="login.php">Back to Login</a>
    </p>
</div>
    
</body>
</html>
