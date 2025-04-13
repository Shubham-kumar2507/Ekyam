<?php
// Assuming config.php contains database connection details
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=community_chat.php?id=" . $_GET['id']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: communities.php");
    exit;
}

$community_id = intval($_GET['id']);

// Check if user is a member of this community
$memberCheckQuery = "SELECT cm.id, cm.role, c.name as community_name, c.image as community_image 
                    FROM community_members cm 
                    JOIN communities c ON cm.community_id = c.id
                    WHERE cm.community_id = ? AND cm.user_id = ?";
$memberCheckStmt = mysqli_prepare($conn, $memberCheckQuery);
mysqli_stmt_bind_param($memberCheckStmt, "ii", $community_id, $user_id);
mysqli_stmt_execute($memberCheckStmt);
$memberCheckResult = mysqli_stmt_get_result($memberCheckStmt);
$memberData = mysqli_fetch_assoc($memberCheckResult);

// Also check if user is admin of this community
$adminCheckQuery = "SELECT id, name, image FROM communities WHERE id = ? AND admin_id = ?";
$adminCheckStmt = mysqli_prepare($conn, $adminCheckQuery);
mysqli_stmt_bind_param($adminCheckStmt, "ii", $community_id, $user_id);
mysqli_stmt_execute($adminCheckStmt);
$adminCheckResult = mysqli_stmt_get_result($adminCheckStmt);
$adminData = mysqli_fetch_assoc($adminCheckResult);

// If user is neither member nor admin, redirect
if (!$memberData && !$adminData) {
    $_SESSION['error_message'] = "You need to be a member of this community to access the chat.";
    header("Location: community_details.php?id=$community_id");
    exit;
}

// Get community details
$community = $adminData ? $adminData : [
    'name' => $memberData['community_name'],
    'image' => $memberData['community_image']
];

// Create community_chat_messages table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS community_chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    community_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
mysqli_query($conn, $createTableQuery);

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    
    $insertQuery = "INSERT INTO community_chat_messages (community_id, user_id, message) VALUES (?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, "iis", $community_id, $user_id, $message);
    mysqli_stmt_execute($insertStmt);
    
    // Redirect to prevent form resubmission
    header("Location: community_chat.php?id=$community_id");
    exit;
}

// Fetch user details
$userQuery = "SELECT id, full_name, username, profile_image FROM users WHERE id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$user = mysqli_fetch_assoc($userResult);

// Fetch latest chat messages (last 50 messages, order by newest first)
$messagesQuery = "SELECT cm.id, cm.message, cm.created_at, u.id as user_id, u.full_name, u.username, u.profile_image 
                 FROM community_chat_messages cm 
                 JOIN users u ON cm.user_id = u.id 
                 WHERE cm.community_id = ? 
                 ORDER BY cm.created_at DESC 
                 LIMIT 50";
$messagesStmt = mysqli_prepare($conn, $messagesQuery);
mysqli_stmt_bind_param($messagesStmt, "i", $community_id);
mysqli_stmt_execute($messagesStmt);
$messagesResult = mysqli_stmt_get_result($messagesStmt);
$messages = mysqli_fetch_all($messagesResult, MYSQLI_ASSOC);
// Reverse the messages so newest are at the bottom
$messages = array_reverse($messages);

// Fetch online members (ideally, you would implement a proper online tracking system)
// For now, we'll just fetch recent members as a placeholder
$onlineMembersQuery = "SELECT u.id, u.full_name, u.username, u.profile_image 
                       FROM community_members cm 
                       JOIN users u ON cm.user_id = u.id 
                       WHERE cm.community_id = ? 
                       ORDER BY RAND() LIMIT 5";
$onlineMembersStmt = mysqli_prepare($conn, $onlineMembersQuery);
mysqli_stmt_bind_param($onlineMembersStmt, "i", $community_id);
mysqli_stmt_execute($onlineMembersStmt);
$onlineMembersResult = mysqli_stmt_get_result($onlineMembersStmt);
$onlineMembers = mysqli_fetch_all($onlineMembersResult, MYSQLI_ASSOC);

