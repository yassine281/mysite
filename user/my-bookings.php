<?php
include '../api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all bookings
$sql = "SELECT b.*, h.name as hotel_name, h.address, r.room_type 
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.hotel_id 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.user_id = $user_id 
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - HotelBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .bookings-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .booking-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .status-confirmed { background: #4CAF50; color: white; }
        .status-pending { background: #FFA500; color: white; }
        .status-cancelled { background: #f44336; color: white; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.html" class="logo">🏨 HotelBook</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="my-bookings.php" class="active">My Bookings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="bookings-container">
        <h1>My Bookings</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap;">
                        <div style="flex: 1;">
                            <h3><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                            <p>📍 <?php echo htmlspecialchars($booking['address']); ?></p>
                            <p>🛏️ <?php echo htmlspecialchars($booking['room_type']); ?></p>
                            <p><strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?></p>
                            <p><strong>Check-in:</strong> <?php echo $booking['check_in']; ?> | <strong>Check-out:</strong> <?php echo $booking['check_out']; ?></p>
                            <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                            <p><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                        </div>
                        <div style="text-align: right;">
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                            <p style="margin-top: 1rem; font-size: 0.9rem;">
                                Booked on: <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                            </p>
                            <?php if ($booking['status'] === 'confirmed' && $booking['check_in'] > date('Y-m-d')): ?>
                                <button onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)" 
                                        style="margin-top: 1rem; background: #f44336; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;">
                                    Cancel Booking
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <p>You don't have any bookings yet.</p>
                <a href="../index.html" class="btn-submit" style="display: inline-block; text-decoration: none; margin-top: 1rem;">Book Your First Stay</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                // Add cancel booking logic here
                alert('Booking cancellation requested. This feature will be implemented in the backend.');
            }
        }
    </script>
</body>
</html>
