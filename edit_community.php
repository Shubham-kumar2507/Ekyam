<?php
require_once 'config.php';
session_start();

// Check if user is logged in
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
$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch community details and verify admin status
$query = "SELECT * FROM communities WHERE id = ? AND admin_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $community_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Not the admin of this community
    header("Location: communities.php");
    exit;
}

$community = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    
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
            $user_id
        );
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Record activity
            $activity_query = "INSERT INTO community_activity (community_id, user_id, activity_type, activity_date) 
                              VALUES (?, ?, 'updated community details', CURRENT_TIMESTAMP)";
            $activity_stmt = mysqli_prepare($conn, $activity_query);
            mysqli_stmt_bind_param($activity_stmt, "ii", $community_id, $user_id);
            mysqli_stmt_execute($activity_stmt);
            
            $success = true;
            
            // Refresh community data
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $community_id, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $community = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Failed to update community: " . mysqli_error($conn);
        }
    }
}

// Fetch categories for dropdown
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Community - <?php echo htmlspecialchars($community['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Libre Baskerville', serif;
            background-color: #f8f5f1;
            color: #2c3e50;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: #2c3e50;
        }
        .classic-border {
            border: 1px solid #d4c9b0;
            border-radius: 4px;
        }
        .classic-card {
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            border: 1px solid #d4c9b0;
        }
        .classic-header {
            background: linear-gradient(135deg, #3c6e71, #284b63);
            color: white;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .classic-btn {
            background-color: #3c6e71;
            color: white;
            transition: all 0.3s ease;
            border-radius: 4px;
            font-weight: 500;
        }
        .classic-btn:hover {
            background-color: #284b63;
            transform: translateY(-1px);
        }
        .form-input {
            border: 1px solid #d4c9b0;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #3c6e71;
            outline: none;
            box-shadow: 0 0 0 3px rgba(60, 110, 113, 0.2);
        }
        .ornament {
            height: 2px;
            background: linear-gradient(to right, transparent, #d4c9b0, transparent);
            margin: 1rem 0;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #d4c9b0;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="classic-card">
            <div class="classic-header p-6">
                <h1 class="text-2xl font-bold">Edit Community Details</h1>
            </div>
            <div class="p-6">
                <?php if ($success): ?>
                    <div class="alert-success mb-6">
                        <i class="fas fa-check-circle mr-2"></i> Community details have been successfully updated.
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert-error mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <ul class="list-disc ml-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="edit_community.php?id=<?php echo $community_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block mb-2 font-semibold">Community Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($community['name']); ?>" required class="form-input w-full">
                        </div>
                        
                        <div>
                            <label for="description" class="block mb-2 font-semibold">Description</label>
                            <textarea id="description" name="description" rows="5" required class="form-input w-full"><?php echo htmlspecialchars($community['description']); ?></textarea>
                            <p class="text-gray-500 text-sm mt-1">Describe your community's mission, goals, and activities.</p>
                        </div>
                        
                        <div>
                            <label for="location" class="block mb-2 font-semibold">Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($community['location']); ?>" required class="form-input w-full">
                            <p class="text-gray-500 text-sm mt-1">City, State, Country or Region</p>
                        </div>
                        
                        <div>
                            <label for="category" class="block mb-2 font-semibold">Category</label>
                            <select id="category" name="category" class="form-input w-full">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($community['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="image" class="block mb-2 font-semibold">Community Image</label>
                            <?php if (!empty($community['image'])): ?>
                                <div class="mb-3">
                                    <p class="mb-2 text-sm text-gray-600">Current Image:</p>
                                    <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="Community Image" class="image-preview">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="image" name="image" class="form-input w-full" accept="image/jpeg,image/png,image/gif">
                            <p class="text-gray-500 text-sm mt-1">Upload a new image (JPEG, PNG, or GIF) or leave blank to keep the current image.</p>
                        </div>
                        
                        <div class="ornament"></div>
                        
                        <div class="flex justify-between">
                            <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="px-6 py-3 bg-gray-300 hover:bg-gray-400 rounded-md transition-all duration-300">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="classic-btn px-6 py-3">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>