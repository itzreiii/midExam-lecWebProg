<?php
session_start();
session_destroy(); // End the session
header("Location: index.php"); // Redirect back to the homepage
exit();
?>
