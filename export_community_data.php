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

// Handle export request
if (isset($_GET['export'])) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="community_data_' . $community['name'] . '_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Export community details
    fputcsv($output, ['Community Details']);
    fputcsv($output, ['Name', 'Description', 'Location', 'Created At']);
    fputcsv($output, [
        $community['name'],
        $community['description'],
        $community['location'],
        $community['created_at']
    ]);
    
    // Add empty line
    fputcsv($output, []);
    
    // Export members
    fputcsv($output, ['Community Members']);
    fputcsv($output, ['Name', 'Email', 'Role', 'Joined At']);
    
    $membersQuery = "SELECT u.full_name, u.email, cm.role, cm.joined_at 
                    FROM community_members cm 
                    JOIN users u ON cm.user_id = u.id 
                    WHERE cm.community_id = ?";
    $membersStmt = mysqli_prepare($conn, $membersQuery);
    mysqli_stmt_bind_param($membersStmt, "i", $community_id);
    mysqli_stmt_execute($membersStmt);
    $members = mysqli_fetch_all(mysqli_stmt_get_result($membersStmt), MYSQLI_ASSOC);
    
    foreach ($members as $member) {
        fputcsv($output, [
            $member['full_name'],
            $member['email'],
            $member['role'],
            $member['joined_at']
        ]);
    }
    
    // Add empty line
    fputcsv($output, []);
    
    // Export projects
    fputcsv($output, ['Community Projects']);
    fputcsv($output, ['Name', 'Description', 'Status', 'Start Date', 'Member Count']);
    
    $projectsQuery = "SELECT name, description, status, start_date, member_count 
                     FROM projects 
                     WHERE community_id = ?";
    $projectsStmt = mysqli_prepare($conn, $projectsQuery);
    mysqli_stmt_bind_param($projectsStmt, "i", $community_id);
    mysqli_stmt_execute($projectsStmt);
    $projects = mysqli_fetch_all(mysqli_stmt_get_result($projectsStmt), MYSQLI_ASSOC);
    
    foreach ($projects as $project) {
        fputcsv($output, [
            $project['name'],
            $project['description'],
            $project['status'],
            $project['start_date'],
            $project['member_count']
        ]);
    }
    
    // Add empty line
    fputcsv($output, []);
    
    // Export resources
    fputcsv($output, ['Community Resources']);
    fputcsv($output, ['Title', 'Description', 'Type', 'Download Count', 'Created At']);
    
    $resourcesQuery = "SELECT title, description, type, download_count, created_at 
                      FROM resources 
                      WHERE community_id = ?";
    $resourcesStmt = mysqli_prepare($conn, $resourcesQuery);
    mysqli_stmt_bind_param($resourcesStmt, "i", $community_id);
    mysqli_stmt_execute($resourcesStmt);
    $resources = mysqli_fetch_all(mysqli_stmt_get_result($resourcesStmt), MYSQLI_ASSOC);
    
    foreach ($resources as $resource) {
        fputcsv($output, [
            $resource['title'],
            $resource['description'],
            $resource['type'],
            $resource['download_count'],
            $resource['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Community Data - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Export Community Data</h1>
                    <a href="community_dashboard.php?id=<?php echo $community_id; ?>" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
                
                <div class="space-y-4">
                    <p class="text-gray-600">
                        Export all community data including members, projects, and resources in CSV format.
                    </p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    The exported CSV file will contain:
                                </p>
                                <ul class="mt-2 text-sm text-blue-700 list-disc list-inside">
                                    <li>Community details</li>
                                    <li>Member list with roles and join dates</li>
                                    <li>Project information</li>
                                    <li>Resource details</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <a href="?id=<?php echo $community_id; ?>&export=1" 
                           class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-download mr-2"></i> Export Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 