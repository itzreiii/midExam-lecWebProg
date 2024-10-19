<?php
// includes/functions.php
// session_start();

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
 
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($location) {
    header("Location: $location");
    exit();
}

function upload_file($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Check if image file is actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return "File is not an image.";
    }
    
    // Check file size
    if ($file["size"] > 500000) {
        return "Sorry, your file is too large.";
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    } else {
        return "Sorry, there was an error uploading your file.";
    }
}