<?php
class ImageUploader {
    public static function upload($file) {
        // Handle file upload and move to 'uploads/' folder
        $target_dir = "../public/assets/uploads/";
        $target_file = $target_dir . basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $target_file);
    }
}
?>
