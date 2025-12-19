<?php
include '../api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user's bookings count
$bookings_sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id";
$bookings_count = $conn->query($bookings_sql)->fetch_assoc()['total'];

// Get upcoming bookings
$upcoming_sql = "SELECT b.*, h.name as hotel_name, h.address, r.room_type 
                 FROM bookings b 
                 JOIN hotels h ON b.hotel_id = h.hotel_id 
                 JOIN rooms r ON b.room_id = r.room_id 
                 WHERE b.user_id = $user_id AND b.check_in >= CURDATE() 
                 ORDER BY b.check_in ASC LIMIT 3";
$upcoming_result = $conn->query($upcoming_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - HotelBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .booking-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.html" class="logo">🏨 HotelBook</a>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="my-bookings.php">My Bookings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p>Manage your bookings and explore new destinations</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $bookings_count; ?></div>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $upcoming_result->num_rows; ?></div>
                <p>Upcoming Trips</p>
            </div>
            <div class="stat-card">
                <div class="stat-number">⭐</div>
                <p>Member Status</p>
            </div>
        </div>

        <h2>Upcoming Bookings</h2>
        <?php if ($upcoming_result->num_rows > 0): ?>
            <?php while ($booking = $upcoming_result->fetch_assoc()): ?>
                <div class="booking-card">
                    <h3><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($booking['address']); ?></p>
                    <p>🛏️ <?php echo htmlspecialchars($booking['room_type']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo $booking['check_in']; ?> | <strong>Check-out:</strong> <?php echo $booking['check_out']; ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                    <span style="background: #4CAF50; color: white; padding: 0.3rem 1rem; border-radius: 5px;">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming bookings. <a href="../index.html">Book now!</a></p>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="my-bookings.php" class="btn-submit" style="display: inline-block; text-decoration: none;">View All Bookings</a>
        </div>
    </div>
</body>
</html>
