<?php
// Assuming config.php contains database connection details
require_once 'config.php';
session_start();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: communities.php");
    exit;
}

$community_id = intval($_GET['id']);

// Fetch community details
$query = "SELECT c.*, COUNT(DISTINCT cm.id) as member_count, u.full_name as admin_name, u.username as admin_username, u.profile_image as admin_image 
         FROM communities c 
         LEFT JOIN community_members cm ON c.id = cm.community_id 
         LEFT JOIN users u ON c.admin_id = u.id 
         WHERE c.id = ?
         GROUP BY c.id";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $community_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: communities.php");
    exit;
}

$community = mysqli_fetch_assoc($result);

// Check if current user is the admin of this community
$is_admin = isset($_SESSION['user_id']) && $community['admin_id'] == $_SESSION['user_id'];
error_log("User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("Community Admin ID: " . $community['admin_id']);
error_log("Is Admin: " . ($is_admin ? 'true' : 'false'));

// Fetch community members
$membersQuery = "SELECT u.id, u.full_name, u.username, u.profile_image, cm.role, cm.joined_at 
                FROM community_members cm 
                JOIN users u ON cm.user_id = u.id 
                WHERE cm.community_id = ? 
                ORDER BY cm.role, cm.joined_at DESC 
                LIMIT 8";

$membersStmt = mysqli_prepare($conn, $membersQuery);
mysqli_stmt_bind_param($membersStmt, "i", $community_id);
mysqli_stmt_execute($membersStmt);
$membersResult = mysqli_stmt_get_result($membersStmt);
$members = mysqli_fetch_all($membersResult, MYSQLI_ASSOC);

// Fetch community projects
$projectsQuery = "SELECT p.id, p.name, p.description, p.status, p.start_date, p.image, p.member_count 
                 FROM projects p 
                 WHERE p.community_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 3";

$projectsStmt = mysqli_prepare($conn, $projectsQuery);
mysqli_stmt_bind_param($projectsStmt, "i", $community_id);
mysqli_stmt_execute($projectsStmt);
$projectsResult = mysqli_stmt_get_result($projectsStmt);
$projects = mysqli_fetch_all($projectsResult, MYSQLI_ASSOC);

// Fetch community resources
$resourcesQuery = "SELECT r.id, r.title, r.description, r.type, r.download_count, u.full_name as uploaded_by_name 
                  FROM resources r 
                  JOIN users u ON r.uploaded_by = u.id 
                  WHERE r.community_id = ? AND r.is_public = 1 
                  ORDER BY r.created_at DESC 
                  LIMIT 3";

$resourcesStmt = mysqli_prepare($conn, $resourcesQuery);
mysqli_stmt_bind_param($resourcesStmt, "i", $community_id);
mysqli_stmt_execute($resourcesStmt);
$resourcesResult = mysqli_stmt_get_result($resourcesStmt);
$resources = mysqli_fetch_all($resourcesResult, MYSQLI_ASSOC);

// Fetch community location for map
$locationQuery = "SELECT latitude, longitude, address 
                 FROM community_locations 
                 WHERE community_id = ?";

$locationStmt = mysqli_prepare($conn, $locationQuery);
mysqli_stmt_bind_param($locationStmt, "i", $community_id);
mysqli_stmt_execute($locationStmt);
$locationResult = mysqli_stmt_get_result($locationStmt);
$location = mysqli_fetch_assoc($locationResult);

// Check if user is logged in
$isLoggedIn = false;
$isMember = false;
$isAdmin = false;

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userId = $_SESSION['user_id'];
    
    // Check if user is a member
    $memberCheckQuery = "SELECT id, role FROM community_members WHERE community_id = ? AND user_id = ?";
    $memberCheckStmt = mysqli_prepare($conn, $memberCheckQuery);
    mysqli_stmt_bind_param($memberCheckStmt, "ii", $community_id, $userId);
    mysqli_stmt_execute($memberCheckStmt);
    $memberCheckResult = mysqli_stmt_get_result($memberCheckStmt);
    $memberData = mysqli_fetch_assoc($memberCheckResult);
    $isMember = $memberData !== null;
    $isAdmin = $isMember && ($memberData['role'] === 'admin' || $community['admin_id'] == $userId);
}

