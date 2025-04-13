<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'user';

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $location = trim($_POST['location']);
    $bio = trim($_POST['bio']);
    
    // Handle profile image upload
    $profile_image = $user['profile_image']; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/profiles/' . $new_filename;
            
            if (!is_dir('uploads/profiles')) {
                mkdir('uploads/profiles', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $upload_path;
            }
        }
    }
    
    // Update user data
    $update_query = "UPDATE users SET full_name = ?, email = ?, location = ?, bio = ?, profile_image = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('sssssi', $full_name, $email, $location, $bio, $profile_image, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile. Please try again.";
    }
}

// Get user's projects
$projects_query = "SELECT p.*, c.name as community_name 
                  FROM projects p 
                  LEFT JOIN communities c ON p.community_id = c.id 
                  WHERE p.created_by = ? 
                  ORDER BY p.created_at DESC 
                  LIMIT 5";
$projects_stmt = $conn->prepare($projects_query);
$projects_stmt->bind_param('i', $user_id);
$projects_stmt->execute();
$projects_result = $projects_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="dashboard.php" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="profile.php" class="hover:text-indigo-200">Profile</a>
                <a href="logout.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-8">
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div class="flex flex-col items-center mb-8">
                            <div class="relative">
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                         alt="Profile Image" 
                                         class="w-32 h-32 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-32 h-32 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <i class="fas fa-user text-4xl text-indigo-500"></i>
                                    </div>
                                <?php endif; ?>
                                <label for="profile_image" class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer">
                                    <i class="fas fa-camera text-indigo-600"></i>
                                </label>
                                <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                            </div>
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($user['location']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea id="bio" name="bio" rows="4" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- User's Projects -->
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Projects</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($project = $projects_result->fetch_assoc()): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                    <a href="project_details.php?id=<?php echo $project['id']; ?>" class="hover:text-indigo-600">
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mb-4 line-clamp-2">
                                    <?php echo htmlspecialchars($project['description']); ?>
                                </p>
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>
                                        <i class="fas fa-users mr-1"></i>
                                        <?php echo $project['member_count']; ?> members
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview profile image before upload
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.relative img');
                    if (img) {
                        img.src = e.target.result;
                    } else {
                        const div = document.querySelector('.relative div');
                        div.innerHTML = `<img src="${e.target.result}" class="w-32 h-32 rounded-full object-cover">`;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 