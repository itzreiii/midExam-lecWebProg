<?php
// register.php
require_once '../config/database.php';
require_once '../includes/functions.php';

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
                // $dir = "https://slategrey-lemur-191861.hostingersite.com/activate-account.php?token=$activation_token";
                $dir = "localhost/webprog-lecture/lec/activate-account.php?token=$activation_token";
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
            } catch(PDOException $e) {
                $error = "Registration failed. Please try again.";
            } 
        }
    }
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ticketbox</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
       body {
    background: linear-gradient(135deg, #7b2ff7, #f107a3);
    background-size: cover;
    background-position: center;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    justify-content: center;
    align-items: center;
}

.wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
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
    padding-bottom: 50px;
}

h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #fff;
}

.alert {
    border: none;
    margin-bottom: 20px;
    border-radius: 10px;
}

.alert-danger {
    background-color: rgba(255, 0, 0, 0.3);
    color: #fff;
}

.alert-success {
    background-color: rgba(40, 167, 69, 0.3);
    color: #fff;
}

form {
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

/* .form-control {
    background-color: #333; 
    border: 1px solid #555; 
    padding: 12px 20px;
    border-radius: 50px;
    font-size: 1.1rem;
}

.form-control:focus {
    background-color: #444; 
    border-color: #777;
    box-shadow: 0 0 0 0.2rem white;
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.6); 
} */

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
    body {
        padding: 15px;
    }

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
    <div class="wrapper">
        <div class="main-container">
            <h2>Register</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Enter your name" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-custom">Register</button>
            </form>

            <div class="links-section">
                <p style="color: white;">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>