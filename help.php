<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - EKYAM</title>
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

    <!-- Help Center Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Search Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">How can we help you?</h1>
                <div class="relative max-w-2xl mx-auto">
                    <input type="text" placeholder="Search for help..." 
                           class="w-full px-6 py-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Categories -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user-plus text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Getting Started</h3>
                    </div>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Creating an Account
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Setting Up Your Profile
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Finding Communities
                        </a></li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-project-diagram text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Projects & Collaboration</h3>
                    </div>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Creating Projects
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Managing Teams
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Resource Sharing
                        </a></li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-users text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Community Management</h3>
                    </div>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Creating Communities
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Managing Members
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Community Guidelines
                        </a></li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-cog text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Account & Settings</h3>
                    </div>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Privacy Settings
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Notification Preferences
                        </a></li>
                        <li><a href="#" class="text-gray-600 hover:text-indigo-600 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>
                            Account Security
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Popular Articles -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Popular Articles</h2>
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">How to Create Your First Project</h3>
                        <p class="text-gray-600 mb-2">Learn the basics of creating and managing projects on EKYAM.</p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">Read more →</a>
                    </div>
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Best Practices for Community Engagement</h3>
                        <p class="text-gray-600 mb-2">Tips and strategies for building and maintaining active communities.</p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">Read more →</a>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Resource Sharing Guidelines</h3>
                        <p class="text-gray-600 mb-2">Understand how to effectively share and manage resources within your community.</p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">Read more →</a>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="bg-indigo-50 rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Still Need Help?</h2>
                <p class="text-gray-600 mb-6">Our support team is here to help you with any questions or issues you may have.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="contact.php" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700">
                        Contact Support
                    </a>
                    <a href="faq.php" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 border border-indigo-600">
                        View FAQ
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
        // Search functionality
        document.querySelector('input[type="text"]').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                // Implement search functionality here
                console.log('Searching for:', this.value);
            }
        });

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