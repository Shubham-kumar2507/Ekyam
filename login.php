<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Database connection
function connectDB() {
    $servername = "localhost";
    $username = "root";  // Default XAMPP username
    $password = "";      // Default XAMPP password
    $dbname = "ekyam_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $conn = connectDB();
    
    // Get form data and sanitize
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Fetch user data
    $sql = "SELECT id, username, password, user_type, full_name, community_id FROM users WHERE username = '$username' OR email = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['community_id'] = $user['community_id'];
            
            // Handle redirect after login
            if (isset($_GET['redirect']) && $_GET['redirect'] === 'create_community') {
                header("Location: create_community.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            // Check if password is stored in plain text (for migration)
            if ($password === $user['password']) {
                // Hash the plain text password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update the password in the database
                $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = " . $user['id'];
                if ($conn->query($update_sql)) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['community_id'] = $user['community_id'];
                    
                    // Handle redirect after login
                    if (isset($_GET['redirect']) && $_GET['redirect'] === 'create_community') {
                        header("Location: create_community.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
            }
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "User not found. Please check your username/email or register.";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            </div>
            <div class="flex items-center space-x-4">
                <a href="login.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 font-bold">Login</a>
                <a href="register.php" class="hover:text-indigo-200">Join Us</a>
                <button class="md:hidden text-xl" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-indigo-800" id="mobileMenu">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="index.html" class="hover:text-indigo-200">Home</a>
                <a href="projects.php" class="hover:text-indigo-200">Projects</a>
                <a href="resources.php" class="hover:text-indigo-200">Resources</a>
                <a href="communities.php" class="hover:text-indigo-200">Communities</a>
                <a href="map.php" class="hover:text-indigo-200">Community Map</a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <div class="flex items-center justify-center min-h-screen bg-gray-100 py-12">
        <div class="bg-white w-full max-w-md rounded-lg shadow-lg overflow-hidden">
            <div class="bg-indigo-700 px-6 py-8 text-white text-center">
                <h1 class="text-3xl font-bold mb-2">Login to EKYAM</h1>
                <p>Access your community and projects</p>
            </div>
            
            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 text-sm font-semibold mb-2">Username or Email</label>
                        <input type="text" id="username" name="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-gray-700 text-sm font-semibold">Password</label>
                            <a href="forgot-password.php" class="text-sm text-indigo-600 hover:text-indigo-800">Forgot Password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    
                    <div class="flex items-center mb-6">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                    </div>
                    
                    <div>
                        <button type="submit" name="login" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-150">
                            Login
                        </button>
                        <p class="text-sm text-center mt-4 text-gray-600">
                            Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-800">Register here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-2xl font-bold mb-4 flex items-center">
                        <span class="mr-2"><i class="fas fa-people-group"></i></span>
                        EKYAM
                    </h2>
                    <p class="text-gray-400 max-w-md">Empowering communities through collaboration, connection, and shared resources.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="index.html" class="text-gray-400 hover:text-white">Home</a></li>
                            <li><a href="projects.php" class="text-gray-400 hover:text-white">Projects</a></li>
                            <li><a href="resources.php" class="text-gray-400 hover:text-white">Resources</a></li>
                            <li><a href="communities.php" class="text-gray-400 hover:text-white">Communities</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                            <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQ</a></li>
                            <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                            <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Connect</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white">Facebook</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Twitter</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Instagram</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">LinkedIn</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; 2025 EKYAM. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white text-lg"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>
</body>
</html>