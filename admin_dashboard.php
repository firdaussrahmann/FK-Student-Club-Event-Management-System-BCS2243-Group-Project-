<?php
session_start();

// Security Check: If not logged in or not an Administrator, redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Administrator Dashboard</span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="profile.php" class="btn btn-sm btn-outline-light me-2">My Profile</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <h2 class="fw-bold">Admin Panel</h2>
                <p class="text-muted">You are logged in as an Administrator. You can manage users, clubs, and events from here.</p>
                <div class="mt-4">
                    <a href="manage_users.php" class="btn btn-dark px-4 py-2 me-2">Manage Users</a>
                    <a href="#" class="btn btn-outline-dark px-4 py-2">System Logs</a>
                </div>
                <hr>
                <div class="alert alert-info">
                    <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['student_id']); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
