<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Database connection
require_once 'config.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If there are no errors, proceed with saving to database
    if (empty($errors)) {
        try {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            // Bind parameters
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            // Execute statement
            if ($stmt->execute()) {
                // Send email notification (optional)
                $to = "support@ekyam.com";
                $email_subject = "New Contact Form Submission: " . $subject;
                $email_message = "Name: " . $name . "\n";
                $email_message .= "Email: " . $email . "\n";
                $email_message .= "Subject: " . $subject . "\n";
                $email_message .= "Message: " . $message . "\n";
                
                $headers = "From: " . $email . "\r\n";
                $headers .= "Reply-To: " . $email . "\r\n";
                
                // Uncomment to enable email sending
               
                
                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            } else {
                throw new Exception("Database error");
            }
            
            // Close statement
            $stmt->close();
            
        } catch (Exception $e) {
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => 'Error processing your request. Please try again later.'
            ]);
        }
    } else {
        // Return validation errors
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
    }
} else {
    // Return error for non-POST requests
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

// Close database connection
$conn->close();
?> 