<?php
require_once 'config.php';
session_start();

// Check if resource ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: resources.php");
    exit;
}

$resource_id = intval($_GET['id']);

// Fetch resource details
$query = "SELECT r.*, u.full_name as uploaded_by_name, u.username as uploaded_by_username, 
          c.name as community_name, c.id as community_id,
          p.name as project_name, p.id as project_id
          FROM resources r
          LEFT JOIN users u ON r.uploaded_by = u.id
          LEFT JOIN communities c ON r.community_id = c.id
          LEFT JOIN projects p ON r.project_id = p.id
          WHERE r.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: resources.php");
    exit;
}

$resource = mysqli_fetch_assoc($result);

// Check if user has access to the resource
$has_access = false;
if (isset($_SESSION['user_id'])) {
    // Check if resource is public
    if ($resource['is_public']) {
        $has_access = true;
    } else {
        // Check if user is a member of the community
        $accessQuery = "SELECT 1 FROM resource_access 
                       WHERE resource_id = ? AND user_id = ?";
        $accessStmt = mysqli_prepare($conn, $accessQuery);
        mysqli_stmt_bind_param($accessStmt, "ii", $resource_id, $_SESSION['user_id']);
        mysqli_stmt_execute($accessStmt);
        $has_access = mysqli_stmt_num_rows($accessStmt) > 0;
    }
}

// Handle resource download
if (isset($_POST['download']) && $has_access) {
    // Increment download count
    $downloadQuery = "UPDATE resources SET download_count = download_count + 1 WHERE id = ?";
    $downloadStmt = mysqli_prepare($conn, $downloadQuery);
    mysqli_stmt_bind_param($downloadStmt, "i", $resource_id);
    mysqli_stmt_execute($downloadStmt);

    // Log download activity
    if (isset($_SESSION['user_id'])) {
        $activityQuery = "INSERT INTO community_activity (community_id, user_id, activity_type, activity_details)
                         VALUES (?, ?, 'download_resource', ?)";
        $activityStmt = mysqli_prepare($conn, $activityQuery);
        $details = "Downloaded resource: " . $resource['title'];
        mysqli_stmt_bind_param($activityStmt, "iis", $resource['community_id'], $_SESSION['user_id'], $details);
        mysqli_stmt_execute($activityStmt);
    }

    // Redirect to download URL or file
    if (!empty($resource['url'])) {
        header("Location: " . $resource['url']);
    } else if (!empty($resource['file_path'])) {
        header("Location: " . $resource['file_path']);
    }
    exit;
}

// Fetch resource tags (with error handling)
$tags = [];
try {
    // Check if resource_tags table exists
    $checkTableQuery = "SHOW TABLES LIKE 'resource_tags'";
    $tableExists = mysqli_num_rows(mysqli_query($conn, $checkTableQuery)) > 0;

    if ($tableExists) {
        $tagsQuery = "SELECT t.name FROM resource_tags t
                     JOIN resource_tag_relationships rtr ON t.id = rtr.tag_id
                     WHERE rtr.resource_id = ?";
        $tagsStmt = mysqli_prepare($conn, $tagsQuery);
        mysqli_stmt_bind_param($tagsStmt, "i", $resource_id);
        mysqli_stmt_execute($tagsStmt);
        $tagsResult = mysqli_stmt_get_result($tagsStmt);
        
        // Fixed: Properly fetch all tags from result
        while ($tag = mysqli_fetch_assoc($tagsResult)) {
            $tags[] = $tag;
        }
    }
} catch (Exception $e) {
    // Silently handle the error - tags will be empty
    error_log("Error fetching resource tags: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resource['title']); ?> - EKYAM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.75rem 1.5rem;
        }
        .page-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
            width: 100%;
        }
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }
        .card-body {
            padding: 1.5rem;
        }
        .resource-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .resource-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .meta-item i {
            margin-right: 0.5rem;
        }
        .meta-item a {
            color: #495057;
            text-decoration: none;
            transition: color 0.2s;
        }
        .meta-item a:hover {
            color: #212529;
            text-decoration: underline;
        }
        .resource-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }
        .download-count {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        .download-btn {
            background-color: #4a5568;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .download-btn:hover {
            background-color: #2d3748;
        }
        .resource-type {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .tag {
            background-color: #e2e8f0;
            color: #4a5568;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .tag:hover {
            background-color: #cbd5e0;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .description {
            line-height: 1.6;
            color: #495057;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .detail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .detail-item {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .detail-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            color: #212529;
            font-weight: 600;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 0.375rem 0.375rem 0;
            display: flex;
            align-items: center;
        }
        .alert-icon {
            color: #ffc107;
            font-size: 1.25rem;
            margin-right: 1rem;
        }
        .alert-content {
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="max-w-4xl mx-auto">
            <!-- Resource Header -->
            <div class="card">
                <div class="card-body">
                    <h1 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h1>
                    
                    <div class="resource-meta">
                        <?php if (!empty($resource['community_name'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-users"></i>
                                <a href="community_details.php?id=<?php echo $resource['community_id']; ?>">
                                    <?php echo htmlspecialchars($resource['community_name']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($resource['project_name'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-project-diagram"></i>
                                <a href="project_details.php?id=<?php echo $resource['project_id']; ?>">
                                    <?php echo htmlspecialchars($resource['project_name']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                        </div>
                    </div>

                    <!-- Resource Type Badge -->
                    <div class="resource-type
                        <?php
                        switch ($resource['type']) {
                            case 'document':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'link':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'video':
                                echo 'bg-red-100 text-red-800';
                                break;
                            case 'image':
                                echo 'bg-purple-100 text-purple-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <i class="fas fa-<?php
                            switch ($resource['type']) {
                                case 'document':
                                    echo 'file-alt';
                                    break;
                                case 'link':
                                    echo 'link';
                                    break;
                                case 'video':
                                    echo 'video';
                                    break;
                                case 'image':
                                    echo 'image';
                                    break;
                                default:
                                    echo 'file';
                            }
                        ?> mr-2"></i>
                        <?php echo ucfirst($resource['type']); ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($tags)): ?>
                        <div class="tag-container">
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag">
                                    #<?php echo htmlspecialchars($tag['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="resource-actions">
                        <div class="download-count">
                            <i class="fas fa-download mr-2"></i>
                            <?php echo $resource['download_count']; ?> downloads
                        </div>
                        
                        <?php if ($has_access): ?>
                            <form method="post">
                                <button type="submit" name="download" class="download-btn">
                                    <i class="fas fa-download mr-2"></i> Download
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resource Description -->
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">Description</h2>
                    <p class="description"><?php echo nl2br(htmlspecialchars($resource['description'])); ?></p>
                </div>
            </div>

            <!-- Access Information -->
            <?php if (!$has_access): ?>
                <div class="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        You don't have access to this resource. Please contact the community administrator or resource owner for access.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Resource Details -->
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">Details</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <p class="detail-label">Uploaded by</p>
                            <p class="detail-value"><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Upload date</p>
                            <p class="detail-value"><?php echo date('F j, Y', strtotime($resource['created_at'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Last updated</p>
                            <p class="detail-value"><?php echo date('F j, Y', strtotime($resource['updated_at'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Visibility</p>
                            <p class="detail-value"><?php echo $resource['is_public'] ? 'Public' : 'Private'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>