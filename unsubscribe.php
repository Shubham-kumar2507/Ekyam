<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $query = "UPDATE newsletter_subscribers SET is_active = FALSE WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "You have been successfully unsubscribed from our newsletter.";
        } else {
            $error = "Error unsubscribing. Please try again.";
        }
    } else {
        $error = "Invalid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from EKYAM Newsletter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Newsletter Unsubscribe</h1>
                <p class="text-gray-600 mt-2">Manage your newsletter subscription preferences</p>
            </div>
            
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="GET" action="" class="space-y-4">
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter your email address">
                </div>
                
                <button type="submit"
                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Unsubscribe
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Changed your mind?</p>
                <a href="subscribe_newsletter.php" class="text-indigo-600 hover:text-indigo-800">
                    Subscribe to our newsletter
                </a>
            </div>
        </div>
    </div>
</body>
</html> 