<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Administrators can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_users.php");
    exit();
}

$message = '';
$messageType = '';

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM user WHERE User_ID = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE user SET userName = ?, userEmail = ?, userRole = ? WHERE User_ID = ?");
        $stmt->execute([$name, $email, $role, $id]);
        $message = "User updated successfully!";
        $messageType = "success";

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM user WHERE User_ID = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'topbar.php'; ?>
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Edit User Details</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($message): ?>
                                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="<?php echo htmlspecialchars($user['userName']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="<?php echo htmlspecialchars($user['userEmail']); ?>" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value="Administrator" <?php if ($user['userRole'] == 'Administrator')
                                                echo 'selected'; ?>>Administrator</option>
                                            <option value="Student" <?php if ($user['userRole'] == 'Student')
                                                echo 'selected'; ?>>Student</option>
                                            <option value="Committee" <?php if (strpos($user['userRole'], 'Committee') !== false)
                                                echo 'selected'; ?>>Committee</option>
                                        </select>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary fw-bold py-2">Update User</button>
                                        <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>