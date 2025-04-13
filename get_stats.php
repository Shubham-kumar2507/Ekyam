<?php
require_once 'config.php';

// Fetch statistics
$stats = array();

// Get communities count
$query = "SELECT COUNT(*) as count FROM communities";
$result = mysqli_query($conn, $query);
$stats['communities'] = mysqli_fetch_assoc($result)['count'];

// Get active projects count
$query = "SELECT COUNT(*) as count FROM projects WHERE status != 'completed'";
$result = mysqli_query($conn, $query);
$stats['projects'] = mysqli_fetch_assoc($result)['count'];

// Get resources count
$query = "SELECT COUNT(*) as count FROM resources";
$result = mysqli_query($conn, $query);
$stats['resources'] = mysqli_fetch_assoc($result)['count'];

// Get members count
$query = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $query);
$stats['members'] = mysqli_fetch_assoc($result)['count'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($stats);
?> 