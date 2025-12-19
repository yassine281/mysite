<?php
include 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;

$sql = "SELECT b.*, h.name as hotel_name, h.address, r.room_type 
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.hotel_id 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.booking_id = $booking_id AND b.user_id = {$_SESSION['user_id']}";
$result = $conn->query($sql);
$booking = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - HotelBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">🏨 HotelBook</a>
            <ul class="nav-links">
                <li><a href="user/dashboard.php">Dashboard</a></li>
                <li><a href="api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container" style="max-width: 700px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">✅</div>
        <h2>Booking Confirmed!</h2>
        <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">
            Your booking has been successfully confirmed.
        </p>

        <?php if ($booking): ?>
            <div style="background: var(--light-color); padding: 2rem; border-radius: 10px; text-align: left; margin-bottom: 2rem;">
                <h3>Booking Details</h3>
                <p><strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?></p>
                <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['address']); ?></p>
                <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
                <p><strong>Check-in:</strong> <?php echo $booking['check_in']; ?></p>
                <p><strong>Check-out:</strong> <?php echo $booking['check_out']; ?></p>
                <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                <hr style="margin: 1rem 0;">
                <h3 style="color: var(--primary-color);">Total: $<?php echo number_format($booking['total_price'], 2); ?></h3>
            </div>

            <p style="margin-bottom: 2rem;">
                A confirmation email has been sent to your registered email address.
            </p>

            <a href="user/my-bookings.php" class="btn-submit" style="display: inline-block; text-decoration: none;">
                View My Bookings
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
