<?php
require_once 'config.php';

// Fetch featured projects from database
$query = "SELECT p.*, c.name as community_name, u.full_name as creator_name 
          FROM projects p 
          LEFT JOIN communities c ON p.community_id = c.id 
          LEFT JOIN users u ON p.created_by = u.id 
          WHERE p.is_featured = 1 
          ORDER BY p.created_at DESC 
          LIMIT 6";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - EKYAM</title>
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
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'Homepage.php'; ?>" class="hover:text-indigo-200">Home</a>
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

    <!-- Projects Section -->
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Featured Projects</h1>
            <a href="create_project.php" class="bg-indigo-700 text-white px-4 py-2 rounded-lg hover:bg-indigo-600">
                <i class="fas fa-plus mr-2"></i>Create Project
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while ($project = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if ($project['image']): ?>
                        <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-project-diagram text-4xl text-indigo-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                <?php echo htmlspecialchars($project['status']); ?>
                            </span>
                            <span class="text-gray-500 text-sm">
                                <?php echo date('M d, Y', strtotime($project['start_date'])); ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($project['name']); ?></h3>
                        <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($project['description']); ?></p>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 mr-2"></i>
                                <span class="text-gray-600"><?php echo $project['member_count']; ?> members</span>
                            </div>
                            <a href="project_details.php?id=<?php echo $project['id']; ?>" class="text-indigo-700 hover:text-indigo-500">
                                View Details
                            </a>
                        </div>
                        
                        <?php if ($project['community_name']): ?>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-500">Community: <?php echo htmlspecialchars($project['community_name']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
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