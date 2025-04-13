<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - EKYAM</title>
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

    <!-- FAQ Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h1>
                <p class="text-xl text-gray-600">Find answers to common questions about EKYAM</p>
            </div>

            <!-- Search Section -->
            <div class="relative max-w-2xl mx-auto mb-12">
                <input type="text" placeholder="Search FAQs..." 
                       class="w-full px-6 py-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- FAQ Categories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <a href="#getting-started" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-rocket text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Getting Started</h3>
                </a>
                <a href="#account" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Account & Profile</h3>
                </a>
                <a href="#communities" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Communities</h3>
                </a>
            </div>

            <!-- FAQ Sections -->
            <div class="space-y-8">
                <!-- Getting Started -->
                <div id="getting-started" class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800">Getting Started</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">What is EKYAM?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>EKYAM is a platform that connects communities through shared resources and collaborative projects. We aim to foster unity and collaboration among diverse groups by providing tools and spaces for meaningful interaction.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">How do I join EKYAM?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>To join EKYAM, simply click the "Join Us" button in the navigation bar and complete the registration form. You'll need to provide some basic information and create an account. Once registered, you can start exploring communities and projects.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">Is EKYAM free to use?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>Yes, EKYAM is completely free to use. We believe in making community collaboration accessible to everyone. There are no hidden fees or premium features - all our tools and resources are available to all members.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account & Profile -->
                <div id="account" class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800">Account & Profile</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">How do I update my profile?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>You can update your profile by clicking on your profile picture in the navigation bar and selecting "Edit Profile". From there, you can update your personal information, profile picture, and preferences.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">How do I change my password?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>To change your password, go to your profile settings and select "Security". From there, you can update your password. You'll need to enter your current password and then your new password twice for confirmation.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">Can I delete my account?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>Yes, you can delete your account at any time. Go to your profile settings and select "Account". At the bottom of the page, you'll find the option to delete your account. Please note that this action is irreversible.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Communities -->
                <div id="communities" class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800">Communities</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">How do I join a community?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>To join a community, browse the Communities page and click on a community that interests you. If the community is open, you can join immediately. If it's private, you'll need to request an invitation from the community admin.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">How do I create a community?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>To create a community, go to the Communities page and click "Create Community". You'll need to provide basic information about your community, set up guidelines, and choose privacy settings. Once created, you can invite members and start sharing resources.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                                <h3 class="text-lg font-semibold text-gray-800">What are community guidelines?</h3>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div class="mt-4 text-gray-600 hidden">
                                <p>Community guidelines are rules and expectations that help maintain a positive and productive environment. They cover topics like respectful communication, content sharing, and member behavior. Each community may have its own specific guidelines in addition to EKYAM's general guidelines.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="bg-indigo-50 rounded-lg p-8 mt-12 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Still Have Questions?</h2>
                <p class="text-gray-600 mb-6">Our support team is here to help you with any questions or issues you may have.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="contact.php" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700">
                        Contact Support
                    </a>
                    <a href="help.php" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 border border-indigo-600">
                        Visit Help Center
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
        // FAQ Toggle Function
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }

        // Search functionality
        document.querySelector('input[type="text"]').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.toLowerCase();
                const faqItems = document.querySelectorAll('.p-6');
                
                faqItems.forEach(item => {
                    const question = item.querySelector('h3').textContent.toLowerCase();
                    const answer = item.querySelector('.text-gray-600').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        item.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        item.style.display = 'none';
                    }
                });
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