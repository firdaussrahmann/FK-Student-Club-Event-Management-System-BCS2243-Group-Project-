<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Administrators can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: index.php");
    exit();
}

$message = '';
$messageType = '';

// Handle Delete User
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $pdo->beginTransaction();

        // Delete from subtype tables first due to foreign keys
        $pdo->prepare("DELETE FROM student WHERE User_ID = ?")->execute([$delete_id]);
        $pdo->prepare("DELETE FROM admin WHERE User_ID = ?")->execute([$delete_id]);
        $pdo->prepare("DELETE FROM club_membership WHERE User_ID = ?")->execute([$delete_id]);

        // Finally delete from user table
        $pdo->prepare("DELETE FROM user WHERE User_ID = ?")->execute([$delete_id]);

        $pdo->commit();
        $message = "User deleted successfully.";
        $messageType = "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error deleting user: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch all users with their subtype IDs
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
    <title>Manage Users - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content">
            <?php include 'topbar.php'; ?>
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Manage User Accounts</h2>
                    <a href="register.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add New
                        User</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span
                                                    class="fw-bold"><?php echo ($u['userRole'] === 'Administrator') ? $u['staffID'] : $u['studentID']; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['userName']); ?></td>
                                            <td><?php echo htmlspecialchars($u['userEmail']); ?></td>
                                            <td><span
                                                    class="badge bg-light text-dark border"><?php echo $u['userRole']; ?></span>
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td class="text-end pe-4">
                                                <a href="edit_user.php?id=<?php echo $u['User_ID']; ?>"
                                                    class="btn btn-sm btn-outline-primary me-1"><i
                                                        class="bi bi-pencil"></i></a>
                                                <a href="manage_users.php?delete=<?php echo $u['User_ID']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this user?')"><i
                                                        class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>