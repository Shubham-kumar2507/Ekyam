<?php
// Start session
session_start();

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

// Process registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $conn = connectDB();
    
    // Get form data and sanitize
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $location = $conn->real_escape_string($_POST['location']);
    
    // Validation
    $errors = [];
    
    // Check if username already exists
    $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $errors[] = "Username already taken. Please choose another.";
    }
    
    // Check if email already exists
    $result = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered. Please use another email or login to your account.";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Password strength validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user data
        $sql = "INSERT INTO users (username, email, password, full_name, user_type, location) 
                VALUES ('$username', '$email', '$hashed_password', '$full_name', '$user_type', '$location')";
        
        if ($conn->query($sql) === TRUE) {
            // Get the new user's ID
            $user_id = $conn->insert_id;
            
            // If registering as community admin, create community
            if ($user_type === 'community_admin' && isset($_POST['community_name'])) {
                $community_name = $conn->real_escape_string($_POST['community_name']);
                $community_description = $conn->real_escape_string($_POST['community_description']);
                $community_location = $conn->real_escape_string($_POST['community_location']);
                
                // Insert community data
                $sql = "INSERT INTO communities (name, description, location, admin_id) 
                        VALUES ('$community_name', '$community_description', '$community_location', $user_id)";
                
                if ($conn->query($sql) === TRUE) {
                    $community_id = $conn->insert_id;
                    
                    // Update user's community_id
                    $sql = "UPDATE users SET community_id = $community_id WHERE id = $user_id";
                    $conn->query($sql);
                } else {
                    $errors[] = "Error creating community: " . $conn->error;
                }
            }
            
            if (empty($errors)) {
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['full_name'] = $full_name;
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $errors[] = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EKYAM</title>
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
                <a href="login.php" class="hover:text-indigo-200">Login</a>
                <a href="register.php" class="bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 font-bold">Join Us</a>
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

    <!-- Registration Section -->
    <div class="flex items-center justify-center min-h-screen bg-gray-100 py-12">
        <div class="bg-white w-full max-w-2xl rounded-lg shadow-lg overflow-hidden">
            <div class="bg-indigo-700 px-6 py-8 text-white text-center">
                <h1 class="text-3xl font-bold mb-2">Join EKYAM</h1>
                <p>Create an account to connect with communities and collaborate on projects</p>
            </div>
            
            <div class="p-6">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <ul class="list-disc ml-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Account Type</label>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center">
                                <input type="radio" id="individual" name="user_type" value="individual" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500" checked>
                                <label for="individual" class="ml-2 text-sm text-gray-600">Individual Member</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="community_admin" name="user_type" value="community_admin" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500">
                                <label for="community_admin" class="ml-2 text-sm text-gray-600">Community Administrator</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="full_name" class="block text-gray-700 text-sm font-semibold mb-2">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="location" class="block text-gray-700 text-sm font-semibold mb-2">Location</label>
                            <input type="text" id="location" name="location" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="username" class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
                            <input type="text" id="username" name="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                            <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                            <p class="text-xs text-gray-500 mt-1">At least 8 characters with a mix of letters, numbers, and symbols</p>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-gray-700 text-sm font-semibold mb-2">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                    </div>
                    
                    <!-- Community admin fields (initially hidden) -->
                    <div id="communityFields" class="hidden border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Community Information</h3>
                        
                        <div class="mb-4">
                            <label for="community_name" class="block text-gray-700 text-sm font-semibold mb-2">Community Name</label>
                            <input type="text" id="community_name" name="community_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>
                        
                        <div class="mb-4">
                            <label for="community_description" class="block text-gray-700 text-sm font-semibold mb-2">Community Description</label>
                            <textarea id="community_description" name="community_description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="community_location" class="block text-gray-700 text-sm font-semibold mb-2">Community Location</label>
                            <input type="text" id="community_location" name="community_location" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" name="register" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-150">
                            Create Account
                        </button>
                        <p class="text-sm text-center mt-4 text-gray-600">
                            Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-800">Login here</a>
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
        
        // Show/hide community fields based on user type selection
        document.querySelectorAll('input[name="user_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'community_admin') {
                    document.getElementById('communityFields').classList.remove('hidden');
                    document.getElementById('community_name').setAttribute('required', 'required');
                    document.getElementById('community_description').setAttribute('required', 'required');
                    document.getElementById('community_location').setAttribute('required', 'required');
                } else {
                    document.getElementById('communityFields').classList.add('hidden');
                    document.getElementById('community_name').removeAttribute('required');
                    document.getElementById('community_description').removeAttribute('required');
                    document.getElementById('community_location').removeAttribute('required');
                }
            });
        });
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                event.preventDefault();
                return false;
            }
            
            if (password.length < 8) {
                alert("Password must be at least 8 characters long!");
                event.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>