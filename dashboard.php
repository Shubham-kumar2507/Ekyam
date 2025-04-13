<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
function connectDB() {
    $servername = "localhost";
    $username = "root";  // Default XAMPP username
    $password = "";      // Default XAMPP password
    $dbname = "ekyam_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Get user data
$conn = connectDB();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$full_name = $_SESSION['full_name'];

// Fetch complete user data including profile picture
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user's community data if applicable
$community_data = null;
if ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    $community_id = $_SESSION['community_id'];
    $result = $conn->query("SELECT * FROM communities WHERE id = $community_id");
    if ($result->num_rows > 0) {
        $community_data = $result->fetch_assoc();
    }
}

// Get community members if user is a community admin
$community_members = [];
if ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    $community_id = $_SESSION['community_id'];
    $result = $conn->query("SELECT id, username, full_name, location FROM users WHERE community_id = $community_id");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $community_members[] = $row;
        }
    }
}

// Get projects
$projects = [];
$project_query = "";

if ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    // Get community projects
    $community_id = $_SESSION['community_id'];
    $project_query = "SELECT * FROM projects WHERE community_id = $community_id ORDER BY created_at DESC LIMIT 5";
} else {
    // Get projects for individual user
    $project_query = "SELECT p.* FROM projects p 
                      JOIN project_members pm ON p.id = pm.project_id
                      WHERE pm.user_id = $user_id
                      ORDER BY p.created_at DESC LIMIT 5";
}

$result = $conn->query($project_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Get resources
$resources = [];
$resource_query = "";

if ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    // Get community resources
    $community_id = $_SESSION['community_id'];
    $resource_query = "SELECT * FROM resources WHERE community_id = $community_id ORDER BY created_at DESC LIMIT 5";
} else {
    // Get resources for individual user
    $resource_query = "SELECT r.* FROM resources r 
                      JOIN resource_access ra ON r.id = ra.resource_id
                      WHERE ra.user_id = $user_id
                      ORDER BY r.created_at DESC LIMIT 5";
}

$result = $conn->query($resource_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}

