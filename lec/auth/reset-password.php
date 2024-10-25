<?php
// reset-password.php
require_once '../config/database.php';
require_once '../includes/functions.php';

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
    <title>Reset Password - Ticketbox</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        html, body {
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, #7b2ff7, #f107a3);
            background-size: cover;
            background-position: center;
            color: #ffffff;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 15px;
        }

        .main-container {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            margin: auto;
        }

        h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #fff;
        }

        .alert-danger {
            background-color: rgba(255, 0, 0, 0.3);
            border: none;
            color: #fff;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.3);
            border: none;
            color: #fff;
            margin-bottom: 20px;
        }

        form {
            box-shadow: none;
            background-color: rgba(0, 0, 0, 0.0);
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 1.1rem;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: #f107a3;
            box-shadow: 0 0 0 0.2rem rgba(241, 7, 163, 0.25);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-custom {
            padding: 15px 30px;
            font-size: 1.2rem;
            margin: 10px 0;
            border-radius: 50px;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary {
            background-color: #6a0dad;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5d008f;
            transform: scale(1.05);
        }

        .links-section {
            margin-top: 20px;
            font-size: 1.1rem;
        }

        .links-section a {
            color: #f107a3;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .links-section a:hover {
            color: #d30690;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 30px;
            }

            h2 {
                font-size: 2rem;
            }

            .form-control, .btn-custom {
                font-size: 1rem;
                padding: 10px 20px;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 20px;
            }

            h2 {
                font-size: 1.8rem;
            }

            .form-control, .btn-custom {
                font-size: 0.9rem;
                padding: 10px 15px;
            }

            .links-section {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container wrapper">
        <div class="main-container">
            <h2>Reset Password</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($email): ?>
                <form method="POST" action="">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter new password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-custom">Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="links-section">
                <p>
                    <a href="login.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>