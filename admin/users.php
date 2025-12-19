<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../api/config.php';

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">🏨 Admin Panel</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-hotels.php">Hotels</a></li>
                <li><a href="manage-bookings.php">Bookings</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Registered Users</h1>
        
        <table style="width: 100%; background: white; border-collapse: collapse; margin-top: 2rem;">
            <thead>
                <tr style="background: var(--dark-color); color: white;">
                    <th style="padding: 1rem; text-align: left;">ID</th>
                    <th style="padding: 1rem; text-align: left;">Name</th>
                    <th style="padding: 1rem; text-align: left;">Email</th>
                    <th style="padding: 1rem; text-align: left;">Phone</th>
                    <th style="padding: 1rem; text-align: left;">Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 1rem;"><?php echo $user['user_id']; ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td style="padding: 1rem;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
