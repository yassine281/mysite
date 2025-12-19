<?php
include 'api/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$hotel_id = $_GET['hotel_id'] ?? 0;
$room_id = $_GET['room_id'] ?? 0;
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 1;

// Get hotel and room details
$hotel_sql = "SELECT * FROM hotels WHERE hotel_id = $hotel_id";
$hotel = $conn->query($hotel_sql)->fetch_assoc();

$room_sql = "SELECT * FROM rooms WHERE room_id = $room_id";
$room = $conn->query($room_sql)->fetch_assoc();

// Calculate number of nights
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date1->diff($date2)->days;
$total_price = $room['price_per_night'] * $nights;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking - HotelBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
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

    <div class="form-container" style="max-width: 800px;">
        <h2>Complete Your Booking</h2>
        
        <div style="background: var(--light-color); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
            <p><?php echo htmlspecialchars($room['room_type']); ?></p>
            <p><strong>Check-in:</strong> <?php echo $check_in; ?></p>
            <p><strong>Check-out:</strong> <?php echo $check_out; ?></p>
            <p><strong>Guests:</strong> <?php echo $guests; ?></p>
            <p><strong>Nights:</strong> <?php echo $nights; ?></p>
            <hr style="margin: 1rem 0;">
            <h3 style="color: var(--primary-color);">Total: $<?php echo number_format($total_price, 2); ?></h3>
        </div>

        <form id="bookingForm">
            <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
            <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">

            <div class="form-group">
                <label>Special Requests (Optional)</label>
                <textarea name="special_requests" placeholder="Any special requests?"></textarea>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method" style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 5px;">
                    <option value="pay_at_hotel">Pay at Hotel</option>
                    <option value="credit_card">Credit Card (Coming Soon)</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Confirm Booking</button>
        </form>
    </div>

    <script>
        document.getElementById('bookingForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('api/create-booking.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = 'confirmation.php?booking_id=' + result.booking_id;
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Booking failed. Please try again.');
            }
        });
    </script>
</body>
</html>
