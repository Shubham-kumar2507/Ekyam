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

// Verify admin status
$adminQuery = "SELECT id FROM communities WHERE id = ? AND admin_id = ?";
$adminStmt = mysqli_prepare($conn, $adminQuery);
mysqli_stmt_bind_param($adminStmt, "ii", $community_id, $_SESSION['user_id']);
mysqli_stmt_execute($adminStmt);
$adminResult = mysqli_stmt_get_result($adminStmt);

if (mysqli_num_rows($adminResult) == 0) {
    header("Location: communities.php");
    exit;
}

// Handle member role update
if (isset($_POST['update_role'])) {
    $member_id = intval($_POST['member_id']);
    $new_role = $_POST['new_role'];
    
    $updateQuery = "UPDATE community_members SET role = ? WHERE community_id = ? AND user_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "sii", $new_role, $community_id, $member_id);
    mysqli_stmt_execute($updateStmt);
    
    $_SESSION['success_message'] = "Member role updated successfully.";
    header("Location: community_members.php?id=" . $community_id);
    exit;
}

// Handle member removal
if (isset($_POST['remove_member'])) {
    $member_id = intval($_POST['member_id']);
    
    $removeQuery = "DELETE FROM community_members WHERE community_id = ? AND user_id = ?";
    $removeStmt = mysqli_prepare($conn, $removeQuery);
    mysqli_stmt_bind_param($removeStmt, "ii", $community_id, $member_id);
    mysqli_stmt_execute($removeStmt);
    
    $_SESSION['success_message'] = "Member removed successfully.";
    header("Location: community_members.php?id=" . $community_id);
    exit;
}

// Fetch community members
$membersQuery = "SELECT u.id, u.full_name, u.username, u.profile_image, cm.role, cm.joined_at 
                FROM community_members cm 
                JOIN users u ON cm.user_id = u.id 
                WHERE cm.community_id = ? 
                ORDER BY cm.role, cm.joined_at DESC";
$membersStmt = mysqli_prepare($conn, $membersQuery);
mysqli_stmt_bind_param($membersStmt, "i", $community_id);
mysqli_stmt_execute($membersStmt);
$members = mysqli_fetch_all(mysqli_stmt_get_result($membersStmt), MYSQLI_ASSOC);

// Fetch community admin
$adminQuery = "SELECT u.id, u.full_name, u.username, u.profile_image 
              FROM communities c 
              JOIN users u ON c.admin_id = u.id 
              WHERE c.id = ?";
$adminStmt = mysqli_prepare($conn, $adminQuery);
mysqli_stmt_bind_param($adminStmt, "i", $community_id);
mysqli_stmt_execute($adminStmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($adminStmt));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - <?php echo htmlspecialchars($admin['full_name']); ?>'s Community</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
 

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Members</h1>
            <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove();">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Admin Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Community Admin</h2>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if (!empty($admin['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Admin" class="w-12 h-12 rounded-full">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium"><?php echo htmlspecialchars($admin['full_name']); ?></h3>
                    <p class="text-gray-500">@<?php echo htmlspecialchars($admin['username']); ?></p>
                </div>
                <div class="ml-auto">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">
                        Administrator
                    </span>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Community Members</h2>
            <?php if (empty($members)): ?>
                <p class="text-gray-500">No members yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($members as $member): ?>
                        <div class="flex items-center p-4 border rounded-lg">
                            <div class="flex-shrink-0">
                                <?php if (!empty($member['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="Member" class="w-10 h-10 rounded-full">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-grow">
                                <h3 class="text-lg font-medium"><?php echo htmlspecialchars($member['full_name']); ?></h3>
                                <p class="text-gray-500">@<?php echo htmlspecialchars($member['username']); ?></p>
                                <p class="text-sm text-gray-500">
                                    Joined <?php echo date('M j, Y', strtotime($member['joined_at'])); ?>
                                </p>
                            </div>
                            <div class="ml-4">
                                <form method="post" class="flex items-center space-x-2">
                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                    <select name="new_role" class="border rounded px-2 py-1 text-sm">
                                        <option value="member" <?php echo $member['role'] === 'member' ? 'selected' : ''; ?>>Member</option>
                                        <option value="moderator" <?php echo $member['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                    </select>
                                    <button type="submit" name="update_role" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                        Update
                                    </button>
                                    <button type="submit" name="remove_member" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700" onclick="return confirm('Are you sure you want to remove this member?');">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 