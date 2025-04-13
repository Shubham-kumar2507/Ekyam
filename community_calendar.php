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

// Fetch community details and verify admin status
$query = "SELECT c.*, u.full_name as admin_name 
          FROM communities c 
          JOIN users u ON c.admin_id = u.id 
          WHERE c.id = ? AND c.admin_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $community_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: communities.php");
    exit;
}

$community = mysqli_fetch_assoc($result);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_event':
                $title = mysqli_real_escape_string($conn, $_POST['title']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
                $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
                $location = mysqli_real_escape_string($conn, $_POST['location']);
                $event_type = mysqli_real_escape_string($conn, $_POST['event_type']);
                
                $query = "INSERT INTO community_events (community_id, title, description, start_date, end_date, location, event_type, created_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "issssssi", $community_id, $title, $description, $start_date, $end_date, $location, $event_type, $_SESSION['user_id']);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Event added successfully!";
                } else {
                    $error_message = "Error adding event: " . mysqli_error($conn);
                }
                break;
                
            case 'delete_event':
                $event_id = intval($_POST['event_id']);
                $query = "DELETE FROM community_events WHERE id = ? AND community_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $event_id, $community_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Event deleted successfully!";
                } else {
                    $error_message = "Error deleting event: " . mysqli_error($conn);
                }
                break;
        }
    }
}

// Fetch events for the current month
$current_month = date('Y-m');
$query = "SELECT * FROM community_events 
          WHERE community_id = ? 
          AND DATE(start_date) >= ? 
          AND DATE(start_date) < DATE_ADD(?, INTERVAL 1 MONTH)
          ORDER BY start_date";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $community_id, $current_month, $current_month);
mysqli_stmt_execute($stmt);
$events = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($community['name']); ?> - Calendar Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($community['name']); ?> - Calendar Management</h1>
                <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Event Form -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Add New Event</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_event">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                            <input type="text" name="title" id="title" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                            <select name="event_type" id="event_type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="meeting">Meeting</option>
                                <option value="workshop">Workshop</option>
                                <option value="social">Social</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" id="start_date" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                            <input type="datetime-local" name="end_date" id="end_date"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="location"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Add Event
                        </button>
                    </div>
                </form>
            </div>

            <!-- Events List -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Upcoming Events</h2>
                <?php if (empty($events)): ?>
                    <p class="text-gray-500">No events scheduled for this month.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($events as $event): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($event['description']); ?></p>
                                        <div class="mt-2 text-sm text-gray-500">
                                            <p><i class="fas fa-calendar-alt mr-2"></i> 
                                                <?php echo date('F j, Y g:i A', strtotime($event['start_date'])); ?>
                                                <?php if ($event['end_date']): ?>
                                                    - <?php echo date('g:i A', strtotime($event['end_date'])); ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php if ($event['location']): ?>
                                                <p><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form method="POST" class="flex items-center">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 