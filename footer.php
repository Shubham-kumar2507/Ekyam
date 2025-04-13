<!-- Footer -->
<footer class="bg-gray-900 text-white">
    <!-- Newsletter section -->
    <div class="bg-indigo-700 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-xl mx-auto text-center">
                <h2 class="text-2xl font-bold mb-4">Stay Updated with EKYAM</h2>
                <p class="mb-6">Subscribe to our newsletter to get the latest updates about community projects and resources.</p>
                
                <!-- Newsletter Form -->
                <form id="newsletter-form" class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto">
                    <input 
                        type="email" 
                        id="newsletter-email" 
                        name="email" 
                        placeholder="Enter your email address" 
                        required
                        class="bg-white text-gray-800 px-4 py-2 rounded-l flex-grow focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    >
                    <button 
                        type="submit" 
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-r font-medium transition duration-200"
                    >
                        Subscribe
                    </button>
                </form>
                
                <!-- Status message -->
                <div id="newsletter-message" class="mt-4 hidden">
                    <p id="newsletter-message-text" class="text-sm"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">About EKYAM</h3>
                <p class="text-gray-400">Empowering communities through collaboration and resource sharing.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="Homepage.php" class="text-gray-400 hover:text-white">Home</a></li>
                    <li><a href="communities.php" class="text-gray-400 hover:text-white">Communities</a></li>
                    <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                    <li><a href="resources.php" class="text-gray-400 hover:text-white">Resources</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                <p class="text-gray-400">Email: support@ekyam.com</p>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> EKYAM. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Newsletter Form JavaScript -->
<script src="js/newsletter.js"></script> 