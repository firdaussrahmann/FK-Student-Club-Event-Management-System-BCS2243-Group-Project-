<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Administrators can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$id_to_edit = $_GET['id'];
$message = '';
$messageType = '';

// Handle Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['userName']);
    $email = trim($_POST['userEmail']);
    $role = $_POST['userRole'];
    $status = $_POST['userStatus'];

    try {
        $update_sql = "UPDATE user SET userName = ?, userEmail = ?, userRole = ?, userStatus = ? WHERE User_ID = ?";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$name, $email, $role, $status, $id_to_edit]);
        
        $message = "User updated successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM user WHERE User_ID = ?");
$stmt->execute([$id_to_edit]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Edit User Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                        <?php endif; ?>

                        <form action="edit_user.php?id=<?= $id_to_edit ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="userName" class="form-control" value="<?= htmlspecialchars($user['userName']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="userEmail" class="form-control" value="<?= htmlspecialchars($user['userEmail']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="userRole" class="form-select">
                                    <option value="Student" <?= $user['userRole'] === 'Student' ? 'selected' : '' ?>>Student</option>
                                    <option value="Committee" <?= $user['userRole'] === 'Committee' ? 'selected' : '' ?>>Committee Member</option>
                                    <option value="Administrator" <?= $user['userRole'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Account Status</label>
                                <select name="userStatus" class="form-select">
                                    <option value="Active" <?= ($user['userStatus'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($user['userStatus'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
