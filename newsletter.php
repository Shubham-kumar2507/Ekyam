<?php
session_start();
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'You are already subscribed to our newsletter!';
            } else {
                // Insert new subscriber
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
                $stmt->execute([$email]);
                $message = 'Thank you for subscribing to our newsletter!';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .newsletter-card {
            background-image: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1267&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .newsletter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 0;
        }
        .card-content {
            position: relative;
            z-index: 1;
        }
        .pulse-button {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        .wave-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        .wave-bg svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 150px;
        }
        .wave-bg .shape-fill {
            fill: #4F46E5;
        }
        .mobile-menu {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="Homepage.php" class="text-2xl font-bold flex items-center animate__animated animate__fadeIn">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6">
                <a href="Homepage.php" class="hover:text-indigo-200 transition duration-300 transform hover:-translate-y-1">Home</a>
                <a href="projects.php" class="hover:text-indigo-200 transition duration-300 transform hover:-translate-y-1">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200 transition duration-300 transform hover:-translate-y-1">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200 transition duration-300 transform hover:-translate-y-1">Communities</a>
                <a href="map.php" class="hover:text-indigo-200 transition duration-300 transform hover:-translate-y-1">Community Map</a>
                <a href="newsletter.php" class="text-white font-semibold border-b-2 border-white transition duration-300 transform hover:-translate-y-1">Newsletter</a>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hover:text-indigo-200 transition duration-300">
                        <i class="fas fa-user-circle mr-1"></i> Profile
                    </a>
                    <a href="logout.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 transition duration-300 shadow-md">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-indigo-200 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="register.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 transition duration-300 shadow-md">
                        <i class="fas fa-user-plus mr-1"></i> Join Us
                    </a>
                <?php endif; ?>
                
                <!-- Mobile menu button -->
                <button class="md:hidden focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu bg-indigo-800 md:hidden" id="mobile-menu">
            <div class="px-4 py-3 space-y-2">
                <a href="Homepage.php" class="block py-2 px-4 hover:bg-indigo-700 rounded">Home</a>
                <a href="projects.php" class="block py-2 px-4 hover:bg-indigo-700 rounded">Projects</a>
                <a href="resources.php" class="block py-2 px-4 hover:bg-indigo-700 rounded">Resources</a>
                <a href="communities.php" class="block py-2 px-4 hover:bg-indigo-700 rounded">Communities</a>
                <a href="map.php" class="block py-2 px-4 hover:bg-indigo-700 rounded">Community Map</a>
                <a href="newsletter.php" class="block py-2 px-4 bg-indigo-600 rounded">Newsletter</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="gradient-bg text-white py-16 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate__animated animate__fadeInDown">Stay Connected with EKYAM</h1>
            <p class="text-xl md:text-2xl max-w-2xl mx-auto animate__animated animate__fadeInUp">
                Join our community newsletter and never miss important updates, inspiring stories, and opportunities to make a difference.
            </p>
        </div>
        <div class="">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
            </svg>
        </div>
    </div>

    <!-- Newsletter Content -->
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Left Column: Benefits -->
                <div class="md:w-2/5 animate__animated animate__fadeInLeft">
                    <h2 class="text-2xl font-bold text-indigo-700 mb-6">Why Subscribe?</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                <i class="fas fa-newspaper text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Monthly Digest</h3>
                                <p class="text-gray-600">Get curated updates on projects and community initiatives.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                <i class="fas fa-lightbulb text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Inspiring Stories</h3>
                                <p class="text-gray-600">Read success stories from communities around the world.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Event Invitations</h3>
                                <p class="text-gray-600">Be the first to know about workshops and community gatherings.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-indigo-100 p-3 rounded-full mr-4">
                                <i class="fas fa-hands-helping text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Volunteer Opportunities</h3>
                                <p class="text-gray-600">Discover ways to contribute your skills and time.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Subscribe Form -->
                <div class="md:w-3/5 animate__animated animate__fadeInRight">
                    <div class="newsletter-card rounded-2xl shadow-xl overflow-hidden">
                        <div class="card-content p-8">
                            <h2 class="text-3xl font-bold text-indigo-800 mb-2">Subscribe to Our Newsletter</h2>
                            <p class="text-gray-600 mb-6">Join our growing community of changemakers today!</p>

                            <?php if ($message): ?>
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md animate__animated animate__fadeIn">
                                    <div class="flex items-center">
                                        <div class="py-1"><i class="fas fa-check-circle text-green-500 mr-3"></i></div>
                                        <div>
                                            <p class="font-bold">Success!</p>
                                            <p><?php echo htmlspecialchars($message); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md animate__animated animate__fadeIn">
                                    <div class="flex items-center">
                                        <div class="py-1"><i class="fas fa-exclamation-circle text-red-500 mr-3"></i></div>
                                        <div>
                                            <p class="font-bold">Error</p>
                                            <p><?php echo htmlspecialchars($error); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-6">
                                <div class="relative">
                                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                                    <div class="flex">
                                        <div class="bg-gray-100 flex items-center pl-4 rounded-l-lg border border-r-0 border-gray-300">
                                            <i class="fas fa-envelope text-gray-500"></i>
                                        </div>
                                        <input type="email" id="email" name="email" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="yourname@example.com">
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="updates" name="updates" checked
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="updates" class="ml-2 block text-sm text-gray-700">
                                            I'd like to receive community updates (2-4 emails per month)
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" id="privacy" name="privacy" required
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="privacy" class="ml-2 block text-sm text-gray-700">
                                            I agree to EKYAM's <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-indigo-600 to-indigo-800 text-white px-6 py-4 rounded-lg hover:from-indigo-700 hover:to-indigo-900 transition duration-300 transform hover:scale-105 font-bold shadow-lg pulse-button">
                                    <i class="fas fa-paper-plane mr-2"></i> Subscribe Now
                                </button>
                                
                                <p class="text-sm text-gray-500 text-center mt-4">
                                    You can unsubscribe at any time. We respect your privacy.
                                </p>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Testimonial -->
                    <div class="mt-8 bg-indigo-50 p-6 rounded-lg shadow border border-indigo-100">
                        <div class="flex items-start">
                            <div class="text-indigo-500 mr-4">
                                <i class="fas fa-quote-left text-4xl opacity-50"></i>
                            </div>
                            <div>
                                <p class="text-gray-700 italic mb-4">
                                    "The EKYAM newsletter has been an incredible source of inspiration for our community initiatives. We've connected with like-minded groups and found resources we wouldn't have discovered otherwise!"
                                </p>
                                <div class="flex items-center">
                                    <div class="bg-indigo-200 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Priya Sharma</p>
                                        <p class="text-sm text-gray-500">Community Leader, Green Futures Initiative</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="bg-indigo-700 text-white py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Join Our Growing Community</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-6 bg-indigo-800 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-4xl font-bold mb-2">5,000+</div>
                    <div class="text-indigo-200">Newsletter Subscribers</div>
                </div>
                
                <div class="p-6 bg-indigo-800 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-4xl font-bold mb-2">120+</div>
                    <div class="text-indigo-200">Active Communities</div>
                </div>
                
                <div class="p-6 bg-indigo-800 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-4xl font-bold mb-2">350+</div>
                    <div class="text-indigo-200">Successful Projects</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">EKYAM</h3>
                    <p class="text-gray-400">Connecting communities, empowering change-makers, and building a better future together.</p>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="Homepage.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <address class="text-gray-400 not-italic">
                        <p>123 Community Lane</p>
                        <p>Collaboration City, CC 12345</p>
                        <p>info@ekyam.org</p>
                    </address>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> EKYAM. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu.style.display === 'block') {
                mobileMenu.style.display = 'none';
            } else {
                mobileMenu.style.display = 'block';
            }
        });
        
        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.animate__animated');
                elements.forEach(function(element) {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    if (elementPosition < windowHeight - 100) {
                        const animationClass = element.dataset.animation || 'animate__fadeIn';
                        element.classList.add(animationClass);
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run on initial load
        });
    </script>
</body>
</html> 