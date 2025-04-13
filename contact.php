<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - EKYAM</title>
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

    <!-- Contact Form Section -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Contact Us</h1>
                <p class="text-xl text-gray-600">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Get in Touch</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Our Location</h3>
                                <p class="text-gray-600">LPU Phagwara Punjab 844411 India</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-phone text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Phone</h3>
                                <p class="text-gray-600">+91 9931372825</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Email</h3>
                                <p class="text-gray-600">support@ekyam.com</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div class="mt-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <form id="contactForm" action="process_contact.php" method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter your full name">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter your email address">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" id="subject" name="subject" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter the subject">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea id="message" name="message" rows="4" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                      placeholder="Enter your message"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-300">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-12 bg-indigo-50 rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600 mb-6">Check our FAQ section for quick answers to common questions.</p>
                <a href="faq.php" class="inline-block bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-100 border border-indigo-600">
                    Visit FAQ
                </a>
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
        // Form validation and submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !subject || !message) {
                alert('Please fill in all fields');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Create form data
            const formData = new FormData(this);
            
            // Send form data to server
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your message! We will get back to you soon.');
                    this.reset();
                } else {
                    alert('There was an error sending your message. Please try again later.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error sending your message. Please try again later.');
            });
        });
    </script>
</body>
</html> 