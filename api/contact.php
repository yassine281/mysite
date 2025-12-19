<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Validate
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['error' => 'All fields are required']);
        exit();
    }
    
    // Insert message
    $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at) 
            VALUES ('$name', '$email', '$subject', '$message', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['error' => 'Failed to send message: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>
