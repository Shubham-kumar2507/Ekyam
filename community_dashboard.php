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

// Fetch community statistics
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
$memberResult = mysqli_stmt_get_result($memberStmt);
$stats['members'] = mysqli_fetch_assoc($memberResult)['count'] + 1; // +1 for admin

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

// Fetch recent activities
$activitiesQuery = "SELECT ca.*, u.full_name, u.username 
                   FROM community_activity ca 
                   JOIN users u ON ca.user_id = u.id 
                   WHERE ca.community_id = ? 
                   ORDER BY ca.activity_date DESC 
                   LIMIT 10";
$activitiesStmt = mysqli_prepare($conn, $activitiesQuery);
mysqli_stmt_bind_param($activitiesStmt, "i", $community_id);
mysqli_stmt_execute($activitiesStmt);
$activities = mysqli_fetch_all(mysqli_stmt_get_result($activitiesStmt), MYSQLI_ASSOC);

// Fetch events for the current month
$current_month = date('Y-m');
$eventsQuery = "SELECT * FROM community_events 
                WHERE community_id = ? 
                AND DATE(start_date) >= ? 
                AND DATE(start_date) < DATE_ADD(?, INTERVAL 1 MONTH)
                ORDER BY start_date";
$eventsStmt = mysqli_prepare($conn, $eventsQuery);
mysqli_stmt_bind_param($eventsStmt, "iss", $community_id, $current_month, $current_month);
mysqli_stmt_execute($eventsStmt);
$events = mysqli_fetch_all(mysqli_stmt_get_result($eventsStmt), MYSQLI_ASSOC);

