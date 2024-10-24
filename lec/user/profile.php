<?php
// Start the session and include the necessary files
session_start();
include_once '../config/database.php';  // Include the database connection script
require_once '../includes/functions.php'; // Include the functions file for is_logged_in()
include_once '../includes/header.php';    // Include the header

// Check if the user is logged in, if not redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$database = new Database();

$conn = $database->getConnection();

// Fetch the user's profile information from the database
try {
    // Prepare the SQL statement
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql); // Ensure $conn is defined in database.php
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the user data was found
    if (!$user) {
        echo "<p>User not found!</p>";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Display error message if there's an issue
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #1a1a2e;
            color: white;
            font-family: 'Roboto', sans-serif;
        }


        h2 {
            text-align: center;
            margin: 40px;
            color:#efefef;
        }

        .card {
            background-color: #16213e;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            transition: 0.3s;
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.5);
        }

        .card-body p {
            font-size: 18px;
            color: #efefef;
        }

        .btn-primary {
    background-color: #6028a7;
    border: none;
    width: 100%;
    padding: 10px;
    font-size: 18px;
    border-radius: 8px;
    color: white;
    text-align: center;
    text-transform: uppercase;
    transition: 0.3s ease-in-out;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(96, 40, 167, 0.7), 0 0 30px rgba(96, 40, 167, 0.5);
}

.btn-primary::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 300%;
    height: 100%;
    background-color: #6028a7;
    transition: 0.5s;
    opacity: 0.5;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    background-color: #6028a7; /* Same base color */
    color: white;
    opacity: 1;
    box-shadow: 0 0 20px rgba(206, 21, 218, 0.7), 0 0 30px rgba(206, 21, 218, 0.6), 0 0 40px rgba(206, 21, 218, 0.5);
}


        @media only screen and (max-width: 768px) {
            

            .card-body p {
                font-size: 16px;
            }

            .btn-primary {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>   
    <div class="card">
        <div class="card-body">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Joined Date:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>

</body>
</html>
