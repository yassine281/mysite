<?php
include 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login to book']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $hotel_id = intval($_POST['hotel_id']);
    $room_id = intval($_POST['room_id']);
    $check_in = $conn->real_escape_string($_POST['check_in']);
    $check_out = $conn->real_escape_string($_POST['check_out']);
    $guests = intval($_POST['guests']);
    $total_price = floatval($_POST['total_price']);
    
    // Insert booking
    $sql = "INSERT INTO bookings (user_id, hotel_id, room_id, check_in, check_out, guests, total_price, status, payment_status, created_at) 
            VALUES ($user_id, $hotel_id, $room_id, '$check_in', '$check_out', $guests, $total_price, 'confirmed', 'unpaid', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        $booking_id = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'booking_id' => $booking_id,
            'message' => 'Booking created successfully'
        ]);
    } else {
        echo json_encode(['error' => 'Booking failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>
