<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../utils/Auth.php';

class UserController {
    private $userModel;
    private $eventModel;
    private $registrationModel;

    public function __construct() {
        $this->userModel = new User();
        $this->eventModel = new Event();
        $this->registrationModel = new Registration();
    }

    public function dashboard() {
        // Check login status
        if (!Auth::isLoggedIn()) {
            header('Location: ?action=login');
            exit;
        }

        $userId = Auth::getUserId();
        $user = $this->userModel->getUserById($userId);
        $registeredEvents = $this->registrationModel->getUserRegisteredEvents($userId);
        $upcomingEvents = $this->eventModel->getUpcomingEvents();

        // Include the dashboard view with the data
        include '../src/views/user/dashboard.php';
    }

    public function updateProfile() {
        Auth::requireLogin();
        $userId = Auth::getUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Replace deprecated FILTER_SANITIZE_STRING with htmlspecialchars
            $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');

            $success = $this->userModel->updateUser($userId, [
                'name' => $name,
                'email' => $email
            ]);

            if ($success) {
                $_SESSION['success'] = 'Profile updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }

            header('Location: ?action=profile');
            exit;
        }
    }
}