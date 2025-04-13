<?php
require_once 'config.php';
session_start();

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || !isset($_GET['community_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$community_id = intval($_GET['community_id']);

// Fetch updated statistics
$stats = [
    'members' => 0,
    'projects' => 0,
    'resources' => 0,
    'active_members' => 0
];

// Get member count
$memberQuery = "SELECT COUNT(*) as count FROM community_members WHERE community_id = ?";
$memberStmt = mysqli_prepare($conn, $memberQuery);
mysqli_stmt_bind_param($memberStmt, "i", $community_id);
mysqli_stmt_execute($memberStmt);
$stats['members'] = mysqli_fetch_assoc(mysqli_stmt_get_result($memberStmt))['count'] + 1; // +1 for admin

// Get project count
$projectQuery = "SELECT COUNT(*) as count FROM projects WHERE community_id = ?";
$projectStmt = mysqli_prepare($conn, $projectQuery);
mysqli_stmt_bind_param($projectStmt, "i", $community_id);
mysqli_stmt_execute($projectStmt);
$stats['projects'] = mysqli_fetch_assoc(mysqli_stmt_get_result($projectStmt))['count'];

// Get resource count
$resourceQuery = "SELECT COUNT(*) as count FROM resources WHERE community_id = ?";
$resourceStmt = mysqli_prepare($conn, $resourceQuery);
mysqli_stmt_bind_param($resourceStmt, "i", $community_id);
mysqli_stmt_execute($resourceStmt);
$stats['resources'] = mysqli_fetch_assoc(mysqli_stmt_get_result($resourceStmt))['count'];

// Get active members (members who have participated in the last 30 days)
$activeQuery = "SELECT COUNT(DISTINCT user_id) as count 
                FROM community_activity 
                WHERE community_id = ? AND activity_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$activeStmt = mysqli_prepare($conn, $activeQuery);
mysqli_stmt_bind_param($activeStmt, "i", $community_id);
mysqli_stmt_execute($activeStmt);
$stats['active_members'] = mysqli_fetch_assoc(mysqli_stmt_get_result($activeStmt))['count'];

header('Content-Type: application/json');
echo json_encode($stats);
?> 