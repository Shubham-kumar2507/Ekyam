<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EKYAM - Uniting Communities</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
                <a href="newsletter.php" class="hover:text-indigo-200">Newsletter</a>
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
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
                <a href="newsletter.php" class="hover:text-indigo-200">Newsletter</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-4 py-20 md:py-32 flex flex-col md:flex-row items-center relative z-10">
            <div class="md:w-1/2 md:pr-10">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">Uniting Communities Through Meaningful Collaboration</h1>
                <p class="text-xl mb-8 text-indigo-100">EKYAM brings diverse communities together through resource sharing, collaborative projects, and meaningful connections that create lasting impact.</p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="register.php" class="bg-white text-indigo-700 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-100 text-center transform hover:scale-105 transition duration-300">Join EKYAM</a>
                    <a href="#how-it-works" class="border-2 border-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-600 text-center transform hover:scale-105 transition duration-300">Learn More</a>
                </div>
                
                <!-- Community Logos -->
                <div class="mt-12">
                    <p class="text-indigo-100 mb-4">Trusted by leading communities:</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-leaf text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Urban Gardeners</h4>
                                    <p class="text-xs text-indigo-200">50+ Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-laptop-code text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Tech Innovators</h4>
                                    <p class="text-xs text-indigo-200">120+ Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-palette text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Art Collective</h4>
                                    <p class="text-xs text-indigo-200">75+ Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Health & Wellness</h4>
                                    <p class="text-xs text-indigo-200">90+ Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Education Hub</h4>
                                    <p class="text-xs text-indigo-200">200+ Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/5 p-4 rounded-lg border border-indigo-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-recycle text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold">Sustainability</h4>
                                    <p class="text-xs text-indigo-200">150+ Members</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:w-1/2 mt-10 md:mt-0">
                <div class="relative">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-yellow-400 rounded-full opacity-20"></div>
                    <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-pink-400 rounded-full opacity-20"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Community Growth</h3>
                                    <p class="text-indigo-100">+50% in 2024</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-project-diagram text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Active Projects</h3>
                                    <p class="text-indigo-100">100+ Ongoing</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-share-nodes text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Resources Shared</h3>
                                    <p class="text-indigo-100">500+ Items</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-handshake text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Partnerships</h3>
                                    <p class="text-indigo-100">25+ Active</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-100 to-transparent"></div>
    </header>

    <!-- Stats Section -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div class="bg-indigo-50 p-6 rounded-lg shadow">
                    <p class="text-4xl font-bold text-indigo-700 mb-2" id="communitiesCount">0</p>
                    <p class="text-gray-600">Communities</p>
                </div>
                <div class="bg-indigo-50 p-6 rounded-lg shadow">
                    <p class="text-4xl font-bold text-indigo-700 mb-2" id="projectsCount">0</p>
                    <p class="text-gray-600">Active Projects</p>
                </div>
                <div class="bg-indigo-50 p-6 rounded-lg shadow">
                    <p class="text-4xl font-bold text-indigo-700 mb-2" id="resourcesCount">0</p>
                    <p class="text-gray-600">Shared Resources</p>
                </div>
                <div class="bg-indigo-50 p-6 rounded-lg shadow">
                    <p class="text-4xl font-bold text-indigo-700 mb-2" id="membersCount">0</p>
                    <p class="text-gray-600">Members</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">How EKYAM Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">A simple, yet powerful platform designed to bring communities together through meaningful collaboration and resource sharing.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-3xl font-bold text-indigo-600">1</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Connect</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Join or create community profiles to connect with others who share your interests, cultural background, or geographic location.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Create your community profile</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Set your community goals</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Invite members to join</span>
                        </li>
                    </ul>
                </div>

                <!-- Step 2 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-3xl font-bold text-indigo-600">2</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Share</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Share knowledge, skills, spaces, tools, and other resources to strengthen communities and reduce duplication of efforts.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>List available resources</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Share expertise and skills</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Coordinate resource usage</span>
                        </li>
                    </ul>
                </div>

                <!-- Step 3 -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-3xl font-bold text-indigo-600">3</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Collaborate</h3>
                    </div>
                    <p class="text-gray-600 mb-6">Create or join collaborative projects that bring together diverse perspectives to solve common challenges.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Start new projects</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Join existing initiatives</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Track progress together</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Additional Features -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-indigo-50 p-8 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Track Progress</h3>
                    </div>
                    <p class="text-gray-600">Monitor community growth, resource utilization, and project milestones with our intuitive dashboard.</p>
                </div>
                <div class="bg-indigo-50 p-8 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Safe & Secure</h3>
                    </div>
                    <p class="text-gray-600">Your community data is protected with enterprise-grade security and privacy controls.</p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="mt-16 text-center">
                <a href="register.php" class="inline-block bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition duration-300">
                    Start Your Community Journey
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Projects -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold">Featured Projects</h2>
                <a href="projects.php" class="text-indigo-700 hover:text-indigo-500 font-semibold">View All Projects</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8" id="featuredProjects">
                <?php
                require_once 'config.php';
                
                // Fetch featured projects
                $query = "SELECT p.*, c.name as community_name, u.full_name as creator_name 
                         FROM projects p 
                         LEFT JOIN communities c ON p.community_id = c.id 
                         LEFT JOIN users u ON p.created_by = u.id 
                         WHERE p.is_featured = 1 
                         ORDER BY p.created_at DESC 
                         LIMIT 3";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($project = mysqli_fetch_assoc($result)) {
                        echo '<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">';
                        if ($project['image']) {
                            echo '<img src="' . htmlspecialchars($project['image']) . '" alt="' . htmlspecialchars($project['name']) . '" class="w-full h-48 object-cover">';
                        } else {
                            echo '<div class="w-full h-48 bg-indigo-100 flex items-center justify-center">';
                            echo '<i class="fas fa-project-diagram text-4xl text-indigo-400"></i>';
                            echo '</div>';
                        }
                        
                        echo '<div class="p-6">';
                        echo '<div class="flex items-center justify-between mb-4">';
                        echo '<span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">' . htmlspecialchars($project['status']) . '</span>';
                        echo '<span class="text-gray-500 text-sm">' . date('M d, Y', strtotime($project['start_date'])) . '</span>';
                        echo '</div>';
                        
                        echo '<h3 class="text-xl font-semibold mb-2">' . htmlspecialchars($project['name']) . '</h3>';
                        echo '<p class="text-gray-600 mb-4 line-clamp-2">' . htmlspecialchars($project['description']) . '</p>';
                        
                        echo '<div class="flex items-center justify-between">';
                        echo '<div class="flex items-center">';
                        echo '<i class="fas fa-users text-gray-400 mr-2"></i>';
                        echo '<span class="text-gray-600">' . $project['member_count'] . ' members</span>';
                        echo '</div>';
                        echo '<a href="project_details.php?id=' . $project['id'] . '" class="text-indigo-700 hover:text-indigo-500">View Details</a>';
                        echo '</div>';
                        
                        if ($project['community_name']) {
                            echo '<div class="mt-4 pt-4 border-t border-gray-100">';
                            echo '<p class="text-sm text-gray-500">Community: ' . htmlspecialchars($project['community_name']) . '</p>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-span-3 text-center py-8">';
                    echo '<p class="text-gray-500">No featured projects available at the moment.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Community Map Preview -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 md:pr-10 mb-8 md:mb-0">
                    <h2 class="text-3xl font-bold mb-6">Discover Communities Near You</h2>
                    <p class="text-gray-600 mb-6">Our interactive map helps you find and connect with communities in your area. See what resources are being shared locally and find opportunities for collaboration.</p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="map.php" class="bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-600 inline-block text-center">Explore the Map</a>
                        <button id="locateMe" class="border-2 border-indigo-700 text-indigo-700 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 inline-block text-center">
                            <i class="fas fa-location-arrow mr-2"></i>Find My Location
                        </button>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <div id="mapPreview" class="h-96 rounded-lg"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Community Stories</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-200 flex items-center justify-center mr-4">
                            <span class="text-indigo-700 font-semibold">NK</span>
                        </div>
                        <div>
                            <h4 class="font-semibold">Nita Kumar</h4>
                            <p class="text-gray-600 text-sm">Urban Gardening Collective</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"EKYAM helped our community garden connect with three other neighborhoods. We now share tools, seeds, and knowledge, allowing us to grow more food while using fewer resources."</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-200 flex items-center justify-center mr-4">
                            <span class="text-indigo-700 font-semibold">MR</span>
                        </div>
                        <div>
                            <h4 class="font-semibold">Miguel Rodriguez</h4>
                            <p class="text-gray-600 text-sm">Cultural Heritage Foundation</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Through EKYAM's platform, we found partners for our multicultural festival. What started as a small event has grown into a city-wide celebration bringing together communities that had never collaborated before."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-indigo-700 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Ready to Connect Your Community?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Join EKYAM today and be part of a growing network of communities working together to create positive change.</p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="register.php" class="bg-white text-indigo-700 px-6 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-100">Create an Account</a>
                <a href="communities.php" class="border-2 border-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-600">Browse Communities</a>
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

    <!-- JavaScript for dynamic content and interactions -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // Fetch and update stats
        fetch('get_stats.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('communitiesCount').textContent = data.communities;
                document.getElementById('projectsCount').textContent = data.projects;
                document.getElementById('resourcesCount').textContent = data.resources;
                document.getElementById('membersCount').textContent = data.members;
            })
            .catch(error => console.error('Error fetching stats:', error));

        // Newsletter form submission
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real implementation, this would send the data to your PHP backend
            alert('Thanks for subscribing to our newsletter!');
            this.reset();
        });
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        const map = L.map('mapPreview').setView([20.5937, 78.9629], 5); // Default to India

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Sample community markers (replace with your actual data)
        const communities = [
            {
                name: "Urban Gardeners",
                location: [28.6139, 77.2090], // Delhi
                members: 50,
                type: "gardening"
            },
            {
                name: "Tech Innovators",
                location: [19.0760, 72.8777], // Mumbai
                members: 120,
                type: "technology"
            },
            {
                name: "Art Collective",
                location: [12.9716, 77.5946], // Bangalore
                members: 75,
                type: "art"
            },
            {
                name: "Health & Wellness",
                location: [22.5726, 88.3639], // Kolkata
                members: 90,
                type: "health"
            },
            {
                name: "Education Hub",
                location: [13.0827, 80.2707], // Chennai
                members: 200,
                type: "education"
            },
            {
                name: "Sustainability Group",
                location: [18.5204, 73.8567], // Pune
                members: 150,
                type: "sustainability"
            }
        ];

        // Add markers for each community
        communities.forEach(community => {
            const icon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="bg-indigo-600 text-white p-2 rounded-full shadow-lg">
                    <i class="fas fa-${getIconForType(community.type)}"></i>
                </div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            const marker = L.marker(community.location, { icon: icon })
                .bindPopup(`
                    <div class="p-2">
                        <h3 class="font-bold text-lg">${community.name}</h3>
                        <p class="text-gray-600">${community.members} members</p>
                        <a href="community_details.php?name=${encodeURIComponent(community.name)}" 
                           class="text-indigo-600 hover:text-indigo-800 text-sm">View Details</a>
                    </div>
                `);
            marker.addTo(map);
        });

        // Function to get appropriate icon based on community type
        function getIconForType(type) {
            const icons = {
                gardening: 'leaf',
                technology: 'laptop-code',
                art: 'palette',
                health: 'heartbeat',
                education: 'graduation-cap',
                sustainability: 'recycle'
            };
            return icons[type] || 'users';
        }

        // Handle "Find My Location" button click
        document.getElementById('locateMe').addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const { latitude, longitude } = position.coords;
                        map.setView([latitude, longitude], 13);
                        
                        // Add user location marker
                        const userIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: '<div class="bg-green-500 text-white p-2 rounded-full shadow-lg"><i class="fas fa-user"></i></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        });
                        
                        L.marker([latitude, longitude], { icon: userIcon })
                            .bindPopup('Your Location')
                            .addTo(map);
                    },
                    error => {
                        alert('Unable to retrieve your location. Please ensure location services are enabled.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });

        // Add custom CSS for map controls
        const style = document.createElement('style');
        style.textContent = `
            .custom-div-icon {
                background: transparent;
                border: none;
            }
            .leaflet-popup-content {
                margin: 0;
            }
            .leaflet-popup-content-wrapper {
                padding: 0;
                border-radius: 8px;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>