<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['hotel_id']) || !isset($input['room_type']) || !isset($input['check_in']) || !isset($input['check_out'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: hotel_id, room_type, check_in, check_out'
    ]);
    exit;
}

$hotel_id = intval($input['hotel_id']);
$room_type = $conn->real_escape_string($input['room_type']);
$check_in = $conn->real_escape_string($input['check_in']);
$check_out = $conn->real_escape_string($input['check_out']);
$guests = isset($input['guests']) ? intval($input['guests']) : 1;

// Validate dates
$check_in_date = strtotime($check_in);
$check_out_date = strtotime($check_out);
$today = strtotime(date('Y-m-d'));

if ($check_in_date < $today) {
    echo json_encode([
        'success' => false,
        'message' => 'Check-in date cannot be in the past'
    ]);
    exit;
}

if ($check_out_date <= $check_in_date) {
    echo json_encode([
        'success' => false,
        'message' => 'Check-out date must be after check-in date'
    ]);
    exit;
}

// Get room details
$room_query = "SELECT * FROM rooms 
               WHERE hotel_id = $hotel_id 
               AND room_type = '$room_type' 
               AND capacity >= $guests
               LIMIT 1";

$room_result = $conn->query($room_query);

if ($room_result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Room type not found or insufficient capacity for the number of guests'
    ]);
    exit;
}

$room = $room_result->fetch_assoc();

// Check for overlapping bookings
$booking_query = "SELECT COUNT(*) as booked_rooms 
                  FROM bookings 
                  WHERE hotel_id = $hotel_id 
                  AND room_type = '$room_type'
                  AND status IN ('confirmed', 'pending')
                  AND (
                      (check_in_date <= '$check_in' AND check_out_date > '$check_in')
                      OR (check_in_date < '$check_out' AND check_out_date >= '$check_out')
                      OR (check_in_date >= '$check_in' AND check_out_date <= '$check_out')
                  )";

$booking_result = $conn->query($booking_query);
$booking_data = $booking_result->fetch_assoc();
$booked_rooms = intval($booking_data['booked_rooms']);

// Calculate available rooms
$available_rooms = $room['total_rooms'] - $booked_rooms;

// Calculate total price
$days = ceil(($check_out_date - $check_in_date) / (60 * 60 * 24));
$price_per_night = floatval($room['price']);
$total_price = $price_per_night * $days;

// Response
if ($available_rooms > 0) {
    echo json_encode([
        'success' => true,
        'available' => true,
        'message' => 'Room is available!',
        'data' => [
            'room_id' => $room['id'],
            'room_type' => $room['room_type'],
            'price_per_night' => $price_per_night,
            'total_nights' => $days,
            'total_price' => $total_price,
            'available_rooms' => $available_rooms,
            'capacity' => $room['capacity'],
            'amenities' => $room['amenities'],
            'description' => $room['description']
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'available' => false,
        'message' => 'No rooms available for the selected dates',
        'data' => [
            'room_type' => $room['room_type'],
            'available_rooms' => 0
        ]
    ]);
}

$conn->close();
?>
