<?php
    $message = $_SESSION['message'] ?? '';
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['message'], $_SESSION['error']);
?>

<div class="container py-4">
    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form action="?action=update_profile" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registered Events Section -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">My Registered Events</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($registeredEvents)): ?>
                        <p class="text-muted">You haven't registered for any events yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registeredEvents as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($event['date'])) ?></td>
                                            <td><?= htmlspecialchars($event['location']) ?></td>
                                            <td>
                                                <a href="?action=event_details&id=<?= $event['id'] ?>" class="btn btn-sm btn-info">Details</a>
                                                <form action="?action=cancel_registration" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this registration?')">Cancel</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Events Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Upcoming Events You Might Like</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingEvents)): ?>
                        <p class="text-muted">No upcoming events at the moment.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <?php if ($event['image_path']): ?>
                                            <img src="<?= htmlspecialchars($event['image_path']) ?>" class="card-img-top" alt="Event Image">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                                            <p class="card-text"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                                            <a href="?action=event_details&id=<?= $event['id'] ?>" class="btn btn-primary btn-sm">Learn More</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>