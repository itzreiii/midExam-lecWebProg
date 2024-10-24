
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
$conn = $database->getConnection(); // Get the database connection

// Fetch the user's profile information from the database
try {
    $sql = "SELECT name, email FROM users WHERE id = :id"; // Select only necessary fields
    $stmt = $conn->prepare($sql);
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['name'];
    $new_email = $_POST['email'];

    // Update the user's profile information
    try {
        $update_sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':name', $new_username);
        $update_stmt->bindParam(':email', $new_email);
        $update_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $update_stmt->execute();

        // Redirect to profile page or show success message
        header('Location: profile.php');
        exit();
    } catch (PDOException $e) {
        echo "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        background-color: #1a1a2e;
        color: white;
        font-family: 'Roboto', sans-serif;
    }

    h2 {
        text-align: center;
        margin: 30px auto;
        color: #efefef;
        font-size: 32px;
        font-weight: 300;
        padding-top: 50px;
    }

    .card {
        background-color: #16213e;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        transition: 0.3s;
        margin-bottom: 40px;
    }


    .card-body p, .form-label {
        font-size: 18px;
        color: #efefef;
    }

    .form-control {
        background-color: #16213e;
        border: 1px solid #efefef;
        color: white;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-control:focus {
        background-color: #1a1a2e;
        border-color: #6028a7;
        box-shadow: 0 0 8px rgba(96, 40, 167, 0.7);
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
        background-color: #6028a7;
        color: white;
        opacity: 1;
        box-shadow: 0 0 20px rgba(206, 21, 218, 0.7), 0 0 30px rgba(206, 21, 218, 0.6), 0 0 40px rgba(206, 21, 218, 0.5);
    }
    

    @media only screen and (max-width: 768px) {
        .card-body p, .form-label {
            font-size: 16px;
        }

        .btn-primary {
            font-size: 16px;
        }
    }
</style>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>
    <div class="card">
        <div class="card-body">
            <form action="edit-profile.php" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label"><p style="color: black;">Username:</p></label>
                    <input type="text" id="username" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group pb-3">
                    <label for="email" class="form-label"><p style="color: black;">Email:</p></label>
                    <input type="email" id="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-10">Update Profile</button>
            </form>
        </div>
    </div>
    

</body>
</html>

