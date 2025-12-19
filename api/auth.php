<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register') {
        // Registration
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['error' => 'Email already registered']);
            exit();
        }
        
        // Insert new user
        $sql = "INSERT INTO users (name, email, phone, password, role) 
                VALUES ('$name', '$email', '$phone', '$password', 'user')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['error' => 'Registration failed: ' . $conn->error]);
        }
    }
    
    elseif ($action === 'login') {
        // Login
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful',
                    'role' => $user['role']
                ]);
            } else {
                echo json_encode(['error' => 'Invalid password']);
            }
        } else {
            echo json_encode(['error' => 'User not found']);
        }
    }
    
    else {
        echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>
