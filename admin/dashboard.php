<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../api/config.php';

// Get statistics
$hotels_count = $conn->query("SELECT COUNT(*) as total FROM hotels")->fetch_assoc()['total'];
$bookings_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status='confirmed'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HotelBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🏨 Admin Panel</a>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage-hotels.php">Hotels</a></li>
                <li><a href="manage-bookings.php">Bookings</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $hotels_count; ?></div>
                <p>Total Hotels</p>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $bookings_count; ?></div>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $users_count; ?></div>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($revenue, 0); ?></div>
                <p>Total Revenue</p>
            </div>
        </div>

        <h2>Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem;">
            <a href="manage-hotels.php" class="btn-submit" style="text-decoration: none; text-align: center;">Manage Hotels</a>
            <a href="manage-bookings.php" class="btn-submit" style="text-decoration: none; text-align: center;">View Bookings</a>
            <a href="users.php" class="btn-submit" style="text-decoration: none; text-align: center;">Manage Users</a>
        </div>
    </div>
</body>
</html>
