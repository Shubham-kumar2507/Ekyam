<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is a community admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if community ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: communities.php");
    exit;
}

$community_id = intval($_GET['id']);

// Verify user is admin of this community
$query = "SELECT c.* FROM communities c WHERE c.id = ? AND c.admin_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $community_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: communities.php");
    exit;
}

$community = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    
    if (!empty($name) && !empty($description) && !empty($location)) {
        // Handle image upload
        $image = $community['image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/communities/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists
                    if (!empty($community['image']) && file_exists($community['image'])) {
                        unlink($community['image']);
                    }
                    $image = $upload_path;
                }
            }
        }
        
        // Update community settings
        $updateQuery = "UPDATE communities SET 
                       name = ?, 
                       description = ?, 
                       location = ?, 
                       category = ?, 
                       image = ?,
                       updated_at = NOW()
                       WHERE id = ?";
        
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "sssssi", 
            $name, $description, $location, $category, $image, $community_id);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $_SESSION['success_message'] = "Community settings updated successfully!";
            header("Location: community_settings.php?id=$community_id");
            exit;
        } else {
            $error_message = "Error updating community settings. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Settings - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Community Settings</h1>
                    <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Community Name</label>
                        <input type="text" name="name" id="name" required
                               value="<?php echo htmlspecialchars($community['name']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($community['description']); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="location" required
                               value="<?php echo htmlspecialchars($community['location']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <input type="text" name="category" id="category"
                               value="<?php echo htmlspecialchars($community['category'] ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Community Image</label>
                        <?php if (!empty($community['image'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($community['image']); ?>" 
                                     alt="Community Image" 
                                     class="h-32 w-32 object-cover rounded-lg">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 