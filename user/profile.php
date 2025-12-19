<?php
include '../api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$user = $conn->query($sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HotelBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.html" class="logo">🏨 HotelBook</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="my-bookings.php">My Bookings</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container">
        <h2>My Profile</h2>
        
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Member Since</label>
            <input type="text" value="<?php echo date('F Y', strtotime($user['created_at'])); ?>" readonly>
        </div>

        <p style="text-align: center; color: #666; margin-top: 2rem;">
            Profile editing will be available in a future update.
        </p>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="dashboard.php" class="btn-submit" style="display: inline-block; text-decoration: none;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
