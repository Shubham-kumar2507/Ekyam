<?php
require_once 'config.php';
session_start();

// Check if resource ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: resources.php');
    exit;
}

$resource_id = intval($_GET['id']);
$user_logged_in = isset($_SESSION['user_id']);
$user_id = $user_logged_in ? $_SESSION['user_id'] : null;

// Fetch resource information using prepared statement
$query = "SELECT r.* FROM resources r WHERE r.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Resource not found
    header('Location: resources.php');
    exit;
}

$resource = $result->fetch_assoc();

// Check if user has access to this resource
$has_access = false;

if ($resource['is_public']) {
    $has_access = true;
} elseif ($user_logged_in) {
    // Check if user is the uploader
    if ($resource['uploaded_by'] == $user_id) {
        $has_access = true;
    } else {
        // Check if user has explicit access
        $access_query = "SELECT 1 FROM resource_access WHERE resource_id = ? AND user_id = ? LIMIT 1";
        $access_stmt = $conn->prepare($access_query);
        $access_stmt->bind_param('ii', $resource_id, $user_id);
        $access_stmt->execute();
        
        if ($access_stmt->get_result()->num_rows > 0) {
            $has_access = true;
        } else {
            // Check if user is part of the community that owns the resource
            if ($resource['community_id']) {
                $community_query = "SELECT 1 FROM community_members 
                                   WHERE community_id = ? AND user_id = ? LIMIT 1";
                $community_stmt = $conn->prepare($community_query);
                $community_stmt->bind_param('ii', $resource['community_id'], $user_id);
                $community_stmt->execute();
                
                if ($community_stmt->get_result()->num_rows > 0) {
                    $has_access = true;
                }
            }
            
            // Check if user is part of a project that uses this resource
            if (!$has_access) {
                $project_query = "SELECT 1 FROM project_members pm
                                 JOIN project_resources pr ON pm.project_id = pr.project_id
                                 WHERE pr.resource_id = ? AND pm.user_id = ? LIMIT 1";
                $project_stmt = $conn->prepare($project_query);
                $project_stmt->bind_param('ii', $resource_id, $user_id);
                $project_stmt->execute();
                
                if ($project_stmt->get_result()->num_rows > 0) {
                    $has_access = true;
                }
            }
        }
    }
}

if (!$has_access) {
    // Redirect to resources page if no access
    header('Location: resources.php?error=access_denied');
    exit;
}

// Check if the file exists
$file_path = $resource['file_path'];
if (empty($file_path) || !file_exists($file_path)) {
    header('Location: resources.php?error=file_not_found');
    exit;
}

// Update download count using prepared statement
$update_query = "UPDATE resources SET download_count = download_count + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param('i', $resource_id);
$update_stmt->execute();

// Get file info
$file_name = basename($file_path);
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Set appropriate headers
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'zip' => 'application/zip',
    'mp4' => 'video/mp4',
    'mp3' => 'audio/mpeg',
    'txt' => 'text/plain',
    'csv' => 'text/csv'
];

$content_type = $content_types[$file_extension] ?? 'application/octet-stream';

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Output file
readfile($file_path);
exit;
?> 