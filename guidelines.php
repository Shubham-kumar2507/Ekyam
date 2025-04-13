<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Guidelines - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="Homepage.php" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="hidden md:flex space-x-6">
                <a href="Homepage.php" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
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
                    <a href="login.php" class="hover:text-indigo-200">Login</a>
                    <a href="register.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100">Join Us</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Guidelines Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Community Guidelines</h1>
                <p class="text-xl text-gray-600">Our commitment to fostering a safe, inclusive, and collaborative environment</p>
            </div>

            <!-- Table of Contents -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Table of Contents</h2>
                <ul class="space-y-2">
                    <li><a href="#respect" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-chevron-right text-xs mr-2"></i>
                        Respect and Inclusion
                    </a></li>
                    <li><a href="#collaboration" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-chevron-right text-xs mr-2"></i>
                        Collaboration and Communication
                    </a></li>
                    <li><a href="#content" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-chevron-right text-xs mr-2"></i>
                        Content and Resource Sharing
                    </a></li>
                    <li><a href="#safety" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-chevron-right text-xs mr-2"></i>
                        Safety and Privacy
                    </a></li>
                    <li><a href="#enforcement" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <i class="fas fa-chevron-right text-xs mr-2"></i>
                        Enforcement and Reporting
                    </a></li>
                </ul>
            </div>

            <!-- Guidelines Sections -->
            <div class="space-y-8">
                <!-- Respect and Inclusion -->
                <div id="respect" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-heart text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Respect and Inclusion</h2>
                    </div>
                    <div class="space-y-4">
                        <p class="text-gray-600">We believe in creating a welcoming environment for all community members. Our platform thrives on diversity and mutual respect.</p>
                        <ul class="list-disc list-inside space-y-2 text-gray-600">
                            <li>Treat all members with respect and kindness</li>
                            <li>Embrace diversity and different perspectives</li>
                            <li>Use inclusive language and avoid discriminatory remarks</li>
                            <li>Respect cultural differences and personal boundaries</li>
                        </ul>
                    </div>
                </div>

                <!-- Collaboration and Communication -->
                <div id="collaboration" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-comments text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Collaboration and Communication</h2>
                    </div>
                    <div class="space-y-4">
                        <p class="text-gray-600">Effective communication is key to successful collaboration. We encourage open, constructive dialogue.</p>
                        <ul class="list-disc list-inside space-y-2 text-gray-600">
                            <li>Communicate clearly and professionally</li>
                            <li>Be open to feedback and different viewpoints</li>
                            <li>Resolve conflicts respectfully and constructively</li>
                            <li>Give credit where credit is due</li>
                        </ul>
                    </div>
                </div>

                <!-- Content and Resource Sharing -->
                <div id="content" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-share-alt text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Content and Resource Sharing</h2>
                    </div>
                    <div class="space-y-4">
                        <p class="text-gray-600">We encourage sharing knowledge and resources while maintaining quality and relevance.</p>
                        <ul class="list-disc list-inside space-y-2 text-gray-600">
                            <li>Share accurate and helpful information</li>
                            <li>Respect intellectual property rights</li>
                            <li>Ensure content is appropriate and relevant</li>
                            <li>Maintain transparency in resource sharing</li>
                        </ul>
                    </div>
                </div>

                <!-- Safety and Privacy -->
                <div id="safety" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Safety and Privacy</h2>
                    </div>
                    <div class="space-y-4">
                        <p class="text-gray-600">Protecting our community members' safety and privacy is our top priority.</p>
                        <ul class="list-disc list-inside space-y-2 text-gray-600">
                            <li>Protect personal and sensitive information</li>
                            <li>Report suspicious or harmful behavior</li>
                            <li>Respect others' privacy and boundaries</li>
                            <li>Use secure communication channels</li>
                        </ul>
                    </div>
                </div>

                <!-- Enforcement and Reporting -->
                <div id="enforcement" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-gavel text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Enforcement and Reporting</h2>
                    </div>
                    <div class="space-y-4">
                        <p class="text-gray-600">We take violations of our guidelines seriously and have clear processes for enforcement.</p>
                        <ul class="list-disc list-inside space-y-2 text-gray-600">
                            <li>Report violations through appropriate channels</li>
                            <li>Provide clear evidence when reporting issues</li>
                            <li>Understand the consequences of guideline violations</li>
                            <li>Cooperate with community moderators</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Agreement Section -->
            <div class="bg-indigo-50 rounded-lg p-8 mt-12 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Community Agreement</h2>
                <p class="text-gray-600 mb-6">By participating in our community, you agree to follow these guidelines and contribute to creating a positive environment for all members.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="register.php" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700">
                        Join Our Community
                    </a>
                    <a href="contact.php" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 border border-indigo-600">
                        Report a Concern
                    </a>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html> 