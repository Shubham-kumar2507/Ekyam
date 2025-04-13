<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $response = ['success' => false, 'message' => ''];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit();
    }
    
    try {
        // Check if email already exists
        $check_query = "SELECT id, is_active FROM newsletter_subscribers WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $subscriber = $result->fetch_assoc();
            if ($subscriber['is_active']) {
                $response['message'] = 'You are already subscribed to our newsletter';
            } else {
                // Reactivate subscription
                $update_query = "UPDATE newsletter_subscribers SET is_active = TRUE, subscribed_at = CURRENT_TIMESTAMP WHERE email = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('s', $email);
                if ($update_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Welcome back! You have been resubscribed to our newsletter';
                } else {
                    $response['message'] = 'Error reactivating subscription. Please try again.';
                }
            }
        } else {
            // Insert new subscription
            $insert_query = "INSERT INTO newsletter_subscribers (email) VALUES (?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param('s', $email);
            if ($insert_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Thank you for subscribing to our newsletter!';
                
                // Send welcome email
                $subject = "Welcome to EKYAM Newsletter";
                $message = file_get_contents("templates/newsletter/welcome.html");
                $message = str_replace('{{email}}', $email, $message);
                
                $headers = "From: EKYAM <noreply@ekyam.org>\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                mail($email, $subject, $message, $headers);
            } else {
                $response['message'] = 'Error subscribing to newsletter. Please try again.';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'An error occurred. Please try again later.';
    }
    
    echo json_encode($response);
} else {
    $response = ['success' => false, 'message' => 'Invalid request method'];
    echo json_encode($response);
}
?> 