<?php
// index.php
session_start();
require_once 'db_connect.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Administrator') {
        header("Location: admin_dashboard.php");
    } elseif (strpos($_SESSION['role'], 'Committee') !== false) {
        header("Location: committee_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    // Modified query to handle specialization (JOIN across subtype tables)
    $sql = "SELECT u.*, s.studentID, a.staffID, u.userName as name
            FROM user u
            LEFT JOIN student s ON u.User_ID = s.User_ID
            LEFT JOIN admin a ON u.User_ID = a.User_ID
            WHERE s.studentID = ? OR a.staffID = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login_id, $login_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['userPassword'])) {
        // Create session variables
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['userRole'];
        $_SESSION['studentID'] = ($user['userRole'] === 'Administrator') ? $user['staffID'] : $user['studentID'];

        // Role-based redirection
        if ($user['userRole'] === 'Administrator') {
            header("Location: admin_dashboard.php");
        } elseif (strpos($user['userRole'], 'Committee') !== false) {
            header("Location: committee_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid Student ID/Staff ID or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FK Student Club Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">FK SYSTEM</h3>
                            <p class="text-muted">Welcome back! Please login.</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="index.php" method="POST">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID / Admin ID</label>
                                <input type="text" class="form-control" id="student_id" name="student_id" required
                                    autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Log In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>