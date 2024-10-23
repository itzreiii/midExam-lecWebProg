<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .main-container {
            text-align: center;
            background-color: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .btn-custom {
            padding: 15px 30px;
            font-size: 1.2rem;
            margin: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="main-container">
    <h1>Welcome to Event Registration</h1>
    <p>Register for the latest events or log in to manage your registrations!</p>
    
    <a href="login.php" class="btn btn-primary btn-custom">Login</a>
    <a href="register.php" class="btn btn-success btn-custom">Register</a>
</div>

</body>
</html>