// Handle join community request
if (isset($_POST['join_community']) && $isLoggedIn && !$isMember && !$isAdmin) {
    $joinQuery = "INSERT INTO community_members (community_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())";
    $joinStmt = mysqli_prepare($conn, $joinQuery);
    mysqli_stmt_bind_param($joinStmt, "ii", $community_id, $userId);
    
    if (mysqli_stmt_execute($joinStmt)) {
        $isMember = true;
        $_SESSION['success_message'] = "You have successfully joined the community!";
        $_SESSION['community_id'] = $community_id;
        header("Location: community_details.php?id=$community_id");
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to join community. Please try again.";
    }
}

// Handle leave community request
if (isset($_POST['leave_community']) && $isLoggedIn && $isMember && !$isAdmin) {
    $leaveQuery = "DELETE FROM community_members WHERE community_id = ? AND user_id = ?";
    $leaveStmt = mysqli_prepare($conn, $leaveQuery);
    mysqli_stmt_bind_param($leaveStmt, "ii", $community_id, $userId);
    
    if (mysqli_stmt_execute($leaveStmt)) {
        $isMember = false;
        $_SESSION['success_message'] = "You have left the community.";
        header("Location: community_details.php?id=$community_id");
        exit;
    }
}

// Handle member role update
if (isset($_POST['update_role']) && $isLoggedIn && $isAdmin) {
    $memberId = intval($_POST['member_id']);
    $newRole = $_POST['new_role'];
    
    // Prevent changing admin's role
    if ($memberId != $community['admin_id']) {
        $updateRoleQuery = "UPDATE community_members SET role = ? WHERE community_id = ? AND user_id = ?";
        $updateRoleStmt = mysqli_prepare($conn, $updateRoleQuery);
        mysqli_stmt_bind_param($updateRoleStmt, "sii", $newRole, $community_id, $memberId);
        mysqli_stmt_execute($updateRoleStmt);
        
        $_SESSION['success_message'] = "Member role updated successfully.";
        header("Location: community_details.php?id=$community_id");
        exit;
    }
}

// Handle member removal
if (isset($_POST['remove_member']) && $isLoggedIn && $isAdmin) {
    $memberId = intval($_POST['member_id']);
    
    // Prevent removing admin
    if ($memberId != $community['admin_id']) {
        $removeMemberQuery = "DELETE FROM community_members WHERE community_id = ? AND user_id = ?";
        $removeMemberStmt = mysqli_prepare($conn, $removeMemberQuery);
        mysqli_stmt_bind_param($removeMemberStmt, "ii", $community_id, $memberId);
        mysqli_stmt_execute($removeMemberStmt);
        
        $_SESSION['success_message'] = "Member removed successfully.";
        header("Location: community_details.php?id=$community_id");
        exit;
    }
}

