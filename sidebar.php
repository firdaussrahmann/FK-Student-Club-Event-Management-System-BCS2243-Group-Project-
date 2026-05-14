<!-- sidebar.php -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --sidebar-width: 250px;
        --admin-color: #4e73df;
        --student-color: #1cc88a;
        --committee-color: #36b9cc;
    }
    
    #wrapper {
        display: flex;
        width: 100%;
        align-items: stretch;
    }

    #sidebar {
        min-width: var(--sidebar-width);
        max-width: var(--sidebar-width);
        min-height: 100vh;
        color: #fff;
        transition: all 0.3s;
        <?php
        $bg_color = 'var(--admin-color)';
        if ($_SESSION['role'] === 'Student') $bg_color = 'var(--student-color)';
        if (strpos($_SESSION['role'], 'Committee') !== false) $bg_color = 'var(--committee-color)';
        echo "background: $bg_color;";
        ?>
    }

    #sidebar .sidebar-header {
        padding: 20px;
        background: rgba(0,0,0,0.1);
    }

    #sidebar ul.components {
        padding: 20px 0;
    }

    #sidebar ul li a {
        padding: 12px 20px;
        font-size: 1.1em;
        display: block;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: 0.3s;
    }

    #sidebar ul li a:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }

    #sidebar ul li.active > a {
        color: #fff;
        background: rgba(255,255,255,0.2);
        border-left: 4px solid #fff;
    }

    #content {
        width: 100%;
        padding: 20px;
        background: #f8f9fc;
    }

    .user-info {
        padding: 20px;
        font-size: 0.9em;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: auto;
    }
</style>

<nav id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0 fw-bold">FK-SYSTEM</h4>
        <small class="opacity-75"><?php echo $_SESSION['role']; ?> Panel</small>
    </div>

    <ul class="list-unstyled components">
        <!-- Dashboard Link (Visible to all) -->
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' || basename($_SERVER['PHP_SELF']) == 'student_dashboard.php' || basename($_SERVER['PHP_SELF']) == 'committee_dashboard.php') ? 'active' : ''; ?>">
            <a href="<?php 
                if($_SESSION['role'] === 'Administrator') echo 'admin_dashboard.php';
                elseif(strpos($_SESSION['role'], 'Committee') !== false) echo 'committee_dashboard.php';
                else echo 'student_dashboard.php';
            ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'Administrator'): ?>
            <!-- Admin Only Links -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php' || basename($_SERVER['PHP_SELF']) == 'edit_user.php') ? 'active' : ''; ?>">
                <a href="manage_users.php"><i class="bi bi-people me-2"></i> Manage Users</a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
                <a href="register.php"><i class="bi bi-person-plus me-2"></i> Register New User</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
