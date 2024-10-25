<?php
session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Fetch all users
$query = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at ASC";
$users = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/adminheader.php';  // Include the header/navbar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            padding: 1rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }
        
        @media (min-width: 768px) {
            .page-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header h2 {
            font-size: 1.1rem;
            margin: 0;
            color: var(--primary-color);
        }
        
        .user-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-right: 0.75rem;
        }
        
        .role-badge {
            padding: 0.4em 0.8em;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin: 0.5rem 0;
        }
        
        .btn-back {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            background-color: #6c757d;
            border: none;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }
        
        .info-item i {
            width: 20px;
            margin-right: 0.5rem;
            color: #666;
        }
        
        /* Desktop view table */
        @media (min-width: 768px) {
            .mobile-view {
                display: none;
            }
            
            .desktop-view {
                display: block;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
        
        /* Mobile view cards */
        @media (max-width: 767px) {
            .mobile-view {
                display: block;
            }
            
            .desktop-view {
                display: none;
            }
            
            .card-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-back {
                width: 100%;
                justify-content: center;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .action-buttons {
                margin-top: 1rem;
                display: grid;
                gap: 0.5rem;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <h1 class="page-title fw-bold">
        <i class="fas fa-users me-2"></i>
        User Management
    </h1>
    
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-users me-2 text-primary"></i>
                <h2>User List</h2>
            </div>
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Dashboard
            </a>
        </div>
        
        <div class="card-body">
            <!-- Desktop View - Table -->
            <div class="desktop-view">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                                            <small class="text-muted">ID: <?= htmlspecialchars($user['id']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-muted me-2"></i>
                                        <?= htmlspecialchars($user['email']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $roleClass = match($user['role']) {
                                        'admin' => 'bg-danger text-white',
                                        'moderator' => 'bg-warning text-dark',
                                        default => 'bg-success text-white'
                                    };
                                    ?>
                                    <span class="role-badge <?= $roleClass ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar text-muted me-2"></i>
                                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="delete_user.php?id=<?= $user['id']?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View - Cards -->
            <div class="mobile-view">
                <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                            <small class="text-muted">ID: <?= htmlspecialchars($user['id']) ?></small>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <?= htmlspecialchars($user['email']) ?>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                    </div>

                    <?php
                    $roleClass = match($user['role']) {
                        'admin' => 'bg-danger text-white',
                        'moderator' => 'bg-warning text-dark',
                        default => 'bg-success text-white'
                    };
                    ?>
                    <span class="role-badge <?= $roleClass ?>">
                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                    </span>

                    <div class="action-buttons">
                        <a href="delete_user.php?id=<?= $user['id']?>" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Delete User
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    
<script>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        Swal.fire({
            title: 'Success!',
            text: 'User has been deleted.',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>
</body>
</html>