// Handle edit community request
if (isset($_POST['edit_community']) && $isLoggedIn && $isAdmin) {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $errors = [];
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Community name is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    // Process image upload if provided
    $image_path = $community['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $upload_dir = 'uploads/community_images/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                    
                    // Delete old image if it exists and isn't a default image
                    if (!empty($community['image']) && file_exists($community['image']) && strpos($community['image'], 'default') === false) {
                        unlink($community['image']);
                    }
                } else {
                    $errors[] = "Failed to upload image";
                }
            } else {
                $errors[] = "Invalid file type. Please upload JPEG, PNG, or GIF.";
            }
        } else {
            $errors[] = "Image upload error: " . $_FILES['image']['error'];
        }
    }
    
    // If no errors, update the community
    if (empty($errors)) {
        $update_query = "UPDATE communities SET 
                        name = ?, 
                        description = ?, 
                        location = ?, 
                        category = ?, 
                        image = ?,
                        updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ? AND admin_id = ?";
                        
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param(
            $update_stmt, 
            "sssssii", 
            $name, 
            $description, 
            $location, 
            $category, 
            $image_path, 
            $community_id, 
            $userId
        );
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Record activity
            $activity_query = "INSERT INTO community_activity (community_id, user_id, activity_type, activity_date) 
                              VALUES (?, ?, 'updated community details', CURRENT_TIMESTAMP)";
            $activity_stmt = mysqli_prepare($conn, $activity_query);
            mysqli_stmt_bind_param($activity_stmt, "ii", $community_id, $userId);
            mysqli_stmt_execute($activity_stmt);
            
            $_SESSION['success_message'] = "Community details have been successfully updated.";
            
            // Refresh community data
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $community_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $community = mysqli_fetch_assoc($result);
            
            header("Location: community_details.php?id=$community_id");
            exit;
        } else {
            $errors[] = "Failed to update community: " . mysqli_error($conn);
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($community['name']); ?> - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100 font-sans">
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
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-indigo-800" id="mobileMenu">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="Homepage.php" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200 text-indigo-100">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
            </div>
        </div>
    </nav>

    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative container mx-auto mt-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"> <?php echo $_SESSION['success_message']; ?></span>
            <button class="absolute top-0 bottom-0 right-0 px-4 py-3 alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Community Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row items-start md:items-center">
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                    <?php if (!empty($community['image'])): ?>
                        <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>" class="w-24 h-24 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-4xl text-indigo-400"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-grow">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($community['name']); ?></h1>
                            <?php if ($is_admin): ?>
                                <a href="edit_community.php?id=<?php echo $community_id; ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-edit mr-2"></i> Edit Community
                                </a>
                            <?php endif; ?>
                            <div class="flex flex-wrap items-center text-gray-600 mb-2">
                                <?php if (!empty($community['category'])): ?>
                                    <span class="mr-4">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?php echo htmlspecialchars($community['category']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="mr-4">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?php echo htmlspecialchars($community['location']); ?>
                                </span>
                                <span class="mr-4">
                                    <i class="fas fa-users mr-1"></i>
                                    <?php 
                                    // Get accurate member count including admin
                                    $countQuery = "SELECT COUNT(*) as total_members FROM community_members WHERE community_id = ?";
                                    $countStmt = mysqli_prepare($conn, $countQuery);
                                    mysqli_stmt_bind_param($countStmt, "i", $community_id);
                                    mysqli_stmt_execute($countStmt);
                                    $countResult = mysqli_stmt_get_result($countStmt);
                                    $countData = mysqli_fetch_assoc($countResult);
                                    echo intval($countData['total_members']) + 1; // +1 for admin
                                    ?> members
                                </span>
                                <span>
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Established: <?php echo date('M Y', strtotime($community['created_at'])); ?>
                                </span>
                            </div>
                            <div class="mb-4 flex items-center">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                    <?php if (!empty($community['admin_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($community['admin_image']); ?>" alt="Admin" class="w-6 h-6 rounded-full">
                                    <?php else: ?>
                                        <i class="fas fa-user text-indigo-600 text-sm"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="text-gray-600 text-sm">
                                    Admin: <?php echo htmlspecialchars($community['admin_name']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-4 md:mt-0">
                            <?php if ($isLoggedIn && !$isMember && !$isAdmin): ?>
                                <form method="post" action="">
                                    <button type="submit" name="join_community" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                                        <i class="fas fa-plus mr-2"></i> Join Community
                                    </button>
                                </form>
                            <?php elseif ($isLoggedIn && $isMember): ?>
                                <div class="flex space-x-2">
                                    <a href="community_chat.php?id=<?php echo $community_id; ?>" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium hover:bg-indigo-200">
                                        <i class="fas fa-comments mr-1"></i> Chat
                                    </a>
                                    <form method="post" action="" onsubmit="return confirm('Are you sure you want to leave this community?');">
                                        <button type="submit" name="leave_community" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300">
                                            <i class="fas fa-sign-out-alt mr-1"></i> Leave
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($isLoggedIn && $isAdmin): ?>
                                <div class="flex space-x-2">
                                    <a href="edit_community.php?id=<?php echo $community_id; ?>" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium hover:bg-indigo-200" onclick="event.preventDefault(); window.location.href='edit_community.php?id=<?php echo $community_id; ?>';">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700">
                                        <i class="fas fa-tachometer-alt mr-1"></i> Manage
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="login.php?redirect=community_details.php?id=<?php echo $community_id; ?>" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Join
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - About and Map -->
            <div class="lg:col-span-2">
                <!-- About Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-4">About</h2>
                    <p class="text-gray-700 mb-6"><?php echo nl2br(htmlspecialchars($community['description'])); ?></p>
                    
                    <!-- Location Map -->
                    <?php if (!empty($location)): ?>
                        <h3 class="text-xl font-semibold mb-4">Location</h3>
                        <div id="community-map" class="w-full h-64 rounded-lg mb-4"></div>
                        <?php if (!empty($location['address'])): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($location['address']); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Projects Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Projects</h2>
                        <a href="projects.php?community=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-4xl mb-3">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">No projects yet</h3>
                            <p class="text-gray-600">This community hasn't started any projects.</p>
                            <?php if ($isLoggedIn && ($isMember || $isAdmin)): ?>
                                <a href="create_project.php?community=<?php echo $community_id; ?>" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-plus mr-1"></i> Create a project
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($projects as $project): ?>
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition duration-300">
                                    <?php if (!empty($project['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>" class="w-full h-40 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-40 bg-indigo-50 flex items-center justify-center">
                                            <i class="fas fa-project-diagram text-4xl text-indigo-300"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="px-2 py-1 bg-<?php 
                                                if ($project['status'] == 'completed') echo 'green';
                                                elseif ($project['status'] == 'in_progress') echo 'blue';
                                                elseif ($project['status'] == 'planning') echo 'yellow';
                                                else echo 'gray';
                                            ?>-100 text-<?php 
                                                if ($project['status'] == 'completed') echo 'green';
                                                elseif ($project['status'] == 'in_progress') echo 'blue';
                                                elseif ($project['status'] == 'planning') echo 'yellow';
                                                else echo 'gray';
                                            ?>-800 rounded text-xs font-semibold">
                                                <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                            </span>
                                            <span class="text-gray-500 text-sm">
                                                <i class="fas fa-calendar mr-1"></i> <?php echo date('M Y', strtotime($project['start_date'])); ?>
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($project['name']); ?></h3>
                                        <p class="text-gray-600 mb-4 text-sm line-clamp-2"><?php echo htmlspecialchars($project['description']); ?></p>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-500 text-sm">
                                                <i class="fas fa-users mr-1"></i> <?php echo $project['member_count']; ?> members
                                            </span>
                                            <a href="project_details.php?id=<?php echo $project['id']; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                Details <i class="fas fa-chevron-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Resources Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Public Resources</h2>
                        <a href="resources.php?community=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <?php if (empty($resources)): ?>
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-4xl mb-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">No public resources yet</h3>
                            <p class="text-gray-600">This community hasn't shared any public resources.</p>
                            <?php if ($isLoggedIn && ($isMember || $isAdmin)): ?>
                                <a href="create-resource.php?community=<?php echo $community_id; ?>" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-upload mr-1"></i> Share a resource
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($resources as $resource): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-300">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 p-3 bg-indigo-50 rounded-lg mr-4">
                                            <?php 
                                            switch ($resource['type']) {
                                                case 'document':
                                                    echo '<i class="fas fa-file-alt text-indigo-500 text-xl"></i>';
                                                    break;
                                                case 'link':
                                                    echo '<i class="fas fa-link text-indigo-500 text-xl"></i>';
                                                    break;
                                                case 'video':
                                                    echo '<i class="fas fa-video text-indigo-500 text-xl"></i>';
                                                    break;
                                                case 'image':
                                                    echo '<i class="fas fa-image text-indigo-500 text-xl"></i>';
                                                    break;
                                                default:
                                                    echo '<i class="fas fa-file text-indigo-500 text-xl"></i>';
                                            }
                                            ?>
                                        </div>
                                        <div class="flex-grow">
                                            <h3 class="text-lg font-medium mb-1"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                            <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <span class="mr-4">
                                                    <i class="fas fa-user mr-1"></i> 
                                                    <?php echo htmlspecialchars($resource['uploaded_by_name']); ?>
                                                </span>
                                                <span>
                                                    <i class="fas fa-download mr-1"></i> 
                                                    <?php echo $resource['download_count']; ?> downloads
                                                </span>
                                            </div>
                                        </div>
                                        <a href="resource_details.php?id=<?php echo $resource['id']; ?>" class="flex-shrink-0 text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column - Members and Quick Actions -->
            <div>
                <!-- Members Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Members</h2>
                        <a href="community_members.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <!-- Admin Card -->
                    <div class="flex items-center p-3 bg-indigo-50 rounded-lg mb-4">
                        <div class="flex-shrink-0 mr-3">
                            <?php if (!empty($community['admin_image'])): ?>
                                <img src="<?php echo htmlspecialchars($community['admin_image']); ?>" alt="Admin" class="w-10 h-10 rounded-full">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-indigo-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-indigo-600"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow">
                            <div class="font-medium"><?php echo htmlspecialchars($community['admin_name']); ?></div>
                            <div class="text-sm text-indigo-700">Administrator</div>
                        </div>
                    </div>
                    
                    <!-- Members List -->
                    <?php if (empty($members)): ?>
                        <p class="text-gray-500 text-center py-4">No other members yet.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($members as $member): ?>
                                <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 mr-3">
                                        <?php if (!empty($member['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="Member" class="w-8 h-8 rounded-full">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600 text-sm"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="font-medium text-sm"><?php echo htmlspecialchars($member['full_name']); ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo ucfirst($member['role']); ?> • Joined <?php echo date('M Y', strtotime($member['joined_at'])); ?>
                                        </div>
                                    </div>
                                    <?php if ($isAdmin): ?>
                                        <div class="relative">
                                            <button class="text-gray-400 hover:text-gray-600 member-options" data-id="<?php echo $member['id']; ?>">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 member-dropdown">
                                                <form method="post" action="" class="px-4 py-2">
                                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                    <select name="new_role" class="w-full text-sm border rounded p-1 mb-2">
                                                        <option value="member" <?php echo $member['role'] === 'member' ? 'selected' : ''; ?>>Member</option>
                                                        <option value="moderator" <?php echo $member['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                                    </select>
                                                    <button type="submit" name="update_role" class="w-full text-left text-sm text-indigo-600 hover:text-indigo-800">
                                                        Update Role
                                                    </button>
                                                </form>
                                                <form method="post" action="" onsubmit="return confirm('Are you sure you want to remove this member?');" class="px-4 py-2">
                                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                    <button type="submit" name="remove_member" class="w-full text-left text-sm text-red-600 hover:text-red-800">
                                                        Remove Member
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <?php if ($isLoggedIn && ($isMember || $isAdmin)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
                        <div class="space-y-3">
                            <a href="create_project.php?community=<?php echo $community_id; ?>" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                <i class="fas fa-plus-circle text-indigo-600 mr-3"></i>
                                <span>Create Project</span>
                            </a>
                            <a href="create-resource.php?community=<?php echo $community_id; ?>" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                <i class="fas fa-upload text-indigo-600 mr-3"></i>
                                <span>Share Resource</span>
                            </a>
                            <a href="community_chat.php?id=<?php echo $community_id; ?>" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                <i class="fas fa-comments text-indigo-600 mr-3"></i>
                                <span>Community Chat</span>
                            </a>
                            <?php if ($isAdmin): ?>
                                <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                    <i class="fas fa-tachometer-alt text-indigo-600 mr-3"></i>
                                    <span>Community Dashboard</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Community Modal -->
    <div id="editCommunityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Community Details</h3>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                        <button class="absolute top-0 bottom-0 right-0 px-4 py-3 alert-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Community Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($community['name']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($community['description']); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($community['location']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <?php
                            $categories = [
                                'Education',
                                'Environment',
                                'Technology',
                                'Health',
                                'Social',
                                'Arts',
                                'Business',
                                'Community Development',
                                'Agriculture',
                                'Other'
                            ];
                            foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($community['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Community Image</label>
                        <?php if (!empty($community['image'])): ?>
                            <div class="mb-2">
                                <p class="text-sm text-gray-600">Current Image:</p>
                                <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="Community Image" class="h-20 w-20 object-cover rounded">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="closeEditModal" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" name="edit_community" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize map if location exists
        <?php if (!empty($location)): ?>
            var map = L.map('community-map').setView([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>]).addTo(map)
                .bindPopup('<?php echo htmlspecialchars($community['name']); ?>')
                .openPopup();
        <?php endif; ?>

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // Member options dropdown
        document.querySelectorAll('.member-options').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling;
                document.querySelectorAll('.member-dropdown').forEach(d => {
                    if (d !== dropdown) d.classList.add('hidden');
                });
                dropdown.classList.toggle('hidden');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.member-dropdown').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        });

        // Close alert messages
        document.querySelectorAll('.alert-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });

        // Edit Community Modal
        const editCommunityBtn = document.getElementById('editCommunityBtn');
        const editCommunityModal = document.getElementById('editCommunityModal');
        const closeEditModal = document.getElementById('closeEditModal');

        if (editCommunityBtn) {
            editCommunityBtn.addEventListener('click', () => {
                editCommunityModal.classList.remove('hidden');
            });
        }

        if (closeEditModal) {
            closeEditModal.addEventListener('click', () => {
                editCommunityModal.classList.add('hidden');
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === editCommunityModal) {
                editCommunityModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html> 