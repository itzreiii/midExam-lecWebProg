<?php
// login.php
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, password, role, name, account_activation_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();

        if ($user && $user['account_activation_hash'] === null) {

            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../user/register-event.php');
                }
            } else {
                $error = "Invalid password!";
            }
        } elseif ($user['account_activation_hash'] != null) {
            $error = "Silahkan verifikasi akun terlebih dahulu.";
        } else {
            $error = "Invalid Username or Password.";
        }
    } else {
        $error = "Email not found!";
    }
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ticketbox</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #7b2ff7, #f107a3);
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
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

        .alert {
            background-color: rgba(255, 0, 0, 0.3);
            border: none;
            color: #fff;
            margin-bottom: 20px;
        }

       

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            color: #1a1a2e;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 1.1rem;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(106, 13, 173, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
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
            <h2>Login to Ticketbox</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
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

                <button type="submit" class="btn btn-primary btn-custom">Login</button>
            </form>

            <div class="links-section">
                <p>
                    <a href="forgot-password.php">Forgot Password?</a>
                </p>
                <p>
                    Don't have an account? <a href="register.php">Register</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>