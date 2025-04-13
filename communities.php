<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination setup
$recordsPerPage = 9;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Filter setup
$category = isset($_GET['category']) ? $_GET['category'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$query = "SELECT c.*, COUNT(cm.id) as member_count, u.full_name as admin_name 
         FROM communities c 
         LEFT JOIN community_members cm ON c.id = cm.community_id 
         LEFT JOIN users u ON c.admin_id = u.id";

$where = [];
$params = [];

if (!empty($category)) {
    $where[] = "c.category = ?";
    $params[] = $category;
}

if (!empty($location)) {
    $where[] = "c.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($search)) {
    $where[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY c.id ORDER BY c.name ASC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $offset;

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params) - 2) . 'ii'; // string types + 2 integers for limit and offset
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $communities = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $communities = [];
}

// Count total communities for pagination
$countQuery = "SELECT COUNT(DISTINCT c.id) as total FROM communities c";
if (!empty($where)) {
    $countQuery .= " WHERE " . implode(" AND ", $where);
}

$countStmt = mysqli_prepare($conn, $countQuery);
if ($countStmt) {
    if (!empty($params) && count($params) > 2) { // Exclude LIMIT and OFFSET params
        $countTypes = str_repeat('s', count($params) - 2);
        $countParams = array_slice($params, 0, count($params) - 2);
        mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $countRow = mysqli_fetch_assoc($countResult);
    $totalCommunities = $countRow['total'];
} else {
    $totalCommunities = 0;
}

$totalPages = ceil($totalCommunities / $recordsPerPage);

// Get distinct categories for filter
$categoryQuery = "SELECT DISTINCT category FROM communities WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row['category'];
}

// Get distinct locations for filter
$locationQuery = "SELECT DISTINCT location FROM communities WHERE location IS NOT NULL AND location != '' ORDER BY location";
$locationResult = mysqli_query($conn, $locationQuery);
$locations = [];
while ($row = mysqli_fetch_assoc($locationResult)) {
    $locations[] = $row['location'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communities - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'Homepage.php'; ?>" class="hover:text-indigo-200">Home</a>
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

    <!-- Page Header -->
    <header class="bg-indigo-700 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold mb-4">Discover Communities</h1>
            <p class="text-xl max-w-3xl">Connect with diverse communities sharing resources, knowledge, and collaborating on meaningful projects.</p>
        </div>
    </header>

    <!-- Filter Section -->
    <section class="bg-white py-8 shadow-md">
        <div class="container mx-auto px-4">
            <form action="communities.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search communities" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="absolute right-3 top-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="category" class="block text-gray-700 mb-2">Category</label>
                    <select id="category" name="category" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $cat === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="location" class="block text-gray-700 mb-2">Location</label>
                    <select id="location" name="location" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $loc === $location ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="self-end">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 w-full">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Communities Grid -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <?php if (!empty($search) || !empty($category) || !empty($location)): ?>
                <div class="mb-8 flex justify-between items-center">
                    <h2 class="text-2xl font-bold">
                        Showing results
                        <?php if (!empty($search)): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                        <?php if (!empty($category)): ?>
                            in <?php echo htmlspecialchars($category); ?> category
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            from <?php echo htmlspecialchars($location); ?>
                        <?php endif; ?>
                    </h2>
                    <a href="communities.php" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-times mr-1"></i> Clear filters
                    </a>
                </div>
            <?php endif; ?>

            <?php if (empty($communities)): ?>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-5xl mb-4">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">No communities found</h3>
                    <p class="text-gray-600">Try adjusting your filters or search criteria</p>
                    <a href="communities.php" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">View all communities</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($communities as $community): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition duration-300">
                            <?php if (!empty($community['image'])): ?>
                                <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-users text-4xl text-indigo-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <?php if (!empty($community['category'])): ?>
                                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                            <?php echo htmlspecialchars($community['category']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>
                                    <span class="text-gray-500 text-sm">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo htmlspecialchars($community['location']); ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($community['name']); ?></h3>
                                <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($community['description']); ?></p>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-users text-gray-400 mr-2"></i>
                                        <span class="text-gray-600"><?php echo intval($community['member_count']) + 1; ?> members</span>
                                    </div>
                                    <a href="community_details.php?id=<?php echo $community['id']; ?>" class="text-indigo-700 hover:text-indigo-500">View Details</a>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                        <i class="fas fa-user text-indigo-600"></i>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        Admin: <?php echo htmlspecialchars($community['admin_name']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center mt-12">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($location) ? '&location=' . urlencode($location) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            
                            if ($endPage - $startPage < 4 && $startPage > 1) {
                                $startPage = max(1, $endPage - 4);
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($location) ? '&location=' . urlencode($location) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?php echo $i === $page ? 'bg-indigo-50 text-indigo-600 z-10' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> text-sm font-medium">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($location) ? '&location=' . urlencode($location) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Create Community CTA -->
    <section class="bg-indigo-50 py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-6">Can't find your community?</h2>
                <p class="text-xl text-gray-600 mb-8">Create your own community on EKYAM and connect with like-minded individuals, share resources, and collaborate on projects that matter.</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create_community.php" class="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 inline-block">
                        Create a Community
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=create_community" class="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 inline-block">
                        Create a Community
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">EKYAM</h3>
                    <p class="text-gray-400">Fostering unity and collaboration among diverse communities through shared resources and projects.</p>
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
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <div class="mt-4">
                        <form id="newsletterForm">
                            <div class="flex">
                                <input type="email" placeholder="Your email" class="px-4 py-2 w-full rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
                                <button type="submit" class="bg-indigo-600 px-4 py-2 rounded-r-lg hover:bg-indigo-500">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">© 2025 EKYAM. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a href="terms.php" class="text-gray-400 hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // Newsletter form submission
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real implementation, this would send the data to your PHP backend
            alert('Thanks for subscribing to our newsletter!');
            this.reset();
        });

        // Auto-submit form when select filters change
        document.getElementById('category').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('location').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html> 