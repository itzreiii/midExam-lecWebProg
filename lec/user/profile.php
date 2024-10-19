<?php
// Start the session and include the necessary files
session_start();
include_once '../config/database.php';  // Adjust the path if needed
include_once '../includes/header.php';  // Include the header

// Check if the user is logged in, if not redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the user's profile information from the database
try {
    $sql = "SELECT * FROM users WHERE id = :id";
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
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="profile-container">
    <h2><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
    
    <div class="profile-details">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        <p><strong>Joined Date:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
    </div>

    <a href="edit-profile.php" class="btn">Edit Profile</a>
</div> 

</body>
</html>

<?php
include_once '../includes/footer.php';  // Include the footer
?>
