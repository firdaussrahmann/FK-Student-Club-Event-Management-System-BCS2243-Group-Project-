<?php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: index.php");
    exit();
}

// --- 1. Fetch Summary Statistics ---

// Total Students (including Committee members)
$stmt = $pdo->query("SELECT COUNT(*) FROM user WHERE userRole = 'Student' OR userRole LIKE 'Committee%'");
$totalStudents = $stmt->fetchColumn();

// Total Active Clubs
$stmt = $pdo->query("SELECT COUNT(*) FROM club WHERE clubStatus = 'Active'");
$activeClubs = $stmt->fetchColumn();

// Upcoming Events
$stmt = $pdo->query("SELECT COUNT(*) FROM event WHERE eventDate >= CURDATE()");
$upcomingEvents = $stmt->fetchColumn();

// --- 2. Fetch Data for Charts ---

// User Role Distribution (Pie Chart)
$stmt = $pdo->query("SELECT userRole, COUNT(*) as count FROM user GROUP BY userRole");
$roleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent User Registrations (Table)
$stmt = $pdo->query("SELECT userName, userRole, userEmail FROM user ORDER BY User_ID DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FK System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { transition: transform 0.2s; border: none; }
        .stat-card:hover { transform: translateY(-5px); }
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df 0%, #224abe 100%); }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%); }
        .bg-gradient-info { background: linear-gradient(45deg, #36b9cc 0%, #258391 100%); }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">FK ADMIN PANEL</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Logged in: <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
                <a href="profile.php" class="btn btn-sm btn-outline-light me-2">Profile</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-gradient-primary text-white shadow">
                    <div class="card-body py-4">
                        <h6 class="text-uppercase mb-1">Total Students</h6>
                        <h2 class="display-6 fw-bold"><?php echo $totalStudents; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-gradient-success text-white shadow">
                    <div class="card-body py-4">
                        <h6 class="text-uppercase mb-1">Active Clubs</h6>
                        <h2 class="display-6 fw-bold"><?php echo $activeClubs; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-gradient-info text-white shadow">
                    <div class="card-body py-4">
                        <h6 class="text-uppercase mb-1">Upcoming Events</h6>
                        <h2 class="display-6 fw-bold"><?php echo $upcomingEvents; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Charts Column -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary">User Role Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="roleChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary">Recent User Registrations</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($user['userName']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo $user['userRole']; ?></span></td>
                                        <td><?php echo htmlspecialchars($user['userEmail']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Column -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="manage_users.php" class="btn btn-outline-dark text-start py-2">
                                <i class="bi bi-people me-2"></i> Manage User Accounts
                            </a>
                            <a href="register.php" class="btn btn-outline-dark text-start py-2">
                                <i class="bi bi-person-plus me-2"></i> Register New User
                            </a>
                            <a href="#" class="btn btn-outline-secondary text-start py-2 disabled">
                                <i class="bi bi-calendar-event me-2"></i> Manage Events (Module 2)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for Chart.js
        const roleLabels = <?php echo json_encode(array_column($roleData, 'userRole')); ?>;
        const roleCounts = <?php echo json_encode(array_column($roleData, 'count')); ?>;

        // Role Distribution Chart
        const ctx = document.getElementById('roleChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: roleLabels,
                datasets: [{
                    label: 'Number of Users',
                    data: roleCounts,
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>
