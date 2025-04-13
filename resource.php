<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if resource ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: resources.php');
    exit();
}

$resource_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'user';

// Get resource details with access control
$query = "SELECT r.*, u.full_name as uploader_name, 
          p.name as project_name, c.name as community_name
          FROM resources r
          JOIN users u ON r.uploaded_by = u.id
          LEFT JOIN projects p ON r.project_id = p.id
          LEFT JOIN communities c ON r.community_id = c.id
          WHERE r.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: resources.php');
    exit();
}

$resource = $result->fetch_assoc();
$result->free();
$stmt->close();

// Check access permissions
$has_access = false;
if ($user_type === 'system_admin') {
    $has_access = true;
} elseif ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    $has_access = ($resource['community_id'] == $_SESSION['community_id'] || $resource['is_public'] == 1);
} else {
    // Check if resource is public or user has explicit access
    $access_query = "SELECT 1 FROM resource_access WHERE resource_id = ? AND user_id = ?";
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param('ii', $resource_id, $user_id);
    $access_stmt->execute();
    $access_result = $access_stmt->get_result();
    $has_access = ($resource['is_public'] == 1 || $access_result->num_rows > 0);
    $access_result->free();
    $access_stmt->close();
}

if (!$has_access) {
    header('Location: resources.php');
    exit();
}

// Update access count
$update_query = "INSERT INTO resource_access (resource_id, user_id, granted_at) 
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE granted_at = NOW()";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param('ii', $resource_id, $user_id);
$update_stmt->execute();
$update_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resource['title']); ?> - EKYAM</title>
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

    <!-- Resource Details -->
    <div class="container mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($resource['title']); ?></h1>
                <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                    <?php echo htmlspecialchars($resource['type']); ?>
                </span>
            </div>

            <div class="prose max-w-none mb-8">
                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($resource['description'])); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Resource Details</h3>
                    <div class="space-y-2">
                        <p class="text-gray-600">
                            <i class="fas fa-user mr-2"></i>
                            Uploaded by: <?php echo htmlspecialchars($resource['uploader_name']); ?>
                        </p>
                        <p class="text-gray-600">
                            <i class="fas fa-calendar mr-2"></i>
                            Created: <?php echo date('F j, Y', strtotime($resource['created_at'])); ?>
                        </p>
                        <?php if ($resource['project_name']): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-project-diagram mr-2"></i>
                                Project: <?php echo htmlspecialchars($resource['project_name']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($resource['community_name']): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-users mr-2"></i>
                                Community: <?php echo htmlspecialchars($resource['community_name']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Access Resource</h3>
                    <div class="space-y-4">
                        <?php if (!empty($resource['url'])): ?>
                            <a href="<?php echo htmlspecialchars($resource['url']); ?>" 
                               target="_blank" 
                               class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Open External Link
                            </a>
                        <?php elseif (!empty($resource['file_path'])): ?>
                            <a href="download_resource.php?id=<?php echo $resource['id']; ?>" 
                               class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-download mr-2"></i>
                                Download File
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="resources.php" class="text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Resources
                </a>
            </div>
        </div>
    </div>
</body>
</html> 