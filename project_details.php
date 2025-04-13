<?php
require_once 'config.php';

// Get project ID from URL and validate
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($project_id <= 0) {
    header("Location: projects.php");
    exit();
}

// Fetch project details with prepared statement
$query = "SELECT p.*, c.name as community_name, u.full_name as creator_name, u.username as creator_username
          FROM projects p 
          LEFT JOIN communities c ON p.community_id = c.id 
          LEFT JOIN users u ON p.created_by = u.id 
          WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $project_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);

if (!$project) {
    header("Location: projects.php");
    exit();
}

// Fetch project members with prepared statement
$members_query = "SELECT u.id, u.username, u.full_name, u.profile_image, pm.role, pm.joined_at
                 FROM project_members pm
                 JOIN users u ON pm.user_id = u.id
                 WHERE pm.project_id = ?
                 ORDER BY pm.joined_at DESC";
$stmt = mysqli_prepare($conn, $members_query);
mysqli_stmt_bind_param($stmt, "i", $project_id);
mysqli_stmt_execute($stmt);
$members_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['name']); ?> - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="Homepage.html" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="Homepage.php" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hover:text-indigo-200">
                        <i class="fas fa-user mr-1"></i> Profile
                    </a>
                    <a href="logout.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-indigo-200" id="loginBtn">Login</a>
                    <a href="register.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100">Join Us</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Project Details -->
    <div class="container mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if ($project['image']): ?>
                <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>" class="w-full h-64 object-cover">
            <?php else: ?>
                <div class="w-full h-64 bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-project-diagram text-6xl text-indigo-400"></i>
                </div>
            <?php endif; ?>

            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($project['name']); ?></h1>
                        <div class="flex items-center space-x-4">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                <?php echo htmlspecialchars($project['status']); ?>
                            </span>
                            <span class="text-gray-500">
                                Created by <a href="profile.php?username=<?php echo htmlspecialchars($project['creator_username']); ?>" class="text-indigo-600 hover:text-indigo-500">
                                    <?php echo htmlspecialchars($project['creator_name']); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                    <a href="join_project.php?id=<?php echo $project_id; ?>" class="bg-indigo-700 text-white px-6 py-2 rounded-lg hover:bg-indigo-600">
                        Join Project
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Project Timeline</h3>
                        <div class="space-y-2">
                            <p class="text-gray-600">
                                <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>
                                Start Date: <?php echo date('M d, Y', strtotime($project['start_date'])); ?>
                            </p>
                            <?php if ($project['end_date']): ?>
                                <p class="text-gray-600">
                                    <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                                    End Date: <?php echo date('M d, Y', strtotime($project['end_date'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Project Stats</h3>
                        <div class="space-y-2">
                            <p class="text-gray-600">
                                <i class="fas fa-users text-indigo-500 mr-2"></i>
                                Members: <?php echo $project['member_count']; ?>
                            </p>
                            <?php if ($project['community_name']): ?>
                                <p class="text-gray-600">
                                    <i class="fas fa-building text-indigo-500 mr-2"></i>
                                    Community: <?php echo htmlspecialchars($project['community_name']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Project Status</h3>
                        <div class="space-y-2">
                            <p class="text-gray-600">
                                <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                                Status: <?php echo htmlspecialchars($project['status']); ?>
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-star text-indigo-500 mr-2"></i>
                                <?php echo $project['is_featured'] ? 'Featured Project' : 'Regular Project'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-semibold mb-4">About the Project</h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-4">Project Members</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php while ($member = mysqli_fetch_assoc($members_result)): ?>
                            <div class="bg-gray-50 p-4 rounded-lg flex items-center">
                                <?php if ($member['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="w-12 h-12 rounded-full mr-4">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                        <span class="text-indigo-700 font-semibold">
                                            <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h4 class="font-semibold"><?php echo htmlspecialchars($member['full_name']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($member['role']); ?></p>
                                    <p class="text-xs text-gray-400">Joined <?php echo date('M d, Y', strtotime($member['joined_at'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">EKYAM</h3>
                    <p class="text-gray-400">Fostering unity and collaboration among diverse communities.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                        <li><a href="resources.php" class="text-gray-400 hover:text-white">Resources</a></li>
                        <li><a href="communities.php" class="text-gray-400 hover:text-white">Communities</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="help.php" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="guidelines.php" class="text-gray-400 hover:text-white">Community Guidelines</a></li>
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> EKYAM. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 