// Get user's joined communities count
$joinedCommunitiesQuery = "SELECT COUNT(*) as joined_count FROM community_members WHERE user_id = ?";
$joinedCommunitiesStmt = $conn->prepare($joinedCommunitiesQuery);
$joinedCommunitiesStmt->bind_param('i', $user_id);
$joinedCommunitiesStmt->execute();
$joinedCommunitiesResult = $joinedCommunitiesStmt->get_result();
$joinedCommunitiesData = $joinedCommunitiesResult->fetch_assoc();
$joinedCommunitiesCount = $joinedCommunitiesData['joined_count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold flex items-center hover:text-indigo-200">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="dashboard.php" class="hover:text-indigo-200">
                    <i class="fas fa-home mr-1"></i> Home
                </a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
                <a href="newsletter.php" class="hover:text-indigo-200">Newsletter</a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center space-x-1 hover:text-indigo-200">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 w-48 bg-white rounded-md shadow-lg py-1 mt-2 z-10 hidden group-hover:block">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-100">Profile</a>
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-100">Settings</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-100" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                    </div>
                </div>
                <button class="md:hidden text-xl" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-indigo-800" id="mobileMenu">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
                <a href="newsletter.php" class="hover:text-indigo-200">Newsletter</a>
                <a href="profile.php" class="hover:text-indigo-200">Profile</a>
                <a href="settings.php" class="hover:text-indigo-200">Settings</a>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
            <p class="text-gray-600 mt-2">
                <?php if ($user_type === 'community_admin'): ?>
                    Manage your community, projects, and resources
                <?php else: ?>
                    Access your projects, resources, and connect with communities
                <?php endif; ?>
            </p>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stats Overview -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Overview</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-indigo-50 rounded-lg p-4 border-l-4 border-indigo-500">
                            <h3 class="text-gray-500 text-sm">Active Projects</h3>
                            <p class="text-2xl font-bold text-indigo-600"><?php echo count($projects); ?></p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                            <h3 class="text-gray-500 text-sm">Available Resources</h3>
                            <p class="text-2xl font-bold text-green-600"><?php echo count($resources); ?></p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
                            <h3 class="text-gray-500 text-sm">
                                <?php echo ($user_type === 'community_admin') ? 'Community Members' : 'Communities Joined'; ?>
                            </h3>
                            <p class="text-2xl font-bold text-purple-600">
                                <?php echo ($user_type === 'community_admin') ? count($community_members) : $joinedCommunitiesCount; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Recent Projects</h2>
                        <a href="projects.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            View All Projects
                        </a>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-6">
                            <p class="text-gray-500">No projects found.</p>
                            <a href="create_project.php" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Create Your First Project
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($projects as $project): ?>
                                <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                    <h3 class="font-semibold text-lg">
                                        <a href="project.php?id=<?php echo $project['id']; ?>" class="hover:text-indigo-600">
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 text-sm mt-1 line-clamp-2">
                                        <?php echo htmlspecialchars($project['description']); ?>
                                    </p>
                                    <div class="flex items-center mt-2 text-sm text-gray-500">
                                        <span class="mr-3"><i class="far fa-calendar-alt mr-1"></i> <?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                                        <span><i class="fas fa-users mr-1"></i> <?php echo $project['member_count'] ?? '0'; ?> Members</span>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <a href="project.php?id=<?php echo $project['id']; ?>" 
                                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                            View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Resources -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Recent Resources</h2>
                        <a href="resources.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                            View All <i class="fas fa-chevron-right ml-1 text-xs"></i>
                        </a>
                    </div>
                    
                    <?php if (empty($resources)): ?>
                    <div class="text-center py-10 bg-gray-50 rounded-lg">
                        <div class="inline-block p-4 rounded-full bg-green-100 text-green-600 mb-4">
                            <i class="fas fa-box-open text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No resources yet</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-4">Start sharing knowledge by uploading your first resource.</p>
                        <a href="create-resource.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-md">
                            <i class="fas fa-plus mr-2"></i> Add Your First Resource
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($resources as $resource): ?>
                        <div class="border border-gray-200 rounded-xl p-4 transition-all duration-300 card-hover">
                            <div class="flex items-start space-x-4">
                                <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fas fa-file-alt text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <p class="text-gray-500 text-sm mt-1">
                                        <?php if (isset($resource['community_name']) && !empty($resource['community_name'])): ?>
                                        <span class="inline-flex items-center mr-3">
                                            <i class="fas fa-users mr-1"></i> <?php echo htmlspecialchars($resource['community_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <span class="inline-flex items-center">
                                            <i class="fas fa-user mr-1"></i> <?php echo isset($resource['uploader_name']) ? htmlspecialchars($resource['uploader_name']) : 'Unknown User'; ?>
                                        </span>
                                    </p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-0.5 rounded-full">
                                            <?php echo htmlspecialchars($resource['type']); ?>
                                        </span>
                                        <a href="resource.php?id=<?php echo $resource['id']; ?>" 
                                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                            View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Profile Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col items-center text-center">
                        <div class="h-24 w-24 rounded-full overflow-hidden">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                     alt="Profile Picture" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="h-full w-full bg-indigo-100 flex items-center justify-center text-indigo-500 text-4xl">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-4 text-xl font-semibold"><?php echo htmlspecialchars($full_name); ?></h2>
                        <p class="text-gray-500 mt-1">
                            <?php echo ($user_type === 'community_admin') ? 'Community Administrator' : 'Individual Member'; ?>
                        </p>
                        <?php if ($user_type === 'community_admin' && $community_data): ?>
                            <p class="text-indigo-600 mt-1"><?php echo htmlspecialchars($community_data['name']); ?></p>
                        <?php endif; ?>
                        <div class="mt-4 w-full">
                            <a href="profile.php" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center">
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Community Members (for community admins) -->
                <?php if ($user_type === 'community_admin' && !empty($community_members)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Community Members</h2>
                        <a href="manage-members.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Manage Members
                        </a>
                    </div>
                    <div class="space-y-3">
                        <?php foreach (array_slice($community_members, 0, 5) as $member): ?>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 text-sm mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium"><?php echo htmlspecialchars($member['full_name']); ?></h3>
                                    <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($member['location']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($community_members) > 5): ?>
                            <div class="text-center mt-2">
                                <a href="manage-members.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                    View All Members (<?php echo count($community_members); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="create_project.php" class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition duration-150">
                            <div class="h-8 w-8 rounded-full bg-indigo-200 flex items-center justify-center text-indigo-600 mr-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">New Project</h3>
                                <p class="text-gray-500 text-xs">Create a new project</p>
                            </div>
                        </a>
                        
                        <a href="create-resource.php" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-150">
                            <div class="h-8 w-8 rounded-full bg-green-200 flex items-center justify-center text-green-600 mr-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Add Resource</h3>
                                <p class="text-gray-500 text-xs">Share a new resource</p>
                            </div>
                        </a>
                        
                        <?php if ($user_type === 'community_admin'): ?>
                        <a href="community-settings.php" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-150">
                            <div class="h-8 w-8 rounded-full bg-purple-200 flex items-center justify-center text-purple-600 mr-3">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Community Settings</h3>
                                <p class="text-gray-500 text-xs">Manage your community</p>
                            </div>
                        </a>
                        <?php else: ?>
                        <a href="communities.php" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-150">
                            <div class="h-8 w-8 rounded-full bg-purple-200 flex items-center justify-center text-purple-600 mr-3">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Find Communities</h3>
                                <p class="text-gray-500 text-xs">Join a new community</p>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-2xl font-bold mb-4 flex items-center">
                        <span class="mr-2"><i class="fas fa-people-group"></i></span>
                        EKYAM
                    </h2>
                    <p class="text-gray-400 max-w-md">Empowering communities through collaboration, connection, and shared resources.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="dashboard.php" class="text-gray-400 hover:text-white">Dashboard</a></li>
                            <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                            <li><a href="resources.php" class="text-gray-400 hover:text-white">Resources</a></li>
                            <li><a href="communities.php" class="text-gray-400 hover:text-white">Communities</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                            <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                            <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                            <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; 2025 EKYAM. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>
</body>
</html>