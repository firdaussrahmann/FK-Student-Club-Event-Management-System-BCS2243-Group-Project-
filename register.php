<?php
// register.php
session_start();
require_once 'db_connect.php';

// Optional: Ensure only Admins can access this page
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
//     header("Location: login.php");
//     exit();
// }

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Basic validation
    if (empty($student_id) || empty($name) || empty($email) || empty($password) || empty($role)) {
        $message = 'All fields are required.';
        $messageType = 'danger';
    } else {
        // Check if identifier exists in either student or admin tables, and check email in user table
        $check_sql = "SELECT u.User_ID FROM user u 
                      LEFT JOIN student s ON u.User_ID = s.User_ID 
                      LEFT JOIN admin a ON u.User_ID = a.User_ID 
                      WHERE s.studentID = ? OR a.staffID = ? OR u.userEmail = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$student_id, $student_id, $email]);

        if ($stmt->rowCount() > 0) {
            $message = 'A user with this ID or Email already exists.';
            $messageType = 'danger';
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();

                // 1. Insert into the base 'user' table
                $user_sql = "INSERT INTO user (userName, userEmail, userPassword, userRole, userStatus) VALUES (?, ?, ?, ?, 'Active')";
                $user_stmt = $pdo->prepare($user_sql);
                $user_stmt->execute([$name, $email, $hashed_password, $role]);

                $new_user_id = $pdo->lastInsertId();

                // 2. Insert into the specific subtype table based on role
                if ($role === 'Administrator') {
                    $admin_sql = "INSERT INTO admin (User_ID, staffID) VALUES (?, ?)";
                    $admin_stmt = $pdo->prepare($admin_sql);
                    $admin_stmt->execute([$new_user_id, $student_id]);
                } else {
                    // Default to student for Committee and Student roles
                    $student_sql = "INSERT INTO student (User_ID, studentID, totalPoints) VALUES (?, ?, 0)";
                    $student_stmt = $pdo->prepare($student_sql);
                    $student_stmt->execute([$new_user_id, $student_id]);
                }

                $pdo->commit();
                $message = 'User registered successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Error registering user: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">FK Admin Panel</a>
            <div class="d-flex text-white align-items-center">
                <?php if (isset($_SESSION['name'])): ?>
                    <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
                        (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
                    <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="card-title fw-bold">Register New User</h5>
                    </div>
                    <div class="card-body p-4">

                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?>" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID / Admin ID</label>
                                <input type="text" class="form-control" id="student_id" name="student_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Temporary Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label for="role" class="form-label">User Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="" disabled selected>Select a role...</option>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Committee">Committee Member</option>
                                    <option value="Student">Student</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold">Register User</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>