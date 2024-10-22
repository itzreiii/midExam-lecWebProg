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
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Link to your CSS file -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

<?php
include_once '../includes/footer.php';  // Include the footer
?>