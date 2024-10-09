<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_registration');

// Improved database connection with error handling
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper functions
function sanitize($data) {
    global $db;
    return mysqli_real_escape_string($db, htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Database initialization
function initDatabase() {
    global $db;
    
    try {
        // Create users table
        $db->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NULL,
            phone VARCHAR(20) UNIQUE NULL,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            auth_type ENUM('email', 'phone') NOT NULL DEFAULT 'email',
            verification_code VARCHAR(6) NULL,
            is_verified BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Create events table
        $db->query("CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            time TIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            max_participants INT NOT NULL,
            image_path VARCHAR(255),
            status ENUM('open', 'closed', 'canceled') DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Create registrations table
        $db->query("CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_id INT NOT NULL,
            registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )");

        // Create default admin user if not exists
        $adminEmail = 'admin@example.com';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminName = 'Admin';
        
        $stmt = $db->prepare("INSERT IGNORE INTO users (name, email, password, is_admin, is_verified, auth_type) 
                             VALUES (?, ?, ?, 1, 1, 'email')");
        $stmt->bind_param("sss", $adminName, $adminEmail, $adminPassword);
        $stmt->execute();

        return true;
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

// Initialize the database
initDatabase();

// Routing
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

// Handle AJAX requests
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $response['message'] = 'Invalid request';
        echo json_encode($response);
        exit;
    }

    switch ($_POST['ajax_action']) {
        case 'login':
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];
            
            if (!$email) {
                $response['message'] = 'Invalid email format';
                break;
            }
            
            $stmt = $db->prepare("SELECT id, password, name, is_admin FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $response = ['success' => true, 'message' => 'Login successful!'];
                } else {
                    $response['message'] = 'Invalid password';
                }
            } else {
                $response['message'] = 'User not found';
            }
            break;
            
        case 'register':
            $name = trim($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];
            
            if (!$email) {
                $response['message'] = 'Invalid email format';
                break;
            }
            
            if (strlen($password) < 6) {
                $response['message'] = 'Password must be at least 6 characters';
                break;
            }
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $response['message'] = 'Email already registered';
                break;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $db->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['is_admin'] = 0;
                $response = ['success' => true, 'message' => 'Registration successful!'];
            } else {
                $response['message'] = 'Registration failed. Please try again.';
            }
            break;

        case 'verify':
            $code = sanitize($_POST['verification_code']);
            $identifier = sanitize($_POST['identifier']);
            $auth_type = sanitize($_POST['auth_type']);

            if (!preg_match('/^\d{6}$/', $code)) {
                $response['message'] = 'Invalid verification code format';
                echo json_encode($response);
                exit;
            }

            $field = $auth_type === 'email' ? 'email' : 'phone';
            
            $stmt = $db->prepare("SELECT id FROM users WHERE $field = ? AND verification_code = ? AND is_verified = 0");
            $stmt->bind_param("ss", $identifier, $code);
            $stmt->execute();
            
            if ($result = $stmt->get_result()) {
                if ($user = $result->fetch_assoc()) {
                    $update = $db->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
                    $update->bind_param("i", $user['id']);
                    
                    if ($update->execute()) {
                        $_SESSION['user_id'] = $user['id'];
                        $response = ['success' => true, 'message' => 'Verification successful'];
                    } else {
                        $response['message'] = 'Verification failed';
                    }
                } else {
                    $response['message'] = 'Invalid verification code';
                }
            }
            break;
    }

    echo json_encode($response);
    exit;
}

// File upload function
function uploadImage($file) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Crop image
        $image = imagecreatefromstring(file_get_contents($target_file));
        $width = imagesx($image);
        $height = imagesy($image);
        $size = min($width, $height);
        $cropped = imagecreatetruecolor(300, 200);
        imagecopyresampled($cropped, $image, 0, 0, ($width - $size) / 2, ($height - $size) / 2, 300, 200, $size, $size);
        imagejpeg($cropped, $target_file);
        imagedestroy($image);
        imagedestroy($cropped);
        
        return $target_file;
    } else {
        return false;
    }
}

