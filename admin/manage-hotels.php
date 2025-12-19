<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../api/config.php';

$hotels = $conn->query("SELECT * FROM hotels ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🏨 Admin Panel</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-hotels.php" class="active">Hotels</a></li>
                <li><a href="manage-bookings.php">Bookings</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Manage Hotels</h1>
        
        <table style="width: 100%; background: white; border-collapse: collapse; margin-top: 2rem;">
            <thead>
                <tr style="background: var(--dark-color); color: white;">
                    <th style="padding: 1rem; text-align: left;">ID</th>
                    <th style="padding: 1rem; text-align: left;">Name</th>
                    <th style="padding: 1rem; text-align: left;">City</th>
                    <th style="padding: 1rem; text-align: left;">Rating</th>
                    <th style="padding: 1rem; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($hotel = $hotels->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 1rem;"><?php echo $hotel['hotel_id']; ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($hotel['name']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($hotel['city']); ?></td>
                    <td style="padding: 1rem;">⭐ <?php echo $hotel['rating']; ?></td>
                    <td style="padding: 1rem;">
                        <button style="background: #4CAF50; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;">Edit</button>
                        <button style="background: #f44336; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
