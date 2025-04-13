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

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build the query
$query = "SELECT r.*, u.full_name as uploader_name, 
          (SELECT COUNT(*) FROM resource_access ra WHERE ra.resource_id = r.id) as access_count,
          p.name as project_name, c.name as community_name
          FROM resources r
          JOIN users u ON r.uploaded_by = u.id
          LEFT JOIN projects p ON r.project_id = p.id
          LEFT JOIN communities c ON r.community_id = c.id
          WHERE 1=1";

$params = array();
$types = array();

// Access control based on user type
if ($user_type === 'community_admin' && isset($_SESSION['community_id'])) {
    $community_id = $_SESSION['community_id'];
    $query .= " AND (r.community_id = ? OR r.is_public = 1)";
    $params[] = $community_id;
    $types[] = 'i';
} elseif ($user_type === 'system_admin') {
    // System admins can see all resources
    $query .= " AND 1=1";
} else {
    // Regular users can see public resources and those they have access to
    $query .= " AND (r.is_public = 1 OR EXISTS (
        SELECT 1 FROM resource_access ra 
        WHERE ra.resource_id = r.id AND ra.user_id = ?
    ))";
    $params[] = $user_id;
    $types[] = 'i';
}

// Filter by project if specified
if ($project_id > 0) {
    $query .= " AND r.project_id = ?";
    $params[] = $project_id;
    $types[] = 'i';
}

if (!empty($search)) {
    $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types[] = 's';
    $types[] = 's';
}

if (!empty($type)) {
    $query .= " AND r.type = ?";
    $params[] = $type;
    $types[] = 's';
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM ($query) as count_query";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param(implode('', $types), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Add pagination and sorting
$query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types[] = 'i';
$types[] = 'i';

// Execute the main query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(implode('', $types), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$resources = array();
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

// Get available projects for filter
$projects_query = "SELECT id, name FROM projects ORDER BY name";
$projects_result = $conn->query($projects_query);
$projects = array();
while ($row = $projects_result->fetch_assoc()) {
    $projects[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - EKYAM</title>
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

    <!-- Resources Section -->
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Resources</h1>
            <a href="create-resource.php" class="bg-indigo-700 text-white px-4 py-2 rounded-lg hover:bg-indigo-600">
                <i class="fas fa-plus mr-2"></i>Add Resource
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form action="resources.php" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-grow">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search resources..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-full md:w-48">
                    <select name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="document" <?php echo $type === 'document' ? 'selected' : ''; ?>>Document</option>
                        <option value="link" <?php echo $type === 'link' ? 'selected' : ''; ?>>Link</option>
                        <option value="video" <?php echo $type === 'video' ? 'selected' : ''; ?>>Video</option>
                        <option value="image" <?php echo $type === 'image' ? 'selected' : ''; ?>>Image</option>
                        <option value="other" <?php echo $type === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <select name="project_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Projects</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo $project_id == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Search
                </button>
            </form>
        </div>

        <!-- Resources Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($resources)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No resources found.</p>
                    <a href="create-resource.php" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Add Your First Resource
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($resources as $resource): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">
                                    <a href="resource.php?id=<?php echo $resource['id']; ?>" class="hover:text-indigo-600">
                                        <?php echo htmlspecialchars($resource['title']); ?>
                                    </a>
                                </h3>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($resource['type']); ?>
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($resource['description']); ?>
                            </p>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span>
                                    <i class="fas fa-user mr-1"></i>
                                    <?php echo htmlspecialchars($resource['uploader_name']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-users mr-1"></i>
                                    <?php echo $resource['access_count']; ?> access
                                </span>
                            </div>
                            <?php if ($resource['project_name']): ?>
                                <div class="mt-2 text-sm text-gray-500">
                                    <i class="fas fa-project-diagram mr-1"></i>
                                    <?php echo htmlspecialchars($resource['project_name']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($resource['community_name']): ?>
                                <div class="mt-2 text-sm text-gray-500">
                                    <i class="fas fa-users mr-1"></i>
                                    <?php echo htmlspecialchars($resource['community_name']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Shared by <?php echo htmlspecialchars($resource['uploader_name']); ?>
                                </div>
                                
                                <?php if (!empty($resource['url'])): ?>
                                <a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-external-link-alt mr-1"></i> Open
                                </a>
                                <?php elseif (!empty($resource['file_path'])): ?>
                                <a href="download_resource.php?id=<?php echo $resource['id']; ?>" class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center">
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&project_id=<?php echo $project_id; ?>" 
                           class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&project_id=<?php echo $project_id; ?>" 
                           class="px-4 py-2 border rounded-lg <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&project_id=<?php echo $project_id; ?>" 
                           class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Newsletter Section -->
    <section class="bg-indigo-700 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-4">Stay Updated with EKYAM</h2>
            <p class="text-xl mb-6 max-w-3xl mx-auto">Subscribe to our newsletter for the latest resources and community updates.</p>
            <form id="newsletterForm" action="subscribe_newsletter.php" method="POST" class="max-w-md mx-auto">
                <div class="flex">
                    <input type="email" name="email" placeholder="Your email" required
                           class="flex-grow px-4 py-2 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
                    <button type="submit" class="bg-white text-indigo-700 px-6 py-2 rounded-r-lg hover:bg-indigo-100">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">EKYAM</h3>
                    <p class="text-gray-400">Fostering unity and collaboration among diverse communities.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                        <li><a href="resources.php" class="text-gray-400 hover:text-white">Resources</a></li>
                        <li><a href="communities.php" class="text-gray-400 hover:text-white">Communities</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="help.php" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="guidelines.php" class="text-gray-400 hover:text-white">Community Guidelines</a></li>
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
        
        // Newsletter form submission
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('subscribe_newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.reset();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while subscribing. Please try again.');
            });
        });
    </script>
</body>
</html> 