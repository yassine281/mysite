<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Handle both POST (JSON) and GET (URL parameters) requests
$location = isset($input['location']) ? $conn->real_escape_string($input['location']) : 
            (isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '');

$check_in = isset($input['check_in']) ? $conn->real_escape_string($input['check_in']) : 
            (isset($_GET['check_in']) ? $conn->real_escape_string($_GET['check_in']) : '');

$check_out = isset($input['check_out']) ? $conn->real_escape_string($input['check_out']) : 
             (isset($_GET['check_out']) ? $conn->real_escape_string($_GET['check_out']) : '');

$guests = isset($input['guests']) ? intval($input['guests']) : 
          (isset($_GET['guests']) ? intval($_GET['guests']) : 1);

$min_price = isset($input['min_price']) ? floatval($input['min_price']) : 
             (isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0);

$max_price = isset($input['max_price']) ? floatval($input['max_price']) : 
             (isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999);

$rating = isset($input['rating']) ? floatval($input['rating']) : 
          (isset($_GET['rating']) ? floatval($_GET['rating']) : 0);

// Build the query
$query = "SELECT DISTINCT h.*, 
          MIN(r.price) as min_room_price,
          MAX(r.price) as max_room_price,
          COUNT(DISTINCT r.id) as total_room_types
          FROM hotels h
          LEFT JOIN rooms r ON h.id = r.hotel_id";

$conditions = [];
$having_conditions = [];

// Add location filter
if (!empty($location)) {
    $conditions[] = "(h.location LIKE '%$location%' OR h.city LIKE '%$location%' OR h.name LIKE '%$location%')";
}

// Add rating filter
if ($rating > 0) {
    $conditions[] = "h.rating >= $rating";
}

// Add WHERE clause if conditions exist
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Group by hotel
$query .= " GROUP BY h.id";

// Add price range filter (using HAVING because it's an aggregate)
if ($min_price > 0 || $max_price < 999999) {
    $having_conditions[] = "min_room_price >= $min_price";
    $having_conditions[] = "max_room_price <= $max_price";
}

if (!empty($having_conditions)) {
    $query .= " HAVING " . implode(" AND ", $having_conditions);
}

// Add ordering
$query .= " ORDER BY h.rating DESC, min_room_price ASC";

// Execute query
$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database query error: ' . $conn->error
    ]);
    exit;
}

$hotels = [];

while ($row = $result->fetch_assoc()) {
    // Get available rooms for this hotel
    $hotel_id = $row['id'];
    $rooms_query = "SELECT * FROM rooms WHERE hotel_id = $hotel_id";
    
    // Add capacity filter if guests specified
    if ($guests > 0) {
        $rooms_query .= " AND capacity >= $guests";
    }
    
    // Add price filter
    $rooms_query .= " AND price >= $min_price AND price <= $max_price";
    
    $rooms_result = $conn->query($rooms_query);
    
    // Check availability for the selected dates
    $available_rooms = [];
    
    if (!empty($check_in) && !empty($check_out)) {
        while ($room = $rooms_result->fetch_assoc()) {
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
            
            $room['available_count'] = $room['total_rooms'] - $booked;
            
            if ($room['available_count'] > 0) {
                $available_rooms[] = $room;
            }
        }
        
        // Skip hotel if no rooms available for these dates
        if (empty($available_rooms)) {
            continue;
        }
    } else {
        // No dates specified, just show all rooms
        while ($room = $rooms_result->fetch_assoc()) {
            $room['available_count'] = $room['available_rooms'];
            $available_rooms[] = $room;
        }
    }
    
    // Skip hotel if no rooms match criteria
    if (empty($available_rooms)) {
        continue;
    }
    
    // Calculate nights if dates provided
    $nights = 1;
    if (!empty($check_in) && !empty($check_out)) {
        $check_in_date = strtotime($check_in);
        $check_out_date = strtotime($check_out);
        $nights = max(1, ceil(($check_out_date - $check_in_date) / (60 * 60 * 24)));
    }
    
    // Add hotel data
    $hotels[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'location' => $row['location'],
        'city' => $row['city'],
        'address' => $row['address'],
        'rating' => floatval($row['rating']),
        'description' => $row['description'],
        'image' => $row['image'],
        'amenities' => $row['amenities'],
        'phone' => $row['phone'],
        'email' => $row['email'],
        'min_price' => floatval($row['min_room_price']),
        'max_price' => floatval($row['max_room_price']),
        'total_room_types' => intval($row['total_room_types']),
        'available_rooms' => $available_rooms,
        'nights' => $nights
    ];
}

// Response
if (count($hotels) > 0) {
    echo json_encode([
        'success' => true,
        'count' => count($hotels),
        'message' => count($hotels) . ' hotel(s) found',
        'search_params' => [
            'location' => $location,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'rating' => $rating
        ],
        'data' => $hotels
    ]);
} else {
    echo json_encode([
        'success' => true,
        'count' => 0,
        'message' => 'No hotels found matching your criteria',
        'search_params' => [
            'location' => $location,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'rating' => $rating
        ],
        'data' => []
    ]);
}

$conn->close();
?>
