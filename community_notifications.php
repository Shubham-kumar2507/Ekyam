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
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $notification_type = $_POST['notification_type'];
    
    if (!empty($subject) && !empty($message)) {
        // Get all community members
        $membersQuery = "SELECT u.id, u.email, u.full_name 
                        FROM community_members cm 
                        JOIN users u ON cm.user_id = u.id 
                        WHERE cm.community_id = ?";
        $membersStmt = mysqli_prepare($conn, $membersQuery);
        mysqli_stmt_bind_param($membersStmt, "i", $community_id);
        mysqli_stmt_execute($membersStmt);
        $members = mysqli_fetch_all(mysqli_stmt_get_result($membersStmt), MYSQLI_ASSOC);
        
        // Send notifications to each member
        foreach ($members as $member) {
            // Store notification in database
            $notificationQuery = "INSERT INTO messages (sender_id, receiver_id, community_id, subject, content, sent_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())";
            $notificationStmt = mysqli_prepare($conn, $notificationQuery);
            mysqli_stmt_bind_param($notificationStmt, "iiiss", $_SESSION['user_id'], $member['id'], $community_id, $subject, $message);
            mysqli_stmt_execute($notificationStmt);
            
            // Send email notification
            $to = $member['email'];
            $headers = "From: EKYAM <noreply@ekyam.com>\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $email_message = "
                <html>
                <body>
                    <h2>$subject</h2>
                    <p>Hello {$member['full_name']},</p>
                    <p>$message</p>
                    <p>This is a notification from the {$community['name']} community.</p>
                    <p>You can view this message in your EKYAM inbox.</p>
                    <p>Best regards,<br>The EKYAM Team</p>
                </body>
                </html>
            ";
            
            mail($to, $subject, $email_message, $headers);
        }
        
        $_SESSION['success_message'] = "Notification sent successfully to all community members!";
        header("Location: community_notifications.php?id=$community_id");
        exit;
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
    <title>Send Community Notification - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Send Community Notification</h1>
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
                
                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" id="subject" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="notification_type" class="block text-sm font-medium text-gray-700">Notification Type</label>
                        <select name="notification_type" id="notification_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="general">General Announcement</option>
                            <option value="project">Project Update</option>
                            <option value="event">Event Reminder</option>
                            <option value="urgent">Urgent Message</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" id="message" rows="6" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 