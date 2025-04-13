<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="Homepage.php">Ekyam</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'Homepage.php' ? 'active' : ''; ?>" href="Homepage.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'communities.php' ? 'active' : ''; ?>" href="communities.php">
                        <i class="fas fa-users"></i> Communities
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'projects.php' ? 'active' : ''; ?>" href="projects.php">
                        <i class="fas fa-project-diagram"></i> Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'newsletter.php' ? 'active' : ''; ?>" href="newsletter.php">
                        <i class="fas fa-envelope"></i> Newsletter
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                            <?php if ($_SESSION['user_type'] === 'individual' || $_SESSION['user_type'] === 'community_admin'): ?>
                                <li><a class="dropdown-item" href="create_project.php"><i class="fas fa-plus-circle"></i> Create Project</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'register.php' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 