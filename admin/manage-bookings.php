<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../api/config.php';

$bookings = $conn->query("SELECT b.*, h.name as hotel_name, u.name as user_name 
                          FROM bookings b 
                          JOIN hotels h ON b.hotel_id = h.hotel_id 
                          JOIN users u ON b.user_id = u.user_id 
                          ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🏨 Admin Panel</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-hotels.php">Hotels</a></li>
                <li><a href="manage-bookings.php" class="active">Bookings</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>All Bookings</h1>
        
        <table style="width: 100%; background: white; border-collapse: collapse; margin-top: 2rem;">
            <thead>
                <tr style="background: var(--dark-color); color: white;">
                    <th style="padding: 1rem; text-align: left;">ID</th>
                    <th style="padding: 1rem; text-align: left;">Guest</th>
                    <th style="padding: 1rem; text-align: left;">Hotel</th>
                    <th style="padding: 1rem; text-align: left;">Check-in</th>
                    <th style="padding: 1rem; text-align: left;">Total</th>
                    <th style="padding: 1rem; text-align: left;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 1rem;">#<?php echo $booking['booking_id']; ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                    <td style="padding: 1rem;"><?php echo $booking['check_in']; ?></td>
                    <td style="padding: 1rem;">$<?php echo number_format($booking['total_price'], 2); ?></td>
                    <td style="padding: 1rem;">
                        <span style="background: #4CAF50; color: white; padding: 0.3rem 0.8rem; border-radius: 5px;">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
