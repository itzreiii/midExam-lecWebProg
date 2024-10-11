<?php
require_once '../src/models/User.php';
require_once '../src/utils/Security.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        // Handle GET requests by redirecting
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=home');
            exit;
        }

        // Validate inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        if (!$email || !$password) {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: ?action=login');
            exit;
        }

        // Attempt login
        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'Invalid credentials';
                header('Location: ?action=login');
                exit;
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            // Regenerate session ID for security
            session_regenerate_id(true);

            header('Location: ?action=dashboard');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred during login';
            error_log($e->getMessage());
            header('Location: ?action=login');
            exit;
        }
    }

    public function register($data) {
        try {
            // Validate input data
            if (!$this->validateRegistrationData($data)) {
                $_SESSION['error'] = 'Invalid registration data';
                header('Location: ?action=register');
                exit;
            }

            // Sanitize inputs
            $name = Security::sanitizeInput($data['name']);
            $email = Security::sanitizeInput($data['email']);
            $password = password_hash($data['password'], PASSWORD_BCRYPT);

            // Check if email exists
            if ($this->userModel->emailExists($email)) {
                $_SESSION['error'] = 'Email already exists';
                header('Location: ?action=register');
                exit;
            }

            // Create user
            $userId = $this->userModel->createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'user'
            ]);

            if ($userId) {
                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: ?action=login');
            } else {
                throw new Exception('Failed to create user');
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred during registration';
            error_log($e->getMessage());
            header('Location: ?action=register');
            exit;
        }
    }

    public function logout() {
        // Destroy session
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        header('Location: ?action=login');
        exit;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    private function validateRegistrationData($data) {
        return (
            isset($data['name']) && strlen($data['name']) >= 2 &&
            isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
            isset($data['password']) && strlen($data['password']) >= 8
        );
    }
}