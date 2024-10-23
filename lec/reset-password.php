<?php
// reset-password.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';
$email = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_hash = hash("sha256", $token);
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verify token and check if it's expired
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token_hash]);
    
    if ($stmt->rowCount() > 0) {
        $email = $stmt->fetchColumn();
    } else {
        $error = "Invalid or expired reset link";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and remove reset token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE email = ?");
        
        try {
            $stmt->execute([$hashed_password, $email]);
            $success = "Password updated successfully!";
            header("refresh:2;url=login.php");
        } catch(PDOException $e) {
            $error = "Password update failed. Please try again.";
        }
    }
}
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
    <h2 class="text-center mb-4">Reset Password</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($email): ?>
        <form method="POST" action="">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    <?php endif; ?>
    
    <p class="text-center mt-3">
        <a href="login.php">Back to Login</a>
    </p>
</div>
    
</body>
</html>