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
            --accent-color: #4895ef;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
        }
        
        .page-title {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            margin: 0;
            color: var(--primary-color);
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            background-color: var(--light-bg);
            border-bottom: 2px solid #e0e0e0;
            color: #555;
            font-weight: 600;
            padding: 1rem;
            white-space: nowrap;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .role-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-back {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            background-color: #6c757d;
            border: none;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 15px;
            }
            
            .card-body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid px-4 py-5">
    <h1 class="page-title text-primary fw-bold">User Management</h1>
    
    <!-- User Table -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-users me-2 text-primary"></i>
                <h2>User List</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
        <div class="card-body">
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
                                <!-- Delete user -->
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
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    
    <script>
        // Menampilkan SweetAlert jika ada status sukses
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            Swal.fire({
                title: 'Sukses!',
                text: 'User berhasil dihapus.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>
</html>