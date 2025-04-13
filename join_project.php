<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if project exists
$project_query = "SELECT * FROM projects WHERE id = $project_id";
$project_result = mysqli_query($conn, $project_query);
$project = mysqli_fetch_assoc($project_result);

if (!$project) {
    header("Location: projects.php");
    exit();
}

// Check if user is already a member
$member_query = "SELECT * FROM project_members WHERE project_id = $project_id AND user_id = $user_id";
$member_result = mysqli_query($conn, $member_query);

if (mysqli_num_rows($member_result) > 0) {
    header("Location: project_details.php?id=$project_id");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Insert new member
    $insert_query = "INSERT INTO project_members (project_id, user_id, role) VALUES ($project_id, $user_id, '$role')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Update project member count
        $update_query = "UPDATE projects SET member_count = member_count + 1 WHERE id = $project_id";
        mysqli_query($conn, $update_query);
        
        header("Location: project_details.php?id=$project_id");
        exit();
    } else {
        $error = "Failed to join project. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Project - EKYAM</title>
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
                <a href="Homepage.html" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="logout.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Join Project Form -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Join Project: <?php echo htmlspecialchars($project['name']); ?></h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Your Role in the Project</label>
                    <select name="role" id="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Select a role</option>
                        <option value="volunteer">Volunteer</option>
                        <option value="contributor">Contributor</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="advisor">Advisor</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="w-full bg-indigo-700 text-white px-6 py-3 rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Join Project
                    </button>
                </div>

                <div class="text-center">
                    <a href="project_details.php?id=<?php echo $project_id; ?>" class="text-indigo-600 hover:text-indigo-500">
                        Cancel and return to project
                    </a>
                </div>
            </form>
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