// Add admin to online members if not already included
if ($adminData) {
    $adminIncluded = false;
    $adminQuery = "SELECT id, full_name, username, profile_image FROM users WHERE id = (SELECT admin_id FROM communities WHERE id = ?)";
    $adminStmt = mysqli_prepare($conn, $adminQuery);
    mysqli_stmt_bind_param($adminStmt, "i", $community_id);
    mysqli_stmt_execute($adminStmt);
    $adminResult = mysqli_stmt_get_result($adminStmt);
    $adminUser = mysqli_fetch_assoc($adminResult);
    
    if ($adminUser) {
        foreach ($onlineMembers as $member) {
            if ($member['id'] == $adminUser['id']) {
                $adminIncluded = true;
                break;
            }
        }
        
        if (!$adminIncluded) {
            array_unshift($onlineMembers, $adminUser);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Chat - <?php echo htmlspecialchars($community['name']); ?> - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .chat-container {
            height: calc(100vh - 230px);
        }
        .messages-container {
            height: calc(100vh - 300px);
            overflow-y: auto;
        }
        @media (max-width: 768px) {
            .chat-container {
                height: calc(100vh - 180px);
            }
            .messages-container {
                height: calc(100vh - 250px);
            }
        }
    </style>
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
                <a href="profile.php" class="hover:text-indigo-200">My Profile</a>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
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

    <!-- Chat Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="community_details.php?id=<?php echo $community_id; ?>" class="text-indigo-600 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center">
                        <?php if (!empty($community['image'])): ?>
                            <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>" class="w-10 h-10 rounded-full object-cover mr-3">
                        <?php else: ?>
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-users text-indigo-400"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 class="text-xl font-bold"><?php echo htmlspecialchars($community['name']); ?> Chat</h1>
                            <p class="text-sm text-gray-500"><?php echo count($onlineMembers); ?> members online</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <button class="text-gray-600 hover:text-indigo-600 mr-4" id="toggleMembersBtn">
                        <i class="fas fa-users"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Chat Container -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex chat-container">
            <!-- Chat Window -->
            <div class="flex-grow bg-white rounded-lg shadow-md p-4 flex flex-col">
                <!-- Messages -->
                <div class="messages-container" id="messagesContainer">
                    <?php if (empty($messages)): ?>
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="text-gray-400 text-5xl mb-4">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3 class="text-xl font-semibold mb-2">No messages yet</h3>
                                <p class="text-gray-600">Be the first to start a conversation!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="mb-4 <?php echo ($message['user_id'] == $user_id) ? 'flex justify-end' : 'flex'; ?>">
                                <?php if ($message['user_id'] != $user_id): ?>
                                    <div class="flex-shrink-0 mr-3">
                                        <?php if (!empty($message['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($message['profile_image']); ?>" alt="<?php echo htmlspecialchars($message['full_name']); ?>" class="w-8 h-8 rounded-full">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="<?php echo ($message['user_id'] == $user_id) ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-800'; ?> rounded-lg px-4 py-2 max-w-md">
                                    <?php if ($message['user_id'] != $user_id): ?>
                                        <div class="font-medium text-sm mb-1">
                                            <?php echo htmlspecialchars($message['full_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    <div class="text-xs <?php echo ($message['user_id'] == $user_id) ? 'text-indigo-200' : 'text-gray-500'; ?> mt-1 text-right">
                                        <?php echo date('g:i A', strtotime($message['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($message['user_id'] == $user_id): ?>
                                    <div class="flex-shrink-0 ml-3">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-8 h-8 rounded-full">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-indigo-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-indigo-500"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Message Input -->
                <form method="post" action="" class="mt-4 border-t pt-4">
                    <div class="flex">
                        <input type="text" name="message" placeholder="Type your message..." required
                            class="flex-grow border rounded-l-lg p-3 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                            <i class="fas fa-paper-plane mr-1"></i> Send
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Online Members Sidebar -->
            <div class="w-64 bg-white rounded-lg shadow-md p-4 ml-4 hidden md:block" id="membersSidebar">
                <h2 class="text-lg font-semibold mb-4">Online Members</h2>
                
                <div class="space-y-3">
                    <?php foreach ($onlineMembers as $member): ?>
                        <div class="flex items-center">
                            <div class="relative mr-3">
                                <?php if (!empty($member['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="w-10 h-10 rounded-full">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
                            </div>
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($member['full_name']); ?></div>
                                <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($member['username']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 pt-4 border-t">
                    <a href="community_members.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                        <i class="fas fa-users mr-2"></i> View All Members
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Scroll to bottom of chat on page load
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });

        // Toggle mobile menu
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // Toggle members sidebar on mobile
        document.getElementById('toggleMembersBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('membersSidebar');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('md:block');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('right-4');
            sidebar.classList.toggle('top-24');
            sidebar.classList.toggle('z-10');
        });

        // Auto-refresh messages every 10 seconds
        setInterval(function() {
            // This would ideally be replaced with WebSockets or AJAX for better performance
            // For now, a simple page refresh will do
            location.reload();
        }, 30000); // Refresh every 30 seconds
    </script>
</body>
</html> 