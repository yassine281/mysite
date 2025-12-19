<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get hotel ID from request
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hotel_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid hotel ID'
    ]);
    exit;
}

// Get check-in and check-out dates (optional)
$check_in = isset($_GET['check_in']) ? $conn->real_escape_string($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? $conn->real_escape_string($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// Get hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = $hotel_id LIMIT 1";
$hotel_result = $conn->query($hotel_query);

if ($hotel_result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Hotel not found'
    ]);
    exit;
}

$hotel = $hotel_result->fetch_assoc();

// Get all rooms for this hotel
$rooms_query = "SELECT * FROM rooms WHERE hotel_id = $hotel_id ORDER BY price ASC";
$rooms_result = $conn->query($rooms_query);

$rooms = [];

while ($room = $rooms_result->fetch_assoc()) {
    $room_data = [
        'id' => $room['id'],
        'room_type' => $room['room_type'],
        'price' => floatval($room['price']),
        'capacity' => intval($room['capacity']),
        'description' => $room['description'],
        'amenities' => $room['amenities'],
        'total_rooms' => intval($room['total_rooms']),
        'available_rooms' => intval($room['available_rooms'])
    ];
    
    // If dates are provided, check actual availability
    if (!empty($check_in) && !empty($check_out)) {
        // Check for overlapping bookings
        $booking_query = "SELECT COUNT(*) as booked_rooms 
                         FROM bookings 
                         WHERE hotel_id = $hotel_id 
                         AND room_type = '{$room['room_type']}'
                         AND status IN ('confirmed', 'pending')
                         AND (
                             (check_in_date <= '$check_in' AND check_out_date > '$check_in')
                             OR (check_in_date < '$check_out' AND check_out_date >= '$check_out')
                             OR (check_in_date >= '$check_in' AND check_out_date <= '$check_out')
                         )";
        
        $booking_result = $conn->query($booking_query);
        $booking_data = $booking_result->fetch_assoc();
        $booked = intval($booking_data['booked_rooms']);
        
        $room_data['available_for_dates'] = $room['total_rooms'] - $booked;
        
        // Calculate total price for the stay
        $check_in_date = strtotime($check_in);
        $check_out_date = strtotime($check_out);
        $nights = max(1, ceil(($check_out_date - $check_in_date) / (60 * 60 * 24)));
        
        $room_data['nights'] = $nights;
        $room_data['total_price'] = floatval($room['price']) * $nights;
        $room_data['is_available'] = $room_data['available_for_dates'] > 0;
    } else {
        $room_data['available_for_dates'] = intval($room['available_rooms']);
        $room_data['is_available'] = $room['available_rooms'] > 0;
        $room_data['nights'] = 1;
        $room_data['total_price'] = floatval($room['price']);
    }
    
    $rooms[] = $room_data;
}

// Get hotel statistics
$stats_query = "SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
                AVG(CASE WHEN status = 'completed' THEN 5 ELSE NULL END) as avg_guest_rating
                FROM bookings 
                WHERE hotel_id = $hotel_id";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent reviews (if you have a reviews table)
$reviews = [];
$reviews_query = "SELECT r.*, u.name as guest_name, u.email as guest_email
                  FROM reviews r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.hotel_id = $hotel_id
                  ORDER BY r.created_at DESC
                  LIMIT 10";

if ($conn->query("SHOW TABLES LIKE 'reviews'")->num_rows > 0) {
    $reviews_result = $conn->query($reviews_query);
    
    while ($review = $reviews_result->fetch_assoc()) {
        $reviews[] = [
            'id' => $review['id'],
            'guest_name' => $review['guest_name'],
            'rating' => floatval($review['rating']),
            'comment' => $review['comment'],
            'created_at' => $review['created_at']
        ];
    }
}

// Build response
$response = [
    'success' => true,
    'message' => 'Hotel details retrieved successfully',
    'data' => [
        'hotel' => [
            'id' => $hotel['id'],
            'name' => $hotel['name'],
            'location' => $hotel['location'],
            'city' => $hotel['city'],
            'address' => $hotel['address'],
            'rating' => floatval($hotel['rating']),
            'description' => $hotel['description'],
            'image' => $hotel['image'],
            'amenities' => $hotel['amenities'],
            'phone' => $hotel['phone'],
            'email' => $hotel['email'],
            'website' => isset($hotel['website']) ? $hotel['website'] : '',
            'created_at' => $hotel['created_at']
        ],
        'rooms' => $rooms,
        'statistics' => [
            'total_bookings' => intval($stats['total_bookings']),
            'confirmed_bookings' => intval($stats['confirmed_bookings']),
            'total_room_types' => count($rooms),
            'min_price' => count($rooms) > 0 ? min(array_column($rooms, 'price')) : 0,
            'max_price' => count($rooms) > 0 ? max(array_column($rooms, 'price')) : 0
        ],
        'reviews' => $reviews,
        'search_params' => [
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests
        ]
    ]
];

echo json_encode($response);

$conn->close();
?>
