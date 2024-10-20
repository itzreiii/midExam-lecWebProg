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

    echo $new_username;
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
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="profile-container">
    <h2>Edit Profile</h2>
    
    <form action="edit-profile.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

</body>
</html>

<?php
include_once '../includes/footer.php';  // Include the footer
?>