// Main content
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-backdrop.show { opacity: 0.7; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="?action=home">Event System</a>
        <div class="navbar-nav ms-auto">
            <?php if (isLoggedIn()): ?>
                <span class="nav-link">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <?php if (isAdmin()): ?>
                    <a class="nav-link" href="?action=admin_dashboard">Admin Dashboard</a>
                <?php endif; ?>
                <a class="nav-link" href="?action=my_events">My Events</a>
                <a class="nav-link" href="?action=logout">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
<?php
switch ($action) {
    case 'home':
        // Display available events
        $result = $db->query("SELECT * FROM events WHERE status = 'open' ORDER BY date");
        ?>
        <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="jumbotron bg-light p-5 rounded">
                    <h1 class="display-4">Welcome to EventMaster</h1>
                    <p class="lead">Discover and join exciting events in your area. From conferences to workshops, find your next adventure here!</p>
                    <?php if (!isLoggedIn()): ?>
                        <hr class="my-4">
                        <p>Join our community to register for events and get personalized recommendations.</p>
                        <button class="btn btn-primary btn-lg me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                        <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h2 class="mb-4">Featured Events</h2>
        <div class="row">
            <?php
            $result = $db->query("SELECT *, 
                                 (SELECT COUNT(*) FROM registrations WHERE event_id = events.id) as participant_count 
                                 FROM events 
                                 WHERE status = 'open' AND date >= CURDATE() 
                                 ORDER BY date ASC LIMIT 6");
            while ($event = $result->fetch_assoc()):
                $spotsLeft = $event['max_participants'] - $event['participant_count'];
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($event['image_path']): ?>
                            <img src="<?= htmlspecialchars($event['image_path']) ?>" class="card-img-top" alt="Event Image" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                            <div class="text-muted mb-2">
                                <i class="bi bi-calendar"></i> <?= date('F j, Y', strtotime($event['date'])) ?><br>
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?= $spotsLeft > 0 ? 'success' : 'danger' ?>">
                                    <?= $spotsLeft > 0 ? "$spotsLeft spots left" : 'Fully booked' ?>
                                </span>
                                <a href="?action=event_details&id=<?= $event['id'] ?>" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    break;
        
        case 'event_details':
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                echo "<div class='alert alert-danger'>Invalid event ID.</div>";
                break;
            }
            
            $event_id = (int)$_GET['id'];
            $stmt = $db->prepare("SELECT e.*, 
                                  (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as current_registrations
                                  FROM events e WHERE e.id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            
            if (!$event) {
                echo "<div class='alert alert-danger'>Event not found.</div>";
                break;
            }
        
            // Check if user is already registered
            $is_registered = false;
            if (isLoggedIn()) {
                $check_stmt = $db->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
                $check_stmt->bind_param("ii", $_SESSION['user_id'], $event_id);
                $check_stmt->execute();
                $is_registered = $check_stmt->get_result()->num_rows > 0;
            }
        
            // Display event details
            ?>
            <div class="card">
                <?php if ($event['image_path']): ?>
                    <img src="<?= htmlspecialchars($event['image_path']) ?>" class="card-img-top" alt="Event Image">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= htmlspecialchars($event['name']) ?></h1>
                    <p class="card-text"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    <div class="mb-3">
                        <strong>Date:</strong> <?= htmlspecialchars($event['date']) ?><br>
                        <strong>Time:</strong> <?= htmlspecialchars($event['time']) ?><br>
                        <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?><br>
                        <strong>Available Spots:</strong> <?= $event['max_participants'] - $event['current_registrations'] ?> 
                        out of <?= $event['max_participants'] ?>
                    </div>
                    
                    <?php if (isLoggedIn() && !isAdmin()): ?>
                        <?php if ($is_registered): ?>
                            <div class="alert alert-success">You are registered for this event!</div>
                            <form method="post" action="?action=unregister_event">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                <button type="submit" class="btn btn-danger">Cancel Registration</button>
                            </form>
                        <?php elseif ($event['current_registrations'] < $event['max_participants']): ?>
                            <form method="post" action="?action=register_event">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                <button type="submit" class="btn btn-primary">Register for Event</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">This event is full.</div>
                        <?php endif; ?>
                    <?php elseif (!isLoggedIn()): ?>
                        <div class="alert alert-info">Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to register for this event.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            break;
        
    case 'login':
        ?>
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
        <form method="post" action="?action=login">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <?php
        break;
        
    case 'register':
        ?>
        <h1>Register</h1>
        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
        <form method="post" action="?action=register">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <?php
        break;
        
    case 'admin_dashboard':
        if (!isAdmin()) {
            header("Location: ?action=home");
            exit;
        }
        ?>
        <h1>Admin Dashboard</h1>
        <h2>Create New Event</h2>
        <form method="post" action="?action=create_event" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Event Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Max Participants</label>
                <input type="number" name="max_participants" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Event Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Create Event</button>
        </form>

        <h2 class="mt-4">Existing Events</h2>
        <?php
        $result = $db->query("SELECT events.*, COUNT(registrations.id) as registrant_count 
                              FROM events 
                              LEFT JOIN registrations ON events.id = registrations.event_id 
                              GROUP BY events.id");
        while ($event = $result->fetch_assoc()):
        ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                    <p>Registrants: <?= $event['registrant_count'] ?> / <?= $event['max_participants'] ?></p>
                    <a href="?action=view_registrations&event_id=<?= $event['id'] ?>" class="btn btn-info">View Registrations</a>
                    <a href="?action=edit_event&event_id=<?= $event['id'] ?>" class="btn btn-warning">Edit Event</a>
                </div>
            </div>
        <?php endwhile; ?>
        <?php
        break;
        
    case 'view_registrations':
        if (!isAdmin()) {
            header("Location: ?action=home");
            exit;
        }
        $event_id = (int)$_GET['event_id'];
        $stmt = $db->prepare("SELECT users.name, users.email FROM registrations 
                              JOIN users ON registrations.user_id = users.id 
                              WHERE registrations.event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        <h1>Event Registrations</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($registration = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($registration['name']) ?></td>
                        <td><?= htmlspecialchars($registration['email']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php
        break;
        
    case 'my_events':
        if (!isLoggedIn()) {
            header("Location: ?action=login");
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $stmt = $db->prepare("SELECT events.* FROM events 
                              JOIN registrations ON events.id = registrations.event_id 
                              WHERE registrations.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        <h1>My Registered Events</h1>
        <?php while ($event = $result->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                    <p><?= htmlspecialchars($event['description']) ?></p>
                    <p>Date: <?= $event['date'] ?> at <?= $event['time'] ?></p>
                    <p>Location: <?= htmlspecialchars($event['location']) ?></p>
                    <a href="?action=event_details&id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
        <?php
        break;
        
    case 'logout':
    session_destroy();
    header("Location: ?action=login");
    exit;
        
    case 'register_event':
        if (!isLoggedIn() || isAdmin()) {
            header("Location: ?action=home");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event_id = (int)$_POST['event_id'];
            $user_id = $_SESSION['user_id'];
            
            // Check if user is already registered
            $stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                echo "<p>You are already registered for this event.</p>";
                break;
            }
            
            // Check if event is full
            $stmt = $db->prepare("SELECT max_participants, (SELECT COUNT(*) FROM registrations WHERE event_id = events.id) as current_participants FROM events WHERE id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            if ($event['current_participants'] >= $event['max_participants']) {
                echo "<p>Sorry, this event is already full.</p>";
                break;
            }
            
            // Register user for the event
            $stmt = $db->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $event_id);
            if ($stmt->execute()) {
                echo "<p>You have successfully registered for the event.</p>";
            } else {
                echo "<p>There was an error registering for the event. Please try again.</p>";
            }
        }
        break;
        
    case 'create_event':
        if (!isAdmin()) {
            header("Location: ?action=home");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $date = $_POST['date'];
            $time = $_POST['time'];
            $location = sanitize($_POST['location']);
            $max_participants = (int)$_POST['max_participants'];
            
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image_path = uploadImage($_FILES['image']);
            }
            
            $stmt = $db->prepare("INSERT INTO events (name, description, date, time, location, max_participants, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $description, $date, $time, $location, $max_participants, $image_path);
            
            if ($stmt->execute()) {
                echo "<p>Event created successfully.</p>";
            } else {
                echo "<p>There was an error creating the event. Please try again.</p>";
            }
        }
        break;
        
    case 'edit_event':
        if (!isAdmin()) {
            header("Location: ?action=home");
            exit;
        }
        
        $event_id = (int)$_GET['event_id'];
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        
        if (!$event) {
            echo "<p>Event not found.</p>";
            break;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $date = $_POST['date'];
            $time = $_POST['time'];
            $location = sanitize($_POST['location']);
            $max_participants = (int)$_POST['max_participants'];
            
            $image_path = $event['image_path'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $new_image_path = uploadImage($_FILES['image']);
                if ($new_image_path) {
                    $image_path = $new_image_path;
                }
            }
            
            $stmt = $db->prepare("UPDATE events SET name = ?, description = ?, date = ?, time = ?, location = ?, max_participants = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $name, $description, $date, $time, $location, $max_participants, $image_path, $event_id);
            
            if ($stmt->execute()) {
                echo "<p>Event updated successfully.</p>";
            } else {
                echo "<p>There was an error updating the event. Please try again.</p>";
            }
        }
        ?>
        <h1>Edit Event</h1>
        <form method="post" action="?action=edit_event&event_id=<?= $event_id ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Event Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($event['name']) ?>">
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required><?= htmlspecialchars($event['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required value="<?= $event['date'] ?>">
            </div>
            <div class="mb-3">
                <label>Time</label>
                <input type="time" name="time" class="form-control" required value="<?= $event['time'] ?>">
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required value="<?= htmlspecialchars($event['location']) ?>">
            </div>
            <div class="mb-3">
                <label>Max Participants</label>
                <input type="number" name="max_participants" class="form-control" required value="<?= $event['max_participants'] ?>">
            </div>
            <div class="mb-3">
                <label>Event Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if ($event['image_path']): ?>
                    <img src="<?= htmlspecialchars($event['image_path']) ?>" class="img-thumbnail mt-2" style="max-width: 200px;" alt="Current Event Image">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
        <?php
        break;
}
?>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <div id="loginMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <div id="registerMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Your Account</h5>
            </div>
            <div class="modal-body">
                <form id="verificationForm">
                    <input type="hidden" name="auth_type" id="verificationAuthType">
                    <input type="hidden" name="identifier" id="verificationIdentifier">
                    <div class="mb-3">
                        <label class="form-label">Enter Verification Code</label>
                        <input type="text" class="form-control" name="verification_code" required pattern="[0-9]{6}" maxlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </form>
                <div id="verificationMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));

    // Form handling for authentication
    function handleAuth(formId, action) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('ajax_action', action);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById(`${action}Message`);
                messageDiv.innerHTML = data.message;
                messageDiv.className = `alert ${data.success ? 'alert-success' : 'alert-danger'}`;
                
                if (data.success) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById(`${action}Message`).innerHTML = 'An error occurred. Please try again.';
                document.getElementById(`${action}Message`).className = 'alert alert-danger';
            });
        });
    }

    // Handle authentication type switch
    document.getElementById('authType')?.addEventListener('change', function() {
        const emailField = document.getElementById('emailField');
        const phoneField = document.getElementById('phoneField');
        const emailInput = emailField.querySelector('input');
        const phoneInput = phoneField.querySelector('input');
        
        if (this.value === 'email') {
            emailField.style.display = 'block';
            phoneField.style.display = 'none';
            emailInput.required = true;
            phoneInput.required = false;
        } else {
            emailField.style.display = 'none';
            phoneField.style.display = 'block';
            emailInput.required = false;
            phoneInput.required = true;
        }
    });

    // Initialize form handlers
    handleAuth('loginForm', 'login');
    handleAuth('registerForm', 'register');
    handleAuth('verificationForm', 'verify');
});
</script>

</body>
</html>