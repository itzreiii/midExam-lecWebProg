<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        /* Background gradien dan gambar opsional */
        body {
            background: linear-gradient(135deg, #7b2ff7, #f107a3);
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #ffffff;
        }

        /* Style container utama */
        .main-container {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6); /* Transparan untuk melihat latar belakang */
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Tombol */
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

        .btn-success {
            background-color: #f107a3;
            border: none;
        }

        .btn-success:hover {
            background-color: #d30690;
            transform: scale(1.05);
        }

        /* Responsif untuk mobile */
        @media (max-width: 768px) {
            .main-container {
                padding: 30px;
            }

            h1 {
                font-size: 2rem;
            }

            p {
                font-size: 1rem;
            }

            .btn-custom {
                font-size: 1rem;
                padding: 10px 20px;
            }
        }

        /* Responsif untuk layar yang sangat kecil */
        @media (max-width: 480px) {
            .main-container {
                padding: 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            p {
                font-size: 0.9rem;
            }

            .btn-custom {
                font-size: 0.9rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <h1>Welcome to Ticketbox</h1>
    <p>Register for the latest events or log in to manage your registrations!</p>
    
    <a href="auth/login.php" class="btn btn-primary btn-custom">Login</a>
    <a href="auth/register.php" class="btn btn-success btn-custom">Register</a>
</div>

</body>
</html>
