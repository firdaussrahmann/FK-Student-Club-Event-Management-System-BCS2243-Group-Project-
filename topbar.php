<!-- topbar.php -->
<style>
    .topbar {
        height: 70px;
        background: #fff;
        display: flex;
        align-items: center;
        padding: 0 25px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        margin-bottom: 25px;
        margin-left: -20px;
        margin-right: -20px;
        margin-top: -20px;
    }
    .topbar .user-profile-dropdown {
        margin-left: auto;
        display: flex;
        align-items: center;
    }
    .topbar .user-info-text {
        text-align: right;
        margin-right: 15px;
        line-height: 1.2;
    }
    .topbar .user-name {
        font-weight: 700;
        color: #5a5c69;
        display: block;
        font-size: 0.9rem;
    }
    .topbar .user-id {
        font-size: 0.75rem;
        color: #b7b9cc;
    }
    .topbar .profile-img-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e3e6f0;
        cursor: pointer;
    }
</style>

<div class="topbar">
    <!-- Search or Title can go here in future -->
    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <h5 class="text-gray-800 mb-0"><?php echo htmlspecialchars($_SESSION['role']); ?> Dashboard</h5>
    </div>

    <div class="user-profile-dropdown dropdown">
        <div class="user-info-text d-none d-lg-block">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <span class="user-id"><?php echo htmlspecialchars($_SESSION['studentID']); ?></span>
        </div>
        
        <div class="dropdown">
            <div class="profile-img-circle shadow-sm" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-fill text-gray-400"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2 text-gray-400"></i> My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>
