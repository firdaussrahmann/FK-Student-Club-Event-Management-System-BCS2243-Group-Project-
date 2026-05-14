<?php
session_start();

// Security Check: If not logged in or not a Student, redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Student Home</span>
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
                <h2 class="fw-bold text-success">Welcome Back!</h2>
                <p class="text-muted">This is your student home page. You can view your points, register for events, and
                    join clubs here.</p>
                <hr>
                <div class="alert alert-success">
                    <strong>Student ID:</strong> <?php echo htmlspecialchars($_SESSION['student_id']); ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>