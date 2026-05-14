<?php
// login.php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    if (empty($student_id) || empty($password)) {
        $error = 'Please enter both Student ID and Password.';
    } else {
        // Fetch user using JOIN to check studentID or staffID across tables
        $sql = "SELECT u.*, s.studentID, a.staffID 
                FROM user u 
                LEFT JOIN student s ON u.User_ID = s.User_ID 
                LEFT JOIN admin a ON u.User_ID = a.User_ID 
                WHERE s.studentID = ? OR a.staffID = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $student_id]);
        $user = $stmt->fetch();

        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['userPassword'])) {
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['role'] = $user['userRole'];
            $_SESSION['name'] = $user['userName'];

            // Store the specific identifier (studentID or staffID)
            $_SESSION['student_id'] = ($user['userRole'] === 'Administrator') ? $user['staffID'] : $user['studentID'];

            // Redirect based on role (Changed from $user['role'] to $user['userRole'])
            if ($user['userRole'] === 'Administrator') {
                header("Location: admin_dashboard.php");
            } elseif ($user['userRole'] === 'Committee') {
                header("Location: committee_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = 'Invalid Student ID or Password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FK Student Club System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-primary">Faculty of Computing</h4>
                            <p class="text-muted">Student Club & Event Management System</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
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