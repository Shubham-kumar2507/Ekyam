<?php
require_once 'config.php';
session_start();

// Initialize variables
$error = '';
$message = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=create_project.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch communities for dropdown (communities where user is a member or admin)
$communities_query = "SELECT c.* FROM communities c 
                     LEFT JOIN community_members cm ON c.id = cm.community_id 
                     WHERE c.admin_id = ? OR cm.user_id = ?
                     ORDER BY c.name";
$stmt = mysqli_prepare($conn, $communities_query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$communities_result = mysqli_stmt_get_result($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $community_id = !empty($_POST['community_id']) ? (int)$_POST['community_id'] : NULL;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Basic validation
    if (empty($name) || empty($description) || empty($start_date)) {
        $error = "Please fill all required fields";
    } else {
        // Handle image upload if present
        $image_path = NULL;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/projects/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('project_') . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check file type
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed";
            }
        }
        
        if (empty($error)) {
            // Insert project into database
            $query = "INSERT INTO projects (name, description, status, image, start_date, end_date, 
                      community_id, is_featured, created_by) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssssiis", $name, $description, $status, $image_path, 
                                  $start_date, $end_date, $community_id, $is_featured, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Get the new project ID
                $project_id = mysqli_insert_id($conn);
                
                // Add current user as a project member with 'leader' role
                $member_query = "INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'leader')";
                $member_stmt = mysqli_prepare($conn, $member_query);
                mysqli_stmt_bind_param($member_stmt, "ii", $project_id, $user_id);
                mysqli_stmt_execute($member_stmt);
                
                $message = "Project created successfully";
                
                // Redirect to the new project page
                header("Location: project_details.php?id=" . $project_id);
                exit;
            } else {
                $error = "Error creating project: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Custom styles */
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
        }
        .input-focus:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6366f1;
        }
        .select2-container .select2-selection--single {
            height: 42px !important;
            padding: 6px 10px;
            border-radius: 0.5rem;
            border-color: #d1d5db;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }
        .hover-scale {
            transition: transform 0.2s;
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        /* Image upload preview */
        .file-upload-preview {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .file-upload-preview.has-image .upload-placeholder {
            opacity: 0;
        }
        .preview-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .preview-image.visible {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans custom-scrollbar">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="Homepage.html" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="Homepage.php" class="hover:text-indigo-200 transition-colors flex items-center">
                    <i class="fas fa-home mr-1"></i> Home
                </a>
                <a href="projects.php" class="hover:text-indigo-200 transition-colors flex items-center">
                    <i class="fas fa-project-diagram mr-1"></i> Projects
                </a>
                <a href="resources.php" class="hover:text-indigo-200 transition-colors flex items-center">
                    <i class="fas fa-book mr-1"></i> Resources
                </a>
                <a href="communities.php" class="hover:text-indigo-200 transition-colors flex items-center">
                    <i class="fas fa-users mr-1"></i> Communities
                </a>
                <a href="map.php" class="hover:text-indigo-200 transition-colors flex items-center">
                    <i class="fas fa-map-marked-alt mr-1"></i> Community Map
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hover:text-indigo-200 transition-colors">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <a href="logout.php" class="hover:text-indigo-200 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-indigo-200 transition-colors">Login</a>
                    <a href="register.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 transition-colors">Join Us</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-indigo-600 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-2 drop-shadow-md">Create a New Project</h1>
            <p class="text-indigo-100 max-w-2xl mx-auto">Share your vision, find collaborators, and make a difference in your community.</p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-500">
                <a href="Homepage.php" class="hover:text-indigo-600 transition-colors"><i class="fas fa-home mr-2"></i>Home</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
                <a href="projects.php" class="hover:text-indigo-600 transition-colors">Projects</a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
                <span class="text-gray-700 font-medium">Create Project</span>
            </div>
        </div>
    </div>

    <!-- Create Project Form -->
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-4xl mx-auto bg-white rounded-xl card-shadow p-8 border border-gray-100 hover-scale">
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="create_project.php" method="post" enctype="multipart/form-data" class="space-y-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">Project Details</h2>
                        <p class="text-gray-500 text-sm">Fields marked with * are required</p>
                    </div>
                    <div class="flex items-center space-x-2 text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-lightbulb"></i>
                        <span class="text-sm">Need help? <a href="#" class="underline">View guidelines</a></span>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <!-- Project name -->
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none input-focus pl-10 transition-all"
                                   placeholder="Enter your project name">
                            <i class="fas fa-project-diagram absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500"></i>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Choose a clear, concise name that reflects your project's purpose</p>
                    </div>
                    
                    <!-- Project description -->
                    <div>
                        <label for="description" class="block text-gray-700 font-medium mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <div class="border rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b flex items-center space-x-2">
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <span class="border-r h-5 mx-1"></span>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Numbered List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <span class="border-r h-5 mx-1"></span>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Add Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="p-1 rounded hover:bg-gray-200" title="Add Image">
                                    <i class="fas fa-image"></i>
                                </button>
                            </div>
                            <textarea id="description" name="description" rows="6" required
                                  class="w-full px-4 py-3 focus:outline-none input-focus resize-none"
                                  placeholder="Describe your project in detail..."></textarea>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Include the project's goals, significance, and potential impact</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-gray-700 font-medium mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="status" name="status" required
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none input-focus pl-10 appearance-none transition-all">
                                    <option value="planning">Planning</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <i class="fas fa-tasks absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500"></i>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>
                        
                        <!-- Community -->
                        <div>
                            <label for="community_id" class="block text-gray-700 font-medium mb-2">
                                Community (Optional)
                            </label>
                            <div class="relative">
                                <select id="community_id" name="community_id"
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none input-focus transition-all">
                                    <option value="">-- No Community --</option>
                                    <?php while ($community = mysqli_fetch_assoc($communities_result)): ?>
                                        <option value="<?php echo $community['id']; ?>">
                                            <?php echo htmlspecialchars($community['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Associate this project with one of your communities</p>
                        </div>
                        
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-gray-700 font-medium mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="date" id="start_date" name="start_date" required
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none input-focus pl-10 transition-all">
                                <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500"></i>
                            </div>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-gray-700 font-medium mb-2">
                                End Date (Optional)
                            </label>
                            <div class="relative">
                                <input type="date" id="end_date" name="end_date"
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none input-focus pl-10 transition-all">
                                <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Leave blank if this is an ongoing project</p>
                        </div>
                    </div>
                </div>
                
                <!-- Project Image -->
                <div class="border-t border-gray-200 pt-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Project Image</h3>
                    <label for="image" class="block text-gray-700 font-medium mb-2">
                        Cover Image (Optional)
                    </label>
                    <div class="file-upload-preview relative mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-indigo-100 border-dashed rounded-lg bg-indigo-50 h-64 overflow-hidden">
                        <div class="upload-placeholder space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-5xl text-indigo-400 mb-2"></i>
                            <div class="flex flex-col">
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="bg-indigo-600 text-white rounded-md px-3 py-1 font-medium text-sm cursor-pointer hover:bg-indigo-700 transition-colors">
                                        Browse files
                                    </span>
                                    <span class="ml-2">or drag and drop</span>
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <img id="preview" class="preview-image" src="#" alt="Preview">
                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                    </div>
                </div>
                
                <!-- Featured option for admins -->
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'system_admin'): ?>
                <div class="flex items-center p-4 bg-indigo-50 rounded-lg mt-6 border-l-4 border-indigo-500">
                    <input type="checkbox" name="is_featured" class="h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500 border-indigo-300">
                    <label class="ml-3 text-gray-700">
                        <span class="font-medium">Feature this project on the homepage</span>
                        <p class="text-sm text-gray-500">This will make your project visible in the featured section on the main page</p>
                    </label>
                </div>
                <?php endif; ?>
                
                <!-- Form actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="projects.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-indigo-700 text-white rounded-lg hover:bg-indigo-600 transition duration-200 flex items-center shadow-md">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Tips for Creating a Successful Project
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="text-indigo-600 text-lg mb-2">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Define Clear Goals</h4>
                    <p class="text-gray-600 text-sm">Clearly outline what your project aims to accomplish with specific, measurable objectives.</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="text-indigo-600 text-lg mb-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Target Your Audience</h4>
                    <p class="text-gray-600 text-sm">Identify who will benefit from your project and why it matters to them.</p>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="text-indigo-600 text-lg mb-2">
                        <i class="fas fa-image"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Use Visual Appeal</h4>
                    <p class="text-gray-600 text-sm">Upload a high-quality image that represents your project's essence and attracts attention.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-people-group mr-2"></i>
                        EKYAM
                    </h3>
                    <p class="text-gray-400">Fostering unity and collaboration among diverse communities.</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="#" class="bg-indigo-700 hover:bg-indigo-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-indigo-700 hover:bg-indigo-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-indigo-700 hover:bg-indigo-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> About Us</a></li>
                        <li><a href="projects.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Projects</a></li>
                        <li><a href="resources.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Resources</a></li>
                        <li><a href="communities.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Communities</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="help.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Help Center</a></li>
                        <li><a href="guidelines.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Community Guidelines</a></li>
                        <li><a href="faq.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> FAQ</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors flex items-center"><i class="fas fa-angle-right mr-2 text-xs"></i> Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Newsletter</h4>
                    <p class="text-gray-400 text-sm mb-3">Stay updated with our latest projects and community initiatives</p>
                    <form class="flex">
                        <input type="email" placeholder="Your email" class="px-4 py-2 rounded-l-lg w-full focus:outline-none" />
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> EKYAM. All rights reserved.</p>
                <div class="mt-3 text-sm">
                    <a href="privacy.php" class="text-gray-500 hover:text-white mx-2 transition-colors">Privacy Policy</a>
                    <span class="text-gray-600">|</span>
                    <a href="terms.php" class="text-gray-500 hover:text-white mx-2 transition-colors">Terms of Service</a>
                    <span class="text-gray-600">|</span>
                    <a href="accessibility.php" class="text-gray-500 hover:text-white mx-2 transition-colors">Accessibility</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Select2 for better dropdowns
        $(document).ready(function() {
            $('#community_id').select2({
                placeholder: "Select a community",
                allowClear: true,
                width: '100%'
            });
            
            // Custom editor functionalities (simplified version without tinyMCE)
            $('.bg-gray-50 button').on('click', function() {
                $(this).toggleClass('bg-gray-200');
                
                // This is a simple simulation - in a real implementation you'd add actual formatting
                // functionality based on which button was clicked
                
                // Focus back on the textarea
                $('#description').focus();
            });
            
            // Image upload preview
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview').attr('src', e.target.result).addClass('visible');
                        $('.file-upload-preview').addClass('has-image');
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Allow clicking anywhere in the upload area to trigger file input
            $('.file-upload-preview').on('click', function(e) {
                if ($(e.target).attr('id') !== 'image') {
                    $('#image').click();
                }
            });
            
            // Drag and drop functionality
            $('.file-upload-preview').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('bg-indigo-100');
            }).on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('bg-indigo-100');
            }).on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('bg-indigo-100');
                
                if (e.originalEvent.dataTransfer.files.length) {
                    const file = e.originalEvent.dataTransfer.files[0];
                    if (file.type.match('image.*')) {
                        $('#image')[0].files = e.originalEvent.dataTransfer.files;
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#preview').attr('src', e.target.result).addClass('visible');
                            $('.file-upload-preview').addClass('has-image');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });
            
            // Fancy form interactions
            $('input, textarea, select').on('focus', function() {
                $(this).closest('div').find('label').addClass('text-indigo-600');
            }).on('blur', function() {
                $(this).closest('div').find('label').removeClass('text-indigo-600');
            });
            
            // Animated scroll for tips section
            $('.fas.fa-lightbulb').closest('a').on('click', function(e) {
                e.preventDefault();
                const target = $($(this).attr('href'));
                
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            });
            
            // Form validation enhancement
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // Check required fields
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('border-red-500').removeClass('input-focus');
                        $(this).closest('div').find('label').addClass('text-red-500');
                        
                        // Add error message if not already present
                        if (!$(this).next('.text-red-500').length) {
                            $(this).after('<p class="text-red-500 text-xs mt-1">This field is required</p>');
                        }
                    } else {
                        $(this).removeClass('border-red-500');
                        $(this).closest('div').find('label').removeClass('text-red-500');
                        $(this).next('.text-red-500').remove();
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    const firstError = $('.border-red-500').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 400);
                    }
                }
            });
        });
    </script>
</body>
</html>
