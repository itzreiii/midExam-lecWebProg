<?php
class Auth {
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
}
?>
