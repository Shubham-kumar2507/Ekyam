<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has permission to create communities
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'individual' && $_SESSION['user_type'] !== 'community_admin')) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$formData = [
    'name' => '',
    'description' => '',
    'location' => '',
    'category' => 'environment',
    'image' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    
    // Store form data for repopulation
    $formData = [
        'name' => $name,
        'description' => $description,
        'location' => $location,
        'category' => $category
    ];
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Community name is required.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Community name must be at least 3 characters long.";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required.";
    } elseif (strlen($description) < 10) {
        $errors[] = "Description must be at least 10 characters long.";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required.";
    }
    
    // Check if community name already exists
    $check_query = "SELECT id FROM communities WHERE name = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $name);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $errors[] = "A community with this name already exists.";
    }
    
    if (empty($errors)) {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/communities/' . $new_filename;
                
                if (!is_dir('uploads/communities')) {
                    mkdir('uploads/communities', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image = $upload_path;
                } else {
                    $errors[] = "Error uploading image. Please try again.";
                }
            } else {
                $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        }
        
        if (empty($errors)) {
            // Insert community into database
            $query = "INSERT INTO communities (name, description, location, image, category, admin_id) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $description, $location, $image, $category, $_SESSION['user_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $community_id = mysqli_insert_id($conn);
                
                // Add creator as admin member
                $member_query = "INSERT INTO community_members (community_id, user_id, role) VALUES (?, ?, 'admin')";
                $member_stmt = mysqli_prepare($conn, $member_query);
                mysqli_stmt_bind_param($member_stmt, "ii", $community_id, $_SESSION['user_id']);
                mysqli_stmt_execute($member_stmt);
                
                $_SESSION['success_message'] = "Community created successfully!";
                header("Location: community_details.php?id=" . $community_id);
                exit();
            } else {
                $errors[] = "Error creating community. Please try again.";
            }
        }
    }
    
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Community - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="index.html" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="Homepage.php" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200 text-indigo-100 border-b-2 border-indigo-300">Communities</a>
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
                <button class="md:hidden text-xl" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-indigo-700 text-white px-6 py-4">
                    <h1 class="text-2xl font-bold">Create New Community</h1>
                    <p class="text-indigo-100">Start a new community and connect with like-minded individuals</p>
                </div>
                
                <div class="p-6">
                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                            <p><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Community Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                            <p class="text-sm text-gray-500 mt-1">Choose a unique and descriptive name for your community</p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-gray-700 font-medium mb-2">Description *</label>
                            <textarea id="description" name="description" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required><?php echo htmlspecialchars($formData['description']); ?></textarea>
                            <p class="text-sm text-gray-500 mt-1">Describe your community's purpose and goals</p>
                        </div>
                        
                        <div>
                            <label for="location" class="block text-gray-700 font-medium mb-2">Location *</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($formData['location']); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                            <p class="text-sm text-gray-500 mt-1">Where is your community based?</p>
                        </div>
                        
                        <div>
                            <label for="category" class="block text-gray-700 font-medium mb-2">Category *</label>
                            <select id="category" name="category" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="environment" <?php echo $formData['category'] === 'environment' ? 'selected' : ''; ?>>Environment</option>
                                <option value="education" <?php echo $formData['category'] === 'education' ? 'selected' : ''; ?>>Education</option>
                                <option value="health" <?php echo $formData['category'] === 'health' ? 'selected' : ''; ?>>Health</option>
                                <option value="social" <?php echo $formData['category'] === 'social' ? 'selected' : ''; ?>>Social</option>
                                <option value="technology" <?php echo $formData['category'] === 'technology' ? 'selected' : ''; ?>>Technology</option>
                                <option value="art" <?php echo $formData['category'] === 'art' ? 'selected' : ''; ?>>Art & Culture</option>
                                <option value="sports" <?php echo $formData['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                <option value="business" <?php echo $formData['category'] === 'business' ? 'selected' : ''; ?>>Business</option>
                                <option value="other" <?php echo $formData['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="image" class="block text-gray-700 font-medium mb-2">Community Image</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" id="image" name="image" accept="image/*" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           onchange="previewImage(this)">
                                </div>
                                <div id="imagePreview" class="hidden w-24 h-24 rounded-lg overflow-hidden border border-gray-300">
                                    <img src="" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Upload a representative image for your community (optional)</p>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-300">
                                Create Community
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
            }
        }

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html> 