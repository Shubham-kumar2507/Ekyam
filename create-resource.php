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
$community_id = isset($_GET['community']) ? (int)$_GET['community'] : ($_SESSION['community_id'] ?? null);

// Get available projects for the user
$projects_query = "SELECT p.id, p.name 
                  FROM projects p 
                  LEFT JOIN project_members pm ON p.id = pm.project_id 
                  WHERE (p.created_by = ? OR pm.user_id = ? OR ? = 'system_admin')";
$stmt = $conn->prepare($projects_query);
$stmt->bind_param('iis', $user_id, $user_id, $user_type);
$stmt->execute();
$projects_result = $stmt->get_result();
$projects = array();
while ($row = $projects_result->fetch_assoc()) {
    $projects[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
    
    // Validate input
    $errors = array();
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if (!in_array($type, ['document', 'link', 'video', 'image', 'other'])) {
        $errors[] = "Invalid resource type";
    }
    
    // Handle file upload if type is document or image
    $file_path = null;
    if ($type === 'document' || $type === 'image') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = $type === 'document' 
                ? ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
                : ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($_FILES['file']['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
            } else {
                $upload_dir = 'uploads/resources/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $errors[] = "Failed to upload file";
                }
            }
        } else {
            $errors[] = "File upload is required for this resource type";
        }
    }
    
    // Handle URL if type is link or video
    $url = null;
    if ($type === 'link' || $type === 'video') {
        $url = trim($_POST['url']);
        if (empty($url)) {
            $errors[] = "URL is required for this resource type";
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid URL format";
        }
    }
    
    if (empty($errors)) {
        // Insert resource into database
        $query = "INSERT INTO resources (title, description, type, url, file_path, community_id, project_id, is_public, uploaded_by) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssiiii', $title, $description, $type, $url, $file_path, $community_id, $project_id, $is_public, $user_id);
        
        if ($stmt->execute()) {
            $resource_id = $stmt->insert_id;
            
            // Create initial access record
            $access_query = "INSERT INTO resource_access (resource_id, user_id) VALUES (?, ?)";
            $access_stmt = $conn->prepare($access_query);
            $access_stmt->bind_param('ii', $resource_id, $user_id);
            $access_stmt->execute();
            
            header("Location: resource.php?id=" . $resource_id);
            exit();
        } else {
            $errors[] = "Failed to create resource";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Resource - EKYAM</title>
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

    <!-- Create Resource Form -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Create New Resource</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="create-resource.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-6">
                    <label for="title" class="block text-gray-700 font-medium mb-2">Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea id="description" name="description" required rows="4"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="type" class="block text-gray-700 font-medium mb-2">Resource Type</label>
                    <select id="type" name="type" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onchange="toggleResourceFields()">
                        <option value="">Select Type</option>
                        <option value="document" <?php echo isset($_POST['type']) && $_POST['type'] === 'document' ? 'selected' : ''; ?>>Document</option>
                        <option value="link" <?php echo isset($_POST['type']) && $_POST['type'] === 'link' ? 'selected' : ''; ?>>Link</option>
                        <option value="video" <?php echo isset($_POST['type']) && $_POST['type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                        <option value="image" <?php echo isset($_POST['type']) && $_POST['type'] === 'image' ? 'selected' : ''; ?>>Image</option>
                        <option value="other" <?php echo isset($_POST['type']) && $_POST['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div id="file-upload-field" class="mb-6 hidden">
                    <label for="file" class="block text-gray-700 font-medium mb-2">File</label>
                    <input type="file" id="file" name="file"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-sm text-gray-500 mt-1">
                        For documents: PDF, DOC, DOCX<br>
                        For images: JPG, PNG, GIF
                    </p>
                </div>
                
                <div id="url-field" class="mb-6 hidden">
                    <label for="url" class="block text-gray-700 font-medium mb-2">URL</label>
                    <input type="url" id="url" name="url"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>">
                </div>
                
                <?php if (!empty($projects)): ?>
                    <div class="mb-6">
                        <label for="project_id" class="block text-gray-700 font-medium mb-2">Associated Project (Optional)</label>
                        <select id="project_id" name="project_id"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>" <?php echo isset($_POST['project_id']) && $_POST['project_id'] == $project['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_public" class="form-checkbox h-5 w-5 text-indigo-600"
                               <?php echo isset($_POST['is_public']) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-gray-700">Make this resource public</span>
                    </label>
                </div>
                
                <div class="flex justify-end">
                    <a href="resources.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 mr-4">Cancel</a>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                        Create Resource
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleResourceFields() {
            const type = document.getElementById('type').value;
            const fileField = document.getElementById('file-upload-field');
            const urlField = document.getElementById('url-field');
            
            fileField.classList.add('hidden');
            urlField.classList.add('hidden');
            
            if (type === 'document' || type === 'image') {
                fileField.classList.remove('hidden');
            } else if (type === 'link' || type === 'video') {
                urlField.classList.remove('hidden');
            }
        }
        
        // Initialize fields on page load
        document.addEventListener('DOMContentLoaded', toggleResourceFields);
    </script>
</body>
</html> 