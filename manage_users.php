<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Administrators can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle Account Deletion
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    try {
        $pdo->beginTransaction();
        
        // Due to the specialization (Foreign Keys), we delete from subtypes first if no CASCADE is set
        // But if you set ON DELETE CASCADE, deleting from 'user' is enough.
        // We'll delete from 'user' directly assuming CASCADE or handling it manually here.
        $stmt = $pdo->prepare("DELETE FROM student WHERE User_ID = ?");
        $stmt->execute([$id_to_delete]);
        
        $stmt = $pdo->prepare("DELETE FROM admin WHERE User_ID = ?");
        $stmt->execute([$id_to_delete]);
        
        $stmt = $pdo->prepare("DELETE FROM user WHERE User_ID = ?");
        $stmt->execute([$id_to_delete]);
        
        $pdo->commit();
        $message = "User deleted successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error deleting user: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch all users with their IDs
$sql = "SELECT u.*, s.studentID, a.staffID 
        FROM user u 
        LEFT JOIN student s ON u.User_ID = s.User_ID 
        LEFT JOIN admin a ON u.User_ID = a.User_ID 
        ORDER BY u.User_ID DESC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_dashboard.php">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">User Management</h2>
            <a href="register.php" class="btn btn-primary">+ Add New User</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Identifier (SID/AID)</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $u['User_ID'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($u['userName']) ?></td>
                                <td><?= htmlspecialchars($u['userEmail']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ($u['userRole'] === 'Administrator') ? htmlspecialchars($u['staffID']) : htmlspecialchars($u['studentID']) ?>
                                    </span>
                                </td>
                                <td><?= $u['userRole'] ?></td>
                                <td>
                                    <span class="badge <?= $u['userStatus'] === 'Active' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $u['userStatus'] ?? 'Active' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_user.php?id=<?= $u['User_ID'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="manage_users.php?delete=<?= $u['User_ID'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
