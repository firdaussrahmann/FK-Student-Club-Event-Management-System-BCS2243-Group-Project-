<?php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// 1. Fetch current user data (including ID from subtype tables)
$sql = "SELECT u.*, s.studentID, a.staffID 
        FROM user u 
        LEFT JOIN student s ON u.User_ID = s.User_ID 
        LEFT JOIN admin a ON u.User_ID = a.User_ID 
        WHERE u.User_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['userName']);
    $email = trim($_POST['userEmail']);
    $phone = trim($_POST['userPhoneNumber']);
    $new_password = $_POST['new_password'];
    
    try {
        $pdo->beginTransaction();
        
        // Handle Profile Picture Upload
        $profile_pic = $user['userProfilePicture'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = "profile_" . $user_id . "_" . time() . "." . $ext;
                $target = "uploads/" . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                    $profile_pic = $new_filename;
                }
            }
        }

        // Update Base Information
        $update_sql = "UPDATE user SET userName = ?, userEmail = ?, userPhoneNumber = ?, userProfilePicture = ? WHERE User_ID = ?";
        $update_params = [$name, $email, $phone, $profile_pic, $user_id];
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute($update_params);

        // Update Password if provided
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $pw_sql = "UPDATE user SET userPassword = ? WHERE User_ID = ?";
            $pdo->prepare($pw_sql)->execute([$hashed, $user_id]);
        }

        $pdo->commit();
        
        // Refresh local data
        $_SESSION['name'] = $name;
        $message = "Profile updated successfully!";
        $messageType = "success";
        
        // Re-fetch user data to refresh the page
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error updating profile: " . $e->getMessage();
        $messageType = "danger";
    }
}

// 3. Fetch Club Memberships
$membership_sql = "SELECT m.*, c.clubName 
                   FROM club_membership m 
                   JOIN club c ON m.Club_ID = c.Club_ID 
                   WHERE m.User_ID = ? AND m.membershipStatus = 'Active'";
$m_stmt = $pdo->prepare($membership_sql);
$m_stmt->execute([$user_id]);
$memberships = $m_stmt->fetchAll();

$display_id = ($user['userRole'] === 'Administrator') ? $user['staffID'] : $user['studentID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">FK System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="javascript:history.back()">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Manage Profile</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?= $user['userProfilePicture'] ? 'uploads/'.$user['userProfilePicture'] : 'https://via.placeholder.com/150' ?>" 
                                     class="profile-img mb-3 border shadow-sm" alt="Profile Picture">
                                <div>
                                    <label for="profile_pic" class="btn btn-sm btn-outline-secondary">Change Photo</label>
                                    <input type="file" id="profile_pic" name="profile_pic" class="d-none" accept="image/*">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">User ID / Student ID</label>
                                    <input type="text" class="form-control bg-light" value="<?= $display_id ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control bg-light" value="<?= $user['userRole'] ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="userName" class="form-control" value="<?= htmlspecialchars($user['userName']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="userEmail" class="form-control" value="<?= htmlspecialchars($user['userEmail']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="userPhoneNumber" class="form-control" value="<?= htmlspecialchars($user['userPhoneNumber'] ?? '') ?>" placeholder="e.g. 012-3456789">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                            </div>

                            <!-- Club Membership Section -->
                            <div class="mb-4">
                                <label class="form-label fw-bold border-bottom w-100 pb-1">Club Memberships</label>
                                <?php if (count($memberships) > 0): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($memberships as $m): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <span class="fw-bold"><?= htmlspecialchars($m['clubName']) ?></span>
                                                    <div class="small text-muted">Role: <?= htmlspecialchars($m['membershipRole'] ?? 'Member') ?></div>
                                                </div>
                                                <span class="badge bg-success rounded-pill">Joined <?= date('M Y', strtotime($m['joinDate'])) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">No active club memberships found.</p>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
