<?php
class UserController {
    public function myEvents($userId) {
        $registration = new Registration();
        return $registration->getUserEvents($userId);
    }

    public function editProfile($userId, $data) {
        // Edit user profile
    }
}
?>
