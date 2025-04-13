<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $bio = mysqli_real_escape_string($conn, $_POST['bio']);
        
        $query = "UPDATE users SET full_name = ?, location = ?, bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $full_name, $location, $bio, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['full_name'] = $full_name;
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile. Please try again.";
        }
    }
    
    if (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password. Please try again.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
    
    if (isset($_POST['update_notifications'])) {
        // Update notification preferences
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $project_updates = isset($_POST['project_updates']) ? 1 : 0;
        $community_updates = isset($_POST['community_updates']) ? 1 : 0;
        
        $query = "UPDATE users SET 
                 email_notifications = ?,
                 project_updates = ?,
                 community_updates = ?
                 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iiii", $email_notifications, $project_updates, $community_updates, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Notification preferences updated successfully!";
        } else {
            $error_message = "Error updating notification preferences. Please try again.";
        }
    }
}

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EKYAM</title>
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
            </div>
        </div>
    </nav>

    <!-- Settings Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Settings</h1>
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Profile Settings</h2>
                <form method="POST" action="">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($user['location']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" id="bio" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Change Password</h2>
                <form method="POST" action="">
                    <input type="hidden" name="change_password" value="1">
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" id="new_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Notification Preferences</h2>
                <form method="POST" action="">
                    <input type="hidden" name="update_notifications" value="1">
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="email_notifications" id="email_notifications" 
                                   <?php echo ($user['email_notifications'] ?? 0) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="email_notifications" class="ml-2 block text-sm text-gray-700">
                                Email Notifications
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="project_updates" id="project_updates"
                                   <?php echo ($user['project_updates'] ?? 0) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="project_updates" class="ml-2 block text-sm text-gray-700">
                                Project Updates
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="community_updates" id="community_updates"
                                   <?php echo ($user['community_updates'] ?? 0) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="community_updates" class="ml-2 block text-sm text-gray-700">
                                Community Updates
                            </label>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            Update Preferences
                        </button>
                    </div>
                </form>
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
</body>
</html> 