// Create a map of days with events
$daysWithEvents = [];
foreach ($events as $event) {
    $day = date('j', strtotime($event['start_date']));
    $daysWithEvents[$day] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($community['name']); ?> - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Libre Baskerville', serif;
            background-color: #f8f5f1;
            color: #2c3e50;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: #2c3e50;
        }
        .classic-border {
            border: 1px solid #d4c9b0;
            border-radius: 4px;
        }
        .classic-card {
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            border: 1px solid #d4c9b0;
            transition: all 0.3s ease;
        }
        .classic-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .classic-header {
            background: linear-gradient(135deg, #3c6e71, #284b63);
            color: white;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .classic-btn {
            background-color: #3c6e71;
            color: white;
            transition: all 0.3s ease;
            border-radius: 4px;
            font-weight: 500;
        }
        .classic-btn:hover {
            background-color: #284b63;
            transform: translateY(-1px);
        }
        .sidebar-item {
            transition: all 0.3s ease;
            border-radius: 4px;
            padding: 0.75rem;
        }
        .sidebar-item:hover {
            background-color: #f0e6d2;
            transform: translateX(5px);
        }
        .stats-counter {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3c6e71;
            font-family: 'Playfair Display', serif;
        }
        .ornament {
            height: 2px;
            background: linear-gradient(to right, transparent, #d4c9b0, transparent);
            margin: 1rem 0;
        }
        .timeline-dot {
            width: 10px;
            height: 10px;
            background-color: #3c6e71;
            border-radius: 50%;
            margin-top: 4px;
            border: 2px solid #f8f5f1;
        }
        .timeline-line {
            position: absolute;
            left: 5px;
            top: 0;
            bottom: 0;
            width: 1px;
            background-color: #d4c9b0;
            transform: translateX(-50%);
        }
        .calendar-day {
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .calendar-day:hover {
            background-color: #f0e6d2;
        }
        .calendar-day.today {
            background-color: #3c6e71;
            color: white;
        }
        .calendar-day.has-event {
            position: relative;
        }
        .calendar-day.has-event::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background-color: #3c6e71;
            border-radius: 50%;
        }
        .resource-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .activity-item {
            transition: all 0.3s ease;
        }
        .activity-item:hover {
            transform: translateX(5px);
        }
        .project-card {
            transition: all 0.3s ease;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
    </style>
    <script>
        // Function to update statistics
        function updateStats() {
            const communityId = <?php echo $community_id; ?>;
            
            fetch(`get_community_stats.php?community_id=${communityId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching stats:', data.error);
                        return;
                    }
                    
                    // Update the stats counters with animation
                    const stats = {
                        'members': data.members,
                        'projects': data.projects,
                        'resources': data.resources,
                        'active_members': data.active_members
                    };
                    
                    Object.keys(stats).forEach(key => {
                        const element = document.querySelector(`.stats-counter[data-stat="${key}"]`);
                        if (element) {
                            const currentValue = parseInt(element.textContent);
                            const newValue = stats[key];
                            
                            if (currentValue !== newValue) {
                                // Add animation class
                                element.classList.add('animate-pulse');
                                
                                // Update the value
                                element.textContent = newValue;
                                
                                // Remove animation class after 1 second
                                setTimeout(() => {
                                    element.classList.remove('animate-pulse');
                                }, 1000);
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                });
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);
        
        // Initial update
        document.addEventListener('DOMContentLoaded', updateStats);
    </script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->


    <div class="container mx-auto px-4 py-8">
        <!-- Header with community info -->
        <div class="classic-card p-8 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="flex items-center mb-6 md:mb-0">
                    <div class="w-20 h-20 rounded-full overflow-hidden mr-6 classic-border">
                        <?php if (!empty($community['image'])): ?>
                            <img src="<?php echo htmlspecialchars($community['image']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-800">
                                <i class="fas fa-users text-3xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($community['name']); ?></h1>
                        <p class="text-gray-600 italic"><?php echo htmlspecialchars($community['location']); ?> • Founded <?php echo date('F Y', strtotime($community['created_at'])); ?></p>
                    </div>
                </div>
                <div>
                    <a href="community_details.php?id=<?php echo $community_id; ?>" class="classic-btn px-6 py-3">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Community
                    </a>
                </div>
            </div>
            <div class="ornament mt-6"></div>
            <p class="text-gray-700 italic mt-6"><?php echo htmlspecialchars($community['description']); ?></p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar with quick actions -->
            <div class="w-full lg:w-1/4">
                <div class="classic-card mb-8">
                    <div class="classic-header p-6">
                        <h2 class="text-2xl font-bold">Admin Controls</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="edit_community.php?id=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-edit text-gray-700 mr-4 w-6"></i>
                                <span>Edit Community Details</span>
                            </a>
                            <a href="create_project.php?community=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-plus-circle text-gray-700 mr-4 w-6"></i>
                                <span>Create New Project</span>
                            </a>
                            <a href="create-resource.php?community=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-upload text-gray-700 mr-4 w-6"></i>
                                <span>Upload Resource</span>
                            </a>
                            <a href="community_members.php?id=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-users-cog text-gray-700 mr-4 w-6"></i>
                                <span>Manage Members</span>
                            </a>
                            <div class="ornament"></div>
                            <a href="community_notifications.php?id=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-bell text-gray-700 mr-4 w-6"></i>
                                <span>Send Notification</span>
                            </a>
                            <a href="export_community_data.php?id=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-file-export text-gray-700 mr-4 w-6"></i>
                                <span>Export Community Data</span>
                            </a>
                            <a href="community_settings.php?id=<?php echo $community_id; ?>" class="sidebar-item flex items-center">
                                <i class="fas fa-cog text-gray-700 mr-4 w-6"></i>
                                <span>Community Settings</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Community member insights -->
                <div class="classic-card">
                    <div class="classic-header p-6">
                        <h2 class="text-2xl font-bold">Member Insights</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <canvas id="memberGrowthChart" width="400" height="300"></canvas>
                        </div>
                        <div class="ornament"></div>
                        <div class="text-center p-4">
                            <p class="text-sm text-gray-600 mb-2">Active participation rate:</p>
                            <div class="flex items-center justify-center">
                                <?php 
                                $participation_rate = $stats['members'] > 0 ? ($stats['active_members'] / $stats['members']) * 100 : 0;
                                $participation_color = $participation_rate >= 70 ? 'bg-green-500' : ($participation_rate >= 40 ? 'bg-yellow-500' : 'bg-red-500');
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="<?php echo $participation_color; ?> h-3 rounded-full" style="width: <?php echo round($participation_rate); ?>%"></div>
                                </div>
                                <span class="ml-4 font-bold text-lg"><?php echo round($participation_rate); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="w-full lg:w-3/4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="classic-card p-8 text-center project-card">
                        <div class="p-4 rounded-full bg-blue-100 text-blue-600 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                            <i class="fas fa-users text-3xl"></i>
                        </div>
                        <p class="text-xl font-bold text-gray-700 mb-2">Members</p>
                        <div class="stats-counter" data-stat="members"><?php echo $stats['members']; ?></div>
                    </div>
                    <div class="classic-card p-8 text-center project-card">
                        <div class="p-4 rounded-full bg-green-100 text-green-600 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                            <i class="fas fa-project-diagram text-3xl"></i>
                        </div>
                        <p class="text-xl font-bold text-gray-700 mb-2">Projects</p>
                        <div class="stats-counter" data-stat="projects"><?php echo $stats['projects']; ?></div>
                    </div>
                    <div class="classic-card p-8 text-center project-card">
                        <div class="p-4 rounded-full bg-amber-100 text-amber-600 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                            <i class="fas fa-file-alt text-3xl"></i>
                        </div>
                        <p class="text-xl font-bold text-gray-700 mb-2">Resources</p>
                        <div class="stats-counter" data-stat="resources"><?php echo $stats['resources']; ?></div>
                    </div>
                    <div class="classic-card p-8 text-center project-card">
                        <div class="p-4 rounded-full bg-purple-100 text-purple-600 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                            <i class="fas fa-user-check text-3xl"></i>
                        </div>
                        <p class="text-xl font-bold text-gray-700 mb-2">Active Users</p>
                        <div class="stats-counter" data-stat="active_members"><?php echo $stats['active_members']; ?></div>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="classic-card mb-8">
                    <div class="classic-header p-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Active Projects</h2>
                        <a href="projects.php?community=<?php echo $community_id; ?>" class="text-white hover:underline">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="p-6">
                        <?php if (empty($activeProjects)): ?>
                            <p class="text-gray-500 text-center py-8">No active projects at the moment</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php foreach ($activeProjects as $project): ?>
                                    <div class="classic-border rounded-lg overflow-hidden project-card">
                                        <div class="h-40 bg-gray-100 relative">
                                            <?php if (!empty($project['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                    <i class="fas fa-project-diagram text-5xl text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="absolute top-3 right-3 bg-green-500 text-white text-xs px-3 py-1 rounded-full">In Progress</div>
                                        </div>
                                        <div class="p-6">
                                            <h3 class="font-bold text-xl mb-3"><?php echo htmlspecialchars($project['name']); ?></h3>
                                            <p class="text-gray-600 text-sm mb-4">
                                                <?php echo substr(htmlspecialchars($project['description']), 0, 100); ?>...
                                            </p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-500">
                                                    <i class="fas fa-users mr-1"></i> <?php echo $project['member_count']; ?> members
                                                </span>
                                                <a href="project_details.php?id=<?php echo $project['id']; ?>" class="classic-btn px-4 py-2 text-sm">
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Trending Resources & Recent Activity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Trending Resources -->
                    <div class="classic-card">
                        <div class="classic-header p-6">
                            <h2 class="text-2xl font-bold">Trending Resources</h2>
                        </div>
                        <div class="p-6">
                            <?php if (empty($trendingResources)): ?>
                                <p class="text-gray-500 text-center py-8">No resources available</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($trendingResources as $resource): ?>
                                        <div class="flex items-start p-4 classic-border rounded-lg">
                                            <?php 
                                            $icon = 'fa-file-alt';
                                            $bgColor = 'bg-blue-100';
                                            $textColor = 'text-blue-600';
                                            
                                            switch($resource['type']) {
                                                case 'document':
                                                    $icon = 'fa-file-alt';
                                                    $bgColor = 'bg-blue-100';
                                                    $textColor = 'text-blue-600';
                                                    break;
                                                case 'link':
                                                    $icon = 'fa-link';
                                                    $bgColor = 'bg-purple-100';
                                                    $textColor = 'text-purple-600';
                                                    break;
                                                case 'video':
                                                    $icon = 'fa-video';
                                                    $bgColor = 'bg-red-100';
                                                    $textColor = 'text-red-600';
                                                    break;
                                                case 'image':
                                                    $icon = 'fa-image';
                                                    $bgColor = 'bg-green-100';
                                                    $textColor = 'text-green-600';
                                                    break;
                                            }
                                            ?>
                                            <div class="flex-shrink-0 mr-4">
                                                <div class="resource-icon <?php echo $bgColor; ?> <?php echo $textColor; ?>">
                                                    <i class="fas <?php echo $icon; ?>"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow">
                                                <h3 class="font-bold text-lg mb-1"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                                <p class="text-sm text-gray-600 line-clamp-2 mb-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <span class="mr-4"><i class="fas fa-download mr-1"></i> <?php echo $resource['download_count']; ?> downloads</span>
                                                    <span><i class="fas fa-eye mr-1"></i> <?php echo $resource['access_count']; ?> views</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-6 text-center">
                                    <a href="resources.php?community=<?php echo $community_id; ?>" class="classic-btn px-6 py-3">
                                        View All Resources
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="classic-card">
                        <div class="classic-header p-6">
                            <h2 class="text-2xl font-bold">Recent Activity</h2>
                        </div>
                        <div class="p-6">
                            <?php if (empty($activities)): ?>
                                <p class="text-gray-500 text-center py-8">No recent activity</p>
                            <?php else: ?>
                                <div class="relative pl-8">
                                    <div class="timeline-line"></div>
                                    <div class="space-y-6">
                                        <?php foreach ($activities as $activity): ?>
                                            <div class="relative activity-item">
                                                <div class="timeline-dot absolute -left-6"></div>
                                                <div class="classic-border rounded-lg p-4">
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0 mr-4">
                                                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                                <i class="fas fa-user text-indigo-600"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm mb-1">
                                                                <span class="font-medium"><?php echo htmlspecialchars($activity['full_name']); ?></span>
                                                                <?php echo htmlspecialchars($activity['activity_type']); ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                <?php echo date('M j, Y g:i A', strtotime($activity['activity_date'])); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="mt-6 text-center">
                                    <a href="activity_log.php?community=<?php echo $community_id; ?>" class="classic-btn px-6 py-3">
                                        View Full Activity Log
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Community Calendar -->
                <div class="classic-card">
                    <div class="classic-header p-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Upcoming Events</h2>
                        <a href="community_calendar.php?id=<?php echo $community_id; ?>" class="text-white hover:underline">
                            Manage Calendar <i class="fas fa-calendar-alt ml-1"></i>
                        </a>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <a href="community_calendar.php?id=<?php echo $community_id; ?>" class="classic-btn px-6 py-3">
                                <i class="fas fa-plus mr-2"></i> Add Event
                            </a>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded classic-btn">Today</button>
                                <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded classic-btn"><i class="fas fa-chevron-left"></i></button>
                                <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded classic-btn"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold"><?php echo date('F Y'); ?></h3>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-7 text-center mb-4">
                            <div class="font-bold p-2">Su</div>
                            <div class="font-bold p-2">Mo</div>
                            <div class="font-bold p-2">Tu</div>
                            <div class="font-bold p-2">We</div>
                            <div class="font-bold p-2">Th</div>
                            <div class="font-bold p-2">Fr</div>
                            <div class="font-bold p-2">Sa</div>
                        </div>
                        
                        <div class="grid grid-cols-7 gap-1 text-center">
                            <?php
                            $first_day = date('w', strtotime(date('Y-m-01')));
                            $days_in_month = date('t');
                            $current_day = date('j');
                            
                            // Previous month days
                            for ($i = 0; $i < $first_day; $i++) {
                                echo '<div class="calendar-day text-gray-400">' . (date('t', strtotime('-1 month')) - $first_day + $i + 1) . '</div>';
                            }
                            
                            // Current month days
                            for ($i = 1; $i <= $days_in_month; $i++) {
                                $classes = 'calendar-day';
                                if ($i == $current_day) {
                                    $classes .= ' today';
                                }
                                if (isset($daysWithEvents[$i])) {
                                    $classes .= ' has-event';
                                }
                                echo '<div class="' . $classes . '">' . $i . '</div>';
                            }
                            
                            // Next month days
                            $remaining_days = 42 - ($first_day + $days_in_month); // 6 rows * 7 days = 42
                            for ($i = 1; $i <= $remaining_days; $i++) {
                                echo '<div class="calendar-day text-gray-400">' . $i . '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Upcoming Events List -->
                        <?php if (!empty($events)): ?>
                            <div class="mt-6">
                                <h3 class="text-lg font-semibold mb-4">Upcoming Events</h3>
                                <div class="space-y-3">
                                    <?php foreach ($events as $event): ?>
                                        <div class="border rounded-lg p-3">
                                            <h4 class="font-medium"><?php echo htmlspecialchars($event['title']); ?></h4>
                                            <p class="text-sm text-gray-600">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                <?php echo date('F j, Y g:i A', strtotime($event['start_date'])); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 